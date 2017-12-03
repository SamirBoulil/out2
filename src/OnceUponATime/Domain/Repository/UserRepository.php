<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Repository;

use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserRepository
{
    public function add(User $user): void;

    public function byId(UserId $userId): ?User;

    public function all(): array;

    public function nextIdentity(): UserId;

    public function byExternalId(ExternalUserId $externalUserId): ?User;
}
