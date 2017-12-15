<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;

/**
 * TODO: Not sure this interface should be in domain ? (but repositories are so...)
 */
interface QuizEventStore
{
    public function add(quizEvent $event): void;

    public function byUser(UserId $userId): array;

    public function questionToAnswerForUser(UserId $userId): ?QuestionId;

    public function guessesCountForCurrentQuestionAndUser(UserId $id): int;

    public function correctlyAnsweredQuestionsByUser(UserId $userId): array;
}
