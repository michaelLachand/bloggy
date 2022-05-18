<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );

        return $this->render('posts/index.html.twig', compact('pagination'));
    }

    #[Route(path:'/posts/{year}/{month}/{day}/{slug}',
            name: 'app_posts_show',
            requirements: [
                'year' => '[0-9]{4}',
                'month' => '[0-9]{2}',
                'day' => '[0-9]{2}',
                'slug' => '[a-z0-9\-]+',
           ] ,
            methods: ['GET'])]
    public function show(int $year, int $month, int $day, string $slug): Response
    {

        $post = $this->postRepository->findOneByPublishDateAndSlug($year, $month, $day, $slug);

        if(is_null($post)){
            throw $this->createNotFoundException();
        }

        return $this->render('posts/show.html.twig',compact('post'));
    }


}
