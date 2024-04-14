<?php

namespace App\Tests\Controller\Admin;

use Symfony\Component\Panther\PantherTestCase;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Entity\Team;
use App\Entity\Player;

class TeamsControllerTest extends PantherTestCase
{
    /**
     * URL for the Teams Admin Page.
     *
     * @var string
     */
    private $teamsAdminURL = '/admin/teams';

    /**
     * @var Team[] Array of created Teams
     */
    private $createdTeams = [];

    /**
     * @var Player[] Array of created Players
     */
    private $createdPlayers = [];

    public function tearDown(): void
    {
        $teamRepo = static::getContainer()->get(TeamRepository::class);
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        // Remove created team entries from the database
        foreach ($this->createdTeams as $team) {
            $teamRepo->remove($team, true);
        }

        // Remove created player entries from the database
        foreach ($this->createdPlayers as $player) {
            $playerRepo->remove($player, true);
        }

        parent::tearDown();
    }

    public function testAddTeam(): void
    {
        $client = static::createPantherClient(
            [],
            [],
            [
                'capabilities' => [
                    'goog:loggingPrefs' => [
                        'browser' => 'ALL', // calls to console.* methods
                        'performance' => 'ALL', // performance data
                    ],
                ],
            ]
        );
        $openFormButton = 'div.open-form-button button';
        $addTeamNode = 'div.add-team-form';
        $addTeamForm = '#add-team';
        $addTeamButton = $addTeamForm.' button';
        $addTeamMessageNode = 'div.add-team-form .message';
        $createdTeamMessage = 'created';

        $crawler = $client->request('GET', $this->teamsAdminURL);

        $this->assertSelectorTextContains('h1', 'Admin Teams - Edit Teams');
        $this->assertSelectorTextContains('h2', 'Admin Teams');

        $this->assertSelectorExists($openFormButton, "Button 'Add Team' is displayed");
        $this->assertSelectorExists($addTeamNode, "Form 'Add Team' exists");
        $this->assertSelectorIsNotVisible($addTeamNode);

        $crawler->filter($openFormButton)->click();

        $client->waitForVisibility($addTeamNode);

        $crawler->filter($addTeamForm)->form([
            'name' => 'Added Panther Test Team',
            'country_code' => 'tt',
            'money_balance' => 6000000,
        ]);

        $this->assertSelectorExists($addTeamButton, "Form Button 'Create Team' exists");

        $crawler->filter($addTeamButton)->click();

        $client->waitForElementToContain($addTeamMessageNode, $createdTeamMessage);

        $resultMessage = $crawler->filter($addTeamMessageNode)->getText();

        // echo "bwsr logs:\n".print_r($client->getWebDriver()->manage()->getLog('browser'), true)."\n";

        $createdTeamMatch = null;

        preg_match('/Team - Create: Team \(([0-9]+)\)/', $resultMessage, $createdTeamMatch);

        $teamRepo = static::getContainer()->get(TeamRepository::class);

        $createdTeam = $teamRepo->find($createdTeamMatch[1]);

        $this->createdTeams[] = $createdTeam;
    }

    public function testAddPlayer(): void
    {
        $client = static::createPantherClient(
            [],
            [],
            [
                'capabilities' => [
                    'goog:loggingPrefs' => [
                        'browser' => 'ALL', // calls to console.* methods
                        'performance' => 'ALL', // performance data
                    ],
                ],
            ]
        );
        $teamRepo = static::getContainer()->get(TeamRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 3');

        $teamAnchor = '#team-'.$team->getId();
        $teamNode = 'div'.$teamAnchor;
        $openFormLink = $teamNode.' a[href="'.$teamAnchor.'"]';

        $addPlayerNode = 'div.add-player-form';
        $addPlayerForm = '#add-player';
        $addPlayerButton = $addPlayerForm.' button';
        $addPlayerMessageNode = $addPlayerNode.' .message';
        $createdPlayerMessage = 'created';

        $crawler = $client->request('GET', $this->teamsAdminURL);

        $this->assertSelectorTextContains('h1', 'Admin Teams - Edit Teams');
        $this->assertSelectorTextContains('h2', 'Admin Teams');

        $this->assertSelectorExists($teamNode, 'Team ('.$team->getId().') is displayed');
        $this->assertSelectorExists($openFormLink, "Link 'Add Player' is displayed");

        $crawler->filter($openFormLink)->click();

        $client->waitForVisibility($addPlayerNode);

        $crawler->filter($addPlayerForm)->form([
            'name' => 'Player Panther',
            'surname' => 'Added Player',
        ]);

        $this->assertSelectorExists($addPlayerButton, "Form Button 'Create Player' exists");

        $crawler->filter($addPlayerButton)->click();

        $client->waitForElementToContain($addPlayerMessageNode, $createdPlayerMessage);

        $resultMessage = $crawler->filter($addPlayerMessageNode)->getText();

        // echo "bwsr logs:\n".print_r($client->getWebDriver()->manage()->getLog('browser'), true)."\n";

        $createdPlayerMatch = null;

        preg_match('/Player - Create: Player \(([0-9]+)\)/', $resultMessage, $createdPlayerMatch);

        $teamRepo = static::getContainer()->get(TeamRepository::class);
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $createdPlayer = $playerRepo->find($createdPlayerMatch[1]);

        $this->createdPlayers[] = $createdPlayer;
    }
}
