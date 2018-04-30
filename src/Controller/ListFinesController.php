<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ListFinesController extends Controller
{
    public function index()
    {
        $request = Request::createFromGlobals();
        // $uaid = $request->server->get('Shib-uaId');
        $uaid = $request->server->get('Shib-uaId');

        return $this->render('list_fines/index.html.twig', [
            'controller_name' => 'ListFinesController',
            'uaid' => $uaid
        ]);
    }
}
