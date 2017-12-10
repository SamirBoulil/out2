<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\RegisterUser\UserRegisteredNotify;
use OnceUponATime\Domain\Event\UserRegistered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRegisteredNotifyMany implements UserRegisteredNotify
{
    /** @var UserRegisteredNotify[] */
    private $notifiers;

    public function __construct(array $notifiers)
    {
        $this->notifiers = $notifiers;
    }

    public function userRegistered(UserRegistered $event): void
    {
        foreach ($this->notifiers as $notify) {
            $notify->userRegistered($event);
        }
    }
}
