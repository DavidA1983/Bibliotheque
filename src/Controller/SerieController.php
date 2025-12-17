<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Form\SerieType;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/')]
class SerieController extends AbstractController
{
    #[Route('', name: 'app_serie_index', methods: ['GET'])]
    public function index(SerieRepository $serieRepository): Response
    {
        return $this->render('serie/index.html.twig', [
            'series' => $serieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_serie_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $serie = new Serie();
    $form = $this->createForm(SerieType::class, $serie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // Gestion de l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
            } catch (FileException $e) {
                // Gérer l’erreur si besoin
            }

            $serie->setImage($newFilename);
        }

        $em->persist($serie);
        $em->flush();

        return $this->redirectToRoute('app_serie_index');
    }

    return $this->render('serie/new.html.twig', [
        'serie' => $serie,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_serie_show', methods: ['GET'])]
    public function show(Serie $serie): Response
    {
    return $this->render('serie/show.html.twig', [
        'serie' => $serie,
    ]);
}


    #[Route('/{id}/edit', name: 'app_serie_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Serie $serie, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $form = $this->createForm(SerieType::class, $serie);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'), // param à créer dans services.yaml
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l’erreur si nécessaire
            }

            $serie->setImage($newFilename);
        }

        $em->flush();
        return $this->redirectToRoute('app_serie_index');
    }

    return $this->render('serie/edit.html.twig', [
        'serie' => $serie,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_serie_delete', methods: ['POST'])]
    public function delete(Request $request, Serie $serie, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$serie->getId(), $request->request->get('_token'))) {
            $em->remove($serie);
            $em->flush();
        }

        return $this->redirectToRoute('app_serie_index');
    }
}
