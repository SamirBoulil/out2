<?php

declare(strict_types=1);

namespace OnceUponATime\Application\ShowLeaderboard;

use OnceUponATime\Domain\Entity\Leaderboard\Leaderboard;
use OnceUponATime\Domain\Entity\Leaderboard\Points;
use OnceUponATime\Domain\Entity\Leaderboard\Rank;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowLeaderboardHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    public function __construct(UserRepository $userRepository, QuizEventStore $quizEventStore)
    {
        $this->userRepository = $userRepository;
        $this->quizEventStore = $quizEventStore;
    }

    public function handle(): Leaderboard
    {
        $leaderboard = new Leaderboard();
        foreach ($this->userRepository->all() as $user) {
            $rank = $this->rankForUser($user);
            $leaderboard->add($rank);
        }

        return $leaderboard;
    }

    private function rankForUser(User $user): Rank
    {
        $points = 0;
        $answersCountForUser = $this->quizEventStore->answersCountAll($user->id());
        foreach ($answersCountForUser as $questionId => $answersCount) {
            $points += $this->pointsForAnswersCount($answersCount);
        }

        return Rank::assign($user->id(), Points::fromInteger($points));
    }

    /**
     * A user can earn 3 points max:
     * - 3 points if he finds the answer without any clue (answersCount = 1)
     * - 2 points if he finds the answer with 1 clue (answersCount = 2)
     * - 1 points if he finds the answer with 2 clue (answersCount = 3)
     *
     * TODO: This kind of rule should probably live in the domain layer ?
     */
    private function pointsForAnswersCount(int $answersCount): int
    {
        return 3 - ($answersCount - 1);
    }
}
