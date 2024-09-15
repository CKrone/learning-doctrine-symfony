<?php

namespace App\Controller;

use App\DTO\SeriesCreateFromInput;
use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Form\SeriesType;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function showSeriesList(Request $request): Response
    {
        $series = $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $series,
        ]);
    }

    #[Route('/series/create', methods: ['GET'])]
    public function addSerieForm(Request $request): Response
    {
        $seriesForm = $this->createForm(SeriesType::class, new SeriesCreateFromInput());
        return $this->renderForm('series/form.html.twig', compact('seriesForm'));
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request): Response
    {
        $input = new SeriesCreateFromInput();
        $seriesForm = $this->createForm(SeriesType::class, $input)->handleRequest($request);

        if (! $seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm'));
        }
        $series = new Series($input->seriesName);

        $this->addFlash('success', "Série '{$series->getName()}' adicionada com sucesso!");
        $this->seriesRepository->add($input);
        return new RedirectResponse('/series');
    }

    /**
     * @throws ORMException
     */
    #[Route('/series/delete/{id}', name: 'app_series_delete', methods: ['DELETE'])]
    public function deleteSeries(int $id, Request $request): Response
    {
        $this->seriesRepository->removeById($id);
        $this->addFlash('success', 'Série removida com sucesso!');

        return new RedirectResponse('/series');
    }

    #[Route('/series/edit/{id}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(int $id): Response
    {
        $series = $this->entityManager->find(Series::class, $id);

        return $this->render( 'series/form.html.twig', [
            'series' => $series
        ]);
    }

    #[Route('/series/edit/{id}', name: 'app_store_series_changes', methods: ['PUT'])]
    public function storeSeriesChanges(int $id, Request $request): Response
    {
        $this->addFlash('success', 'Série editada com sucesso');
        $series = $this->entityManager->getRepository(Series::class)->find($id);
        $series->setName($request->request->get('name'));
        $this->entityManager->flush();

        return new RedirectResponse( '/series');
    }
}
