<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TagRepository;


final class TagController extends AbstractController
{
    #[Route('/tags', name: 'sidebar_tag')]
    public function sidebarTags(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('tag/tags.html.twig', [
            'tags' => $tags,
        ]);
    }
}
