<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Domain\Event\UserRegistered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForNewUser implements UserRegisteredNotify
{
    /** @var AskQuestionHandler */
    private $nextQuestionHandler;

    public function __construct(AskQuestionHandler $nextQuestionHandler)
    {
        $this->nextQuestionHandler = $nextQuestionHandler;
    }

    public function userRegistered(UserRegistered $event): void
    {
        $nextQuestion = new AskQuestion();
        $nextQuestion->externalUserId = (string) $event->externalUserId();
        $this->nextQuestionHandler->handle($nextQuestion);
    }
}
