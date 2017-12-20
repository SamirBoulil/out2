<?php

namespace Tests\Integration\OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use PHPUnit\Framework\TestCase;

class PublishToEventStoreTest extends TestCase
{
    /**
     * @test
     */
    public function it_publishes_questions_answered_event_to_the_event_store()
    {
        $eventStore = new InMemoryQuizEventStore();
        $publisher = new PublishToEventStore($eventStore);
        $questionAnswered = $this->createQuestionAnswered();
        $publisher->questionAnswered($questionAnswered);

        $this->assertSame([$questionAnswered], $eventStore->all());
    }

    /**
     * @test
     */
    public function it_publishes_questions_asked_event_to_the_event_store()
    {
        $eventStore = new InMemoryQuizEventStore();
        $publisher = new PublishToEventStore($eventStore);
        $questionAsked = $this->createQuestionAsked();
        $publisher->questionAsked($questionAsked);

        $this->assertSame([$questionAsked], $eventStore->all());
    }

    /**
     * @test
     */
    public function it_publishes_no_questions_left_event_to_the_event_store()
    {
        $eventStore = new InMemoryQuizEventStore();
        $publisher = new PublishToEventStore($eventStore);
        $quizCompleted = $this->createQuizCompleted();
        $publisher->quizCompleted($quizCompleted);

        $this->assertSame([$quizCompleted], $eventStore->all());
    }

    private function createQuestionAnswered(): QuestionAnswered
    {
        return new QuestionAnswered(
            UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            true
        );
    }

    private function createQuestionAsked(): QuestionAsked
    {
        return new QuestionAsked(
            UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'),
            QuestionId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9')
        );
    }

    private function createQuizCompleted(): QuizCompleted
    {
        return new QuizCompleted(UserId::fromString('7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9'));
    }
}
