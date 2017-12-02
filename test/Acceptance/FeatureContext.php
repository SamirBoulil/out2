<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Behat\Behat\Context\Context;
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
use OnceUponATime\Domain\Repository\QuestionRepositoryInterface;
use OnceUponATime\Domain\Repository\UserRepositoryInterface;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryQuestionRepository;
use OnceUponATime\Infrastructure\Persistence\InMemory\InMemoryUserRepository;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext implements Context
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var bool */
    private $isCorrect;

    public function __construct()
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->questionRepository = new InMemoryQuestionRepository();
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
        $questionHandler = new AnswerQuestionHandler($this->questionRepository);

        $answerQuestion = new AnswerQuestion();
        $answerQuestion->externalId = $externalId;
        $answerQuestion->questionId = $questionId;
        $answerQuestion->answer = $answer;

        $this->isCorrect = $questionHandler->handle($answerQuestion);
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
}
