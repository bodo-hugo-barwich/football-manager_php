<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Team;

class TeamsController extends AbstractController
{
    /**
     * Amount of Teams displayed per page.
     *
     * @var int
     */
    private $page_size = 20;

    public function index(EntityManagerInterface $entityManager): Response
    {
        $teamRepo = $entityManager->getRepository(Team::class);

        $teamsCount = $teamRepo->getTotalCount();
        $pageCount = ceil($teamsCount / $this->page_size);

        $teams = $teamRepo->findBy([], ['name' => 'ASC'], $this->page_size, 0);

        return $this->render('admin/teams.html.twig', ['teams' => $teams, 'page_count' => $pageCount]);
    }

    public function page(EntityManagerInterface $entityManager, int $page = 2): Response
    {
        if ($page < 2) {
            // Redirect to the Teams Index Page
            return $this->redirectToRoute('teams_index', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        $teamRepo = $entityManager->getRepository(Team::class);

        $teamsCount = $teamRepo->getTotalCount();
        $pageCount = ceil($teamsCount / $this->page_size);

        if ($page > $pageCount) {
            // Redirect to the Last Teams Page
            return $this->redirectToRoute('teams_list', ['page' => $pageCount], Response::HTTP_MOVED_PERMANENTLY);
        }

        $offset = $page - 1;

        $teams = $teamRepo->findBy([], ['name' => 'ASC'], $this->page_size, $this->page_size * $offset);

        return $this->render('admin/teams.html.twig', [
            'teams' => $teams, 'current_page' => $page, 'page_count' => $pageCount]);
    }
}
