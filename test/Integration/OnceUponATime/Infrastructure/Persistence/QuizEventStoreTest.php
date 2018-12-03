<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEvent;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Infrastructure\Persistence\File\FileBasedQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuizEventStoreTest extends TestCase
{
    public function getQuizEventStore(): array
    {
        return [
            'in memory quiz event store'  => [new InMemoryQuizEventStore()],
            'file based quiz event store' => [new FileBasedQuizEventStore(tempnam(sys_get_temp_dir(), 'questions'))],
        ];
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_can_add_a_question_answered_and_return_it_by_user(QuizEventStore $quizEventStore)
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $questionAnswered = new QuestionAnswered(
            $userId,
            QuestionId::fromString('22222222-2222-2222-2222-222222222222'),
            true
        );

        $quizEventStore->add($questionAnswered);
        $this->assertSameEvents([$questionAnswered], $quizEventStore->byUser($userId));
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_can_add_multiple_questions_answered_and_return_them_by_user(QuizEventStore $quizEventStore)
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $questionAnswered1 = new QuestionAnswered(
            $userId,
            QuestionId::fromString('11111111-1111-1111-1111-111111111111'),
            true
        );
        $questionAnswered2 = new QuestionAnswered(
            $userId,
            QuestionId::fromString('22222222-2222-2222-2222-222222222222'),
            true
        );
        $anotherUserId = UserId::fromString('22222222-2222-2222-2222-222222222222');
        $questionAnswered3 = new QuestionAnswered(
            $anotherUserId,
            QuestionId::fromString('22222222-2222-2222-2222-222222222222'),
            true
        );

        $quizEventStore->add($questionAnswered1);
        $quizEventStore->add($questionAnswered2);
        $quizEventStore->add($questionAnswered3);
        $this->assertSameEvents(
            [$questionAnswered1, $questionAnswered2],
            $quizEventStore->byUser(UserId::fromString('11111111-1111-1111-1111-111111111111'))
        );
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_returns_all_the_questions_answered_by_all_users(QuizEventStore $quizEventStore)
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $anotherUserId = UserId::fromString('22222222-2222-2222-2222-222222222222');
        $questionId1 = QuestionId::fromString('11111111-1111-1111-1111-111111111111');
        $questionId2 = QuestionId::fromString('22222222-2222-2222-2222-222222222222');

        $questionAsked1 = new QuestionAsked($userId, $questionId1);
        $questionAsked2 = new QuestionAsked($userId, $questionId2);
        $questionAskedOtherUser = new QuestionAsked($anotherUserId, $questionId2);

        $questionAnswered1 = new QuestionAnswered($userId, $questionId1, true);
        $questionAnswered2 = new QuestionAnswered($userId, $questionId2, true);
        $questionAnsweredOtherUser = new QuestionAnswered($anotherUserId, $questionId2, true);

        $quizEventStore->add($questionAsked1);
        $quizEventStore->add($questionAnswered1);
        $quizEventStore->add($questionAsked2);
        $quizEventStore->add($questionAnswered2);
        $quizEventStore->add($questionAskedOtherUser);
        $quizEventStore->add($questionAnsweredOtherUser);

        $this->assertSameEvents(
            [
                $questionAsked1,
                $questionAnswered1,
                $questionAsked2,
                $questionAnswered2,
                $questionAskedOtherUser,
                $questionAnsweredOtherUser,
            ],
            $quizEventStore->all()
        );
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_returns_the_question_the_user_has_to_answer(QuizEventStore $quizEventStore)
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $anotherUserId = UserId::fromString('22222222-2222-2222-2222-222222222222');
        $questionId1 = QuestionId::fromString('11111111-1111-1111-1111-111111111111');
        $questionId2 = QuestionId::fromString('22222222-2222-2222-2222-222222222222');

        $questionAsked1 = new QuestionAsked($userId, $questionId1);
        $questionAsked2 = new QuestionAsked($userId, $questionId2);
        $questionAskedOtherUser = new QuestionAsked($anotherUserId, $questionId1);

        $questionAnswered1 = new QuestionAnswered($userId, $questionId1, true);
        $questionAnswered2 = new QuestionAnswered($userId, $questionId2, true);
        $questionAnsweredOtherUser = new QuestionAnswered($anotherUserId, $questionId1, true);

        $quizEventStore->add($questionAsked1);
        $quizEventStore->add($questionAnswered1);
        $quizEventStore->add($questionAsked2);
        $quizEventStore->add($questionAnswered2);
        $quizEventStore->add($questionAskedOtherUser);
        $quizEventStore->add($questionAnsweredOtherUser);

        $currentQuestionId = $quizEventStore->questionToAnswerForUser($userId);
        $this->assertTrue($questionId2->equals($currentQuestionId));

        $currentQuestionId = $quizEventStore->questionToAnswerForUser($anotherUserId);
        $this->assertTrue($questionId1->equals($currentQuestionId));
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_returns_the_number_of_guesses_a_user_has_made_for_the_current_question(
        QuizEventStore $quizEventStore
    ) {
        $userId1 = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $userId2 = UserId::fromString('22222222-2222-2222-2222-222222222222');
        $newUserId = UserId::fromString('33333333-3333-3333-3333-333333333333');
        $questionId = QuestionId::fromString('11111111-1111-1111-1111-111111111111');

        $questionAskedUser1 = new QuestionAsked($userId1, $questionId);
        $questionAskedUser2 = new QuestionAsked($userId2, $questionId);
        $questionAskedNewUser = new QuestionAsked($newUserId, $questionId);

        $questionAnsweredUser1 = new QuestionAnswered($userId1, $questionId, false);
        $questionAnsweredUser2 = new QuestionAnswered($userId2, $questionId, false);

        $quizEventStore->add($questionAskedUser1);
        $quizEventStore->add($questionAskedUser2);
        $quizEventStore->add($questionAskedNewUser);

        $quizEventStore->add($questionAnsweredUser1);

        $quizEventStore->add($questionAnsweredUser2);
        $quizEventStore->add($questionAnsweredUser2);

        $guessesCount = $quizEventStore->answersCount($userId1);
        $this->assertSame(1, $guessesCount);

        $guessesCount = $quizEventStore->answersCount($userId2);
        $this->assertSame(2, $guessesCount);

        $guessesCount = $quizEventStore->answersCount($newUserId);
        $this->assertSame(0, $guessesCount);
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_returns_the_number_of_guesses_a_user_has_made_for_all_questions(QuizEventStore $quizEventStore)
    {
        $userId1 = UserId::fromString('11111111-1111-1111-1111-111111111111');

        $questionId0 = QuestionId::fromString('00000000-0000-0000-0000-000000000000');
        $questionId1 = QuestionId::fromString('11111111-1111-1111-1111-111111111111');
        $questionId2 = QuestionId::fromString('22222222-2222-2222-2222-222222222222');
        $questionId3 = QuestionId::fromString('33333333-3333-3333-3333-333333333333');

        $questionAsked0 = new QuestionAsked($userId1, $questionId0);
        $questionAskedQ1 = new QuestionAsked($userId1, $questionId1);
        $questionAskedQ2 = new QuestionAsked($userId1, $questionId2);
        $questionAskedQ3 = new QuestionAsked($userId1, $questionId3);

        $questionAnsweredQ1 = new QuestionAnswered($userId1, $questionId1, true);
        $questionAnsweredQ2_1 = new QuestionAnswered($userId1, $questionId2, false);
        $questionAnsweredQ2_2 = new QuestionAnswered($userId1, $questionId2, true);
        $questionAnsweredQ3_1 = new QuestionAnswered($userId1, $questionId3, false);
        $questionAnsweredQ3_2 = new QuestionAnswered($userId1, $questionId3, false);
        $questionAnsweredQ3_3 = new QuestionAnswered($userId1, $questionId3, true);

        $quizEventStore->add($questionAsked0);
        $quizEventStore->add($questionAskedQ1);
        $quizEventStore->add($questionAnsweredQ1);
        $quizEventStore->add($questionAskedQ2);
        $quizEventStore->add($questionAnsweredQ2_1);
        $quizEventStore->add($questionAnsweredQ2_2);
        $quizEventStore->add($questionAskedQ3);
        $quizEventStore->add($questionAnsweredQ3_1);
        $quizEventStore->add($questionAnsweredQ3_2);
        $quizEventStore->add($questionAnsweredQ3_3);

        $guessesCountAll = $quizEventStore->answersCountAll($userId1);
        $this->assertSame([
            '11111111-1111-1111-1111-111111111111' => 1,
            '22222222-2222-2222-2222-222222222222' => 2,
            '33333333-3333-3333-3333-333333333333' => 3,
        ], $guessesCountAll);
    }

    /**
     * @test
     * @dataProvider getQuizEventStore
     */
    public function it_returns_the_questions_the_user_has_answered_correctly(QuizEventStore $quizEventStore)
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $anotherUserId = UserId::fromString('22222222-2222-2222-2222-222222222222');

        $questionId1 = QuestionId::fromString('11111111-1111-1111-1111-111111111111');
        $questionId2 = QuestionId::fromString('22222222-2222-2222-2222-222222222222');
        $questionId3 = QuestionId::fromString('33333333-3333-3333-3333-333333333333');

        $questionAsked1 = new QuestionAsked($userId, $questionId1);
        $questionAsked2 = new QuestionAsked($userId, $questionId2);
        $questionAsked3 = new QuestionAsked($userId, $questionId2);
        $questionAskedOtherUser = new QuestionAsked($anotherUserId, $questionId1);

        $questionAnswered1 = new QuestionAnswered($userId, $questionId1, true);
        $questionAnswered2 = new QuestionAnswered($userId, $questionId2, true);
        $questionAnswered3f = new QuestionAnswered($userId, $questionId3, false);
        $questionAnswered3t = new QuestionAnswered($userId, $questionId3, true);
        $noQuestion = new QuizCompleted($userId);
        $questionAnsweredOtherUser = new QuestionAnswered($anotherUserId, $questionId1, true);

        $quizEventStore->add($questionAsked1);
        $quizEventStore->add($questionAnswered1);
        $quizEventStore->add($questionAsked2);
        $quizEventStore->add($questionAnswered2);
        $quizEventStore->add($questionAskedOtherUser);
        $quizEventStore->add($questionAnsweredOtherUser);
        $quizEventStore->add($questionAsked3);
        $quizEventStore->add($questionAnswered3f);
        $quizEventStore->add($questionAnswered3t);
        $quizEventStore->add($noQuestion);

        $this->assertSameQuestionIds(
            [
                $questionId1,
                $questionId2,
                $questionId3,
            ],
            $quizEventStore->correctlyAnsweredQuestionsByUser($userId)
        );

        $this->assertSameQuestionIds([$questionId1], $quizEventStore->correctlyAnsweredQuestionsByUser($anotherUserId));
    }

    /**
     * @param QuizEvent[] $expectedEvents
     * @param QuizEvent[] $actualEvents
     */
    private function assertSameEvents(array $expectedEvents, array $actualEvents): void
    {
        $this->assertSameSize($expectedEvents, $actualEvents);
        foreach ($expectedEvents as $i => $expectedEvent) {
            $actualEvent = $actualEvents[$i];
            $this->assertInstanceOf(get_class($expectedEvent), $actualEvent);
            if ($expectedEvent instanceof QuestionAsked) {
                $this->assertTrue($expectedEvent->userId()->equals($actualEvent->userId()));
                $this->assertTrue($expectedEvent->questionId()->equals($actualEvent->questionId()));
            }
            if ($expectedEvent instanceof QuestionAnswered) {
                $this->assertTrue($expectedEvent->userId()->equals($actualEvent->userId()));
                $this->assertTrue($expectedEvent->questionId()->equals($actualEvent->questionId()));
                $this->assertEquals($expectedEvent->isCorrect(), $actualEvent->isCorrect());
            }
            if ($expectedEvent instanceof QuizCompleted) {
                $this->assertTrue($expectedEvent->userId()->equals($actualEvent->userId()));
            }
        }
    }

    private function assertSameQuestionIds(array $expectedQuestionIds, array $actualQuestionIds): void
    {
        $this->assertContainsOnlyInstancesOf(QuestionId::class, $expectedQuestionIds);
        $this->assertSameSize($expectedQuestionIds, $actualQuestionIds);
        foreach ($expectedQuestionIds as $i => $expectedQuestionId) {
            $actualQuestionId = $actualQuestionIds[$i];
            $this->assertTrue($expectedQuestionId->equals($actualQuestionId));
        }
    }
}
