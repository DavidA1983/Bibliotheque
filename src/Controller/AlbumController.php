<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Serie;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/album')]
class AlbumController extends AbstractController
{
    #[Route('', name: 'app_album_index', methods: ['GET'])]
    public function index(AlbumRepository $albumRepository): Response
    {
        return $this->render('album/index.html.twig', [
            'albums' => $albumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_album_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        SerieRepository $serieRepository
    ): Response {
        $album = new Album();

        // Lier automatiquement la série si serie_id présent
        $serieId = $request->query->get('serie_id');
        if ($serieId) {
            $serie = $serieRepository->find($serieId);
            if ($serie) {
                $album->setSerie($serie);
            }
        }

        // 👉 Le formulaire en mode NEW → is_edit = false
        $form = $this->createForm(AlbumType::class, $album, [
            'is_edit' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion image couverture
            $imageFile = $form->get('couverture')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $e) {}

                $album->setCouverture($newFilename);
                $album->setCouvertureUpdatedAt(new \DateTime());
            }

            $em->persist($album);
            $em->flush();

            return $this->redirectToRoute('app_serie_show', [
    'id' => $album->getSerie()->getId(),
]);
        }

        return $this->render('album/new.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_album_show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }

   #[Route('/{id}/edit', name: 'app_album_edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    Album $album,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): Response {

    // Formulaire en mode EDIT → is_edit = true
    $form = $this->createForm(AlbumType::class, $album, [
        'is_edit' => true,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // Gestion upload image
        $imageFile = $form->get('couverture')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            // On génère toujours un nouveau nom de fichier
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
            }

            $album->setCouverture($newFilename);
            $album->setCouvertureUpdatedAt(new \DateTime()); // Toujours mettre à jour la date
        }

        $em->flush();

        $this->addFlash('success', 'Album mis à jour avec succès !');

        return $this->redirectToRoute('app_serie_show', [
    'id' => $album->getSerie()->getId(),
]);
    }

    return $this->render('album/edit.html.twig', [
        'album' => $album,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $album->getId(), $request->request->get('_token'))) {
            $em->remove($album);
            $em->flush();
        }

        return $this->redirectToRoute('app_album_index');
    }

    #[Route('/serie/{id}', name: 'app_album_by_serie', methods: ['GET'])]
    public function bySerie(Serie $serie, AlbumRepository $albumRepository): Response
    {
        $albums = $albumRepository->findBy(
            ['serie' => $serie],
            ['numero' => 'ASC']
        );

        return $this->render('album/by_serie.html.twig', [
            'serie' => $serie,
            'albums' => $albums,
        ]);
    }

#[Route('/{id}/toggle-lu', name: 'app_album_toggle_lu', methods: ['POST'])]
public function toggleLu(Request $request, AlbumRepository $albumRepository, EntityManagerInterface $em, int $id): Response
{
    $album = $albumRepository->find($id);
    if (!$album) {
        throw $this->createNotFoundException('Album non trouvé');
    }

    if ($this->isCsrfTokenValid('toggle_lu' . $album->getId(), $request->request->get('_token'))) {
        $album->setLu(!$album->isLu());
        $em->flush();
    }

    // Reste sur la page d’édition si nécessaire
    $redirect = $request->request->get('redirect');
    if ($redirect === 'edit') {
        return $this->redirectToRoute('app_album_edit', ['id' => $album->getId()]);
    }

    return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
}

}

