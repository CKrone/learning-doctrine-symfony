<?php

namespace App\Repository;

use App\DTO\SeriesCreationInputDTO;
use App\Entity\Series;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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
    public function __construct(
        private SeasonRepository $seasonRepository,
        private EpisodeRepository $episodeRepository,
        ManagerRegistry $registry
    ) {
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

//    public function add(Series $serie, bool $flush = false): void
//    {
//        $this->getEntityManager()->persist($serie);
//
//        if ($flush) {
//            $this->getEntityManager()->flush();
//        }
//
//    }

    public function add(SeriesCreationInputDTO $input): Series
    {
        $entityManager = $this->getEntityManager();
        $series = new Series($input->seriesName, $input->coverImage);
        $entityManager->persist($series);
        $entityManager->flush();

        try {
            $this->seasonRepository->addSeasonsQuantity($input->seasonsQuantity, $series->getId());
            $seasons = $this->seasonRepository->findBy(['series' => $series]);
            $this->episodeRepository->addEpisodesPerSeason($input->episodesPerSeason, $seasons);
        } catch (Exception $e) {
            $this->remove($series, true);
        }

        return $series;
    }
}
