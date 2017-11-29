<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

use Assert\Assertion;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Statement
{
    /** @var string */
    private $text;

    public static function fromString(string $text): Statement
    {
        Assertion::notEmpty($text);

        $statement = new self();
        $statement->text = $text;

        return $statement;
    }

    public function __toString()
    {
        return $this->text;
    }
}
