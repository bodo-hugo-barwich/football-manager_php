<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;

class TeamsControllerTest extends PantherTestCase
{
    /**
     * URL for the Teams List Page.
     *
     * @var string
     */
    private $teamsURL = '/teams';

    /**
     * URL for the 2nd Page of the Teams List.
     *
     * @var string
     */
    private $teamsPage2URL = '/teams/2';

    public function testTeamNoPlayers(): void
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

        $team = $teamRepo->findOneByName('Test Team Empty');

        $teamNode = 'div#team-'.$team->getId();
        $teamMessageNode = 'div#team-'.$team->getId().'-message';
        $showPlayersLink = $teamNode.' a';
        $noPlayersMessage = 'no players';

        $crawler = $client->request('GET', $this->teamsURL);

        $this->assertSelectorTextContains('h1', 'Teams List - Display Teams');
        $this->assertSelectorTextContains('h2', 'Teams List');

        $this->assertSelectorExists($teamNode, 'Node for Team ('.$team->getId().') is displayed');
        $this->assertSelectorTextContains($teamNode, 'ID: '.$team->getId(), 'Team ('.$team->getId().') Details are displayed');
        $this->assertSelectorExists($teamMessageNode, 'Team ('.$team->getId().') Message: Message is displayed');
        $this->assertSelectorExists($showPlayersLink, 'Team ('.$team->getId().") Players: Link 'show players' is displayed");
        $this->assertSelectorTextContains($showPlayersLink, 'show players');

        $crawler->filter($showPlayersLink)->click();

        $client->waitForElementToContain($teamMessageNode, $noPlayersMessage);
    }

    public function testShowTeamPlayers(): void
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
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 4');
        $players = $playerRepo->findBy(['team_id' => $team->getId()]);

        $teamNode = 'div#team-'.$team->getId();
        $teamMessageNode = 'div#team-'.$team->getId().'-message';
        $playersNode = 'div#team-'.$team->getId().'-players';
        $showPlayersLink = $teamNode.' a';
        $loadedPlayersMessage = 'found';
        $page2Link = 'a[href="'.$this->teamsPage2URL.'"]';

        $crawler = $client->request('GET', $this->teamsURL);

        $this->assertSelectorTextContains('h1', 'Teams List - Display Teams');
        $this->assertSelectorTextContains('h2', 'Teams List');

        $crawler = $client->clickLink('page 2');

        $crawler->filter($page2Link)->click();

        $client->waitFor($teamNode);

        $currentURL = parse_url($client->getCurrentURL());

        $this->assertEquals($this->teamsPage2URL, $currentURL['path'], 'Purchase Page accessed');

        $crawler = $client->getCrawler();

        $this->assertSelectorTextContains('h2', 'Teams List - Page 2');

        $this->assertSelectorExists($teamNode, 'Node for Team ('.$team->getId().') is displayed');
        $this->assertSelectorTextContains($teamNode, 'ID: '.$team->getId(), 'Team ('.$team->getId().') Details are displayed');
        $this->assertSelectorExists($teamMessageNode, 'Team ('.$team->getId().') Message: Message is displayed');
        $this->assertSelectorExists($showPlayersLink, 'Team ('.$team->getId().") Players: Link 'show players' is displayed");
        $this->assertSelectorTextContains($showPlayersLink, 'show players');

        $crawler->filter($showPlayersLink)->click();

        $client->waitForElementToContain($teamMessageNode, $loadedPlayersMessage);

        $this->assertSelectorExists($playersNode, 'Team ('.$team->getId().') Players: Player List is displayed');

        foreach ($players as $player) {
            $this->assertSelectorTextContains($playersNode, $player->getId().' - '.$player->getSurname(), 'Player ('.$player->getId().'): ID is displayed');
            $this->assertSelectorTextContains($playersNode, $player->getSurname().', '.$player->getName(), 'Player ('.$player->getId().'): Full Name is displayed');
        }
    }

    public function testPurchasePlayer(): void
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
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 1');
        $players = $playerRepo->findBy(['team_id' => $team->getId()], ['surname' => 'ASC', 'name' => 'ASC']);
        $player1 = $players[0];

        $teamNode = 'div#team-'.$team->getId();
        $teamMessageNode = 'div#team-'.$team->getId().'-message';
        $playersNode = 'div#team-'.$team->getId().'-players';
        $showPlayersLink = $teamNode.' a';
        $loadedPlayersMessage = 'found';
        $purchasePlayerURL = '/player/'.$player1->getId().'/purchase';
        $purchasePlayerLink = 'a[href="'.$purchasePlayerURL.'"]';

        $crawler = $client->request('GET', $this->teamsURL);

        $this->assertSelectorTextContains('h1', 'Teams List - Display Teams');
        $this->assertSelectorTextContains('h2', 'Teams List');

        $this->assertSelectorExists($teamNode, 'Node for Team ('.$team->getId().') is displayed');
        $this->assertSelectorTextContains($teamNode, 'ID: '.$team->getId(), 'Team ('.$team->getId().') Details are displayed');
        $this->assertSelectorExists($teamMessageNode, 'Team ('.$team->getId().') Message: Message is displayed');
        $this->assertSelectorExists($showPlayersLink, 'Team ('.$team->getId().") Players: Link 'show players' is displayed");
        $this->assertSelectorTextContains($showPlayersLink, 'show players');

        $crawler->filter($showPlayersLink)->click();

        $client->waitForElementToContain($teamMessageNode, $loadedPlayersMessage);

        $this->assertSelectorExists($playersNode, 'Team ('.$team->getId().') Players: Player List is displayed');

        $this->assertSelectorTextContains($playersNode, $player1->getId().' - '.$player1->getSurname(), 'Player ('.$player1->getId().'): ID is displayed');
        $this->assertSelectorTextContains($playersNode, $player1->getSurname().', '.$player1->getName(), 'Player ('.$player1->getId().'): Full Name is displayed');

        $crawler->filter($purchasePlayerLink)->click();

        $client->waitFor('.purchase-player-form');

        $currentURL = parse_url($client->getCurrentURL());

        $this->assertEquals($purchasePlayerURL, $currentURL['path'], 'Purchase Page accessed');

        $crawler = $client->getCrawler();

        $this->assertSelectorTextContains('h1', 'Player ('.$player1->getId().') -', 'Player ('.$player1->getId().'): ID is displayed');
        $this->assertSelectorTextContains('h1', '- '.$player1->getSurname().', '.$player1->getName().' -', 'Player ('.$player1->getId().'): Full Name is displayed');
        $this->assertSelectorTextContains('h1', 'Purchase', 'Player ('.$player1->getId().'): Purchase Page Title');
    }
}
