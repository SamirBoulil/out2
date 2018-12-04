<?php

declare(strict_types=1);

namespace OnceUponATime\Domain\Event;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;

interface QuizEventStore
{
    public function add(quizEvent $event): void;

    public function byUser(UserId $userId): array;

    public function questionToAnswerForUser(UserId $userId): ?QuestionId;

    public function answersCount(UserId $id): ?int;

    public function answersCountAll(UserId $userId): array;

    public function correctlyAnsweredQuestionsByUser(UserId $userId): array;

    public function isQuizCompleted(UserId $userId): bool;

    public function all(): array;
}
