<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizEvent;
use OnceUponATime\Domain\Event\QuizEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizEventStore implements QuizEventStore
{
    /** @var \OnceUponATime\Domain\Event\QuestionAnswered[] */
    private $events = [];

    public function add(quizEvent $event): void
    {
        $this->events[] = $event;
    }

    public function all(): array
    {
        return $this->events;
    }

    public function questionToAnswerForUser(UserId $userId): QuestionId
    {
        $eventsForUser = $this->byUser($userId);

        foreach (array_reverse($eventsForUser) as $questionAnswered) {
            if ($questionAnswered instanceof QuestionAsked) {
                return $questionAnswered->questionId();
            }
        }

        throw new \LogicException(sprintf('User "%s" has no question to answer.', (string) $userId));
    }

    public function guessesCountForCurrentQuestionAndUser(UserId $userId): int
    {
        $eventsForUser = $this->byUser($userId);
        $guesses = [];

        foreach (array_reverse($eventsForUser) as $questionAnswered) {
            if ($questionAnswered instanceof QuestionAsked) {
                break;
            }
            $guesses[] = $questionAnswered;
        }

        return \count($guesses);
    }

    public function byUser(UserId $userId): array
    {
        $events = [];
        foreach ($this->events as $questionAnswered) {
            if ($questionAnswered->userId()->equals($userId)) {
                $events[] = $questionAnswered;
            }
        }

        return $events;
    }
}
