<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

use Assert\Assertion;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Clue
{
    /** @var string */
    private $text;

    public static function fromString($text): Clue
    {
        Assertion::notEmpty($text);

        $clue = new self();
        $clue->text = $text;

        return $clue;
    }

    public function __toString()
    {
        return $this->text;
    }
}
