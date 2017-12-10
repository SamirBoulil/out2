<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\UserId;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserRegistered
{
    /** @var UserId */
    private $userId;

    /** @var Name */
    private $name;

    public function __construct(UserId $userId, Name $name)
    {
        $this->userId = $userId;
        $this->name = $name;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
