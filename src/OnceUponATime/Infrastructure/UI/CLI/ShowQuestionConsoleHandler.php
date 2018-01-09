<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\InvalidExternalUserId;
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
class ShowQuestionConsoleHandler extends Command
{
    /** @var ShowQuestionHandler */
    private $showQuestionHandler;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(ShowQuestionHandler $registerUserHandler, UserRepository $userRepository)
    {
        parent::__construct();
        $this->showQuestionHandler = $registerUserHandler;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('out:show-question')
            ->setDescription('Shows the question a user has to answer')
            ->setHelp('This commands displays the question\'s statement')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $externalId = $input->getArgument('external-id');
        $user = $this->getUser($externalId);
        if (null === $user) {
            $this->showError($externalId, $output);

            return;
        }
        $question = $this->getQuestion($user);
        if (null !== $question) {
            $output->writeln(sprintf('<info>%s</info>', $question->statement()));

            return;
        }
        $output->writeln("<info>Congratulations you completed the quiz!</info>");
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

    private function getQuestion(User $user): ?Question
    {
        $command = new ShowQuestion();
        $command->userId = (string) $user->id();

        return $this->showQuestionHandler->handle($command);
    }
}
