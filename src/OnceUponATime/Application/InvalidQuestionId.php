<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

/**
 * TODO: How do I unit tests those ? (unit/Application ?)
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidQuestionId extends \InvalidArgumentException
{
    /** @var string */
    private $id;

    public static function fromString(string $id): self
    {
        $e = new InvalidQuestionId(sprintf('Question not found for id: "%s"', $id));
        $e->id = $id;
    }

    public function id(): string
    {
        return $this->id;
    }
}
