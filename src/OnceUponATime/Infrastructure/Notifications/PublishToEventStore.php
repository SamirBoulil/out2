<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\QuestionAnsweredNotify;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\EventStore\QuestionsAnsweredEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishToEventStore implements QuestionAnsweredNotify
{
    /** @var QuestionsAnsweredEventStore */
    private $eventStore;

    public function __construct(QuestionsAnsweredEventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        $this->eventStore->add($event);
    }
}
