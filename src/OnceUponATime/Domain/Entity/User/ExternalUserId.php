<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\User;

use Assert\Assertion;
use Assert\AssertionFailedException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalUserId
{
    /** @var string */
    private $id;

    private function __construct()
    {
    }

    /**
     * @throws AssertionFailedException
     */
    public static function fromString($id): ExternalUserId
    {
        Assertion::notEmpty($id);

        $slackUserId = new self();
        $slackUserId->id = $id;

        return $slackUserId;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals($userId): bool
    {
        return $userId instanceof self && (string) $this === (string) $userId;
    }
}
