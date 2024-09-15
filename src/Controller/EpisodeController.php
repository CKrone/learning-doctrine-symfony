<?php

namespace App\Controller;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EpisodeController extends AbstractController
{
    public function __construct(
        private SeasonRepository $seasonRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/season/{season}/episodes', name: 'app_episode', methods: ['GET'])]
    public function index(int $season): Response
    {
        $season = $this->seasonRepository->find($season);

        return $this->render('episode/index.html.twig', [
            'season' => $season,
            'series' => $season->getSeries(),
            'episodes' => $season->getEpisodes(),
        ]);
    }

    #[Route('/season/{season}/episodes', name: 'app_episodes', methods: ['POST'])]
    public function watched(int $season, Request $request): Response
    {
        $watchedEpisodes = array_keys($request->request->all('episode'));
        $season = $this->seasonRepository->find($season);
        $episodes = $season->getEpisodes();

        foreach ($episodes as $episode) {
            $episode->setWatched(in_array($episode->getId(), $watchedEpisodes));
        }
        $this->entityManager->flush();

        return new RedirectResponse("/season/{$season->getId()}/episodes");
    }
}
