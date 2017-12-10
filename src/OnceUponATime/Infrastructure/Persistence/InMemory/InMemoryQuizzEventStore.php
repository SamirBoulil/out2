<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionAsked;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\QuizzEvent;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\EventStore\QuizzEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizzEventStore implements QuizzEventStore
{
    /** @var QuestionAnswered[] */
    private $questionsAnswered = [];

    public function add(QuizzEvent $questionAnswered): void
    {
        $this->questionsAnswered[] = $questionAnswered;
    }

    public function byUser(UserId $userId): array
    {
        $questionsAnsweredForUser = [];
        foreach ($this->questionsAnswered as $questionAnswered) {
            if ($questionAnswered->userId()->equals($userId)) {
                $questionsAnsweredForUser[] = $questionAnswered;
            }
        }

        return $questionsAnsweredForUser;
    }

    public function all(): array
    {
        return $this->questionsAnswered;
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
