<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEvent;
use OnceUponATime\Domain\Event\QuizEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizEventStore implements QuizEventStore
{
    /** @var QuizEvent[] */
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
        $eventsForUser = $this->byUser($userId);
        if ($this->hasCompletedQuiz($eventsForUser)) {
            return null;
        }

        foreach (array_reverse($eventsForUser) as $quizEventForUser) {
            if ($quizEventForUser instanceof QuestionAsked) {
                return $quizEventForUser->questionId();
            }
        }

        throw new \LogicException(sprintf('User "%s" has no question to answer.', (string) $userId));
    }

    public function answersCount(UserId $userId): ?int
    {
        $eventsForUser = $this->byUser($userId);
        if ($this->hasCompletedQuiz($eventsForUser)) {
            return null;
        }

        $answersCount = 0;
        foreach (array_reverse($eventsForUser) as $quizEvent) {
            if ($this->isNewQuestionEvent($quizEvent)) {
                break;
            }
            $answersCount++;
        }

        return $answersCount;
    }

    public function answersCountAll(UserId $userId): array
    {
        $quizEvents = $this->byUser($userId);

        $answerCountAll = [];
        $answerCount = 0;
        foreach ($quizEvents as $quizEvent) {
            if ($this->isNewQuestionEvent($quizEvent)) {
                $answerCount = 0;
                continue;
            }
            if ($this->isQuestionAnswered($quizEvent)) {
                $answerCount++;
            }
            if ($this->isQuestionCorrectlyAnswered($quizEvent)) {
                $answerCountAll[(string) $quizEvent->questionId()] = $answerCount;
            }
        }

        return $answerCountAll;
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

    private function hasCompletedQuiz(array $eventsForUser): bool
    {
        return end($eventsForUser) instanceof QuizCompleted;
    }

    private function isNewQuestionEvent($quizEvent): bool
    {
        return $quizEvent instanceof QuestionAsked;
    }

    private function isQuestionAnswered($quizEvent): bool
    {
        return $quizEvent instanceof QuestionAnswered;
    }

    private function isQuestionCorrectlyAnswered($quizEvent): bool
    {
        return $quizEvent instanceof QuestionAnswered && $quizEvent->isCorrect();
    }
}
