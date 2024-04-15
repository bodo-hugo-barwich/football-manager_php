<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Team;

/**
 * @extends ServiceEntityRepository<Player>
 *
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    /**
     * Create a new PlayerRepository instance
     *
     * @param ManagerRegistry $registry
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * Commit the new Player to the database
     *
     * @param Player $entity
     * @param bool $flush
     * @psalm-suppress PossiblyUnusedParam
     */
    public function add(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Commit changes on Player instances to the database
     */
    public function write(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Delete a Player entry from the database
     *
     * @param Player $entity
     * @param bool $flush
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function remove(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Look up Players with a Team instance
     *
     * @return Player[] Returns an array of Player objects
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function findByTeam(Team $entity): array
    {
        return $this->createQueryBuilder('p')
        ->andWhere('p.team_id = :team_id')
        ->setParameter('team_id', $entity->getId())
        ->orderBy('p.id', 'ASC')
        // ->setMaxResults(10)
        ->getQuery()
        ->getResult()
        ;
    }

    /**
     * Look up a Player by its 'name' field
     *
     * @param string $name
     * @return Player|NULL
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function findOneByName(string $name): ?Player
    {
        if ('' === $name) {
            return null;
        }

        return $this->createQueryBuilder('p')
        ->andWhere('p.name = :val')
        ->setParameter('val', $name)
        ->getQuery()
        ->getOneOrNullResult();
    }

    /**
     * Look up a Player by its 'surname' field
     *
     * @param string $surname
     * @return Player|NULL
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function findOneBySurname(string $surname): ?Player
    {
        if ('' === $surname) {
            return null;
        }

        return $this->createQueryBuilder('p')
        ->andWhere('p.surname = :val')
        ->setParameter('val', $surname)
        ->getQuery()
        ->getOneOrNullResult();
    }

    /**
     * Get the total count of Player entries in the database
     *
     * @return int
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getTotalCount(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(id) FROM player p';

        $resultSet = $conn->executeQuery($sql);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative()[0];
    }
}
