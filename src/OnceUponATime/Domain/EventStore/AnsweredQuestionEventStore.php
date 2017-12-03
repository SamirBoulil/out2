<?php

namespace OnceUponATime\Domain\EventStore;

use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\UserId;

interface AnsweredQuestionEventStore
{
    public function add(QuestionAnswered $questionAnswered): void;

    public function byUser(UserId $userId): array;
}
