<?php

namespace OnceUponATime\Domain\EventStore;

use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\QuizzEvent;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Entity\UserRegistered;

/**
 * TODO: Not sure this interface should be in domain ? (but repositories are so...)
 */
interface QuizzEventStore
{
    public function add(QuizzEvent $questionAnswered): void;

    public function byUser(UserId $userId): array;

    public function currentQuestionForUser(UserId $userId): QuestionId;
}
