<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NoQuestionToAnswer extends \LogicException
{
    /** @var string */
    private $userId;

    public static function fromString(string $userId): NoQuestionToAnswer
    {
        $e = new self(sprintf('No question to answer for user id "%s".', $userId));
        $e->userId = $userId;

        return $e;
    }

    public function userId(): string
    {
        return $this->userId;
    }
}
