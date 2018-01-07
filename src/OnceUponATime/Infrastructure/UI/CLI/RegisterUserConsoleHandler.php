<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\RegisterUser\RegisterUser;
use OnceUponATime\Application\RegisterUser\RegisterUserHandler;
use OnceUponATime\Application\ShowQuestion\ShowQuestion;
use OnceUponATime\Application\ShowQuestion\ShowQuestionHandler;
use OnceUponATime\Domain\Entity\User\ExternalUserId;
use OnceUponATime\Domain\Repository\UserRepository;
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

    /** @var ShowQuestionHandler */
    private $showQuestionHandler;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        RegisterUserHandler $registerUserHandler,
        ShowQuestionHandler $showQuestionHandler,
        UserRepository $userRepository
    ) {
        parent::__construct();
        $this->registerUserHandler = $registerUserHandler;
        $this->showQuestionHandler = $showQuestionHandler;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('out:register')
            ->setDescription('Register to the Once upon a time game!')
            ->setHelp('This commands allows you to register and start the quiz.')
            ->addArgument('name', InputArgument::REQUIRED, 'Your username')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->registerUser($input->getArgument('name'), $input->getArgument('external-id'), $output);
        $this->showFirstQuestion($input->getArgument('external-id'), $output);
    }

    protected function registerUser(string $name, string $externalUserId, OutputInterface $output): void
    {
        $command = new RegisterUser();
        $command->name = $name;
        $command->externalUserId = $externalUserId;
        $this->registerUserHandler->register($command);
        $output->writeln('<info>You are successfully registered!</info>');
    }

    private function showFirstQuestion(string $externalUserId, OutputInterface $output): void
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalUserId));
        $command = new ShowQuestion();
        $command->userId = (string) $user->id();
        $question = $this->showQuestionHandler->handle($command);
        $output->writeln('<info>Here is your first question:</info>');
        $output->writeln(sprintf('<info>%s</info>', $question->statement()));
    }
}
