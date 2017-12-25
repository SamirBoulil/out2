<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\InvalidExternalUserId;
use OnceUponATime\Application\ShowQuestion\ShowQuestion;
use OnceUponATime\Application\ShowQuestion\ShowQuestionHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowQuestionConsoleHandler extends Command
{
    /** @var ShowQuestionHandler */
    private $showQuestionHandler;

    public function __construct(ShowQuestionHandler $showQuestionHandler)
    {
        $this->showQuestionHandler = $showQuestionHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('out:show-question')
            ->setDescription('Shows the question a user has to answer')
            ->setHelp('This commands displays the question\'s statement')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new ShowQuestion();
        $command->externalUserId = $input->getArgument('external-id');

        try {
            $question = $this->showQuestionHandler->handle($command);
        } catch (InvalidExternalUserId $e) {
            $this->showError($e->id(), $output);

            return;
        }

        $output->writeln(sprintf('<info>%s</info>', $question->statement()));
    }

    private function showError(string $invalidExternalId, OutputInterface $output)
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
}
