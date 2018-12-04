<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\Common;

use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Repository\UserRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractUserRepository implements UserRepository
{
    abstract public function add(User $user): void;

    abstract public function all(): array;

    public function byId(UserId $userId): ?User
    {
        foreach ($this->all() as $id => $user) {
            /** @var User $user */
            if ($user->id()->equals($userId)) {
                return $user;
            }
        }

        return null;
    }

    public function byExternalId(ExternalUserId $externalUserId): ?User
    {
        foreach ($this->all() as $id => $user) {
            /** @var User $user */
            if ($user->externalUserId()->equals($externalUserId)) {
                return $user;
            }
        }

        return null;
    }

    public function nextIdentity(): UserId
    {
        return UserId::fromString((string) Uuid::uuid4());
    }
}
