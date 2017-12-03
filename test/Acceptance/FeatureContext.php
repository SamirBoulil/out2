<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use OnceUponATime\Application\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestionHandler;
use OnceUponATime\Domain\Entity\Clue;
use OnceUponATime\Domain\Entity\ExternalUserId;
use OnceUponATime\Domain\Entity\Name;
use OnceUponATime\Domain\Entity\Question;
use OnceUponATime\Domain\Entity\QuestionId;
use OnceUponATime\Domain\Entity\Statement;
use OnceUponATime\Domain\Entity\User;
use OnceUponATime\Domain\Entity\UserId;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Notifications\NotifyMany;
use OnceUponATime\Infrastructure\Notifications\PublishToEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionAnsweredEventStore;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
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

    /** @var InMemoryQuestionAnsweredEventStore */
    private $eventStore;

    /** @var bool */
    private $isCorrect;

    /** @var bool */
    private $hasThrown;

    public function __construct()
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->questionRepository = new InMemoryQuestionRepository();
        $this->eventStore = new InMemoryQuestionAnsweredEventStore();
        $notifier = new NotifyMany([new PublishToEventStore($this->eventStore)]);
        $this->questionHandler = new AnswerQuestionHandler(
            $this->userRepository,
            $this->questionRepository,
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

    /**
     * @When /^the user "([^"]*)" answers the question "([^"]*)" with answer "([^"]*)"$/
     */
    public function theUserAnswersTheQuestionWithAnswer(string $externalId, string $questionId, string $answer): void
    {
        $this->isCorrect = false;
        $this->hasThrown = false;

        $answerQuestion = new AnswerQuestion();
        $answerQuestion->externalId = $externalId;
        $answerQuestion->questionId = $questionId;
        $answerQuestion->answer = $answer;

        try {
            $this->isCorrect = $this->questionHandler->handle($answerQuestion);
        } catch (\InvalidArgumentException $e) {
            $this->hasThrown = true;
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

    private function createQuestionFromHash(array $row): Question
    {
        return Question::ask(
            QuestionId::fromString($row['id']),
            Statement::fromString($row['statement']),
            ExternalUserId::fromString($row['answer']),
            Clue::FromString($row['clue1']),
            Clue::FromString($row['clue2'])
        );
    }

    /**
     * @Then /^the answer should be ([^"]*)$/
     */
    public function theAnswerShouldBe(string $value)
    {
        if ((true === $this->isCorrect && "correct" === $value) ||
            (false === $this->isCorrect && "incorrect" === $value)
        ) {
            return;
        }

        throw new \RuntimeException(
            sprintf('Expected answer to be %s, %s given.', $value, $this->isCorrect)
        );
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

        $answers = $this->eventStore->byUser($user->id());
        foreach ($answers as $answer) {
            if ($user->id()->equals($answer->userId()) &&
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

    private function isAnswerSame(bool $expectedResult, string $answerResult)
    {
        return ($answerResult === "correctly" && $expectedResult) ||
            ($answerResult === "incorrectly" && !$expectedResult);
    }

    /**
     * @Then /^there should be no answer$/
     */
    public function thereShouldBeNoAnswer()
    {
        if (!$this->hasThrown) {
            throw new \LogicException('Expected handler to throw.');
        }
    }
}
