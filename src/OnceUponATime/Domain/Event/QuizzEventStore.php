<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;

/**
 * TODO: Not sure this interface should be in domain ? (but repositories are so...)
 */
interface QuizzEventStore
{
    public function add(QuizzEvent $event): void;

    public function byUser(UserId $userId): array;

    public function currentQuestionForUser(UserId $userId): QuestionId;
}
