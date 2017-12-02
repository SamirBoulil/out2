<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\ExternalUserId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalUserIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_a_string_and_reverted_back_to_it()
    {
        $id = '<@U041UN08U>';
        $slackUserId = ExternalUserId::fromString($id);
        $this->assertSame($id, (string) $slackUserId);
    }

    /**
     * @test
     */
    public function it_can_be_compared_to_another_slack_user_id()
    {
        $userId1 = ExternalUserId::fromString('<@U041UN08U>');
        $userId2 = ExternalUserId::fromString('<@U041UN08U>');
        $this->assertTrue($userId1->equals($userId2));
    }

    /**
     * @test
     */
    public function it_should_be_a_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        ExternalUserId::fromString('');
    }
}
