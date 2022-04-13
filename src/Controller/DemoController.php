<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/demo', name: 'app_demo')]
    public function index(UserRepository $userRepo,
                          EntityManagerInterface $em
    ): Response
    {
       /* $user = new User;
        $user->setName('Mike')
            ->setEmail('mike@gmail.com')
            ->setPassword('$2y$13$HPRkBkASYrp21AeQKSr8N.3iek9MY.cqUdxpiSnBS/fSwAyvf0T26');

        $userRepo->add($user);*/

        $user = $userRepo->find(1);
        $user->setName('Mike le bogosse');
        $em->flush();

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DemoController.php',
        ]);
    }
}
