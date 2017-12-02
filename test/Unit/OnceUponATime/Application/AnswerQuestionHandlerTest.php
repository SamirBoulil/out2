<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Application\Entity;

use OnceUponATime\Application\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestionHandler;
use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\Statement;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnswerQuestionHandlerTest extends TestCase
{
    private const QUESTION_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';

    private $answerQuestionHandler;

    public function setUp()
    {
        $question = Question::ask(
            QuestionId::fromString(self::QUESTION_ID),
            Statement::fromString('What is the most scared of an elephant ?'),
            ExternalUserId::fromString('<@right_answer>'),
            Clue::fromString('Clue 1'),
            Clue::fromString('Clue 2')
        );
        $questionRepository = new InMemoryQuestionRepository();
        $questionRepository->add($question);

        $this->answerQuestionHandler = new AnswerQuestionHandler($questionRepository);
    }

    /**
     * @test
     */
    public function it_handles_answer_question_objects_and_tells_if_the_question_is_right()
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->questionId = self::QUESTION_ID;
        $answerQuestion->externalId = '<@U041UN08U>';
        $answerQuestion->answer = '<@wrong_answer>';

        $this->assertFalse($this->answerQuestionHandler->handle($answerQuestion));
    }

    /**
     * @test
     */
    public function it_handles_answer_question_objects_and_tells_if_the_question_is_wrong()
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->questionId = self::QUESTION_ID;
        $answerQuestion->externalId = '<@U041UN08U>';
        $answerQuestion->answer = '<@right_answer>';

        $this->assertTrue($this->answerQuestionHandler->handle($answerQuestion));
    }
}
