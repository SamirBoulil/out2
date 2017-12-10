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
    private $nextQuestionHandler;

    public function __construct(AskQuestionHandler $nextQuestionHandler)
    {
        $this->nextQuestionHandler = $nextQuestionHandler;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        if ($event->isCorrect()) {
            $this->askNewQuestion($event);
        }
    }

    private function askNewQuestion(QuestionAnswered $event): void
    {
        $nextQuestion = new AskQuestion();
        $nextQuestion->userId = (string) $event->userId();
        $this->nextQuestionHandler->handle($nextQuestion);
    }
}
