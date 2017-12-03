<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\Leaderboard;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Leaderboard
{
    /** @var array */
    private $ranks = [];

    public function publish(): array
    {
        usort($this->ranks, function (Rank $rank1, Rank $rank2) {
            return $rank2->comparedTo($rank1);
        });

        return $this->ranks;
    }

    public function addRank(Rank $rank): void
    {
        $this->ranks[] = $rank;
    }
}
