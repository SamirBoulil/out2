<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity;

use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Statement;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_with_an_id_an_answer_and_two_clues()
    {
        $questionId = QuestionId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $statement = Statement::fromString('What is the size of an elephant ?');
        $answer = ExternalUserId::fromString('<@U041UN08U>');
        $clue1 = Clue::fromString('clue number one.');
        $clue2 = Clue::fromString('clue number two.');

        $question = Question::ask($questionId, $statement, $answer, $clue1, $clue2);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame($questionId, $question->id());
        $this->assertSame($answer, $question->answer());
        $this->assertSame($clue1, $question->clue1());
        $this->assertSame($clue2, $question->clue2());
    }

    /**
     * @test
     */
    public function it_can_determine_whether_the_answer_is_right()
    {
        $questionId = QuestionId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $statement = Statement::fromString('What is the size of an elephant ?');
        $answer = ExternalUserId::fromString('<@U041UN08U>');
        $clue1 = Clue::fromString('clue number one.');
        $clue2 = Clue::fromString('clue number two.');
        $question = Question::ask($questionId, $statement, $answer, $clue1, $clue2);

        $rightAnswer = ExternalUserId::fromString('<@U041UN08U>');
        $this->assertTrue($question->isCorrect($rightAnswer));
        $wrongAnswer = ExternalUserId::fromString('<@U041XXXXX>');
        $this->assertFalse($question->isCorrect($wrongAnswer));
    }
}
