<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Repository;

use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;

interface QuestionRepository
{
    public function add(Question $user): void;

    public function byId(QuestionId $questionId): ?Question;

    public function all(): array;

    public function nextIdentity(): QuestionId;
}
