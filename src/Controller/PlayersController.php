<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Entity\Team;
use App\Entity\Player;

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

    public function display(EntityManagerInterface $entityManager, int $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);

        if (!$player) {
            throw $this->createNotFoundException('Player ('.$id.'): Player does not exist!');
        }

        return new Response('Player ('.$id.'): '.$player->getSurname().', '.$player->getName());
    }

    public function purchase(EntityManagerInterface $entityManager, int $id): Response
    {
        $playerRepo = $entityManager->getRepository(Player::class);

        $player = $playerRepo->find($id);

        if (!$player) {
            throw $this->createNotFoundException('Player ('.$id.'): Player does not exist!');
        }

        $teamRepo = $entityManager->getRepository(Team::class);

        $team = $teamRepo->find($player->getTeamId());

        if (!$team) {
            throw new UnprocessableEntityHttpException('Player Team ('.$player->getTeamId().'): Team does not exist!');
        }

        return $this->render('player_purchase.html.twig', ['player' => $player, 'team' => $team]);
    }
}
