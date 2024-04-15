<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $surname;

    /**
     * @ORM\Column(type="integer")
     */
    private $team_id;

    /**
     * Imports the Player Data as associative array.
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @param array &$data Reference to an array containing the data for the Player entity
     */
    public function setData(array &$data): self
    {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['surname'])) {
            $this->surname = $data['surname'];
        }
        if (isset($data['teamId'])) {
            $this->team_id = $data['teamId'];
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        if (isset($name)) {
            $this->name = $name;
        } else {
            $this->name = '';
        }

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getTeamId(): ?int
    {
        return $this->team_id;
    }

    /**
     * Assign the Player to a Team by ID
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @param int $team_id
     * @return self
     */
    public function setTeamId(int $team_id): self
    {
        $this->team_id = $team_id;

        return $this;
    }

    public function setTeam(?Team $team): self
    {
        if (isset($team)) {
            $this->team_id = $team->getId();
        } else {
            $this->team_id = -1;
        }

        return $this;
    }

    /**
     * Exports the Player Data to an associative array.
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @return array Array containing the data for the Player entity
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'teamId' => $this->team_id,
        ];
    }
}
