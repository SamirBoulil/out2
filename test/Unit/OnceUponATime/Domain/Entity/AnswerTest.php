<?php

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\Answer;
use PHPUnit\Framework\TestCase;

class AnswerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_a_string()
    {
        $id = '<@U041UN08U>';
        $externalUserId = Answer::fromString($id);
        $this->assertSame($id, (string) $externalUserId);
    }

    /**
     * @test
     */
    public function it_can_be_compared_to_another_answer()
    {
        $userId1 = Answer::fromString('<@U041UN08U>');
        $userId2 = Answer::fromString('<@U041UN08U>');
        $this->assertTrue($userId1->equals($userId2));
    }

    /**
     * @test
     */
    public function it_should_be_an_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        Answer::fromString('');
    }
}
