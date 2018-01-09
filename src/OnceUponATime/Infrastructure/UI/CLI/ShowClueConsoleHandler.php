<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\NoQuestionToAnswer;
use OnceUponATime\Application\ShowClue\ShowClue;
use OnceUponATime\Application\ShowClue\ShowClueHandler;
use OnceUponATime\Domain\Entity\Question\Clue;
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
class ShowClueConsoleHandler extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var ShowClueHandler */
    private $showClueHandler;

    public function __construct(UserRepository $userRepository, ShowClueHandler $showClueHandler)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->showClueHandler = $showClueHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName('out:show-clue')
            ->setDescription('Shows the last clue the game gave you.')
            ->setHelp('This commands displays the question\'s clue')
            ->addArgument('external-id', InputArgument::REQUIRED, 'External user id (Slack id)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $externalId = $input->getArgument('external-id');
        $user = $this->getUser($externalId, $output);
        if (null === $user) {
            $this->showError($externalId, $output);

            return;
        }
        try {
            $clue = $this->getClue($user);
            if (null !== $clue) {
                $output->writeln(sprintf('<info>Clue: %s</info>', (string) $clue));

                return;
            }
            $output->writeln("<info>There is no clue to show.</info>");
        } catch (NoQuestionToAnswer $e) {
            $output->writeln("<info>You've completed the quiz! there is no clue to show.</info>");
        }
    }

    private function getUser(string $externalId, OutputInterface $output): ?User
    {
        $user = $this->userRepository->byExternalId(ExternalUserId::fromString($externalId));
        if (null === $user) {
            $this->showError($externalId, $output);
        }

        return $user;
    }

    private function showError(string $invalidExternalId, OutputInterface $output): void
    {
        $output->writeln(
            sprintf(
                '<error>Sorry an error occured while trying to retrieve the clue for the question "%s".</error>',
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

    private function getClue(User $user): ?Clue
    {
        $showClue = new ShowClue();
        $showClue->userId = (string) $user->id();

        return $this->showClueHandler->handle($showClue);
    }
}
