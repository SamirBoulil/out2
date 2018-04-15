<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence;

use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Persistence\File\FileBasedUserRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRepositoryTest extends TestCase
{
    public function getUserRepositories()
    {
        return [
            'in memory repository' => [new InMemoryUserRepository()],
            'file based repository' => [new FileBasedUserRepository(tempnam(sys_get_temp_dir(), 'users'))],
        ];
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_generates_the_next_user_id(UserRepository $userRepository)
    {
        $userId = $userRepository->nextIdentity();
        $this->assertInstanceOf(UserId::class, $userId);
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_returns_an_empty_array_when_there_is_no_user_registered(UserRepository $userRepository)
    {
        $this->assertSame([], $userRepository->all());
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_adds_a_user_to_the_repository_and_returns_it(UserRepository $userRepository)
    {
        $userId = UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $user = User::register($userId, ExternalUserId::fromString('<@U041UN081>'), Name::fromString('Samir Boulil'));

        $userRepository->add($user);

        $this->assertSameUser($user, $userRepository->byId($userId));
        $this->assertSameUsers([$user], $userRepository->all());
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_adds_a_user_with_id_generated_using_the_next_identity_and_returns_it(
        UserRepository $userRepository
    ) {
        $userId = $userRepository->nextIdentity();
        $externalUserId = ExternalUserId::fromString('<@U041UN081>');
        $user = User::register($userId, $externalUserId, Name::fromString('Samir Boulil'));

        $userRepository->add($user);

        $this->assertSameUser($user, $userRepository->byId($userId));
        $this->assertSameUser($user, $userRepository->byExternalId($externalUserId));
        $this->assertSameUsers([$user], $userRepository->all());
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_adds_multiple_users_to_the_repository(UserRepository $userRepository)
    {
        $userId1 = $userRepository->nextIdentity();
        $user1 = User::register($userId1, ExternalUserId::fromString('<@U041UN081>'), Name::fromString('Samir Boulil'));
        $userId2 = $userRepository->nextIdentity();
        $user2 = User::register($userId2, ExternalUserId::fromString('<@U042UN082>'), Name::fromString('User 2'));

        $userRepository->add($user1);
        $userRepository->add($user2);

        $this->assertSameUser($user1, $userRepository->byId($userId1));
        $this->assertSameUser($user2, $userRepository->byId($userId2));
        $this->assertSameUsers([$user1, $user2], $userRepository->all());
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_returns_null_if_the_user_id_does_not_exists(UserRepository $userRepository)
    {
        $wrongUserId = UserId::fromString('00000000-0000-0000-0000-000000000000');
        $this->assertNull($userRepository->byId($wrongUserId));
    }

    /**
     * @test
     * @dataProvider getUserRepositories
     */
    public function it_returns_null_if_the_external_id_does_not_exists(UserRepository $userRepository)
    {
        $wrongExternalId = ExternalUserId::fromString('00000000-0000-0000-0000-000000000000');
        $this->assertNull($userRepository->byExternalId($wrongExternalId));
    }

    protected function assertSameUsers(array $expectedUsers, array $allUsers): void
    {
        $this->assertCount(\count($expectedUsers), $allUsers);
        foreach ($expectedUsers as $i => $expectedUser) {
            $this->assertSameUser($expectedUser, $allUsers[$i]);
        }
    }

    private function assertSameUser(User $user, User $byId)
    {
        $user->id()->equals($byId->id());
        $user->externalUserId()->equals($byId->externalUserId());
        $this->assertSame((string) $user->name(), (string) $byId->name());
    }
}
