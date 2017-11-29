<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\QuestionId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionIdTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_a_string_and_reverted_back_to_it()
    {
        $id = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
        $question = QuestionId::fromString($id);
        $this->assertSame($id, (string) $question);
    }

    /**
     * @test
     */
    public function it_can_be_compared_to_another_question_id()
    {
        $id1 = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
        $id2 = '8e8ef1c3-0cb5-42ac-b697-3f7bfce24df9';
        $question1 = QuestionId::fromString($id1);
        $question2 = QuestionId::fromString($id1);
        $question3 = QuestionId::fromString($id2);
        $this->assertTrue($question1->equals($question2));
        $this->assertFalse($question1->equals($question3));
    }

    /**
     * @test
     */
    public function it_should_be_a_non_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        QuestionId::fromString('');
    }

    /**
     * @test
     */
    public function it_should_be_an_uuid_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        QuestionId::fromString('invalid-uuid');
    }
}
