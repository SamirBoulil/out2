<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Entity;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuestionAnswered
{
    /** @var QuestionId */
    private $questionId;

    /** @var UserId */
    protected $userId;

    public function __construct(QuestionId $questionId, UserId $userId)
    {
        $this->questionId = $questionId;
        $this->userId = $userId;
    }

    public function questionId(): QuestionId
    {
        return $this->questionId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
