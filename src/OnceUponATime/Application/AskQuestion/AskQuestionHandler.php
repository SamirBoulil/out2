<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AskQuestion;

use OnceUponATime\Application\InvalidExternalUserId;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizzEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AskQuestionHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizzEventStore */
    private $quizzEventStore;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizzEventStore $quizzEventStore
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->quizzEventStore = $quizzEventStore;
    }

    public function handle(AskQuestion $nextQuestion): ?Question
    {
        $user = $this->getUser($nextQuestion);
        $unansweredQuestions = $this->findUnansweredQuestions($user->id());
        if (empty($unansweredQuestions)) {
            return null;
        }

        $question = $this->pickRandomQuestion($unansweredQuestions);

        $this->quizzEventStore->add(new QuestionAsked($user->id(), $question->id()));

        return $question;
    }

    private function getUser(AskQuestion $nextQuestion): User
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($nextQuestion->externalUserId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($nextQuestion->externalUserId);
        }

        return $user;
    }

    private function findUnansweredQuestions(UserId $userId): array
    {
        $unansweredQuestions = [];
        $questions = $this->questionRepository->all();
        foreach ($questions as $question) {
            if (!$this->isQuestionAlreadyAnsweredByUser($question, $userId)) {
                $unansweredQuestions[] = $question;
            }
        }

        return $unansweredQuestions;
    }

    private function isQuestionAlreadyAnsweredByUser(Question $question, UserId $userId): bool
    {
        $answeredQuestions = $this->quizzEventStore->byUser($userId);
        $answeredQuestionIds = array_map(function (QuestionAnswered $question) {
            return (string) $question->questionId();
        }, $answeredQuestions);

        return in_array((string) $question->id(), $answeredQuestionIds);
    }

    private function pickRandomQuestion(array $questions): Question
    {
        return $questions[array_rand($questions)];
    }
}
