<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

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

    /** @var ExternalUserId */
    private $externalUserId;

    public function __construct(UserId $userId, ExternalUserId $externalUserId, Name $name)
    {
        $this->userId = $userId;
        $this->externalUserId = $externalUserId;
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

    public function externalUserId(): ExternalUserId
    {
        return $this->externalUserId;
    }
}
