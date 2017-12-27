<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidExternalUserId extends \InvalidArgumentException
{
    /** @var string */
    private $id;

    public static function fromString(string $id): InvalidExternalUserId
    {
        $e = new self(sprintf('User not found for external id "%s".', $id));
        $e->id = $id;

        return $e;
    }

    public function id(): string
    {
        return $this->id;
    }
}
