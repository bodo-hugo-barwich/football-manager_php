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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function add(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function write(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove(Player $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Player[] Returns an array of Player objects
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

    public function getTotalCount(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(id) FROM player p';

        $resultSet = $conn->executeQuery($sql);

        // returns an array of arrays (i.e. a raw data set)
        return $resultSet->fetchAllAssociative()[0];
    }

    //    public function findOneBySomeField($value): ?Player
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
