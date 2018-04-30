<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ListFinesController extends Controller
{
    /**
     * @Route("/", name="list_fines")
     */
    public function index()
    {
        return $this->render('list_fines/index.html.twig', [
            'controller_name' => 'ListFinesController',
        ]);
    }
}
