<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User
{
    /** @var UserId */
    private $id;

    /** @var ExternalUserId */
    private $externalUserId;

    /** @var Name */
    private $name;

    public static function register(UserId $userId, ExternalUserId $slackUserId, Name $name): User
    {
        $user = new self();
        $user->id = $userId;
        $user->externalUserId = $slackUserId;
        $user->name = $name;

        return $user;
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function externalUserId(): ExternalUserId
    {
        return $this->externalUserId;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
