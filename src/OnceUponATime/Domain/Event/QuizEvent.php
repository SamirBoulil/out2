<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\User\UserId;

interface QuizEvent
{
    public function userId(): UserId;
}
