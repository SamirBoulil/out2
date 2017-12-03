<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Leaderboard;

use OnceUponATime\Domain\Entity\Leaderboard\Points;
use OnceUponATime\Domain\Entity\Leaderboard\Rank;
use OnceUponATime\Domain\Entity\User\UserId;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RankTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_a_user_id_a_positition_and_a_number_of_points()
    {
        $userId = UserId::fromString('3a021c08-ad15-43aa-aba3-8626fecd39a7');
        $points = Points::fromInteger(1500);

        $rank = Rank::assign($userId, $points);

        $this->assertSame($userId, $rank->userId());
        $this->assertSame($points, $rank->points());
    }

    /**
     * @test
     */
    public function it_can_be_compared_to_another_rank()
    {
        $rank1 = $this->createRank('11111111-ad15-43aa-aba3-8626fecd39e8', 1500);
        $rank2 = $this->createRank('11111111-ad15-43aa-aba3-8626fecd39e8', 500);

        $this->assertSame(1, $rank1->comparedTo($rank2));
        $this->assertSame(0, $rank1->comparedTo($rank1));
        $this->assertSame(-1, $rank2->comparedTo($rank1));
    }

    private function createRank($id, $points): Rank
    {
        return Rank::assign(UserId::fromString($id), Points::fromInteger($points));
    }
}
