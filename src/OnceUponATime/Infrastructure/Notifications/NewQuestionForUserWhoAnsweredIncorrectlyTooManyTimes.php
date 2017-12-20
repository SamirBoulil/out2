<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\Notifications;

use OnceUponATime\Application\AnswerQuestion\QuestionAnsweredNotify;
use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizEventStore;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NewQuestionForUserWhoAnsweredIncorrectlyTooManyTimes implements QuestionAnsweredNotify
{
    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var AskQuestionHandler */
    private $askQuestionHandler;

    public function __construct(QuizEventStore $quizEventStore, AskQuestionHandler $askQuestionHandler)
    {
        $this->quizEventStore = $quizEventStore;
        $this->askQuestionHandler = $askQuestionHandler;
    }

    public function questionAnswered(QuestionAnswered $event): void
    {
        $answersCount = $this->quizEventStore->answersCount($event->userId());
        if ($this->tooManyTries($answersCount)) {
            $newQuestionForUser = new AskQuestion();
            $newQuestionForUser->userId = (string) $event->userId();
            $this->askQuestionHandler->handle($newQuestionForUser);
        }
    }

    private function tooManyTries($answersCount): bool
    {
        return $answersCount >= 2;
    }
}
