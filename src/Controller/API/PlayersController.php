<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Entity\Player;

/**
 * @psalm-suppress UnusedClass
 */
class PlayersController extends AbstractController
{
    /**
     * Database Connection.
     *
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function add(Request $request): Response
    {
        $contentType = $request->getContentType();
        // $requestString = $request->getContent();

        if ('json' == $contentType) {
            $requestData = $request->toArray();
        } else {
            $requestData = $request->request->all();
        }

        if (!isset($requestData['name']) || !isset($requestData['surname'])
            || !isset($requestData['team_id'])) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Player: Player missing Fields [ name | surname | team_id ]!'], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException('Player: Player missing Fields [ name | surname | team_id ]!');
            }
        }

        if ('' === $requestData['surname']) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Player: Player Field 'surname' must not be empty!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Player: Player Field 'surname' must not be empty!");
            }
        }

        if (!is_numeric($requestData['team_id'])) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Player: Player Field 'team_id' must be a whole number!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Player: Player Field 'team_id' must be a whole number!");
            }
        }

        $teamRepo = new TeamRepository($this->doctrine);

        $team = $teamRepo->find($requestData['team_id']);

        if (!$team) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Player Team ('.$requestData['team_id'].'): Team does not exist!'], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException('Player Team ('.$requestData['team_id'].'): Team does not exist!');
            }
        }

        $playerRepo = new PlayerRepository($this->doctrine);

        $player = new Player();
        $player->setName($requestData['name']);
        $player->setSurname($requestData['surname']);
        $player->setTeam($team);

        $playerRepo->add($player, true);

        if ('json' == $contentType) {
            return $this->json($player, 200, ['content-type' => 'application/json']);
        } else {
            $playerId = $player->getId();

            if (!isset($playerId)) {
                $playerId = -1;
            }

            return new Response('Player ('.$playerId.'): Player was created');
        }
    }

    public function display(EntityManagerInterface $entityManager, int $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if (!$player) {
            throw $this->createNotFoundException('Player ('.$id.'): Player does not exist!');
        }

        return new Response('Player ('.$id.'): '.$player->getSurname().', '.$player->getName());
    }

    public function edit(Request $request, int $id): Response
    {
        $requestMethod = $request->getMethod();
        $contentType = $request->getContentType();
        // $requestString = $request->getContent();

        if ('json' == $contentType) {
            $requestData = $request->toArray();
        } else {
            $requestData = $request->request->all();
        }

        $playerRepo = new PlayerRepository($this->doctrine);

        $player = $playerRepo->find($id);

        if (!isset($player)) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Player ('.$id.'): Player does not exist!'], 404, ['content-type' => 'application/json']);
            } else {
                return new Response('Player ('.$id.'): Player does not exist!', 404);
            }
        }

        if ('POST' == $requestMethod) {
            if (!isset($requestData['name']) || !isset($requestData['surname'])
                || !isset($requestData['team_id'])) {
                throw new UnprocessableEntityHttpException('Player: Player missing Fields [ name | surname | team_id ]!');
            }
        }

        if (isset($requestData['team_id'])) {
            $teamRepo = new TeamRepository($this->doctrine);

            $team = $teamRepo->find($requestData['team_id']);

            if (!$team) {
                throw new UnprocessableEntityHttpException('Player Team ('.$requestData['team_id'].'): Team does not exist!');
            }
        }

        if ('PUT' == $requestMethod) {
            if (isset($requestData['name'])) {
                $player->setName($requestData['name']);
            }

            if (isset($requestData['surname'])) {
                $player->setSurname($requestData['surname']);
            }

            if (isset($requestData['team_id']) && isset($team)) {
                $player->setTeam($team);
            }
        } else {
            $player->setName($requestData['name']);
            $player->setSurname($requestData['surname']);

            if (isset($team)) {
                $player->setTeam($team);
            }
        }

        $playerRepo->write();

        if ('json' == $contentType) {
            return $this->json($player, 200, ['content-type' => 'application/json']);
        } else {
            return new Response('Player ('.$id.'): Player was updated');
        }
    }

    public function purchase(Request $request, int $id): Response
    {
        $contentType = $request->getContentType();
        // $requestString = $request->getContent();

        if ('json' == $contentType) {
            $requestData = $request->toArray();
        } else {
            $requestData = $request->request->all();
        }

        $playerRepo = new PlayerRepository($this->doctrine);

        $player = $playerRepo->find($id);

        if (!isset($player)) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Player ('.$requestData['id'].'): Player does not exist!'], 404, ['content-type' => 'application/json']);
            } else {
                return new Response('Player ('.$requestData['id'].'): Player does not exist!', 404);
            }
        }

        if (!isset($requestData['price'])
            || !isset($requestData['team_id'])) {
            throw new UnprocessableEntityHttpException('Player Purchase: Player missing Fields [ price | team_id ]!');
        }

        if (!is_numeric($requestData['price'])) {
            throw new UnprocessableEntityHttpException("Player Purchase: Player Field 'price' must be numeric!");
        }

        if (!is_numeric($requestData['team_id'])) {
            throw new UnprocessableEntityHttpException("Player Purchase: Player Field 'team_id' must be numeric!");
        }

        $teamRepo = new TeamRepository($this->doctrine);

        $currentTeam = null;
        $purchasingTeam = $teamRepo->find($requestData['team_id']);

        if (!$purchasingTeam) {
            throw new UnprocessableEntityHttpException('Purchasing Team ('.$requestData['team_id'].'): Team does not exist!');
        }

        if ($player->getTeamId() > 0) {
            $currentTeam = $teamRepo->find($player->getTeamId());
        }

        $purchasingBalance = $purchasingTeam->getMoneyBalance();

        if ($purchasingBalance < $requestData['price']) {
            throw new UnprocessableEntityHttpException('Purchasing Team ('.$purchasingTeam->getId().'): Team does not have sufficient Money Balance!');
        }

        $purchasingTeam->setMoneyBalance($purchasingBalance - $requestData['price']);

        if (isset($currentTeam)) {
            $currentTeam->setMoneyBalance($currentTeam->getMoneyBalance() + $requestData['price']);
        }

        $teamRepo->write();

        $player->setTeam($purchasingTeam);

        $playerRepo->write();

        if ('json' == $contentType) {
            return $this->json($player, 200, ['content-type' => 'application/json']);
        } else {
            return new Response('Player ('.$id.'): Player Purchase completed successfully');
        }
    }

    public function list(Request $request): Response
    {
        $requestData = $request->query->all();

        $playerRepo = new PlayerRepository($this->doctrine);

        if (isset($requestData['team_id'])) {
            if (!is_numeric($requestData['team_id'])) {
                return $this->json(['message' => 'Team Players: Team ID is not valid!'], 422, ['content-type' => 'application/json']);
            }

            $teamRepo = new TeamRepository($this->doctrine);

            $team = $teamRepo->find($requestData['team_id']);

            if (!$team) {
                return $this->json(['message' => 'Team Players: Team ('.$requestData['team_id'].') does not exist!'], 404, ['content-type' => 'application/json']);
            }

            $players = $playerRepo->findBy(['team_id' => $requestData['team_id']], ['surname' => 'ASC', 'name' => 'ASC']);
        } else {
            $players = $playerRepo->findBy([], ['surname' => 'ASC', 'name' => 'ASC']);
        }

        return $this->json($players, 200, ['content-type' => 'application/json']);
    }
}
