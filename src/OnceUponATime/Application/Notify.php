<?php

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\QuestionAnswered;

interface Notify
{
    public function questionAnswered(QuestionAnswered $event): void;
}
