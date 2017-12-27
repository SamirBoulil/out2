<?php

declare(strict_types=1);

namespace Tests\System\CLI;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use LogicException;
use OnceUponATime\Domain\Entity\Question\Answer;
use OnceUponATime\Domain\Entity\Question\Clue;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\Question\QuestionId;
use OnceUponATime\Domain\Entity\Question\Statement;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizEvent;
use OnceUponATime\Domain\Event\QuizEventStore;
use OnceUponATime\Domain\Repository\QuestionRepository;
use OnceUponATime\Domain\Repository\UserRepository;
use OnceUponATime\Infrastructure\Common\OnceUponATimeApplicationContainer;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext implements Context
{
    /** @var OnceUponATimeApplicationContainer */
    private $container;

    /** @var CommandTester */
    private $commandTester;

    public function __construct()
    {
        $this->container = new OnceUponATimeApplicationContainer();
        $this->commandTester = null;
    }

    /**
     * @Given /^the following questions:$/
     */
    public function theFollowingQuestions(TableNode $questions)
    {
        foreach ($questions->getHash() as $question) {
            $this->createQuestion($question);
        }
    }

    /**
     * @When /^I run the command "([^"]*)" with the following arguments:$/
     */
    public function iRunTheCommandWithTheFollowingArguments($commandName, TableNode $arguments)
    {
        $this->commandTester = $this->getCommandTester($commandName);
        $arguments = $this->getArguments($arguments);
        $this->commandTester->execute($arguments);
    }

    /**
     * @Then /^I should see the text "(.*)"$/
     */
    public function iShouldSeeTheText($expectedText)
    {
        $current = trim($this->commandTester->getDisplay());

        if (false === strpos($current, $expectedText)) {
            throw new LogicException(sprintf('Current output is: "%s"', $current));
        }
    }

    /**
     * @Given /^the following users:$/
     */
    public function theFollowingUsers(TableNode $users)
    {
        foreach ($users->getHash() as $question) {
            $this->createUser($question);
        }
    }

    /**
     * @Given /^the following events:$/
     */
    public function theFollowingEvents(TableNode $events)
    {
        foreach ($events->getHash() as $event) {
            $this->addEvent($event);
        }
    }

    private function createQuestion(array $question): void
    {
        $question = Question::ask(
            QuestionId::fromString($question['id']),
            Statement::fromString($question['statement']),
            Answer::fromString($question['answer']),
            Clue::fromString($question['clue_1']),
            Clue::fromString($question['clue_2'])
        );
        $this->container->get(QuestionRepository::class)->add($question);
    }

    private function createUser(array $user): void
    {
        $user = User::register(
            UserId::fromString($user['id']),
            ExternalUserId::fromString($user['external_user_id']),
            Name::fromString($user['name'])
        );
        $this->container->get(UserRepository::class)->add($user);
    }

    private function getCommandTester(string $commandName): CommandTester
    {
        $command = $this->container->getConsoleApplication()->find($commandName);

        return new CommandTester($command);
    }

    private function getArguments(TableNode $arguments): array
    {
        $options = [];
        foreach ($arguments->getHash() as $argument) {
            $options[$argument['argument']] = $argument['value'];
        }

        return $options;
    }

    private function addEvent(array $event): void
    {
        if ('questionAsked' === $event['type']) {
            $event = $this->createQuestionAsked($event);
        }

        $this->container->get(QuizEventStore::class)->add($event);
    }

    private function createQuestionAsked(array $event): QuizEvent
    {
        $questionAsked = new QuestionAsked(
            UserId::fromString($event['user_id']),
            QuestionId::fromString($event['question_id'])
        );
        return $questionAsked;
    }
}
