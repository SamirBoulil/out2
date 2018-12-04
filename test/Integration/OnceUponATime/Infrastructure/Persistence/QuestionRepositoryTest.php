<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence;

use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\Question\Statement;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Infrastructure\Persistence\File\FileBasedQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionRepositoryTest extends TestCase
{
    public function getQuestionRepositories(): array
    {
        return [
            'in memory repository' => [new InMemoryQuestionRepository()],
            'file based repository' => [new FileBasedQuestionRepository(tempnam(sys_get_temp_dir(), 'questions'))],
        ];
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_generates_the_next_question_id(QuestionRepository $repository)
    {
        $questionId = $repository->nextIdentity();
        $this->assertInstanceOf(QuestionId::class, $questionId);
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_returns_an_empty_array_when_there_is_no_questions(QuestionRepository $repository)
    {
        $this->assertSame([], $repository->all());
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_adds_a_question_to_the_repository_and_returns_it(QuestionRepository $repository)
    {
        $questionId = QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $question = Question::ask(
            $questionId,
            Statement::fromString('What is the size of an elephant ?'),
            Answer::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $repository->add($question);
        $this->assertSameQuestion($question, $repository->byId($questionId));
        $this->assertSameQuestions([$question], $repository->all());
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_adds_a_question_with_id_generated_using_the_next_identity(QuestionRepository $repository)
    {
        $questionId = QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9');
        $question = Question::ask(
            $questionId,
            Statement::fromString('What is the size of an elephant ?'),
            Answer::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $repository->add($question);
        $this->assertSameQuestion($question, $repository->byId($questionId));
        $this->assertSameQuestions([$question], $repository->all());
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_adds_multiple_questions_to_the_repository(QuestionRepository $repository)
    {
        $questionId1 = $repository->nextIdentity();
        $question1 = Question::ask(
            $questionId1,
            Statement::fromString('What is the size of an elephant ?'),
            Answer::fromString('<@U041UN08U>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $repository->add($question1);

        $questionId2 = $repository->nextIdentity();
        $question2 = Question::ask(
            $questionId2,
            Statement::fromString('What is the size of an mouse ?'),
            Answer::fromString('<@U041UN0812>'),
            Clue::fromString('clue number one.'),
            Clue::fromString('clue number two.')
        );
        $repository->add($question2);

        $repoQ = $repository->byId($questionId1);
        $this->assertSameQuestion($question1, $repoQ);
        $this->assertSameQuestion($question2, $repository->byId($questionId2));

        $allQuestions = $repository->all();
        $this->assertSameQuestions([$question1, $question2], $allQuestions);
    }

    /**
     * @test
     * @dataProvider getQuestionRepositories
     */
    public function it_returns_null_if_the_id_is_not_found(QuestionRepository $repository)
    {
        $unknownId = QuestionId::fromString('00000000-0000-0000-0000-000000000000');
        $this->assertSame(null, $repository->byId($unknownId));
    }

    protected function assertSameQuestions(array $expectedQuestions, array $allQuestions): void
    {
        $this->assertCount(\count($expectedQuestions), $allQuestions);
        foreach ($expectedQuestions as $i => $expectedUser) {
            $this->assertSameQuestion($expectedUser, $allQuestions[$i]);
        }
    }

    private function assertSameQuestion(Question $user, Question $byId)
    {
        $user->id()->equals($byId->id());
        $this->assertSame((string) $user->statement(), (string) $byId->statement());
        $this->assertSame((string) $user->answer(), (string) $byId->answer());
        $this->assertSame((string) $user->clue1(), (string) $byId->clue1());
        $this->assertSame((string) $user->clue2(), (string) $byId->clue2());
    }
}
