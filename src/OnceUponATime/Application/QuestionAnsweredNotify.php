<?php

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\QuestionAnswered;

interface QuestionAnsweredNotify
{
    public function questionAnswered(QuestionAnswered $event): void;
}
