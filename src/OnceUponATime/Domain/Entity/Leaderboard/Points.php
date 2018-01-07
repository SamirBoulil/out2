<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\Leaderboard;

use Assert\Assertion;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Points
{
    /** @var int */
    private $total;

    public static function fromInteger(int $total): Points
    {
        Assertion::integer($total);

        $points = new self();
        $points->total = $total;

        return $points;
    }

    public function __toString(): string
    {
        return (string) $this->total;
    }

    public function comparedTo(Points $anotherPoint): int
    {
        if ($this->total < $anotherPoint->total) {
            return -1;
        }

        if ($this->total === $anotherPoint->total) {
            return 0;
        }

        return 1;
    }
}
