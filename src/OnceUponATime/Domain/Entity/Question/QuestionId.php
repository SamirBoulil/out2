<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity\Question;

use Assert\Assertion;
use Assert\AssertionFailedException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuestionId
{
    /**
     * @var string
     */
    private $id;

    private function __construct()
    {
    }

    public static function fromString(string $id): QuestionId
    {
        Assertion::notEmpty($id);
        Assertion::uuid($id);

        $userId = new self();
        $userId->id = $id;

        return $userId;
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
