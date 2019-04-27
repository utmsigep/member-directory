<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class DirectoryController extends AbstractController
{
    /**
     * @Route("/directory", name="directory")
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('directory/index.html.twig', [
            'controller_name' => 'DirectoryController',
        ]);
    }
}
