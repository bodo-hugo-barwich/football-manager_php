<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Team;
use App\Entity\Player;

/**
 * @psalm-suppress UnusedClass
 */
class AppFixtures extends Fixture
{
    /**
     * Count of how many Test Teams will be created.
     *
     * @var int
     */
    private $teamsCount = 4;

    /**
     * Count of how many Test Players per Team will be created.
     *
     * @var int
     */
    private $playerCount = 3;

    public function load(ObjectManager $manager): void
    {
        $playerTotalCount = 0;

        for ($team_idx = 1; $team_idx <= $this->teamsCount; ++$team_idx) {
            $team = new Team();
            $team->setName('Test Team No. '.$team_idx);
            $team->setCountry('tt');
            $team->setMoneyBalance($team_idx * 1000000);

            $manager->persist($team);
            $manager->flush();

            for ($player_idx = 1; $player_idx <= $this->playerCount; ++$player_idx) {
                ++$playerTotalCount;

                $player = new Player();
                $player->setName('Test Player '.$playerTotalCount);
                $player->setSurname('No. '.$playerTotalCount);
                $player->setTeam($team);

                $manager->persist($player);
            }
        }

        $team = new Team();
        $team->setName('Test Team Empty');
        $team->setCountry('tt');
        $team->setMoneyBalance(5000000);

        $manager->persist($team);

        $manager->flush();
    }
}
