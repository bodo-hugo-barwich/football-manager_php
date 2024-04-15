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
     * @var Player[]
     */
    private $players;

    public function __construct()
    {
        $this->players = [];
    }

    /**
     * Populate a Team instance by an associative array
     *
     * @param array &$data Reference to an array containing the data for the Player entity
     * @psalm-suppress PossiblyUnusedMethod
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

    /**
     * Returns the country code of the Team
     *
     * @return string|NULL Country code of the Team
     * @psalm-suppress PossiblyUnusedMethod
     */
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
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyNullArgument
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
     * Returns an associative array of Player objects
     *
     * This can be empty if the Player entries are not looked up yet.
     *
     * @return array Associative array of Player objects
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * Add a Player object to the Teams Player list
     *
     * @param Player $player
     * @return self
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function addPlayer(Player $player): self
    {
        $playerId = $player->getId();

        if (!isset($playerId)) {
            $playerId = -1;
        }

        if ($playerId > 0) {
            if (!in_array($playerId, $this->players)) {
                $this->players[$playerId] = $player;
                $player->setTeam($this);
            }
        }

        return $this;
    }

    /**
     * Remove a Player object from the Teams Players List.
     *
     * This does not delete the Player object itself
     *
     * @param Player $player
     * @return self
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function removePlayer(Player $player): self
    {
        $playerId = $player->getId();

        if (!isset($playerId)) {
            $playerId = -1;
        }

        if ($playerId > 0) {
            unset($this->players[$playerId]);

            if ($player->getTeamId() == $this->id) {
                $player->setTeam(null);
            }
        }

        return $this;
    }
}
