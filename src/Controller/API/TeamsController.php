<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Repository\TeamRepository;
use App\Entity\Team;

class TeamsController extends AbstractController
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

        if (!isset($requestData['name']) || !isset($requestData['country_code'])
            || !isset($requestData['money_balance'])) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Team: Team missing Fields [ name | country_code | money_balance ]!'], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException('Team: Team missing Fields [ name | country_code | money_balance ]!');
            }
        }

        if ('' === $requestData['name']) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'name' must not be empty!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'name' must not be empty!");
            }
        }

        if ('' === $requestData['country_code'] || strlen($requestData['country_code']) > 2) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'country_code' must not be empty ".'and an ISO 3166-1 alpha-2 country code!'], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'country_code' must not be empty ".'and an ISO 3166-1 alpha-2 country code!');
            }
        }

        if (!is_numeric($requestData['money_balance'])) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'money_balance' must not be a floating point number!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'money_balance' must not be a floating point number!");
            }
        }

        $teamRepo = new TeamRepository($this->doctrine);
        $team = new Team();

        $team->setName($requestData['name']);
        $team->setCountry($requestData['country_code']);
        $team->setMoneyBalance(floatval($requestData['money_balance']));

        $teamRepo->add($team, true);

        if ('json' == $contentType) {
            return $this->json($team, 200, ['content-type' => 'application/json']);
        } else {
            $teamId = $team->getId();

            if (!isset($teamId)) {
                $teamId = -1;
            }

            return new Response('Team ('.$teamId.'): Team was created.');
        }
    }

    public function display(EntityManagerInterface $entityManager, int $id): Response
    {
        $team = $entityManager->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException('Team ('.$id.'): Team does not exist!');
        }

        return new Response('Team ('.$id.'): '.$team->getName());
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

        // echo "req dta 1 dmp:\n" . print_r($requestData, true). "\n";

        $teamRepo = new TeamRepository($this->doctrine);

        $team = $teamRepo->find($id);

        if (!isset($team)) {
            if ('json' == $contentType) {
                return $this->json(['message' => 'Team ('.$id.'): Team does not exist!'], 404, ['content-type' => 'application/json']);
            } else {
                return new Response('Team ('.$id.'): Team does not exist!', 404);
            }
        }

        if ('POST' == $requestMethod) {
            if (!isset($requestData['name']) || !isset($requestData['country_code'])
                || !isset($requestData['money_balance'])) {
                if ('json' == $contentType) {
                    return $this->json(['message' => 'Team: Team missing Fields [ name | country_code | money_balance ]!'], 422, ['content-type' => 'application/json']);
                } else {
                    throw new UnprocessableEntityHttpException('Team: Team missing Fields [ name | country_code | money_balance ]!');
                }
            }
        }

        if ('' === $requestData['name']) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'name' must not be empty!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'name' must not be empty!");
            }
        }

        if ('' === $requestData['country_code'] || strlen($requestData['country_code']) > 2) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'country_code' must not be empty ".'and an ISO 3166-1 alpha-2 country code!'], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'country_code' must not be empty ".'and an ISO 3166-1 alpha-2 country code!');
            }
        }

        if (!is_numeric($requestData['money_balance'])) {
            if ('json' == $contentType) {
                return $this->json(['message' => "Team: Team Field 'money_balance' must not be a floating point number!"], 422, ['content-type' => 'application/json']);
            } else {
                throw new UnprocessableEntityHttpException("Team: Team Field 'money_balance' must not be a floating point number!");
            }
        }

        if ('PUT' == $requestMethod) {
            if (isset($requestData['name'])) {
                $team->setName($requestData['name']);
            }

            if (isset($requestData['country_code'])) {
                $team->setCountry($requestData['country_code']);
            }

            if (isset($requestData['money_balance'])) {
                $team->setMoneyBalance(floatval($requestData['money_balance']));
            }
        } else {
            $team->setName($requestData['name']);
            $team->setCountry($requestData['country_code']);
            $team->setMoneyBalance(floatval($requestData['money_balance']));
        }

        $teamRepo->write();

        if ('json' == $contentType) {
            return $this->json($team, 200, ['content-type' => 'application/json']);
        } else {
            return new Response('Team ('.$team->getId().'): Team was edited');
        }
    }

    public function list(EntityManagerInterface $entityManager): Response
    {
        $teamRepo = $entityManager->getRepository(Team::class);
        $teams = $teamRepo->findBy([], ['name' => 'ASC']);

        return $this->json($teams, 200, ['content-type' => 'application/json']);
    }
}
