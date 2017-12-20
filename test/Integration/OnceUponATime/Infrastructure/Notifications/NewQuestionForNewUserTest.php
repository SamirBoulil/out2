<?php

declare(strict_types=1);

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
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Event\UserRegistered;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForNewUser;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class NewQuestionForNewUserTest extends TestCase
{
    private const USER_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const NAME = 'My name';

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var NewQuestionForNewUser */
    private $newQuestionForNewUser;

    /** @var UserRepository */
    private $userRepository;

    public function setUp()
    {
        $this->userRepository = new InMemoryUserRepository();
        $userId = UserId::fromString(self::USER_ID);
        $externalUserId = ExternalUserId::fromString('external_user_id');
        $name = Name::fromString(self::NAME);
        $this->userRepository->add(User::register($userId, $externalUserId, $name));

        $questionRepository = new InMemoryQuestionRepository();
        $questionRepository->add(
            Question::ask(
                QuestionId::fromString('11111111-1111-1111-1111-111111111111'),
                Statement::fromString('Question 1'),
                Answer::fromString('answer'),
                Clue::fromString('clue 1'),
                Clue::fromString('clue 2')
            )
        );

        $this->quizEventStore = new InMemoryQuizEventStore();
        $notifier = new PublishToEventStore($this->quizEventStore);
        $nextQuestionHandler = new AskQuestionHandler(
            $this->userRepository,
            $questionRepository,
            $this->quizEventStore,
            $notifier,
            $notifier
        );
        $this->newQuestionForNewUser = new NewQuestionForNewUser($nextQuestionHandler);
    }

    /**
     * @test
     */
    public function it_asks_for_a_new_question_when_a_new_user_registered()
    {
        $userId = UserId::fromString(self::USER_ID);
        $userRegistered = new UserRegistered(
            $userId,
            Name::fromString(self::NAME)
        );

        $this->newQuestionForNewUser->userRegistered($userRegistered);

        $questionsAsked = $this->quizEventStore->all();
        $this->assertCount(1, $questionsAsked);
        $questionAsked = current($questionsAsked);
        $this->assertTrue($questionAsked->userId()->equals($userId));
        $this->assertInstanceOf(QuestionId::class, $questionAsked->questionId());
    }
}
