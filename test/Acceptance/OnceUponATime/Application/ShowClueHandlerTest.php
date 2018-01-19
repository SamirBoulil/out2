<?php

declare(strict_types=1);

namespace Tests\Acceptance\OnceUponATime\Application;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Application\ShowClue\ShowClue;
use OnceUponATime\Application\ShowClue\ShowClueHandler;
use OnceUponATime\Application\ShowClue\ShowClueHandlerResponse;
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
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\QuestionAnsweredNotifyMany;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class ShowClueHandlerTest extends TestCase
{
    private const QUESTION_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const USER_ID = '3a021c08-ad15-43aa-aba3-8626fecd39a7';
    private const USER_ID_2 = '22222222-2222-2222-2222-222222222222';

    /** @var ShowClueHandler */
    private $askClueHandler;

    /** @var UserRepository */
    protected $userRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var QuestionAnsweredNotify */
    private $testEventSubscriber;

    public function setUp()
    {
        $questionId = QuestionId::fromString(self::QUESTION_ID);
        $question = Question::ask(
            $questionId,
            Statement::fromString('What is the most scared of an elephant ?'),
            Answer::fromString('<@right_answer>'),
            Clue::fromString('Clue 1'),
            Clue::fromString('Clue 2')
        );
        $questionRepository = new InMemoryQuestionRepository();
        $questionRepository->add($question);

        $userId = UserId::fromString(self::USER_ID);
        $externalUserId = ExternalUserId::fromString('<@testUser>');
        $name = Name::fromString('Alice Jardin');
        $user = User::register($userId, $externalUserId, $name);

        $this->userRepository = new InMemoryUserRepository();
        $this->userRepository->add($user);

        $this->quizEventStore = new InMemoryQuizEventStore();

        $this->testEventSubscriber = new TestEventSubscriber();

        $this->askClueHandler = new ShowClueHandler(
            $this->userRepository,
            $questionRepository,
            $this->quizEventStore
        );
    }

    /**
     * @test
     */
    public function it_does_not_show_a_clue()
    {
        $this->askQuestionToUser();
        $askClue = new ShowClue();
        $askClue->userId = self::USER_ID;
        $response = $this->askClueHandler->handle($askClue);
        $this->assertNull($response->clue);
        $this->assertFalse($response->isQuizCompleted);
    }

    /**
     * @test
     */
    public function it_shows_the_first_clue_for_the_user()
    {
        $this->askQuestionToUser();
        $this->answerIncorrectly();
        $askClue = new ShowClue();
        $askClue->userId = self::USER_ID;
        $response = $this->askClueHandler->handle($askClue);
        $this->assertSame('Clue 1', (string) $response->clue);
        $this->assertFalse($response->isQuizCompleted);
    }

    /**
     * @test
     */
    public function it_finds_the_second_clue_for_the_user()
    {
        $this->askQuestionToUser();
        $this->answerIncorrectly();
        $this->answerIncorrectly();
        $askClue = new ShowClue();
        $askClue->userId = self::USER_ID;
        $response = $this->askClueHandler->handle($askClue);
        $this->assertSame('Clue 2', (string) $response->clue);
    }

    /**
     * @test
     */
    public function it_throws_if_the_user_is_not_found()
    {
        $this->expectException(InvalidUserId::class);
        $askClue = new ShowClue();
        $askClue->userId = '00000000-0000-0000-0000-000000000000';
        $this->askClueHandler->handle($askClue);
    }

    /**
     * @test
     */
    public function it_throws_if_the_user_has_completed_the_quiz()
    {
        $this->askQuestionToUser();
        $this->userHasCompletedQuiz();
        $showClue = new ShowClue();
        $showClue->userId = self::USER_ID;
        $response = $this->askClueHandler->handle($showClue);
        $this->assertNull($response->clue);
        $this->assertTrue($response->isQuizCompleted);
    }

    /**
     * @test
     */
    public function it_throws_if_the_user_has_no_question_to_answer()
    {
        $this->expectException(NoQuestionToAnswer::class);
        $showClue = new ShowClue();
        $showClue->userId = self::USER_ID;
        $response = $this->askClueHandler->handle($showClue);
        $this->assertNull($response->clue);
        $this->assertTrue($response->isQuizCompleted);
    }

    private function answerIncorrectly(): void
    {
        $questionAnswered = new QuestionAnswered(
            UserId::fromString(self::USER_ID),
            QuestionId::fromString(self::QUESTION_ID),
            false
        );
        $this->quizEventStore->add($questionAnswered);
    }

    private function userHasCompletedQuiz(): void
    {
        $this->quizEventStore->add(new QuizCompleted(UserId::fromString(self::USER_ID)));
    }

    private function askQuestionToUser(): void
    {
        $userId = UserId::fromString(self::USER_ID);
        $questionId = QuestionId::fromString(self::QUESTION_ID);
        $questionAsked = new QuestionAsked($userId, $questionId);
        $this->quizEventStore->add($questionAsked);
    }
}
