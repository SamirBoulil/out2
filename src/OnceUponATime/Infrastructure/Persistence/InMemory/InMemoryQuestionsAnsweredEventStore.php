<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\EventStore\QuestionsAnsweredEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuestionsAnsweredEventStore implements QuestionsAnsweredEventStore
{
    /** @var QuestionAnswered[] */
    private $questionsAnswered = [];

    public function add(QuestionAnswered $questionAnswered): void
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
}
