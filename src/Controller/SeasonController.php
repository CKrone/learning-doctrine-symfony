<?php

namespace App\Controller;

use App\Repository\SeriesRepository;
use DateInterval;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SeasonController extends AbstractController
{
    public function __construct(
        private SeriesRepository $repository,
        private CacheInterface $cache,
    ) {
    }

    #[Route('/series/{seriesId}/seasons', name: 'app_season')]
    public function index(int $seriesId): Response
    {
        $series = $this->repository->find($seriesId);
        $seasons = $this->cache->get(
            "series_{$seriesId}_seasons",
            function (ItemInterface $item) use ($series) {
                $item->expiresAfter(new DateInterval('PT10S'));
                /** @var PersistentCollection $seasons */
                $seasons = $series->getSeasons();
                $seasons->initialize();
                return $seasons;
            }
        );

        return $this->render('season/index.html.twig', [
            'series' => $series,
            'seasons' => $seasons,
        ]);
    }
}
