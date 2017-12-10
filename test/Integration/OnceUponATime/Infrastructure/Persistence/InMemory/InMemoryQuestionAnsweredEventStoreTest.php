<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionAsked;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizzEventStore;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuestionAnsweredEventStoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_question_answered_and_return_it_by_user()
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');

        $questionAnsweredEventStore = new InMemoryQuizzEventStore();
        $questionAnswered = new QuestionAnswered(
            $userId,
            QuestionId::fromString('22222222-2222-2222-2222-222222222222'),
            true
        );

        $questionAnsweredEventStore->add($questionAnswered);
        $this->assertSame([$questionAnswered], $questionAnsweredEventStore->byUser($userId));
    }

    /**
     * @test
     */
    public function it_can_add_multiple_questions_answered_and_return_them_by_user()
    {
        $questionAnsweredEventStore = new InMemoryQuizzEventStore();

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

        $questionAnsweredEventStore->add($questionAnswered1);
        $questionAnsweredEventStore->add($questionAnswered2);
        $questionAnsweredEventStore->add($questionAnswered3);
        $this->assertSame(
            [$questionAnswered1, $questionAnswered2],
            $questionAnsweredEventStore->byUser(UserId::fromString('11111111-1111-1111-1111-111111111111'))
        );
    }

    /**
     * @test
     */
    public function it_returns_all_the_questions_answered_by_all_users()
    {
        $questionAnsweredEventStore = new InMemoryQuizzEventStore();
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

        $questionAnsweredEventStore->add($questionAsked1);
        $questionAnsweredEventStore->add($questionAnswered1);
        $questionAnsweredEventStore->add($questionAsked2);
        $questionAnsweredEventStore->add($questionAnswered2);
        $questionAnsweredEventStore->add($questionAskedOtherUser);
        $questionAnsweredEventStore->add($questionAnsweredOtherUser);

        $this->assertSame(
            [
                $questionAsked1,
                $questionAnswered1,
                $questionAsked2,
                $questionAnswered2,
                $questionAskedOtherUser,
                $questionAnsweredOtherUser,
            ],
            $questionAnsweredEventStore->all()
        );
    }

    /**
     * @test
     */
    public function it_returns_the_current_question_for_the_user()
    {
        $questionAnsweredEventStore = new InMemoryQuizzEventStore();
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

        $questionAnsweredEventStore->add($questionAsked1);
        $questionAnsweredEventStore->add($questionAnswered1);
        $questionAnsweredEventStore->add($questionAsked2);
        $questionAnsweredEventStore->add($questionAnswered2);
        $questionAnsweredEventStore->add($questionAskedOtherUser);
        $questionAnsweredEventStore->add($questionAnsweredOtherUser);

        $currentQuestionId = $questionAnsweredEventStore->currentQuestionForUser($userId);
        $this->assertSame($questionId2, $currentQuestionId);

        $currentQuestionId = $questionAnsweredEventStore->currentQuestionForUser($anotherUserId);
        $this->assertSame($questionId1, $currentQuestionId);
    }
}
