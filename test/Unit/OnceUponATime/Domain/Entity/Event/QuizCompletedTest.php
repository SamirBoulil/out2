<?php

namespace Tests\Unit\OnceUponATime\Domain\Entity\Event;

use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEvent;
use PHPUnit\Framework\TestCase;

class QuizCompletedTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_a_user_id()
    {
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $allQuestionsCorrectlyAnswered = new QuizCompleted($userId);
        $this->assertInstanceOf(QuizEvent::class, $allQuestionsCorrectlyAnswered);
        $this->assertTrue($userId->equals($allQuestionsCorrectlyAnswered->userId()));
    }
}
