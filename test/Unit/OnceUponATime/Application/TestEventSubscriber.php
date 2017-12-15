<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Application;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\NoQuestionsLeftNotify;
use OnceUponATime\Application\AskQuestion\QuestionAskedNotify;
use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Domain\Event\NoQuestionsLeft;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\UserRegistered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestEventSubscriber implements
    QuestionAnsweredNotify,
    UserRegisteredNotify,
    QuestionAskedNotify,
    NoQuestionsLeftNotify
{
    /** @var bool */
    public $isQuestionAnswered = false;

    /** @var bool */
    public $isUserRegistered = false;

    /** @var bool */
    public $isQuestionAsked = false;

    /** @var bool */
    public $isNoQuestionLeft = false;

    public function questionAnswered(QuestionAnswered $event): void
    {
        $this->isQuestionAnswered = true;
    }

    public function userRegistered(UserRegistered $event): void
    {
        $this->isUserRegistered = true;
    }

    public function questionAsked(QuestionAsked $event): void
    {
        $this->isQuestionAsked = true;
    }

    public function noQuestionsLeft(NoQuestionsLeft $event): void
    {
        $this->isNoQuestionLeft = true;
    }
}
