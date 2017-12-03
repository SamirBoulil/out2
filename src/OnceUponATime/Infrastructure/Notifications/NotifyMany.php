<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\Notify;
use OnceUponATime\Domain\Entity\QuestionAnswered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotifyMany implements Notify
{
    /**
     * @var Notify[]
     */
    private $notifiers;

    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        foreach ($this->notifiers as $notify) {
            $notify->questionAnswered($event);
        }
    }
}
