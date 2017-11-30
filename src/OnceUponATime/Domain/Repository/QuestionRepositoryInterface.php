<?php

namespace OnceUponATime\Domain\Repository;

use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\UserId;

interface QuestionRepositoryInterface
{
    public function add(Question $user): void;

    public function byId(QuestionId $userId): Question;

    public function all(): array;

    public function nextIdentity(): QuestionId;
}
