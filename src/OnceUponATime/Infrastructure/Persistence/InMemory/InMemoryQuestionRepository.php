<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Repository\QuestionRepositoryInterface;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuestionRepository implements QuestionRepositoryInterface
{
    /** @var array */
    private $questions = [];

    public function add(Question $question): void
    {
        $this->questions[(string) $question->id()] = $question;
    }

    public function byId(QuestionId $questionId): Question
    {
        return $this->questions[(string) $questionId];
    }

    public function all(): array
    {
        return array_values($this->questions);
    }

    public function nextIdentity(): QuestionId
    {
        return QuestionId::fromString((string) Uuid::uuid4());
    }
}
