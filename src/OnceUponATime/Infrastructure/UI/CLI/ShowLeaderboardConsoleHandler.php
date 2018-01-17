<?php

declare(strict_types=1);

namespace OnceUponATime\Infrastructure\UI\CLI;

use OnceUponATime\Application\ShowLeaderboard\ShowLeaderboardHandler;
use OnceUponATime\Domain\Entity\Leaderboard\Leaderboard;
use OnceUponATime\Domain\Entity\Leaderboard\Rank;
use OnceUponATime\Domain\Entity\User\Name;
use OnceUponATime\Domain\Entity\User\UserId;
use OnceUponATime\Domain\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShowLeaderboardConsoleHandler extends Command
{
    /** @var ShowLeaderboardHandler */
    private $showLeaderboardHandler;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository, ShowLeaderboardHandler $showLeaderboardHandler)
    {
        parent::__construct();
        $this->showLeaderboardHandler = $showLeaderboardHandler;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('out:leaderboard')
            ->setDescription('Display the leaderboard of all players');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $leaderboard = $this->showLeaderboardHandler->handle();
        $this->displayLeaderboard($leaderboard, $output);
    }

    private function displayLeaderboard(Leaderboard $leaderboard, OutputInterface $output)
    {
        $rows = $this->getTableRows($leaderboard->publish());
        $this->displayTable($output, $rows);
    }

    private function getTableRows(array $ranks): array
    {
        $rows = [];
        foreach ($ranks as $i => $rank) {
            $position = $i + 1;
            $name = $this->getUsername($rank->userId());
            $points = $rank->points();
            $rows[] = [$position, (string) $name, (string) $points];
        }

        return $rows;
    }

    private function displayTable(OutputInterface $output, $rows): void
    {
        $table = new Table($output);
        $table->setHeaders(['Rank', 'Name', 'Points']);
        $table->setRows($rows);
        $table->render();
    }

    private function getUsername(UserId $userId): Name
    {
        return $this->userRepository->byId($userId)->name();
    }
}
