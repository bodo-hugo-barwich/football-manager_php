<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    /**
     * Create a new TeamRepository instance.
     *
     * @psalm-suppress PossiblyUnusedParam
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * Commit the new Team to the database.
     *
     * @psalm-suppress PossiblyUnusedParam
     */
    public function add(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Commit changes on Team instances to the database.
     */
    public function write(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Delete a Team entry from the database.
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function remove(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Look up a Player by its 'name' field.
     *
     * @psalm-suppress PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedParam
     */
    public function findOneByName(string $name): ?Team
    {
        if ('' === $name) {
            return null;
        }

        return $this->createQueryBuilder('t')
        ->andWhere('t.name = :val')
        ->setParameter('val', $name)
        ->getQuery()
        ->getOneOrNullResult();
    }

    /**
     * Get the total count of Team entries in the database.
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getTotalCount(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT COUNT(id) as teams_count FROM team';

        $resultSet = $conn->executeQuery($sql);

        $result = $resultSet->fetchAllAssociative();

        // returns an array of arrays (i.e. a raw data set)
        return $result[0]['teams_count'];
    }
}
