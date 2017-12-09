<?php

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Entity\UserRegistered;
use OnceUponATime\Infrastructure\Notifications\UserRegisteredNotifyMany;
use PHPUnit\Framework\TestCase;
use Tests\Unit\OnceUponATime\Application\TestEventSubscriber;

class UserRegisteredNotifyManyTest extends TestCase
{
    /**
     * @test
     */
    public function it_notifies_many_when_a_user_is_registered()
    {
        $subscriber1 = new TestEventSubscriber();
        $subscriber2 = new TestEventSubscriber();
        $userRegistered = $this->createUserRegistered();

        $userRegisteredNotifyMany = new UserRegisteredNotifyMany([$subscriber1, $subscriber2]);
        $userRegisteredNotifyMany->userRegistered($userRegistered);

        $this->assertTrue($subscriber1->isUserRegistered);
        $this->assertTrue($subscriber2->isUserRegistered);
    }

    private function createUserRegistered(): UserRegistered
    {
        return new UserRegistered(
            UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            Name::fromString('Samir'),
            ExternalUserId::fromString('<@external_id>')
        );
    }
}
