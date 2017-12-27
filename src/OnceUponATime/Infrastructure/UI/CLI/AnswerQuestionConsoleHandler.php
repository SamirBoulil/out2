<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\AnswerQuestion\AnswerQuestion;
use OnceUponATime\Application\AnswerQuestion\AnswerQuestionHandler;
use OnceUponATime\Application\AskQuestion\AskQuestion;
use OnceUponATime\Application\AskQuestion\AskQuestionHandler;
use OnceUponATime\Application\InvalidExternalUserId;
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

    /** @var AskQuestionHandler */
    private $askQuestionHandler;

    public function __construct(AnswerQuestionHandler $answerQuestionHandler, AskQuestionHandler $askQuestionHandler)
    {
        $this->answerQuestionHandler = $answerQuestionHandler;

        parent::__construct();
        $this->askQuestionHandler = $askQuestionHandler;
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
        try {
            $isCorrect = $this->answerQuestion($input);
        } catch (InvalidExternalUserId $e) {
            $this->showError($e->id(), $output);

            return;
        }

        if (true === $isCorrect) {
            $output->writeln('<info>Correct! Well done!</info>');
            $askQuestion = new AskQuestion();
            $askQuestion->externalUserId = $input->getArgument('external-id');
            $question = $this->askQuestionHandler->handle($askQuestion);
            if (null !== $question) {
                $output->writeln('Here is a new question for you:');
                $output->writeln(sprintf('<info>%s</info>', $question->statement()));
            } else {
//                $output->writeln('<info>Well done! You have successfully completed the quiz!</info>');
            }
        }
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

    /**
     * @param InputInterface $input
     *
     * @return \OnceUponATime\Domain\Entity\Question\Question
     *
     */
    protected function answerQuestion(InputInterface $input): \OnceUponATime\Domain\Entity\Question\Question
    {
        $command = new AnswerQuestion();
        $command->externalUserId = $input->getArgument('external-id');
        $command->answer = $input->getArgument('answer');

        $isCorrect = $this->answerQuestionHandler->handle($command);

        return $isCorrect;
    }
}
