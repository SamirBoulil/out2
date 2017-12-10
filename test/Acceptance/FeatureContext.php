<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use LogicException;
use OnceUponATime\Application\AnswerQuestion\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestion\AnswerQuestionHandler;
use OnceUponATime\Application\InvalidQuestionId;
use OnceUponATime\Application\InvalidUserId;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\Question\Statement;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Notifications\QuestionAnsweredNotifyMany;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuizEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext implements Context
{
    /** @var UserRepository */
    private $userRepository;

    /** @var QuestionRepository */
    private $questionRepository;

    /** @var AnswerQuestionHandler */
    private $questionHandler;

    /** @var InMemoryQuizEventStore */
    private $questionsAnswered;

    /** @var bool */
    private $isQuestionIdInvalid;

    /** @var bool */
    private $isUserIdInvalid;

    public function __construct()
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->questionRepository = new InMemoryQuestionRepository();
        $this->questionsAnswered = new InMemoryQuizEventStore();
        $notifier = new QuestionAnsweredNotifyMany([new PublishToEventStore($this->questionsAnswered)]);
        $this->questionHandler = new AnswerQuestionHandler(
            $this->userRepository,
            $this->questionRepository,
            $this->questionsAnswered,
            $notifier
        );
    }

    /**
     * @Given /^the user "([^"]*)" is a registered user with id "([^"]*)" and SlackId "([^"]*)"$/
     */
    public function theUserIsARegisteredUserWithId(string $username, string $userId, string $slackId): void
    {
        $user = User::register(
            UserId::fromString($userId),
            ExternalUserId::fromString($slackId),
            Name::fromString($username)
        );
        $this->userRepository->add($user);
    }

    /**
     * @Given /^the questions?:$/
     */
    public function theQuestion(TableNode $questions): void
    {
        foreach ($questions as $questionHash) {
            $question = $this->createQuestionFromHash($questionHash);
            $this->questionRepository->add($question);
        }
    }

    private function createQuestionFromHash(array $row): Question
    {
        return Question::ask(
            QuestionId::fromString($row['id']),
            Statement::fromString($row['statement']),
            Answer::fromString($row['answer']),
            Clue::FromString($row['clue1']),
            Clue::FromString($row['clue2'])
        );
    }

    /**
     * @When /^the user "([^"]*)" answers "([^"]*)"$/
     */
    public function theUserAnswers(string $userId, string $answer): void
    {
        $answerQuestion = new AnswerQuestion();
        $answerQuestion->userId = $userId;
        $answerQuestion->answer = $answer;

        try {
            $this->questionHandler->handle($answerQuestion);
        } catch (InvalidUserId $e) {
            $this->isUserIdInvalid = true;
        }
    }

    /**
     * @Then /^the question "([^"]*)" should be answered by the user "([^"]*)" with a correct answer$/
     */
    public function theQuestionShouldBeAnsweredByTheUserWithACorrectAnswer(string $questionId, string $externalUserId)
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalUserId));
        $answeredQuestion = $this->answeredQuestionRepository->findByUserIdAndQuestionId($user, $questionId);

        if (null !== $answeredQuestion) {
            throw new \RuntimeException('We found no answered questions matching the arguments.');
        }
        if (true !== $answeredQuestion->isCorrect()) {
            throw new \RuntimeException('The question we found was not incorrect.');
        }
    }

    /**
     * @Then /^there is a question answered ([^"]*) by the user "([^"]*)" for the question "([^"]*)"$/
     */
    public function thereIsAQuestionAnsweredByTheUserForTheQuestion(
        string $answerResult,
        string $externalUserId,
        string $questionId
    ) {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalUserId));
        if (null === $user) {
            throw new \LogicException(
                sprintf('Expected valid user external id. "%s" given.', $externalUserId)
            );
        }

        $question = $this->questionRepository->byId(QuestionId::fromString($questionId));
        if (null === $question) {
            throw new \LogicException(
                sprintf('Expected valid question id. "%s" given.', $questionId)
            );
        }

        $answers = $this->questionsAnswered->byUser($user->id());
        foreach ($answers as $answer) {
            if ($answer instanceof QuestionAnswered &&
                $user->id()->equals($answer->userId()) &&
                $question->id()->equals($answer->questionId()) &&
                $this->isAnswerSame($answer->isCorrect(), $answerResult)
            ) {
                return true;
            }
        }

        throw new \LogicException(
            sprintf('Answer for user external id "%s" and question id "%s" not found.', $externalUserId, $questionId)
        );
    }

    private function isAnswerSame(bool $expectedResult, string $answerResult): bool
    {
        return ($answerResult === "correctly" && $expectedResult) ||
            ($answerResult === "incorrectly" && !$expectedResult);
    }

    /**
     * @Then /^there should be no answer for user "([^"]*)"$/
     */
    public function thereShouldBeNoAnswerForUser(string $externalUserId)
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalUserId));
        if (null === $user) {
            throw new \LogicException(
                sprintf('Expected valid user external id. "%s" given.', $externalUserId)
            );
        }

        $answers = $this->questionsAnswered->byUser($user->id());
        if (!empty($answers)) {
            throw new LogicException(
                sprintf('Expected answers for users to be empty. "%s" answers found', count($answers))
            );
        }
    }

    /**
     * @Given /^there should be no question answered$/
     */
    public function thereShouldBeNoQuestionAnswered()
    {
        $questionsAnsweredCount = count($this->questionsAnswered->all());
        if (0 !== $questionsAnsweredCount) {
            throw new \RuntimeException(
                sprintf('Expected questions answered event store to be empty. %s found.', $questionsAnsweredCount)
            );
        }
    }

    /**
     * @Then /^the user id should not be known$/
     */
    public function theUserIdShouldNotBeKnown()
    {
        if (!$this->isUserIdInvalid) {
            throw new \RuntimeException('Handler should have thrown for unknown user id');
        }
    }

    /**
     * @Given /^the question "([^"]*)" has been asked to the user "([^"]*)"$/
     */
    public function theQuestionHasBeenAskedToTheUser($questionId, $userId)
    {
        $questionAsked = new QuestionAsked(UserId::fromString($userId), QuestionId::fromString($questionId));
        $this->questionsAnswered->add($questionAsked);
    }
}
