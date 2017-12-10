<?php

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Event\QuestionAnswered;

interface QuestionAnsweredNotify
{
    public function questionAnswered(QuestionAnswered $event): void;
}
