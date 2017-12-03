<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Statement;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuestionRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_the_next_question_id()
    {
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $questionId = $inMemoryQuestionRepository->nextIdentity();
        $this->assertInstanceOf(QuestionId::class, $questionId);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_questions()
    {
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $this->assertSame([], $inMemoryQuestionRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_a_question_to_the_repository_and_returns_it()
    {
        $questionId = QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $question = Question::ask(
            $questionId,
            Statement::fromString('What is the size of an elephant ?'),
            ExternalUserId::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $inMemoryQuestionRepository->add($question);
        $this->assertSame($question, $inMemoryQuestionRepository->byId($questionId));
        $this->assertSame([$question], $inMemoryQuestionRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_a_question_with_id_generated_using_the_next_identity()
    {
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $questionId = QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $question = Question::ask(
            $questionId,
            Statement::fromString('What is the size of an elephant ?'),
            ExternalUserId::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $inMemoryQuestionRepository->add($question);
        $this->assertSame($question, $inMemoryQuestionRepository->byId($questionId));
        $this->assertSame([$question], $inMemoryQuestionRepository->all());
    }

    /**
     * @test
     */
    public function it_adds_multiple_questions_to_the_repository()
    {
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $questionId1 = $inMemoryQuestionRepository->nextIdentity();
        $question1 = Question::ask(
            $questionId1,
            Statement::fromString('What is the size of an elephant ?'),
            ExternalUserId::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $inMemoryQuestionRepository->add($question1);

        $questionId2 = $inMemoryQuestionRepository->nextIdentity();
        $question2 = Question::ask(
            $questionId2,
            Statement::fromString('What is the size of an mouse ?'),
            ExternalUserId::fromString('<@U041UN0812>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $inMemoryQuestionRepository->add($question2);

        $repoQ = $inMemoryQuestionRepository->byId($questionId1);
        $this->assertSame($question1, $repoQ);
        $this->assertSame($question2, $inMemoryQuestionRepository->byId($questionId2));

        $allQuestions = $inMemoryQuestionRepository->all();
        $this->assertContains($question1, $allQuestions);
        $this->assertContains($question2, $allQuestions);
        $this->assertCount(2, $allQuestions);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_id_is_not_found()
    {
        $inMemoryQuestionRepository = new InMemoryQuestionRepository();
        $unknownId = QuestionId::fromString('00000000-0000-0000-0000-000000000000');
        $this->assertSame(null, $inMemoryQuestionRepository->byId($unknownId));
    }
}
