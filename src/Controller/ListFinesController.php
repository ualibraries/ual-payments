<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Service\RetrieveAlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ListFinesController extends Controller
{
    public function index(RetrieveAlmaUserData $userdata)
    {
        $user = new AlmaUser();
        $uaid = $user->getUaId();

        $alma_user_exists = $userdata->almaUserExists($uaid);

        if ($uaid === null && !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        return $this->render('list_fines/index.html.twig', [
            'controller_name' => 'ListFinesController',
            'full_name' => $userdata->getUsersFullName($uaid),
            'user_fines' => $userdata->getUserFines($uaid)
        ]);
    }
}
