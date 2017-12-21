<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Infrastructure\Notifications\QuizCompletedNotifyMany;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\OnceUponATime\Application\TestEventSubscriber;

class QuizCompletedNotifyManyTest extends TestCase
{
    /**
     * @test
     */
    public function it_notifies_subscribers_when_a_quiz_is_completed()
    {
        $testEventSubscriber1 = new TestEventSubscriber();
        $testEventSubscriber2 = new TestEventSubscriber();
        $questionAskedNotifyMany = new QuizCompletedNotifyMany([$testEventSubscriber1, $testEventSubscriber2]);

        $questionAsked = new QuizCompleted(UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'));
        $questionAskedNotifyMany->quizCompleted($questionAsked);

        $this->assertTrue($testEventSubscriber1->isQuizCompleted);
        $this->assertTrue($testEventSubscriber2->isQuizCompleted);
    }
}
