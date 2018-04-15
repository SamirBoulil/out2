<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Persistence\Common\AbstractUserRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryUserRepository extends AbstractUserRepository
{
    /** @var array */
    private $users = [];

    public function add(User $user): void
    {
        $this->users[(string) $user->id()] = $user;
    }

    public function all(): array
    {
        return array_values($this->users);
    }
}
