<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\File;

use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Infrastructure\Persistence\Common\AbstractUserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileBasedUserRepository extends AbstractUserRepository
{
    private const USER_ID = 'id';
    private const EXTERNAL_USER_ID = 'external_user_id';
    private const USER_NAME = 'name';

    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function add(User $user): void
    {
        $users = $this->all();
        $users[] = $user;
        $normalizedUsers = $this->normalizeUsers($users);

        $fp = fopen($this->filePath, 'w');
        fwrite($fp, json_encode($normalizedUsers));
        fclose($fp);
    }

    public function all(): array
    {
        $fileContent = file_get_contents($this->filePath);
        if (empty($fileContent)) {
            return [];
        }

        $users = $this->denormalizeUsers(json_decode($fileContent, true));

        return $users;
    }

    /**
     * @param User[] $users
     *
     * @return array
     */
    private function normalizeUsers(array $users): array
    {
        $normalizedUsers = [];
        foreach ($users as $user) {
            $normalizedUsers[] = [
                self::USER_ID          => (string) $user->id(),
                self::EXTERNAL_USER_ID => (string) $user->externalUserId(),
                self::USER_NAME        => (string) $user->name(),
            ];
        }

        return $normalizedUsers;
    }

    /**
     * @param array $normalizedUsers
     *
     * @return User[]
     */
    private function denormalizeUsers(array $normalizedUsers): array
    {
        $users = [];
        foreach ($normalizedUsers as $normalizedUser) {
            $users[] = User::register(
                UserId::fromString($normalizedUser[self::USER_ID]),
                ExternalUserId::fromString($normalizedUser[self::EXTERNAL_USER_ID]),
                Name::fromString($normalizedUser[self::USER_NAME])
            );
        }

        return $users;
    }
}
