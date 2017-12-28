<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Domain\Event\UserRegistered;

/**
 * TODO: Would definitely move into application.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForNewUser implements UserRegisteredNotify
{
    /** @var AskQuestionHandler */
    private $askQuestionHandler;

    public function __construct(AskQuestionHandler $askQuestionHandler)
    {
        $this->askQuestionHandler = $askQuestionHandler;
    }

    public function userRegistered(UserRegistered $event): void
    {
        $this->askNewQuestion($event);
    }

    private function askNewQuestion(UserRegistered $event): void
    {
        $askQuestion = new AskQuestion();
        $askQuestion->userId = (string) $event->userId();
        $this->askQuestionHandler->handle($askQuestion);
    }
}
