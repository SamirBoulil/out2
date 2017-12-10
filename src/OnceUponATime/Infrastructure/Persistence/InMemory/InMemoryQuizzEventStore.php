<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizzEvent;
use OnceUponATime\Domain\Event\QuizzEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizzEventStore implements QuizzEventStore
{
    /** @var \OnceUponATime\Domain\Event\QuestionAnswered[] */
    private $events = [];

    public function add(QuizzEvent $event): void
    {
        $this->events[] = $event;
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

    public function all(): array
    {
        return $this->events;
    }

    public function currentQuestionForUser(UserId $userId): QuestionId
    {
        $eventsForUser = $this->byUser($userId);

        foreach (array_reverse($eventsForUser) as $questionAnswered) {
            if ($questionAnswered instanceof QuestionAsked) {
                return $questionAnswered->questionId();
            }
        }

        throw new \LogicException(sprintf('User "%s" has no question to answer.', (string) $userId));
    }
}
