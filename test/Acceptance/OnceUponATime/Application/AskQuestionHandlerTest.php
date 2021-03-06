<?php

declare(strict_types=1);

namespace Tests\Acceptance\OnceUponATime\Application;

use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\Question\Statement;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AskQuestionHandlerTest extends TestCase
{
    private const USER_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const EXTERNAL_USER_ID = 'external_user_id';
    private const QUESTION_ID_2 = '22222222-2222-2222-2222-222222222222';
    private const QUESTION_ID_1 = '11111111-1111-1111-1111-111111111111';

    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var TestEventSubscriber */
    private $testEventSubscriber;

    public function setUp()
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->userRepository->add(
            User::register(
                UserId::fromString(self::USER_ID),
                ExternalUserId::fromString(self::EXTERNAL_USER_ID),
                Name::fromString('Samir')
            )
        );
        $this->questionRepository = new InMemoryQuestionRepository();
        $this->questionRepository->add(
            Question::ask(
                QuestionId::fromString(self::QUESTION_ID_1),
                Statement::fromString('Question 1'),
                Answer::fromString('answer'),
                Clue::fromString('clue 1'),
                Clue::fromString('clue 2')
            )
        );
        $this->questionRepository->add(
            Question::ask(
                QuestionId::fromString(self::QUESTION_ID_2),
                Statement::fromString('Question 1'),
                Answer::fromString('answer'),
                Clue::fromString('clue 1'),
                Clue::fromString('clue 2')
            )
        );
        $this->quizEventStore = new InMemoryQuizEventStore();

        $this->testEventSubscriber = new TestEventSubscriber();
    }

    /**
     * @test
     */
    public function it_finds_the_next_question_for_a_new_user()
    {
        $question = $this->getNextQuestion(self::USER_ID);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertTrue($this->testEventSubscriber->isQuestionAsked);
    }

    /**
     * @test
     */
    public function it_finds_the_next_unresolved_question_for_the_user()
    {
        $this->userHasAnsweredQuestion(self::USER_ID, self::QUESTION_ID_1);
        $question = $this->getNextQuestion(self::USER_ID);
        $this->assertNotNull($question);
        $this->assertInstanceOf(Question::class, $question);
        $this->assertSame(self::QUESTION_ID_2, (string) $question->id());
        $this->assertTrue($this->testEventSubscriber->isQuestionAsked);
    }

    /**
     * @test
     */
    public function it_returns_null_when_the_user_has_answered_all_the_questions()
    {
        $this->userHasAnsweredQuestion(self::USER_ID, self::QUESTION_ID_1);
        $this->userHasAnsweredQuestion(self::USER_ID, self::QUESTION_ID_2);
        $question = $this->getNextQuestion(self::USER_ID);
        $this->assertNull($question);
        $this->assertTrue($this->testEventSubscriber->isQuizCompleted);
    }

    /**
     * @test
     */
    public function it_throws_when_an_unregistered_user_asks_for_the_next_question()
    {
        $this->expectException(InvalidUserId::class);
        $this->getNextQuestion('00000000-0000-0000-0000-000000000000');
    }

    private function getNextQuestion(string $userId): ?Question
    {
        $nextQuestion = new AskQuestion();
        $nextQuestion->userId = $userId;
        $nextQuestionHandler = new AskQuestionHandler(
            $this->userRepository,
            $this->questionRepository,
            $this->quizEventStore,
            $this->testEventSubscriber,
            $this->testEventSubscriber
        );

        return $nextQuestionHandler->handle($nextQuestion);
    }

    private function userHasAnsweredQuestion(string $userId, string $questionId): void
    {
        $questionAnswered = new QuestionAnswered(
            UserId::fromString($userId),
            QuestionId::fromString($questionId),
            true
        );
        $this->quizEventStore->add($questionAnswered);
    }
}
