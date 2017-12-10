<?php

namespace OnceUponATime\Domain\EventStore;

use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\UserId;

/**
 * TODO: Not sure this interface should be in domain ? (but repositories are so...)
 */
interface QuestionsAnsweredEventStore
{
    public function add(QuestionAnswered $questionAnswered): void;

    public function byUser(UserId $userId): array;
}
