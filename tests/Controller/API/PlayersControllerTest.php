<?php

namespace App\Tests\Controller\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Entity\Player;

class PlayersControllerTest extends WebTestCase
{
    /**
     * URL for the Teams API.
     *
     * @var string
     */
    private $playersAPIURL = '/api/players';

    /**
     * @var Player[] Array of created Players
     */
    private $createdPlayers = [];

    /**
     * @var array[] Array of edited Player data-sets
     */
    private $editedPlayers = [];

    public function tearDown(): void
    {
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        // Remove created player entries from the database
        foreach ($this->createdPlayers as $player) {
            $playerRepo->remove($player, true);
        }

        // Reset edited player entries in the database
        foreach ($this->editedPlayers as $playerData) {
            $player = $playerRepo->find($playerData['id']);

            // Restore saved data-set
            $player->setData($playerData);

            $playerRepo->write();
        }

        parent::tearDown();
    }

    public function testAllPlayers()
    {
        $client = static::createClient();
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $firstPlayer = $playerRepo->findOneBySurname('No. 1');
        $lastPlayer = $playerRepo->findOneBySurname('No. 9');

        $client->request('GET', $this->playersAPIURL);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $players = json_decode($responseJSON, true);

        $this->assertEquals(12, count($players), 'Response contains 12 entries');

        $player1 = $players[0];
        $player12 = $players[11];

        $this->assertEquals($firstPlayer->getId(), $player1['id'], "First Response is Player 'No. 1'");
        $this->assertEquals($lastPlayer->getId(), $player12['id'], "Last Response is Player 'No. 9'");
    }

    public function testTeamPlayers()
    {
        $client = static::createClient();
        $teamRepo = static::getContainer()->get(TeamRepository::class);
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 1');

        $firstPlayer = $playerRepo->findOneBySurname('No. 1');
        $lastPlayer = $playerRepo->findOneBySurname('No. 3');

        $client->request('GET', $this->playersAPIURL.'?team_id='.$team->getId());

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $players = json_decode($responseJSON, true);

        $this->assertEquals(3, count($players), 'Response contains 12 entries');

        $player1 = $players[0];
        $player3 = $players[2];

        $this->assertEquals($firstPlayer->getId(), $player1['id'], "First Response is Player 'No. 1'");
        $this->assertEquals($lastPlayer->getId(), $player3['id'], "Last Response is Player 'No. 3'");

        foreach ($players as $player) {
            $this->assertEquals($team->getId(), $player['teamId'], "Player Team is set to Team 'Test Team No. 1'");
        }
    }

    public function testAddPlayerNoName()
    {
        $client = static::createClient();

        $playerNoName = [
            'name' => '',
            'surname' => '',
            'team_id' => -1,
        ];

        $client->request('POST', $this->playersAPIURL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($playerNoName));

        // $request = $client->getRequest();

        $this->assertResponseIsUnprocessable();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertNotEquals(false, strpos($responseData['message'], 'surname'), 'Error Message Surname Field');
        $this->assertNotEquals(false, strpos($responseData['message'], 'must not be empty'), 'Error Message Surname not be empty');
    }

    public function testAddPlayerNoTeam()
    {
        $client = static::createClient();

        $playerNoTeam = [
            'name' => 'Test Player',
            'surname' => 'No Team',
            'team_id' => -1,
        ];

        $client->request('POST', $this->playersAPIURL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($playerNoTeam));

        // $request = $client->getRequest();

        $this->assertResponseIsUnprocessable();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertNotEquals(false, strpos($responseData['message'], 'Team'), 'Error Message Team Field');
        $this->assertNotEquals(false, strpos($responseData['message'], 'not exist'), 'Error Message Surname not exists');
    }

    public function testAddPlayerSuccess()
    {
        $client = static::createClient();
        $teamRepo = static::getContainer()->get(TeamRepository::class);
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 2');

        $playerData = [
            'name' => 'Test Player',
            'surname' => 'Added Player',
            'team_id' => $team->getId(),
        ];

        $client->request('POST', $this->playersAPIURL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($playerData));

        // $request = $client->getRequest();

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertNotEquals(null, $responseData['id'], 'Player was added and ID was assigned');
        $this->assertEquals($team->getId(), $responseData['teamId'], "Player Team: Player added to Team 'Test Team No. 2'");

        $player = $playerRepo->find($responseData['id']);

        // Keep track of the created Player to remove it from the database afterwards
        $this->createdPlayers[] = $player;
    }

    public function testEditPlayer()
    {
        $client = static::createClient();
        $playerRepo = static::getContainer()->get(PlayerRepository::class);

        $player = $playerRepo->findOneBySurname('No. 4');
        $playerData = $player->toArray();

        // Keep track of the original data-set to revert the changes in the database afterwards
        $this->editedPlayers[] = $playerData;

        $playerRequestData = [
            'id' => $player->getId(),
            'name' => $player->getName().' Edited',
            'surname' => $player->getSurname(),
            'team_id' => $player->getTeamId(),
        ];

        $client->request('PUT', $this->playersAPIURL.'/'.$player->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($playerRequestData));

        // $request = $client->getRequest();

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertEquals($playerData['name'].' Edited', $responseData['name'], 'Player was edited');
    }
}
