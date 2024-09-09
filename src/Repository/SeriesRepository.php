<?php

namespace App\Repository;

use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Series>
 *
 * @method Series|null find($id, $lockMode = null, $lockVersion = null)
 * @method Series|null findOneBy(array $criteria, array $orderBy = null)
 * @method Series[]    findAll()
 * @method Series[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Series::class);
    }

    public function remove(Series $serie, bool $flush = false): void
    {
        $this->getEntityManager()->remove($serie);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws ORMException
     */
    public function removeById(int $id): void
    {
        $serie = $this->getEntityManager()->getReference(Series::class, $id);
        $this->remove($serie, true);
    }

    public function add(Series $serie, bool $flush = false): void
    {
        $this->getEntityManager()->persist($serie);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
