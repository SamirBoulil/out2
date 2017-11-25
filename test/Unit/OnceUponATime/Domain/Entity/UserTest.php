<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\SlackUserId;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_with_an_id_a_slack_user_id_and_a_name()
    {
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $slackUserId = SlackUserId::fromString('<@U041UN08U>');
        $name = Name::fromString('Alice Jardin');

        $user = User::create($userId, $slackUserId, $name);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($userId, $user->id());
        $this->assertSame($slackUserId, $user->slackUserId());
        $this->assertSame($name, $user->name());
    }
}
