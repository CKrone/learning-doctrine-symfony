<?php

namespace App\Controller;

use App\DTO\SeriesCreationInputDTO;
use App\Entity\Series;
use App\Form\SeriesEditType;
use App\Form\SeriesType;
use App\Message\SeriesWasCreated;
use App\Message\SeriesWasDeleted;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private MessageBusInterface $messenger,
        private SluggerInterface $slugger
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
        $seriesForm = $this->createForm(SeriesType::class, new SeriesCreationInputDTO());
        return $this->renderForm('series/form.html.twig', compact('seriesForm'));
    }

    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request): Response
    {
        $input = new SeriesCreationInputDTO();
        $seriesForm = $this->createForm(SeriesType::class, $input)->handleRequest($request);

        if (! $seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm'));
        }

        $uploadCoverImage = $seriesForm->get('coverImage')->getData();

        if ($uploadCoverImage) {
            $originalFilename = pathinfo($uploadCoverImage->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename. '-' .uniqid(). '.' .$uploadCoverImage->guessExtension();
            $uploadCoverImage->move($this->getParameter('cover_image_directory'), $fileName);
            $input->coverImage = $fileName;
        }
        $series = new Series($input->seriesName);

        $this->addFlash('success', "Série '{$series->getName()}' adicionada com sucesso!");
        $this->seriesRepository->add($input);
        $this->messenger->dispatch(new SeriesWasCreated($series));

        return new RedirectResponse('/series');
    }

    /**
     * @throws ORMException
     */
    #[Route('/series/delete/{id}', name: 'app_series_delete', methods: ['DELETE'])]
    public function deleteSeries(int $id, Request $request): Response
    {
        $series = $this->entityManager->find(Series::class, $id);
        $this->seriesRepository->remove($series, true);
        $this->messenger->dispatch(new SeriesWasDeleted($series));
        $this->addFlash('success', 'Série removida com sucesso!');

        return new RedirectResponse('/series');
    }

    #[Route('/series/edit/{id}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(int $id): Response
    {
        $series = $this->entityManager->find(Series::class, $id);
        $seriesForm = $this->createForm(SeriesEditType::class, $series);
        return $this->renderForm('series/edit.html.twig', compact('seriesForm', 'series'));
    }

    #[Route('/series/edit/{id}', name: 'app_store_series_changes', methods: ['PATCH'])]
    public function storeSeriesChanges(int $id, Request $request): Response
    {
        $series = $this->entityManager->getRepository(Series::class)->find($id);
        $seriesForm = $this->createForm(SeriesEditType::class, $series);
        $seriesForm->handleRequest($request);

        if (! $seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm', 'series'));
        }

        $this->addFlash('success', "Série \"{$series->getName()}\" editada com sucesso");
        $this->entityManager->flush();

        return new RedirectResponse('/series');
    }
}
