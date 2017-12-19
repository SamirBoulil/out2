<?php

declare(strict_types=1);

namespace OnceUponATime\Application\ShowClue;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowClueHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var QuestionAnsweredNotify */
    private $notify;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizEventStore $quizEventStore,
        QuestionAnsweredNotify $notify
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->quizEventStore = $quizEventStore;
        $this->notify = $notify;
    }

    public function handle(ShowClue $showClue)
    {
        $user = $this->getUser($showClue);
        $question = $this->getQuestionToAnswer($user);
        $answersCount = $this->answersCount($user);

        return $this->getClue($answersCount, $question);
    }

    /**
     * @throws InvalidUserId
     */
    private function getUser(ShowClue $showClue): User
    {
        $userId = UserId::fromString($showClue->userId);
        $user = $this->userRepository->byId($userId);
        if (null === $user) {
            throw InvalidUserId::fromString($showClue->userId);
        }

        return $user;
    }

    private function getQuestionToAnswer(User $user): Question
    {
        $questionId = $this->quizEventStore->questionToAnswerForUser($user->id());
        if (null == $questionId) {
            throw NoQuestionToAnswer::fromString((string) $user->id());
        }

        return $this->questionRepository->byId($questionId);
    }

    private function answersCount(User $user): int
    {
        return $this->quizEventStore->answersCount($user->id());
    }

    private function getClue($guessesCount, $question): Clue
    {
        if (0 === $guessesCount) {
            return $question->clue1();
        }

        return $question->clue2();
    }
}
