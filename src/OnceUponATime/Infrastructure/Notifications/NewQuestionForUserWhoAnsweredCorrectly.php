<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Domain\Event\QuestionAnswered;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForUserWhoAnsweredCorrectly implements QuestionAnsweredNotify
{
    /** @var AskQuestionHandler */
    private $askQuestionHandler;

    public function __construct(AskQuestionHandler $askQuestionHandler)
    {
        $this->askQuestionHandler = $askQuestionHandler;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        if ($event->isCorrect()) {
            $this->askNewQuestion($event);
        }
    }

    private function askNewQuestion(QuestionAnswered $event): void
    {
        $askQuestion = new AskQuestion();
        $askQuestion->userId = (string) $event->userId();
        $this->askQuestionHandler->handle($askQuestion);
    }
}
