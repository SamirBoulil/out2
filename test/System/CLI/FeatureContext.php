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
use OnceUponATime\Domain\Repository\QuestionRepository;
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

    private function createQuestion($question)
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

    /**
     * @When /^I run the command "([^"]*)" with the following arguments:$/
     */
    public function iRunTheCommandWithTheFollowingArguments($commandName, TableNode $arguments)
    {
        $this->commandTester = $this->getCommandTester($commandName);
        $arguments = $this->getArguments($arguments);
        $this->commandTester->execute($arguments);
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

    /**
     * @Then /^I should see the text "([^"]*)"$/
     */
    public function iShouldSeeTheText($expectedText)
    {
        $current = trim($this->commandTester->getDisplay());

        if ($current !== $expectedText) {
            throw new LogicException(sprintf('Current output is: "%s"', $current));
        }
    }
}
