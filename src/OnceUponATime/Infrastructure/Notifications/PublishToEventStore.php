<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\QuestionAskedNotify;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizzEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishToEventStore implements QuestionAnsweredNotify, QuestionAskedNotify
{
    /** @var QuizzEventStore */
    private $eventStore;

    public function __construct(QuizzEventStore $eventStore)
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
}
