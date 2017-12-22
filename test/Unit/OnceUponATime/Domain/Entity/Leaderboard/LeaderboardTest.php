<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Leaderboard;

use OnceUponATime\Domain\Entity\Leaderboard\Leaderboard;
use OnceUponATime\Domain\Entity\Leaderboard\Points;
use OnceUponATime\Domain\Entity\Leaderboard\Rank;
use OnceUponATime\Domain\Entity\User\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LeaderboardTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_constructed_empty()
    {
        $leaderboard = new Leaderboard();
        $this->assertSame([], $leaderboard->publish());
    }

    /**
     * @test
     */
    public function it_can_add_ranks_to_it_and_generate_the_leaderboard_ordered_in_ascending()
    {
        $rank1 = $this->createRank('11111111-1111-43aa-aba3-8626fecd39e8', 5000);
        $rank2 = $this->createRank('22222222-ad15-43aa-aba3-8626fecd39e8', 2500);
        $rank3 = $this->createRank('33333333-1111-43aa-aba3-8626fecd39e8', 1000);

        $leaderboard = new Leaderboard();
        $leaderboard->add($rank2);
        $leaderboard->add($rank1);
        $leaderboard->add($rank3);

        $this->assertSame([$rank1, $rank2, $rank3], $leaderboard->publish());
    }

    private function createRank($id, $points): Rank
    {
        return Rank::assign(UserId::fromString($id), Points::fromInteger($points));
    }
}
