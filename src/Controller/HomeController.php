<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        return $this->render('dashboard/index.html.twig', [
            'controller_name' => "Welcome to my Blog",
        ]);
    }
}