<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\AnswerQuestion\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestion\AnswerQuestionHandler;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\ShowClue\ShowClue;
use OnceUponATime\Application\ShowClue\ShowClueHandler;
use OnceUponATime\Application\ShowQuestion\ShowQuestion;
use OnceUponATime\Application\ShowQuestion\ShowQuestionHandler;
use OnceUponATime\Domain\Entity\Question\Question;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Entity\User\User;
use OnceUponATime\Domain\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AnswerQuestionConsoleHandler extends Command
{
    /** @var AnswerQuestionHandler */
    private $answerQuestionHandler;

    /** @var ShowQuestionHandler */
    private $showQuestionHandler;

    /** @var UserRepository */
    private $userRepository;

    /** @var ShowClueHandler */
    private $showClueHandler;

    public function __construct(
        UserRepository $userRepository,
        AnswerQuestionHandler $answerQuestionHandler,
        ShowQuestionHandler $showQuestionHandler,
        ShowClueHandler $showClueHandler
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->answerQuestionHandler = $answerQuestionHandler;
        $this->showQuestionHandler = $showQuestionHandler;
        $this->showClueHandler = $showClueHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName('out:answer-question')
            ->setDescription('Answer the current question')
            ->setHelp('This command checks the user\'s answer')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)')
            ->addArgument('answer', InputArgument::REQUIRED, 'The answer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $externalId = $input->getArgument('external-id');
        $answer = $input->getArgument('answer');
        $user = $this->getUser($externalId);
        if (null === $user) {
            $this->showError($externalId, $output);

            return;
        }
        if ($this->isAnswerCorrect($user, $answer)) {
            $this->displayNextQuestion($output, $user);
        } else {
            $this->displayNextClue($output, $user);
        }
    }

    private function getUser(string $externalId): ?User
    {
        return $this->userRepository->byExternalId(ExternalUserId::fromString($externalId));
    }

    private function showError(string $invalidExternalId, OutputInterface $output): void
    {
        $output->writeln(
            sprintf(
                '<error>Sorry an error occured while trying to retrieve the question for user "%s".</error>',
                $invalidExternalId
            )
        );
        $output->writeln(
            sprintf(
                '<error>It seems the user with external id "%s" is not registered.</error>',
                $invalidExternalId
            )
        );
    }

    private function isAnswerCorrect(User $user, string $answer): bool
    {
        $command = new AnswerQuestion();
        $command->userId = (string) $user->id();
        $command->answer = $answer;
        $isCorrect = $this->answerQuestionHandler->handle($command);

        return $isCorrect;
    }

    private function displayNextQuestion(OutputInterface $output, $user): void
    {
        $question = $this->getNextQuestion($output, $user);
        if (null !== $question) {
            $output->writeln('Here is a new question for you:');
            $output->writeln(sprintf('<info>%s</info>', $question->statement()));
        } else {
            $output->writeln('<info>Congratulations you completed the quiz!</info>');
        }
    }

    private function getNextQuestion(OutputInterface $output, $user): ?Question
    {
        $output->writeln('<info>Correct! Well done!</info>');
        $showQuestion = new ShowQuestion();
        $showQuestion->userId = (string) $user->id();

        return $this->showQuestionHandler->handle($showQuestion);
    }

    protected function displayNextClue(OutputInterface $output, $user): void
    {
        $output->writeln('Incorrect answer!');
        $output->writeln('Here is a clue:');
        $showClue = new ShowClue();
        $showClue->userId = (string) $user->id();
        $clue = $this->showClueHandler->handle($showClue);
        $output->writeln(sprintf('Clue: %s', (string) $clue));
    }
}
