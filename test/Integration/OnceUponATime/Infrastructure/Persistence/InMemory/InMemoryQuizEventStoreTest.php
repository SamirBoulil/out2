<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuizEventStoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_add_a_question_answered_and_return_it_by_user()
    {
        $userId = UserId::fromString('11111111-1111-1111-1111-111111111111');

        $quizEventStore = new InMemoryQuizEventStore();
        $questionAnswered = new QuestionAnswered(
            $userId,
            QuestionId::fromString('22222222-2222-2222-2222-222222222222'),
            true
        );

        $quizEventStore->add($questionAnswered);
        $this->assertSame([$questionAnswered], $quizEventStore->byUser($userId));
    }

    /**
     * @test
     */
    public function it_can_add_multiple_questions_answered_and_return_them_by_user()
    {
        $quizEventStore = new InMemoryQuizEventStore();

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
        $this->assertSame(
            [$questionAnswered1, $questionAnswered2],
            $quizEventStore->byUser(UserId::fromString('11111111-1111-1111-1111-111111111111'))
        );
    }

    /**
     * @test
     */
    public function it_returns_all_the_questions_answered_by_all_users()
    {
        $quizEventStore = new InMemoryQuizEventStore();
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

        $this->assertSame(
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
     */
    public function it_returns_the_question_the_user_has_to_answer()
    {
        $quizEventStore = new InMemoryQuizEventStore();
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
        $this->assertSame($questionId2, $currentQuestionId);

        $currentQuestionId = $quizEventStore->questionToAnswerForUser($anotherUserId);
        $this->assertSame($questionId1, $currentQuestionId);
    }

    /**
     * @test
     */
    public function it_returns_the_number_of_guesses_a_user_has_made_for_the_current_question()
    {
        $quizEventStore = new InMemoryQuizEventStore();

        $userId1 = UserId::fromString('11111111-1111-1111-1111-111111111111');
        $userId2 = UserId::fromString('22222222-2222-2222-2222-222222222222');
        $newUserId = UserId::fromString('33333333-3333-3333-3333-333333333333');
        $questionId = QuestionId::fromString('11111111-1111-1111-1111-111111111111');

        $questionAskedUser1 = new QuestionAsked($userId1, $questionId);
        $questionAskedUser2 = new QuestionAsked($userId2, $questionId);
        $questionAskedNewUser = new QuestionAsked($userId2, $questionId);

        $questionAnsweredUser1 = new QuestionAnswered($userId1, $questionId, false);
        $questionAnsweredUser2 = new QuestionAnswered($userId2, $questionId, false);

        $quizEventStore->add($questionAskedUser1);
        $quizEventStore->add($questionAskedUser2);
        $quizEventStore->add($questionAskedNewUser);

        $quizEventStore->add($questionAnsweredUser1);

        $quizEventStore->add($questionAnsweredUser2);
        $quizEventStore->add($questionAnsweredUser2);

        $guessesCount = $quizEventStore->guessesCountForCurrentQuestionAndUser($userId1);
        $this->assertSame(1, $guessesCount);

        $guessesCount = $quizEventStore->guessesCountForCurrentQuestionAndUser($userId2);
        $this->assertSame(2, $guessesCount);

        $guessesCount = $quizEventStore->guessesCountForCurrentQuestionAndUser($newUserId);
        $this->assertSame(0, $guessesCount);
    }
}
