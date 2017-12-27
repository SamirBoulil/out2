<?php

declare(strict_types=1);

namespace OnceUponATime\Application\RegisterUser;

use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Event\UserRegistered;
use OnceUponATime\Domain\Repository\UserRepository;

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
        $user = $this->createNewUser($registerUser);
        $this->userRepository->add($user);
        $this->notify->userRegistered(new UserRegistered($user->id(), $user->name()));
    }

    private function createNewUser(RegisterUser $registerUser): User
    {
        $userId = $this->userRepository->nextIdentity();
        $user = User::register(
            $userId,
            ExternalUserId::fromString($registerUser->externalUserId),
            Name::fromString($registerUser->name)
        );

        return $user;
    }
}
