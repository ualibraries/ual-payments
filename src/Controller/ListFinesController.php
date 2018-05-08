<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Entity\Transaction;
use App\Entity\Fee;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \SimpleXMLElement;

class ListFinesController extends Controller
{
    private $user;
    private $api;
    private $userdata;

    public function __construct(AlmaApi $api, AlmaUserData $userdata)
    {
        $this->user = new AlmaUser();
        $this->api = $api;
        $this->userdata = $userdata;
    }

    public function index()
    {
        $this->removePendingFees();
        $uaid = $this->user->getUaId();
        $alma_user_exists = $this->userdata->isValidUser($this->api->findUserById($uaid));

        if ($uaid === null || !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        return $this->render('list_fines/index.html.twig', [
            'full_name' => $this->userdata->getFullNameAsString($this->api->getUserById($uaid)),
            'user_fines' => $this->userdata->listFines($this->api->getUserFines($uaid)),
            'user_id' => $this->user->getUaId()
        ]);
    }

    private function removePendingFees() {
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $entityManager = $this->getDoctrine()->getManager();

        $transactions = $repository->findBy([
            'user_id' => $this->user->getUaId(),
            'status' => 'PENDING'
        ]);
        
        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }
        $entityManager->flush();
    }
}
