<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\SharePostFormType;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    public function share(Request $request,MailerInterface $mailer, string $date, string $slug): Response
    {

        $post = $this->postRepository->findOneByPublishDateAndSlug($date, $slug);

        if(is_null($post)){
            throw $this->createNotFoundException('Post not found!');
        }

        $form = $this->createForm(SharePostFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $data = $form->getData();

           $subject = sprintf('%s recommends you to read "%s"', $data['sender_name'], $post->getTitle());

           $email = (new TemplatedEmail())
                ->from(new Address(
                    $this->getParameter('app.contact_email'),
                    $this->getParameter('app.name')
                    )
                )
                ->to($data['receiver_email'])
                ->subject($subject)
                ->htmlTemplate('emails/posts/share.html.twig')
                ->context([
                    'post' => $post,
                    'sender_name' => $data['sender_name'],
                    'sender_comments' => $data['sender_comments'],

                ]);

           $mailer->send($email);

           $this->addFlash('success', 'Post successfully shared with your friend!');

           return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('posts/share.html.twig', compact('form', 'post'));
    }


}
