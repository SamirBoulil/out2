<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AnswerQuestion;

use OnceUponATime\Application\InvalidExternalUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\UI\CLI\AnswerQuestionConsoleHandler;

/**
 * TODO: This handler returns whether the answer is right for the question. does it make sense ? shouldn't this be
 *       returned (somehow) via the event thrown ?
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnswerQuestionHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var QuestionAnsweredNotify */
    private $notify;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizEventStore $quizEventStore,
        QuestionAnsweredNotify $notify
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->quizEventStore = $quizEventStore;
        $this->notify = $notify;
    }

    public function handle(AnswerQuestion $answerQuestion): AnswerQuestionHandlerResponse
    {
        $user = $this->getUser($answerQuestion);
        if ($this->userHasCompletedQuiz($user)) {
            return $this->createResponse(false, true);
        }

        $isCorrect = $this->answerQuestion($answerQuestion, $user);

        // TODO: Should it really return something ? (CQRS behavior?)
        // TODO: Should it be something as simple as a boolean ? or an object related to the handler instead of a primitive type ?
        return $this->createResponse($isCorrect, false);
    }

    /**
     * @throws InvalidExternalUserId
     */
    private function getUser(AnswerQuestion $answerQuestion): User
    {
        $user = $this->userRepository->byId(UserId::fromString($answerQuestion->userId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($answerQuestion->userId);
        }

        return $user;
    }

    private function userHasCompletedQuiz(User $user): bool
    {
        return $this->quizEventStore->isQuizCompleted($user->id());
    }

    private function createResponse(bool $isCorrect, bool $isQuizCompleted): AnswerQuestionHandlerResponse
    {
        $answer = new AnswerQuestionHandlerResponse();
        $answer->isCorrect = $isCorrect;
        $answer->isQuizCompleted = $isQuizCompleted;

        return $answer;
    }

    private function answerQuestion(AnswerQuestion $answerQuestion, $user): bool
    {
        $question = $this->getCurrentQuestionForUser($user);
        $answer = Answer::fromString($answerQuestion->answer);
        $isCorrect = $question->isCorrect($answer);
        $this->notify->questionAnswered(new QuestionAnswered($user->id(), $question->id(), $isCorrect));

        return $isCorrect;
    }

    private function getCurrentQuestionForUser(User $user): Question
    {
        $questionId = $this->quizEventStore->questionToAnswerForUser($user->id());
        if (null === $questionId) {
            throw NoQuestionToAnswer::fromString((string) $user->id());
        }

        return $this->questionRepository->byId($questionId);
    }
}
