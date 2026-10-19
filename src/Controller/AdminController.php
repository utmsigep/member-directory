<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/admin')]
class AdminController extends AbstractController
{
    #[Route(path: '/', name: 'admin')]
    public function index()
    {
        return $this->render('admin/admin.html.twig');
    }
}
