<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Persistence\Common;

use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Repository\QuestionRepository;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractQuestionRepository implements QuestionRepository
{
    abstract public function add(Question $question): void;

    abstract public function all(): array;

    public function byId(QuestionId $questionId): ?Question
    {
        foreach ($this->all() as $id => $question) {
            /** @var Question $question */
            if ($question->id()->equals($questionId)) {
                return $question;
            }
        }

        return null;
    }

    public function nextIdentity(): QuestionId
    {
        return QuestionId::fromString((string) Uuid::uuid4());
    }
}
