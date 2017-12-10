<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizzEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionAskedTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_constructed_with_a_question_id_and_a_user_id()
    {
        $questionId = QuestionId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $questionAsked = new QuestionAsked($userId, $questionId);
        $this->assertInstanceOf(QuizzEvent::class, $questionAsked);
        $this->assertSame($questionId, $questionAsked->questionId());
        $this->assertSame($userId, $questionAsked->userId());
    }
}
