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
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\NewQuestionForUserWhoAnsweredCorrectly;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForUserWhoAnsweredCorrectlyTest extends TestCase
{
    private const USER_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const NAME = 'My name';
    private const QUESTION_ID = '11111111-1111-1111-1111-111111111111';

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var NewQuestionForUserWhoAnsweredCorrectly */
    private $newQuestionForUserWhoAnsweredCorrectly;

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
                QuestionId::fromString(self::QUESTION_ID),
                Statement::fromString('Question 1'),
                Answer::fromString('answer'),
                Clue::fromString('clue 1'),
                Clue::fromString('clue 2')
            )
        );

        $this->quizEventStore = new InMemoryQuizEventStore();
        $publisher = new PublishToEventStore($this->quizEventStore);
        $nextQuestionHandler = new AskQuestionHandler(
            $this->userRepository,
            $questionRepository,
            $this->quizEventStore,
            $publisher,
            $publisher
        );
        $this->newQuestionForUserWhoAnsweredCorrectly = new NewQuestionForUserWhoAnsweredCorrectly($nextQuestionHandler);
    }

    /**
     * @test
     */
    public function it_asks_for_a_new_question_when_a_user_answers_a_question_correctly()
    {
        $userId = UserId::fromString(self::USER_ID);
        $questionAnsweredCorrectly = new QuestionAnswered(
            $userId,
            QuestionId::fromString(self::QUESTION_ID),
            true
        );

        $this->newQuestionForUserWhoAnsweredCorrectly->questionAnswered($questionAnsweredCorrectly);

        $questionsAsked = $this->quizEventStore->all();
        $this->assertCount(1, $questionsAsked);
        $questionAsked = current($questionsAsked);
        $this->assertTrue($questionAsked->userId()->equals($userId));
        $this->assertInstanceOf(QuestionId::class, $questionAsked->questionId());
    }

    /**
     * @test
     */
    public function it_does_not_ask_for_a_new_question_when_a_user_answers_incorrectly()
    {
        $userId = UserId::fromString(self::USER_ID);
        $questionAnsweredCorrectly = new QuestionAnswered(
            $userId,
            QuestionId::fromString(self::QUESTION_ID),
            false
        );

        $this->newQuestionForUserWhoAnsweredCorrectly->questionAnswered($questionAnsweredCorrectly);

        $this->assertCount(0, $this->quizEventStore->all());
    }
}
