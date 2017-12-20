<?php

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
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
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForUserWhoAnsweredIncorrectly;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\OnceUponATime\Application\TestEventSubscriber;

class NewQuestionForUserWhoAnsweredIncorrectlyTest extends TestCase
{
    private const USER_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const QUESTION_ID = '11111111-1111-1111-1111-111111111111';

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var AskQuestionHandler */
    private $askQuestionHandler;

    public function setUp()
    {
        $questionRepository = new InMemoryQuestionRepository();
        $questionRepository->add(
            Question::ask(
                QuestionId::fromString(self::QUESTION_ID),
                Statement::fromString('Question 1'),
                Answer::fromString('answer'),
                Clue::fromString('clue 1'),
                Clue::fromString('clue 2')
            )
        );

        $userRepository = new InMemoryUserRepository();
        $userRepository->add(
            User::register(
                UserId::fromString(self::USER_ID),
                ExternalUserId::fromString('external_user_id'),
                Name::fromString('my name')
            )
        );

        $this->quizEventStore = new InMemoryQuizEventStore();

        $testEventSubscriber = new TestEventSubscriber();
        $publishToEventStore = new PublishToEventStore($this->quizEventStore);
        $this->askQuestionHandler = new AskQuestionHandler(
            $userRepository,
            $questionRepository,
            $this->quizEventStore,
            $testEventSubscriber,
            $publishToEventStore
        );
    }

    /**
     * @test
     */
    public function it_adds_a_new_question_event_in_the_quiz_event_store_when_the_user_answers_incorrectly_too_many_times()
    {
        $userId = UserId::fromString(self::USER_ID);
        $questionId = QuestionId::fromString(self::QUESTION_ID);

        $this->quizEventStore->add(new QuestionAsked(
            $userId,
            $questionId
        ));
        $this->quizEventStore->add(new QuestionAnswered(
            $userId,
            $questionId,
            false
        ));
        $this->quizEventStore->add(new QuestionAnswered(
            $userId,
            $questionId,
            false
        ));

        $lastTry = new QuestionAnswered($userId, $questionId, false);
        $handler = new NewQuestionForUserWhoAnsweredIncorrectly(
            $this->quizEventStore,
            $this->askQuestionHandler
        );
        $handler->questionAnswered($lastTry);

        $quizEvents = $this->quizEventStore->byUser($userId);
        $lastEvent = end($quizEvents);
        $this->assertInstanceOf(QuestionAsked::class, $lastEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_a_new_question_event_in_the_quiz_event_store_when_the_user_answers_incorrectly_too_many_times()
    {
        $userId = UserId::fromString(self::USER_ID);
        $questionId = QuestionId::fromString(self::QUESTION_ID);

        $this->quizEventStore->add(new QuestionAsked(
            $userId,
            $questionId
        ));
        $this->quizEventStore->add(new QuestionAnswered(
            $userId,
            $questionId,
            false
        ));

        $lastTry = new QuestionAnswered($userId, $questionId, false);
        $handler = new NewQuestionForUserWhoAnsweredIncorrectly(
            $this->quizEventStore,
            $this->askQuestionHandler
        );
        $handler->questionAnswered($lastTry);

        $quizEvents = $this->quizEventStore->byUser($userId);
        $lastEvent = end($quizEvents);
        $this->assertNotInstanceOf(QuestionAsked::class, $lastEvent);
    }
}
