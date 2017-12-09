<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\Answer;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\User;
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

    /** @var QuestionAnsweredNotify */
    private $notify;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuestionAnsweredNotify $notify
    ) {
        $this->questionRepository = $questionRepository;
        $this->userRepository = $userRepository;
        $this->notify = $notify;
    }

    public function handle(AnswerQuestion $answerQuestion): bool
    {
        // TODO: Ok so, those throw an exception, but errors are thrown one at a time
        // what happened when user id is not ok, question id is not ok ?
        $user = $this->getUser($answerQuestion->externalId);
        $question = $this->getQuestion($answerQuestion->questionId);
        $answer = $this->getAnswer($answerQuestion);
        $isCorrect = $question->isCorrect($answer);
        $this->notify->questionAnswered(new QuestionAnswered($user->id(), $question->id(), $isCorrect));

        // TODO: Should it really return something ? (CQRS behavior?)
        // TODO: Should it be something as simple as a boolean ? or an object related to the handler instead of a
        //       primitive type ?
        return $isCorrect;
    }

    /**
     * @throws InvalidUserId
     */
    private function getUser(string $id): User
    {
        $externalUserId = ExternalUserId::fromString($id);
        $user = $this->userRepository->byExternalId($externalUserId);
        if (null === $user) {
            throw new InvalidUserId($id);
        }

        return $user;
    }

    /**
     * @throws InvalidQuestionId
     */
    private function getQuestion(string $id): Question
    {
        $questionId = QuestionId::fromString($id);
        $question = $this->questionRepository->byId($questionId);
        if (null === $question) {
            throw new InvalidQuestionId($id);
        }

        return $question;
    }

    private function getAnswer(AnswerQuestion $answerQuestion): Answer
    {
        return Answer::fromString($answerQuestion->answer);
    }
}
