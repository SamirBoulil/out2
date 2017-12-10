<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Application;

use OnceUponATime\Application\AnswerQuestion\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestion\AnswerQuestionHandler;
use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
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
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Infrastructure\Notifications\QuestionAnsweredNotifyMany;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnswerQuestionHandlerTest extends TestCase
{
    private const QUESTION_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const USER_ID = '3a021c08-ad15-43aa-aba3-8626fecd39a7';

    /** @var QuestionAnsweredNotify */
    private $testEventSubscriber;

    /** @var AnswerQuestionHandler */
    private $answerQuestionHandler;

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

        $userRepository = new InMemoryUserRepository();
        $userRepository->add($user);

        $questionAsked = new QuestionAsked($userId, $questionId);
        $questionAnsweredEventStore = new InMemoryQuizEventStore();
        $questionAnsweredEventStore->add($questionAsked);

        $this->testEventSubscriber = new TestEventSubscriber();
        $notify = new QuestionAnsweredNotifyMany([$this->testEventSubscriber]);

        $this->answerQuestionHandler = new AnswerQuestionHandler(
            $userRepository,
            $questionRepository,
            $questionAnsweredEventStore,
            $notify
        );
    }

    /**
     * @test
     */
    public function it_handles_an_answer_to_a_question_and_tells_if_the_answer_is_correct()
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->userId = self::USER_ID;
        $answerQuestion->answer = '<@wrong_answer>';

        $this->assertFalse($this->answerQuestionHandler->handle($answerQuestion));
        $this->assertTrue($this->testEventSubscriber->isQuestionAnswered);
    }

    /**
     * @test
     */
    public function it_handles_an_answer_to_a_question_and_tells_if_the_answer_is_incorrect()
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->userId = self::USER_ID;
        $answerQuestion->answer = '<@right_answer>';
        $this->assertTrue($this->answerQuestionHandler->handle($answerQuestion));
        $this->assertTrue($this->testEventSubscriber->isQuestionAnswered);
    }

    /**
     * @test
     */
    public function it_throws_if_the_user_id_is_not_found()
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->userId = '00000000-0000-0000-0000-000000000000';
        $answerQuestion->answer = '<@right_answer>';
        $this->expectException(InvalidUserId::class);
        $this->answerQuestionHandler->handle($answerQuestion);
        $this->assertFalse($this->testEventSubscriber->isQuestionAnswered);
    }

    /**
     * TODO: How can I / should I unit test the notification system ?
     * TODO: Quid Acceptance VS unit testing application layer ?
     */
}
