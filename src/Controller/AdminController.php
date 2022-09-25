<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
