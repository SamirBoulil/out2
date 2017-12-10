<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\User;

use OnceUponATime\Domain\Entity\User\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_a_string_and_reverted_back_to_it()
    {
        $id = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
        $userId = UserId::fromString($id);
        $this->assertSame($id, (string)$userId);
    }

    /**
     * @test
     */
    public function it_can_be_compared_to_another_user_id()
    {
        $userId1 = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $userId2 = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $this->assertTrue($userId1->equals($userId2));
    }

    /**
     * @test
     */
    public function it_should_be_a_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        UserId::fromString('');
    }

    /**
     * @test
     */
    public function it_should_be_an_uuid_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        UserId::fromString('invalid uuid');
    }
}
