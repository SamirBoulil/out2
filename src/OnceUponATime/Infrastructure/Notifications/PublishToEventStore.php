<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\QuestionAskedNotify;
use OnceUponATime\Application\AskQuestion\QuizCompletedNotify;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishToEventStore implements QuestionAnsweredNotify, QuestionAskedNotify, QuizCompletedNotify
{
    /** @var QuizEventStore */
    private $eventStore;

    public function __construct(QuizEventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        $this->eventStore->add($event);
    }

    public function questionAsked(QuestionAsked $event): void
    {
        $this->eventStore->add($event);
    }

    public function quizCompleted(QuizCompleted $event): void
    {
        $this->eventStore->add($event);
    }
}
