<?php

declare(strict_types=1);

namespace OnceUponATime\Application\ShowQuestion;

use OnceUponATime\Application\InvalidExternalUserId;
use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowQuestionHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizEventStore $quizEventStore
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->quizEventStore = $quizEventStore;
    }

    public function handle($showQuestion): ?Question
    {
        $user = $this->getUser($showQuestion);
        if ($this->userHasCompletedQuiz($user)) {
            throw NoQuestionToAnswer::fromString((string) $user->id());
        }

        $question = $this->getCurrentQuestion($user);

        return $question;
    }

    private function getUser(ShowQuestion $showQuestion): User
    {
        $user = $this->userRepository->byId(UserId::fromString($showQuestion->userId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($showQuestion->userId);
        }

        return $user;
    }

    private function userHasCompletedQuiz(User $user): bool
    {
        return $this->quizEventStore->isQuizCompleted($user->id());
    }

    private function getCurrentQuestion(User $user): Question
    {
        $questionId = $this->quizEventStore->questionToAnswerForUser($user->id());
        if (null === $questionId) {
            throw NoQuestionToAnswer::fromString((string) $user->id());
        }

        return $this->questionRepository->byId($questionId);
    }
}
