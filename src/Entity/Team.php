<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TeamRepository::class)
 */
class Team
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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $country;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     */
    private $money_balance;

    /**
     * @var ?Player[]
     */
    private $players;

    public function __construct()
    {
        $this->players = [];
    }

    /**
     * @param array &$data Reference to an array containing the data for the Player entity
     */
    public function setData(array &$data): self
    {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        if (isset($data['name'])) {
            $this->setName($data['name']);
        }
        if (isset($data['country'])) {
            $this->setCountry($data['country']);
        }
        if (isset($data['moneyBalance'])) {
            $this->setMoneyBalance($data['moneyBalance']);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getMoneyBalance(): ?float
    {
        return $this->money_balance;
    }

    public function setMoneyBalance(?float $money_balance): self
    {
        $this->money_balance = $money_balance;

        return $this;
    }

    /**
     * Exports the Team Data to an associative array.
     *
     * @return array Array containing the data for the Team entity
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'moneyBalance' => $this->money_balance,
        ];
    }

    /**
     * @return ?array
     */
    public function getPlayers(): ?array
    {
        return $this->players;
    }

    public function addPlayer(Player $player): self
    {
        $playerId = $player->getId();

        if (!isset($playerId)) {
            $playerId = -1;
        }

        if (!in_array($playerId, $this->players)) {
            $this->players[$player->getId()] = $player;
            $player->setTeam($this);
        }

        return $this;
    }

    /*
    public function removePlayer(Player $player): self
    {
        if ($this->players->removeElement($player)) {
            if ($player->getTeamId() == $this->id) {
                $player->setTeam(null);
            }
        }

        return $this;
    }
    */
}
