<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Application;

use OnceUponATime\Application\QuestionAnsweredNotify;
use OnceUponATime\Application\UserRegisteredNotify;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\UserRegistered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestEventSubscriber implements QuestionAnsweredNotify, UserRegisteredNotify
{
    /** @var bool */
    public $isQuestionAnswered = false;

    /** @var bool */
    public $isUserRegistered = false;

    public function questionAnswered(QuestionAnswered $event): void
    {
        $this->isQuestionAnswered = true;
    }

    public function userRegistered(UserRegistered $event): void
    {
        $this->isUserRegistered = true;
    }
}
