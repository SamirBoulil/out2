<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

use Assert\Assertion;

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
        return $userId instanceof ExternalUserId && (string) $this === (string) $userId;
    }
}
