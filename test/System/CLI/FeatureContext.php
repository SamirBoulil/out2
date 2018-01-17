<?php

declare(strict_types=1);

namespace Tests\System\CLI;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
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
use OnceUponATime\Domain\Event\QuestionAnswered;
use OnceUponATime\Domain\Event\QuestionAsked;
use OnceUponATime\Domain\Event\QuizCompleted;
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
    public function iRunTheCommandWithTheFollowingArguments(string $commandName, TableNode $arguments)
    {
        $this->commandTester = $this->getCommandTester($commandName);
        $arguments = $this->getArguments($arguments);
        $this->commandTester->execute($arguments);
    }

    /**
     * @When /^I run the command "([^"]*)":$/
     */
    public function iRunTheCommand(string $commandName)
    {
        $this->commandTester = $this->getCommandTester($commandName);
        $this->commandTester->execute([]);
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
     * @Then /^I should see following table:$/
     */
    public function iShouldSeeFollowingTable(PyStringNode $expectedTable)
    {
        $tableOutput = explode("\n", $this->commandTester->getDisplay());

        foreach (explode("\n", (string) $expectedTable) as $i => $expectedOutput) {
            if ($expectedOutput !== $tableOutput[$i]) {
                throw new LogicException(sprintf('Current output is: "%s"', $tableOutput[$i]));
            }
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
        $quizEvent = null;
        if ('questionAsked' === $event['type']) {
            $quizEvent = $this->createQuestionAsked($event);
        }
        if ('questionAnswered' === $event['type']) {
            $quizEvent = $this->createQuestionAnswered($event);
        }
        if ('quizCompleted' === $event['type']) {
            $quizEvent = $this->createQuizCompleted($event);
        }

        if (null == $quizEvent) {
            throw new \LogicException(sprintf('Unkown event of type %s', $event['type']));
        }

        $this->container->get(QuizEventStore::class)->add($quizEvent);
    }

    /**
     * @Given /^there should be the following events in the event store:$/
     */
    public function thereShouldBeTheFollowingEventsInTheEventStore(TableNode $expectedEvents)
    {
        $events = $this->getEvents();
        foreach ($expectedEvents->getHash() as $i => $expectedEvent) {
            $this->assertSameEvent($expectedEvent, $events[$i]);
        }
    }

    private function getEvents(): array
    {
        return $this->container->get(QuizEventStore::class)->all();
    }

    private function assertSameEvent(array $expectedEvent, QuizEvent $event): void
    {
        $this->assertEventType($expectedEvent, $event);
        if (isset($expectedEvent['user_id'])) {
            $this->assertUserId($expectedEvent['user_id'], $event);
        }
        if (isset($expectedEvent['question_id']) && '' !== $expectedEvent['question_id']) {
            $this->assertQuestionId($expectedEvent['question_id'], $event);
        }
        if (isset($expectedEvent['is_correct']) && '' !== $expectedEvent['is_correct']) {
            $this->assertIsCorrect($expectedEvent['is_correct'], $event);
        }
    }

    private function assertEventType(array $expectedEvent, QuizEvent $event): void
    {
        if (('questionAsked' === $expectedEvent['type'] && !$event instanceof QuestionAsked) ||
            ('questionAnswered' === $expectedEvent['type'] && !$event instanceof QuestionAnswered) ||
            ('quizCompleted' === $expectedEvent['type'] && !$event instanceof QuizCompleted)
        ) {
            throw new \LogicException(
                sprintf('Expected event of type "%s", "%s" given', $expectedEvent['type'], get_class($event))
            );
        }
    }

    private function assertUserId(string $expectedUserId, QuizEvent $event): void
    {
        if ($expectedUserId !== (string) $event->userId()) {
            throw new \LogicException(
                sprintf('Expeceted user_id "%s", "%s" given', $expectedUserId, $event->userId())
            );
        }
    }

    private function assertQuestionId(string $expectedQuestionId, QuizEvent $event): void
    {
        if ($expectedQuestionId !== (string) $event->questionId()) {
            throw new \LogicException(
                sprintf('Expeceted user_id "%s", "%s" given', $expectedQuestionId, $event->questionId())
            );
        }
    }

    private function assertIsCorrect(string $expectedIsCorrect, QuestionAnswered $event): void
    {
        if (($expectedIsCorrect === 'true' && false === $event->isCorrect()) ||
            ($expectedIsCorrect === 'false' && true === $event->isCorrect())
        ) {
            throw new \LogicException(
                sprintf('Expeceted is_correct "%s", "%s" given', $expectedIsCorrect, $event->isCorrect())
            );
        }
    }

    private function createQuestionAsked(array $event): QuizEvent
    {
        $questionAsked = new QuestionAsked(
            UserId::fromString($event['user_id']),
            QuestionId::fromString($event['question_id'])
        );

        return $questionAsked;
    }

    private function createQuestionAnswered($event): QuizEvent
    {
        $isCorrect = null;
        if ('true' === $event['is_correct']) {
            $isCorrect = true;
        }
        if ('false' === $event['is_correct']) {
            $isCorrect = false;
        }
        $questionAnswered = new QuestionAnswered(
            UserId::fromString($event['user_id']),
            QuestionId::fromString($event['question_id']),
            $isCorrect
        );

        return $questionAnswered;
    }

    private function createQuizCompleted($event): QuizEvent
    {
        return new QuizCompleted(UserId::fromString($event['user_id']));
    }
}
