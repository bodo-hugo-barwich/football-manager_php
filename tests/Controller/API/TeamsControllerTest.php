<?php

namespace App\Tests\Controller\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\TeamRepository;
use App\Entity\Team;

class TeamsControllerTest extends WebTestCase
{
    /**
     * URL for the Teams API.
     *
     * @var string
     */
    private $teamsAPIURL = '/api/teams';

    /**
     * URL for the Team API.
     *
     * @var string
     */
    private $teamAPIURL = '/api/team';

    /**
     * @var Team[] Array of created Teams
     */
    private $createdTeams = [];

    /**
     * @var array[] Array of edited Team data-sets
     */
    private $editedTeams = [];

    public function tearDown(): void
    {
        $teamRepo = static::getContainer()->get(TeamRepository::class);

        // Remove created Team entries from the database
        foreach ($this->createdTeams as $team) {
            $teamRepo->remove($team, true);
        }

        // Reset edited Team entries in the database
        foreach ($this->editedTeams as $teamData) {
            $team = $teamRepo->find($teamData['id']);

            // Restore saved data-set
            $team->setData($teamData);

            $teamRepo->write();
        }

        parent::tearDown();
    }

    public function testAllTeams()
    {
        $client = static::createClient();
        $teamRepo = static::getContainer()->get(TeamRepository::class);

        $firstTeam = $teamRepo->findOneByName('Test Team Empty');
        $lastTeam = $teamRepo->findOneByName('Test Team No. 4');

        $client->request('GET', $this->teamsAPIURL);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $teams = json_decode($responseJSON, true);

        $this->assertEquals(5, count($teams), 'Response contains 5 entries');

        $team1 = $teams[0];
        $team5 = $teams[4];

        $this->assertEquals($firstTeam->getId(), $team1['id'], "First Response is Team 'Test Team Empty'");
        $this->assertEquals($lastTeam->getId(), $team5['id'], "Last Response is Team 'Test Team No. 4'");
    }

    public function testAddTeam()
    {
        $client = static::createClient();
        $teamRepo = static::getContainer()->get(TeamRepository::class);

        $teamRequestData = [
            'name' => 'Test Team Added',
            'country_code' => 'tt',
            'money_balance' => 6000000,
        ];

        $client->request('POST', $this->teamAPIURL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($teamRequestData));

        // $request = $client->getRequest();

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertNotEquals(null, $responseData['id'], 'Team was added and ID was assigned');
        $this->assertEquals($teamRequestData['name'], $responseData['name'], "Team: Team added as Team 'Test Team Added'");

        $team = $teamRepo->find($responseData['id']);

        // Keep track of the created Team to remove it from the database afterwards
        $this->createdTeams[] = $team;
    }

    public function testEditTeam()
    {
        $client = static::createClient();
        $teamRepo = static::getContainer()->get(TeamRepository::class);

        $team = $teamRepo->findOneByName('Test Team No. 3');
        $teamData = $team->toArray();

        // Keep track of the original data-set to restore it afterwards
        $this->editedTeams[] = &$teamData;

        $teamRequestData = [
            'name' => $team->getName().' Edited',
            'country_code' => 'tt',
            'money_balance' => 6000000,
        ];

        $client->request('POST', $this->teamAPIURL.'/'.$team->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($teamRequestData));

        // $request = $client->getRequest();

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $responseJSON = $response->getContent();

        $this->assertJson($responseJSON, 'Response is valid JSON');

        $responseData = json_decode($responseJSON, true);

        $this->assertEquals($teamData['name'].' Edited', $responseData['name'], 'Team was edited');
    }
}
