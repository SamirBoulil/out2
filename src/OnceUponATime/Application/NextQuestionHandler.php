<?php

declare(strict_types=1);

namespace OnceUponATime\Application;

use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionAnswered;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\EventStore\QuestionsAnsweredEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NextQuestionHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuestionsAnsweredEventStore */
    private $questionsAnswered;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuestionsAnsweredEventStore $questionsAnswered
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->questionsAnswered = $questionsAnswered;
    }

    public function handle(NextQuestion $nextQuestion): ?Question
    {
        $user = $this->getUser($nextQuestion);
        $unansweredQuestions = $this->findUnansweredQuestions($user->id());
        if (empty($unansweredQuestions)) {
            return null;
        }

        return $this->pickRandomQuestion($unansweredQuestions);
    }

    private function pickRandomQuestion(array $questions): Question
    {
        return $questions[array_rand($questions)];
    }

    private function findUnansweredQuestions(UserId $userId): array
    {
        $unansweredQuestions = [];
        $questions = $this->questionRepository->all();
        foreach ($questions as $question) {
            if (!$this->isQuestionAlreadyAnsweredByUser($question, $userId)) {
                 $unansweredQuestions[] = $question;
            }
        }

        return $unansweredQuestions;
    }

    private function isQuestionAlreadyAnsweredByUser(Question $question, UserId $userId): bool
    {
        $answeredQuestions = $this->questionsAnswered->byUser($userId);
        $answeredQuestionIds = array_map(function (QuestionAnswered $question) {
            return (string) $question->questionId();
        }, $answeredQuestions);

        return in_array((string) $question->id(), $answeredQuestionIds);
    }

    private function getUser(NextQuestion $nextQuestion): User
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($nextQuestion->externalUserId));
        if (null === $user) {
            throw InvalidExternalUserId::fromString($nextQuestion->externalUserId);
        }

        return $user;
    }
}
