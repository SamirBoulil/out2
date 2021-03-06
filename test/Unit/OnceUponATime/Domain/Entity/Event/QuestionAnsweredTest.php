<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizEvent;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionAnsweredTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_constructed_with_a_question_id_and_a_user_id()
    {
        $questionId = QuestionId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $isCorrect = true;
        $questionAnswered = new QuestionAnswered($userId, $questionId, $isCorrect);
        $this->assertInstanceOf(quizEvent::class, $questionAnswered);
        $this->assertSame($questionId, $questionAnswered->questionId());
        $this->assertSame($userId, $questionAnswered->userId());
        $this->assertSame($isCorrect, $questionAnswered->isCorrect());
    }
}
