<?php

namespace Tests\Unit\OnceUponATime\Application;

use OnceUponATime\Application\NextClue;
use OnceUponATime\Application\NextClueHandler;
use OnceUponATime\Application\NoClueAvailable;
use OnceUponATime\Domain\Entity\Answer;
use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\Statement;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\EventStore\QuestionsAnsweredEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionsAnsweredEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

class NextClueHandlerTest extends TestCase
{
    private const USER_ID = '7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9';
    private const EXTERNAL_USER_ID = 'external_user_id';
    private const QUESTION_ID_2 = '22222222-2222-2222-2222-222222222222';
    private const QUESTION_ID_1 = '11111111-1111-1111-1111-111111111111';

    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuestionsAnsweredEventStore */
    private $answeredQuestions;

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
        $this->answeredQuestions = new InMemoryQuestionsAnsweredEventStore();
    }

    /**
     * @test
     */
    public function it_returns_the_first_clue_to_a_user()
    {
        $clue = $this->getNextClue(self::EXTERNAL_USER_ID);
        $this->assertNotNull($clue);
        $this->assertInstanceOf(Clue::class, $clue);
        $this->assertInstanceOf('clue 1', (string) $clue);
    }

    /**
     * @test
     */
    public function it_throws_if_the_user_has_not_answered_a_question_before()
    {
        $this->expectException(NoClueAvailable::class);
        $clue = $this->getNextClue(self::EXTERNAL_USER_ID);
    }

    private function getNextClue(string $externalId): Clue
    {
        $nextClue = new NextClue();
        $nextClue->externalUserId = $externalId;
        $nextClueHandler = new NextClueHandler(
            $this->userRepository,
            $this->questionRepository,
            $this->answeredQuestions
        );

        return $nextClueHandler->handle($nextClue);
    }

    private function userHasAnsweredQuestion(string $userId, string $questionId): void
    {
        $questionAnswered = new QuestionAnswered(
            UserId::fromString($userId),
            QuestionId::fromString($questionId),
            true
        );
        $this->answeredQuestions->add($questionAnswered);
    }

    /**
     * it_returns_the_first_clue_to_a_user
     * it_returns_the_second_clue_to_a_user_given_the_user_has_already_asked_for_the_first_clue
     * it_returns_the_same_last_clue_if_there_is_no_more_clue
     * it_throws_if_the_user_is_not_registered
     */

}
