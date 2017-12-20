<?php

declare(strict_types=1);

namespace OnceUponATime\Application\AskQuestion;

use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AskQuestionHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var QuizEventStore */
    private $quizEventStore;

    /** @var QuizCompletedNotify */
    private $noQuestionsLeftNotify;

    /** @var QuestionAskedNotify */
    private $questionAskedNotify;

    public function __construct(
        UserRepository $userRepository,
        QuestionRepository $questionRepository,
        QuizEventStore $quizEventStore,
        QuizCompletedNotify $noQuestionsLeftNotify,
        QuestionAskedNotify $questionAskedNotify
    ) {
        $this->userRepository = $userRepository;
        $this->questionRepository = $questionRepository;
        $this->quizEventStore = $quizEventStore;
        $this->noQuestionsLeftNotify = $noQuestionsLeftNotify;
        $this->questionAskedNotify = $questionAskedNotify;
    }

    public function handle(AskQuestion $nextQuestion): ?Question
    {
        $user = $this->getUser($nextQuestion);
        $unansweredQuestions = $this->findUnansweredQuestions($user->id());
        if (empty($unansweredQuestions)) {
            $this->noQuestionsLeftNotify->quizCompleted(new QuizCompleted($user->id()));

            return null;
        }
        $question = $this->pickRandomQuestion($unansweredQuestions);
        $this->questionAskedNotify->questionAsked(new QuestionAsked($user->id(), $question->id()));

        return $question;
    }

    private function getUser(AskQuestion $nextQuestion): User
    {
        $user = $this->userRepository->byId(UserId::fromString($nextQuestion->userId));
        if (null === $user) {
            throw InvalidUserId::fromString($nextQuestion->userId);
        }

        return $user;
    }

    private function findUnansweredQuestions(UserId $userId): array
    {
        $answeredQuestionIds = $this->quizEventStore->correctlyAnsweredQuestionsByUser($userId);
        $questions = $this->questionRepository->all();

        $unansweredQuestions = [];
        foreach ($questions as $question) {
            if (!\in_array($question->id(), $answeredQuestionIds, false)) {
                $unansweredQuestions[] = $question;
            }
        }

        return $unansweredQuestions;
    }

    private function pickRandomQuestion(array $questions): Question
    {
        return $questions[array_rand($questions)];
    }
}
