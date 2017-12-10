<?php

declare(strict_types=1);

namespace OnceUponATime\Application\ShowClue;

use Assert\AssertionFailedException;
use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\InvalidUserId;
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

    public function handle(ShowClue $askClue)
    {
        $user = $this->getUser($askClue);
        $question = $this->getQuestionToAnswer($user);
        $guessesCount = $this->guessesCount($user);
        $this->checkGuessesCount($guessesCount, $question, $user);

        return $this->getClue($guessesCount, $question);
    }

    /**
     * @throws InvalidUserId
     */
    private function getUser(ShowClue $askClue): User
    {
        $userId = UserId::fromString($askClue->userId);
        $user = $this->userRepository->byId($userId);
        if (null === $user) {
            throw InvalidUserId::fromString($askClue->userId);
        }

        return $user;
    }

    private function getQuestionToAnswer(User $user): Question
    {
        $questionId = $this->quizEventStore->questionToAnswerForUser($user->id());

        return $this->questionRepository->byId($questionId);
    }

    private function guessesCount(User $user): int
    {
        return $this->quizEventStore->guessesCountForCurrentQuestionAndUser($user->id());
    }

    private function getClue($guessesCount, $question): Clue
    {
        if (0 === $guessesCount) {
            return $question->clue1();
        }

        return $question->clue2();
    }

    /**
     * @throws \LogicException
     */
    private function checkGuessesCount($guessesCount, $question, $user): void
    {
        if (2 < $guessesCount) {
            throw new \LogicException(
                sprintf(
                    'There is no clue available for the question "%s" and user "%s"',
                    $question->id(),
                    $user->id()
                )
            );
        }
    }
}
