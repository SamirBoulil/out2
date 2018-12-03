<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\File;

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
class FileBasedQuizEventStore implements QuizEventStore
{
    const USER_ID = 'user_id';
    const TYPE = 'type';
    const QUESTION_ID = 'question_id';
    const IS_CORRECT = 'is_correct';
    const QUESTION_ASKED = 'question_asked';
    const QUESTION_ANSWERED = 'question_answered';
    const QUIZ_COMPLETED = 'quiz_completed';

    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function add(quizEvent $event): void
    {
        $events = $this->all();
        $events[] = $event;
        $normalizedEvents = $this->normalizeEvents($events);
        $fp = fopen($this->filePath, 'w');
        fwrite($fp, json_encode($normalizedEvents));
        fclose($fp);
    }

    public function byUser(UserId $userId): array
    {
        return array_filter($this->all(), function (QuizEvent $event) use ($userId) {
            return $event->userId()->equals($userId);
        });
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

    public function isQuizCompleted(UserId $userId): bool
    {
        $userEvents = $this->byUser($userId);

        return end($userEvents) instanceof QuizCompleted;
    }

    public function all(): array
    {
        $fileContent = file_get_contents($this->filePath);
        if (empty($fileContent)) {
            return [];
        }

        return $this->denormalizeEvents(json_decode($fileContent, true));
    }

    private function hasCompletedQuiz(array $eventsForUser): bool
    {
        return end($eventsForUser) instanceof QuizCompleted;
    }

    /**
     * @param array $events
     *
     * @return QuizEvent[]
     */
    private function denormalizeEvents(array $events): array
    {
        return array_map(function (array $event) {
            switch ($event[self::TYPE]) {
                case self::QUESTION_ASKED:
                    return new QuestionAsked(
                        UserId::fromString($event[self::USER_ID]),
                        QuestionId::fromString($event[self::QUESTION_ID])
                    );
                case self::QUESTION_ANSWERED:
                    return new QuestionAnswered(
                        UserId::fromString($event[self::USER_ID]),
                        QuestionId::fromString($event[self::QUESTION_ID]),
                        $event[self::IS_CORRECT]
                    );
                case self::QUIZ_COMPLETED:
                    return new QuizCompleted(
                        UserId::fromString($event[self::USER_ID])
                    );
                default:
                    throw new \RuntimeException(sprintf('Impossible to load event: %s', json_encode($event)));
            }
        }, $events);
    }

    private function normalizeEvents($events): array
    {
        return array_map(function (QuizEvent $event) {
            if ($event instanceof QuestionAsked) {
                return [
                    self::USER_ID     => (string) $event->userId(),
                    self::QUESTION_ID => (string) $event->questionId(),
                    self::TYPE        => self::QUESTION_ASKED,
                ];
            }

            if ($event instanceof QuestionAnswered) {
                return [
                    self::USER_ID     => (string) $event->userId(),
                    self::QUESTION_ID => (string) $event->questionId(),
                    self::IS_CORRECT  => $event->isCorrect(),
                    self::TYPE        => self::QUESTION_ANSWERED,
                ];
            }

            if ($event instanceof QuizCompleted) {
                return [
                    self::USER_ID => (string) $event->userId(),
                    self::TYPE    => self::QUIZ_COMPLETED,
                ];
            }

            throw new \RuntimeException(sprintf('Impossible to normalize the event of class %s', get_class($event)));
        }, $events);
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
