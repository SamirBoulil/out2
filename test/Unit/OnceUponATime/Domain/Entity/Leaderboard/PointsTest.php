<?php

declare(strict_types=1);

namespace Tests\Unit\OnceUponATime\Domain\Entity\Leaderboard;

use OnceUponATime\Domain\Entity\Leaderboard\Points;
use PHPUnit\Framework\TestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PointsTest extends TestCase
{
    /**
     * @test
     */
    public function it_wrapps_an_integer()
    {
        $total = 150;
        $points = Points::fromInteger($total);
        $this->assertSame((string) $total, (string) $points);
    }

    /**
     * @test
     */
    public function it_should_be_constructed_from_an_integer()
    {
        $this->expectException(\TypeError::class);
        Points::fromInteger('wrong_input');
    }

    /**
     * @test
     */
    public function it_can_compare_points_to_one_another()
    {
        $points1 = Points::fromInteger(1);
        $points2 = Points::fromInteger(2);
        $this->assertEquals(-1, $points1->comparedTo($points2));
        $this->assertEquals(0, $points1->comparedTo($points1));
        $this->assertEquals(1, $points2->comparedTo($points1));
    }
}
