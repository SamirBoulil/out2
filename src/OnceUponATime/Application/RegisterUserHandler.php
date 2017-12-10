<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Entity\UserRegistered;
use OnceUponATime\Domain\Repository\UserRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterUserHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var UserRegisteredNotify */
    private $notify;

    public function __construct(UserRepository $userRepository, UserRegisteredNotify $notify)
    {
        $this->userRepository = $userRepository;
        $this->notify = $notify;
    }

    public function register(RegisterUser $registerUser): void
    {
        $externalUserId = ExternalUserId::fromString($registerUser->externalUserId);
        $name = Name::fromString($registerUser->name);
        $userId = UserId::fromString((string) Uuid::uuid4());
        $user = User::register($userId, $externalUserId, $name);
        $this->userRepository->add($user);
        $this->notify->userRegistered(new UserRegistered($userId, $externalUserId, $name));
    }
}
