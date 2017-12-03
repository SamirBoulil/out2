<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\Notify;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\EventStore\AnsweredQuestionEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishToEventStore implements Notify
{
    /** @var AnsweredQuestionEventStore */
    private $eventStore;

    public function __construct(AnsweredQuestionEventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        $this->eventStore->add($event);
    }
}
