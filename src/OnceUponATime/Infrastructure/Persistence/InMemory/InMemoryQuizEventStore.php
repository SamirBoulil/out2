<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\NoQuestionsLeft;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizEvent;
use OnceUponATime\Domain\Event\QuizEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizEventStore implements QuizEventStore
{
    /** @var QuestionAnswered[] */
    private $events = [];

    public function add(quizEvent $event): void
    {
        $this->events[] = $event;
    }

    public function all(): array
    {
        return $this->events;
    }

    public function byUser(UserId $userId): array
    {
        $events = [];
        foreach ($this->events as $quizEvent) {
            if ($quizEvent->userId()->equals($userId)) {
                $events[] = $quizEvent;
            }
        }

        return $events;
    }

    public function questionToAnswerForUser(UserId $userId): ?QuestionId
    {
        $quizEventsForUser = $this->byUser($userId);
        if (end($quizEventsForUser) instanceof NoQuestionsLeft) {
            return null;
        }

        foreach (array_reverse($quizEventsForUser) as $quizEventForUser) {
            if ($quizEventForUser instanceof QuestionAsked) {
                return $quizEventForUser->questionId();
            }
        }

        throw new \LogicException(sprintf('User "%s" has no question to answer.', (string) $userId));
    }

    public function guessesCountForCurrentQuestionAndUser(UserId $userId): int
    {
        $eventsForUser = $this->byUser($userId);
        $guesses = [];
        foreach (array_reverse($eventsForUser) as $quizEvent) {
            if ($quizEvent instanceof QuestionAsked) {
                break;
            }
            $guesses[] = $quizEvent;
        }

        return \count($guesses);
    }

    public function correctlyAnsweredQuestionsByUser(UserId $userId): array
    {
        $correctlyAnsweredQuestions = [];
        $userEvents = $this->byUser($userId);
        foreach ($userEvents as $answeredQuestion) {
            if ($answeredQuestion instanceof QuestionAnswered &&
                $answeredQuestion->isCorrect()
            ) {
                $correctlyAnsweredQuestions[] = $answeredQuestion->questionId();
            }
        }

        return $correctlyAnsweredQuestions;
    }
}
