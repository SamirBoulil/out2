<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

use Assert\Assertion;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Answer
{
    /** @var ExternalUserId */
    protected $externalUserId;

    public static function fromString(string $id): Answer
    {
        Assertion::notEmpty($id);

        $answer = new self();
        $answer->externalUserId = ExternalUserId::fromString($id) ;

        return $answer;
    }

    public function __toString(): string
    {
        return (string) $this->externalUserId;
    }

    public function equals($answer): bool
    {
        return $answer instanceof Answer &&
            $this->externalUserId->equals($answer->externalUserId);
    }
}
