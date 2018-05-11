<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HistoryController extends Controller
{
    private $user;
    private $api;
    private $userData;

    public function __construct(AlmaApi $api, AlmaUserData $userData)
    {
        $this->user = new AlmaUser();
        $this->api = $api;
        $this->userData = $userData;
    }

    public function index()
    {
        $userId = $this->user->getUserId();
        $transactions = $this->getDoctrine()->getRepository(Transaction::class)->findBy(['user_id' => $userId]);
        return $this->render('history/index.html.twig', [
            'full_name' => $this->userData->getFullNameAsString($this->api->getUserById($userId)),
            'transactions' => $transactions
        ]);
    }
}
