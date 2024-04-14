<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use Facebook\WebDriver\WebDriverSelect;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;

class PlayersControllerTest extends PantherTestCase
{
    /**
     * URL for Purchase Player Page.
     *
     * @var string
     */
    private $playerPurchaseURL = '/player/:id:/purchase';

    /**
     * @var array[] Array of edited Team data-sets
     */
    private $editedTeams = [];

    /**
     * @var array[] Array of edited Player data-sets
     */
    private $editedPlayers = [];

    public function tearDown(): void
    {
        if (count($this->editedTeams)) {
            $teamRepo = static::getContainer()->get(TeamRepository::class);

            // Restore edited team entries in the database
            foreach ($this->editedTeams as $teamData) {
                $team = $teamRepo->find($teamData['id']);

                // Restore saved data-set
                $team->setData($teamData);
            }

            $teamRepo->write();
        }

        if (count($this->editedPlayers) > 0) {
            $playerRepo = static::getContainer()->get(PlayerRepository::class);

            // Restore edited player entries in the database
            foreach ($this->editedPlayers as $playerData) {
                $player = $playerRepo->find($playerData['id']);

                // Restore saved data-set
                $player->setData($playerData);
            }

            $playerRepo->write();
        }

        parent::tearDown();
    }

    /*
    private function editTeam(array $teamData) {
        $webclient = static::createClient();

    }
    */

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

        $team = $teamRepo->findOneByName('Test Team No. 3');
        $purchasingTeam = $teamRepo->findOneByName('Test Team No. 4');
        $players = $playerRepo->findBy(['team_id' => $team->getId()]);
        $player3 = $players[0];
        // $teamData = $team->toArray();

        $this->editedTeams[] = $team->toArray();
        $this->editedTeams[] = $purchasingTeam->toArray();

        $this->editedPlayers[] = $player3->toArray();

        $purchaseURL = str_replace(':id:', $player3->getId(), $this->playerPurchaseURL);

        $teamsSelect = 'select.teams-select';

        $purchaseFormNode = 'div.purchase-player-form';
        $purchaseForm = '#purchase-player';
        $purchaseButton = $purchaseForm.' button';
        $purchaseMessageNode = $purchaseFormNode.' .message';
        $updatedPlayerMessage = 'updated';

        $crawler = $client->request('GET', $purchaseURL);

        // Wait for the Teams List to get loaded
        $client->waitForElementToContain($teamsSelect, $purchasingTeam->getName());
        $this->assertSelectorTextContains('h1', 'Player ('.$player3->getId().') -', 'Player ('.$player3->getId().'): ID is displayed');
        $this->assertSelectorTextContains('h1', '- '.$player3->getSurname().', '.$player3->getName().' -', 'Player ('.$player3->getId().'): Full Name is displayed');
        $this->assertSelectorTextContains('h1', 'Purchase', 'Player ('.$player3->getId().'): Purchase Page Title');
        $this->assertSelectorTextContains('h2', 'Player Purchase');

        $selectElement = new WebDriverSelect($crawler->filter($teamsSelect)->getElement(0));

        $selectElement->selectByValue($purchasingTeam->getId());

        $crawler->filter($purchaseForm)->form([
            'purchasing_team_id' => $purchasingTeam->getId(),
            'price' => 690000.98,
        ]);

        $this->assertSelectorExists($purchaseButton, "Form Button 'Checkout' exists");

        $crawler->filter($purchaseButton)->click();

        $client->waitForElementToContain($purchaseMessageNode, $updatedPlayerMessage);

        $team = $teamRepo->findOneByName('Test Team No. 3');
        $purchasingTeam = $teamRepo->findOneByName('Test Team No. 4');
        $player3 = $playerRepo->find($player3->getId());

        // $resultMessage = $crawler->filter($purchaseMessageNode)->getText();
        // echo "bwsr logs:\n".print_r($client->getWebDriver()->manage()->getLog('browser'), true)."\n";
    }
}
