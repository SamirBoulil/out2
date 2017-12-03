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
    private $userId;

    /** @var bool */
    private $isCorrect;

    public function __construct(UserId $userId, QuestionId $questionId, bool $isCorrect)
    {
        $this->questionId = $questionId;
        $this->userId = $userId;
        $this->isCorrect = $isCorrect;
    }

    public function questionId(): QuestionId
    {
        return $this->questionId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }
}
