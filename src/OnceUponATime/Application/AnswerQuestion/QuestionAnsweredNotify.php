<?php

namespace OnceUponATime\Application\AnswerQuestion;

use OnceUponATime\Domain\Event\QuestionAnswered;

interface QuestionAnsweredNotify
{
    public function questionAnswered(QuestionAnswered $event): void;
}
