<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\RegisterUser\RegisterUser;
use OnceUponATime\Application\RegisterUser\RegisterUserHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterUserConsoleHandler extends Command
{
    /** @var RegisterUserHandler */
    private $registerUserHandler;

    public function __construct(RegisterUserHandler $registerUserHandler)
    {
        $this->registerUserHandler = $registerUserHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('out:register')
            ->setDescription('Register to the Once upon a time game!')
            ->setHelp('This commands allows you to register and start the quiz.')
            ->addArgument('name', InputArgument::REQUIRED, 'Your username')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new RegisterUser();
        $command->name = $input->getArgument('name');
        $command->externalUserId = $input->getArgument('external-id');

        $this->registerUserHandler->register($command);

        $output->writeln('<success>You are successfully registered</success>');
    }
}
