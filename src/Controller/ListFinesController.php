<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ListFinesController extends Controller
{
    public function index()
    {
        $user = new AlmaUser();
        $uaid = $user->getUaId();

        if ($uaid === null) {
            return $this->render('unauthorized.html.twig');
        }

        return $this->render('list_fines/index.html.twig', [
            'controller_name' => 'ListFinesController',
            'uaid' => $uaid
        ]);
    }
}
