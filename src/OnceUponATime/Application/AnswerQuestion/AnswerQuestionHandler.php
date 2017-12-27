<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AnswerQuestion;

use OnceUponATime\Application\InvalidExternalUserId;
use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
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
    private $questionsAnsweredEventStore;

    /** @var QuestionAnsweredNotify */
    private $notify;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizEventStore $questionsAnsweredEventStore,
        QuestionAnsweredNotify $notify
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->questionsAnsweredEventStore = $questionsAnsweredEventStore;
        $this->notify = $notify;
    }

    public function handle(AnswerQuestion $answerQuestion): bool
    {
        $user = $this->getUser($answerQuestion);
        $question = $this->getCurrentQuestionForUser($user);
        $answer = $this->getAnswer($answerQuestion);
        $isCorrect = $question->isCorrect($answer);
        $this->notify->questionAnswered(new QuestionAnswered($user->id(), $question->id(), $isCorrect));

        // TODO: Should it really return something ? (CQRS behavior?)
        // TODO: Should it be something as simple as a boolean ? or an object related to the handler instead of a primitive type ?
        return $isCorrect;
    }

    /**
     * @throws InvalidExternalUserId
     */
    private function getUser(AnswerQuestion $answerQuestion): User
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($answerQuestion->externalUserId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($answerQuestion->externalUserId);
        }

        return $user;
    }

    private function getCurrentQuestionForUser(User $user): Question
    {
        $questionId = $this->questionsAnsweredEventStore->questionToAnswerForUser($user->id());
        if (null === $questionId) {
            throw NoQuestionToAnswer::fromString((string) $user->id());
        }

        return $this->questionRepository->byId($questionId);
    }

    private function getAnswer(AnswerQuestion $answerQuestion): Answer
    {
        return Answer::fromString($answerQuestion->answer);
    }
}
