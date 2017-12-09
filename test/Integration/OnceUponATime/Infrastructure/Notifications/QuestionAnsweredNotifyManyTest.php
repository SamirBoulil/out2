<?php

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Infrastructure\Notifications\QuestionAnsweredNotifyMany;
use PHPUnit\Framework\TestCase;
use Tests\Unit\OnceUponATime\Application\TestEventSubscriber;

class QuestionAnsweredNotifyManyTest extends TestCase
{
    /**
     * @test
     */
    public function it_notifies_multiple_subscriber_a_question_is_answered()
    {
        $subscriber1 = new TestEventSubscriber();
        $subscriber2 = new TestEventSubscriber();
        $questionAnswered = $this->createQuestionAnswered();

        $questionAnsweredNotifyMany = new QuestionAnsweredNotifyMany([$subscriber1, $subscriber2]);
        $questionAnsweredNotifyMany->questionAnswered($questionAnswered);

        $this->assertTrue($subscriber1->isQuestionAnswered);
        $this->assertTrue($subscriber2->isQuestionAnswered);
    }

    private function createQuestionAnswered(): QuestionAnswered
    {
        return new QuestionAnswered(
            UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            true
        );
    }
}
