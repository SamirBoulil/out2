<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Repository\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array */
    private $users = [];

    public function add(User $user): void
    {
        $this->users[(string) $user->id()] = $user;
    }

    public function byId(UserId $userId): User
    {
        return $this->users[(string) $userId];
    }

    public function all(): array
    {
        return array_values($this->users);
    }

    public function nextIdentity(): UserId
    {
        return UserId::fromString((string) Uuid::uuid4());
    }
}
