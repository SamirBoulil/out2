<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AskQuestion\QuestionAskedNotify;
use OnceUponATime\Domain\Event\QuestionAsked;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionAskedNotifyMany implements QuestionAskedNotify
{
    /** @var QuestionAskedNotify[] */
    private $notifiers;

    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }

    public function questionAsked(QuestionAsked $event): void
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->questionAsked($event);
        }
    }
}
