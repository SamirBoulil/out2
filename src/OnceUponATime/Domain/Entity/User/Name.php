<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\User;

use Assert\Assertion;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Name
{
    /** @var string */
    private $text;

    public static function fromString($text): Name
    {
        Assertion::notEmpty($text);

        $name = new self();
        $name->text = $text;

        return $name;
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
