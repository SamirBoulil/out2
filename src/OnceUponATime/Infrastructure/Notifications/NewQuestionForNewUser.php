<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\NextQuestion;
use OnceUponATime\Application\NextQuestionHandler;
use OnceUponATime\Application\UserRegisteredNotify;
use OnceUponATime\Domain\Entity\UserRegistered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForNewUser implements UserRegisteredNotify
{
    /** @var NextQuestionHandler */
    private $nextQuestionHandler;

    public function __construct(NextQuestionHandler $nextQuestionHandler)
    {
        $this->nextQuestionHandler = $nextQuestionHandler;
    }

    public function userRegistered(UserRegistered $event): void
    {
        $nextQuestion = new NextQuestion();
        $nextQuestion->externalUserId = (string) $event->externalUserId();
        $this->nextQuestionHandler->handle($nextQuestion);
    }
}
