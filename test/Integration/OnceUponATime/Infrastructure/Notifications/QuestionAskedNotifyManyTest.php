<?php

declare(strict_types=1);

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Infrastructure\Notifications\QuestionAskedNotifyMany;
use PHPUnit\Framework\TestCase;
use Tests\Acceptance\OnceUponATime\Application\TestEventSubscriber;

class QuestionAskedNotifyManyTest extends TestCase
{
    /**
     * @test
     */
    public function it_notifies_subscribers_when_a_user_has_been_asked_a_new_question()
    {
        $testEventSubscriber1 = new TestEventSubscriber();
        $testEventSubscriber2 = new TestEventSubscriber();
        $questionAskedNotifyMany = new QuestionAskedNotifyMany([$testEventSubscriber1, $testEventSubscriber2]);

        $questionAsked = new QuestionAsked(
            UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            QuestionId::fromString('11111111-1111-1111-1111-111111111111')
        );
        $questionAskedNotifyMany->questionAsked($questionAsked);
        $this->assertTrue($testEventSubscriber1->isQuestionAsked);
        $this->assertTrue($testEventSubscriber2->isQuestionAsked);
    }
}
