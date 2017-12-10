<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\InMemory;

use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Repository\QuestionRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryQuestionRepository implements QuestionRepository
{
    /** @var array */
    private $questions = [];

    public function add(Question $question): void
    {
        $this->questions[(string) $question->id()] = $question;
    }

    public function byId(QuestionId $questionId): ?Question
    {
        return $this->questions[(string) $questionId] ?? null;
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
