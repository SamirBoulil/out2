<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AnswerQuestion;

use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

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

    public function handle(AnswerQuestion $answerQuestion): bool
    {
        $user = $this->getUser($answerQuestion);
        if ($this->userHasCompletedQuiz($user)) {
            throw NoQuestionToAnswer::fromString((string)$user->id());
        }

        $isCorrect = $this->answerQuestion($answerQuestion, $user);

        return $isCorrect;
    }

    /**
     * @throws InvalidUserId
     */
    private function getUser(AnswerQuestion $answerQuestion): User
    {
        $user = $this->userRepository->byId(UserId::fromString($answerQuestion->userId));
        if (null === $user) {
            throw InvalidUserId::fromString($answerQuestion->userId);
        }

        return $user;
    }

    private function answerQuestion(AnswerQuestion $answerQuestion, User $user): bool
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

    private function userHasCompletedQuiz(User $user): bool
    {
        return $this->quizEventStore->isQuizCompleted($user->id());
    }
}
