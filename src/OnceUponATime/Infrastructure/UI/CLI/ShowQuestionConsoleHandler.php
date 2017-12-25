<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\RegisterUser\RegisterUser;
use OnceUponATime\Application\RegisterUser\RegisterUserHandler;
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
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new ShowQuestion();
        $command->externalUserId = $input->getArgument('external-id');

        $question = $this->showQuestionHandler->handle($command);

        $output->writeln(sprintf('<info>%s</info>', (string) $question->statement()));
    }
}
