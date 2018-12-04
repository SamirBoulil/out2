<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\User\UserId;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuizCompleted implements QuizEvent
{
    /** @var UserId */
    private $userId;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
