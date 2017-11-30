<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\SlackUserId;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryUserRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_the_next_user_id()
    {
        $inMemoryUserRepository = new InMemoryUserRepository();
        $userId = $inMemoryUserRepository->nextIdentity();
        $this->assertInstanceOf(UserId::class, $userId);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_user_registered()
    {
        $inMemoryUserRepository = new InMemoryUserRepository();
        $this->assertSame([], $inMemoryUserRepository->all());
    }
    /**
     * @test
     */
    public function it_adds_a_user_to_the_repository_and_returns_it()
    {
        $userId = UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $user = User::register($userId, SlackUserId::fromString('<@U041UN081>'), Name::fromString('Samir Boulil'));
        $inMemoryUserRepository = new InMemoryUserRepository();
        $inMemoryUserRepository->add($user);
        $this->assertSame($user, $inMemoryUserRepository->byId($userId));
        $this->assertSame([$user], $inMemoryUserRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_a_user_generated_using_the_next_identity()
    {
        $inMemoryUserRepository = new InMemoryUserRepository();
        $userId = $inMemoryUserRepository->nextIdentity();
        $user = User::register($userId, SlackUserId::fromString('<@U041UN081>'), Name::fromString('Samir Boulil'));
        $inMemoryUserRepository->add($user);
        $this->assertSame($user, $inMemoryUserRepository->byId($userId));
        $this->assertSame([$user], $inMemoryUserRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_multiple_users_to_the_repository()
    {
        $inMemoryUserRepository = new InMemoryUserRepository();
        $userId1 = $inMemoryUserRepository->nextIdentity();
        $user1 = User::register($userId1, SlackUserId::fromString('<@U041UN081>'), Name::fromString('Samir Boulil'));
        $inMemoryUserRepository->add($user1);
        $userId2 = $inMemoryUserRepository->nextIdentity();
        $user2 = User::register($userId2, SlackUserId::fromString('<@U042UN082>'), Name::fromString('Samir Boulil'));
        $inMemoryUserRepository->add($user2);
        $this->assertSame($user1, $inMemoryUserRepository->byId($userId1));
        $this->assertSame($user2, $inMemoryUserRepository->byId($userId2));
        $allUsers = $inMemoryUserRepository->all();
        $this->assertContains($user1, $allUsers);
        $this->assertContains($user2, $allUsers);
        $this->assertCount(2, $allUsers);
    }
}
