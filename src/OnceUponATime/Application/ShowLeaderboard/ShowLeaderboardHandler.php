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
        $guessesCountForUser = $this->quizEventStore->answersCountAll($user->id());
        foreach ($guessesCountForUser as $questionId => $guessesCount) {
            $points += $this->pointsForAnswersCount($guessesCount);
        }

        return Rank::assign($user->id(), Points::fromInteger($points));
    }

    /**
     * A user can earn 3 points max:
     * - 3 points if he finds the answer without any clue (guessesCount = 1)
     * - 2 points if he finds the answer with 1 clue (guessesCount = 2)
     * - 1 points if he finds the answer with 2 clue (guessesCount = 3)
     */
    private function pointsForAnswersCount(int $answersCount): int
    {
        return 3 - ($answersCount-1);
    }
}
