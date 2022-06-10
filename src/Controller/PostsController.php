<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\SharePostFormType;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class PostsController extends AbstractController
{

    public function __construct(
        private PostRepository $postRepository)
    {
    }

    #[Route(path:'/', name: 'app_home', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->postRepository->getAllPublishedOrderedQuery();

        $page = $request->query->getInt('page', 1);

        $pagination = $paginator->paginate(
            $query,
            $page,
            Post::NUM_ITEMS_PER_PAGE,
            [
                PaginatorInterface::PAGE_OUT_OF_RANGE => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX
            ]
        );

        return $this->render('posts/index.html.twig', compact('pagination'));
    }

    #[Route(path:'/posts/{date}/{slug}',
            name: 'app_posts_show',
            requirements: [
                'date' => Requirement::DATE_YMD,
                'slug' => Requirement::ASCII_SLUG
           ] ,
            methods: ['GET'])]
    public function show(string $date, string $slug): Response
    {

        $post = $this->postRepository->findOneByPublishDateAndSlug($date, $slug);

        if(is_null($post)){
            throw $this->createNotFoundException();
        }

        return $this->render('posts/show.html.twig',compact('post'));
    }

    #[Route(path:'/posts/{date}/{slug}/share',
            name: 'app_posts_share',
            requirements: [
                'date' => Requirement::DATE_YMD,
                'slug' => Requirement::ASCII_SLUG
            ] ,
            methods: ['GET', 'POST']
    )]
    public function share(string $date, string $slug): Response
    {
        $post = $this->postRepository->findOneByPublishDateAndSlug($date, $slug);

        if(is_null($post)){
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SharePostFormType::class);

        return $this->renderForm('posts/share.html.twig', compact('form', 'post'));
    }


}
