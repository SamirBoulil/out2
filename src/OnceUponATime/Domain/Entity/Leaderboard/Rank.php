<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\Leaderboard;

use OnceUponATime\Domain\Entity\User\UserId;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Rank
{
    /** @var UserId */
    private $userId;

    /** @var Points */
    private $points;

    public static function assign(UserId $userId, Points $points): Rank
    {
        $rank = new self();
        $rank->userId = $userId;
        $rank->points = $points;

        return $rank;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function points(): Points
    {
        return $this->points;
    }

    public function comparedTo(Rank $rank2): int
    {
        return $this->points->comparedTo($rank2->points);
    }
}
