<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Service\AlmaApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \SimpleXMLElement;

class ListFinesController extends Controller
{
    public function index(AlmaApi $userdata)
    {
        $user = new AlmaUser();
        $uaid = $user->getUaId();

        var_dump(new SimpleXMLElement($userdata->getUserFines($uaid)->getBody()));
        exit();

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
