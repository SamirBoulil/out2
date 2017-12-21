<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AskQuestion\QuizCompletedNotify;
use OnceUponATime\Domain\Event\QuizCompleted;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuizCompletedNotifyMany implements QuizCompletedNotify
{
    /** @var QuizCompletedNotify[] */
    private $notifiers;

    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }
    public function quizCompleted(QuizCompleted $event): void
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->quizCompleted($event);
        }
    }
}
