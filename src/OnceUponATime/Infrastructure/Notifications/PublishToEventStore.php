<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\QuestionAnsweredNotify;
use OnceUponATime\Application\QuestionAskedNotify;
use OnceUponATime\Domain\Entity\NextQuestionSelected;
use OnceUponATime\Domain\Event\QuestionAnswered;
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

    public function nextQuestionSelected(NextQuestionSelected $event): void
    {
        $this->eventStore->add($event);
    }
}
