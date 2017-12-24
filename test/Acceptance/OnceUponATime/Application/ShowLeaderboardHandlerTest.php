<?php

declare(strict_types=1);

namespace Tests\Acceptance\OnceUponATime\Application;

use OnceUponATime\Application\ShowLeaderboard\ShowLeaderboardHandler;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowLeaderboardHandlerTest extends TestCase
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    public function setUp()
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->quizEventStore = new InMemoryQuizEventStore();
    }

    /**
     * @test
     */
    public function it_shows_an_emty_leaderboard()
    {
        $ranks = $this->publishLeaderboard();
        $this->assertEmpty($ranks);
    }

    /**
     * @test
     */
    public function it_calculates_1_points_if_the_user_answered_incorrectly_two_times()
    {
        $this->registerUserWithId('11111111-1111-1111-1111-111111111111');
        $this->addQuestionAsked('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', false);
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', false);
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', true);

        $ranks = $this->publishLeaderboard();

        $this->assertCount(1, $ranks);
        $rank = current($ranks);
        $this->assertSame('11111111-1111-1111-1111-111111111111', (string) $rank->userId());
        $this->assertSame('1', (string) $rank->points());
    }

    /**
     * @test
     */
    public function it_calculates_2_points_if_the_user_answered_incorrectly_once()
    {
        $this->registerUserWithId('11111111-1111-1111-1111-111111111111');
        $this->addQuestionAsked('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', false);
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', true);

        $ranks = $this->publishLeaderboard();

        $this->assertCount(1, $ranks);
        $rank = current($ranks);
        $this->assertSame('11111111-1111-1111-1111-111111111111', (string) $rank->userId());
        $this->assertSame('2', (string) $rank->points());
    }

    /**
     * @test
     */
    public function it_calculates_3_points_if_the_user_directly_answered_correctly()
    {
        $this->registerUserWithId('11111111-1111-1111-1111-111111111111');
        $this->addQuestionAsked('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', true);

        $ranks = $this->publishLeaderboard();

        $this->assertCount(1, $ranks);
        $rank = current($ranks);
        $this->assertSame('11111111-1111-1111-1111-111111111111', (string) $rank->userId());
        $this->assertSame('3', (string) $rank->points());
    }

    /**
     * @test
     */
    public function it_generates_the_leaderboard_for_multiple_users()
    {
        $this->registerUserWithId('00000000-0000-0000-0000-000000000000');
        $this->registerUserWithId('11111111-1111-1111-1111-111111111111');
        $this->registerUserWithId('22222222-2222-2222-2222-222222222222');

        $this->addQuestionAsked('00000000-0000-0000-0000-000000000000', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAsked('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAsked('22222222-2222-2222-2222-222222222222', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');
        $this->addQuestionAnswered('22222222-2222-2222-2222-222222222222', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', false);
        $this->addQuestionAnswered('11111111-1111-1111-1111-111111111111', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', true);
        $this->addQuestionAnswered('22222222-2222-2222-2222-222222222222', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', false);
        $this->addQuestionAnswered('22222222-2222-2222-2222-222222222222', 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', true);

        $ranks = $this->publishLeaderboard();

        $this->assertCount(3, $ranks);

        $this->assertSame('11111111-1111-1111-1111-111111111111', (string) $ranks[0]->userId());
        $this->assertSame('3', (string) $ranks[0]->points());

        $this->assertSame('22222222-2222-2222-2222-222222222222', (string) $ranks[1]->userId());
        $this->assertSame('1', (string) $ranks[1]->points());

        $this->assertSame('00000000-0000-0000-0000-000000000000', (string) $ranks[2]->userId());
        $this->assertSame('0', (string) $ranks[2]->points());
    }

    private function addQuestionAsked($userId, $questionId): void
    {
        $this->quizEventStore->add(
            new QuestionAsked(
                UserId::fromString($userId),
                QuestionId::fromString($questionId)
            )
        );
    }

    private function addQuestionAnswered($userId, $questionId, $isCorrect)
    {
        $this->quizEventStore->add(
            new QuestionAnswered(
                UserId::fromString($userId),
                QuestionId::fromString($questionId),
                $isCorrect
            )
        );
    }

    private function publishLeaderboard(): array
    {
        $leaderboardHandler = new ShowLeaderboardHandler($this->userRepository, $this->quizEventStore);

        return $leaderboardHandler->handle()->publish();
    }

    private function registerUserWithId(string $userId): void
    {
        $this->userRepository->add(User::register(
            UserId::fromString($userId),
            ExternalUserId::fromString('<@external_id>'),
            Name::fromString('My name')
        ));
    }
}
