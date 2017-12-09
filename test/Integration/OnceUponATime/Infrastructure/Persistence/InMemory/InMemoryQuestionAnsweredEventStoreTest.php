<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionAnsweredEventStore;
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

        $questionAnsweredEventStore = new InMemoryQuestionAnsweredEventStore();
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
        $questionAnsweredEventStore = new InMemoryQuestionAnsweredEventStore();

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
        $questionAnsweredEventStore = new InMemoryQuestionAnsweredEventStore();

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
            [$questionAnswered1, $questionAnswered2, $questionAnswered3],
            $questionAnsweredEventStore->all()
        );
    }
}
