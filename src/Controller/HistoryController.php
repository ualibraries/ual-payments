<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends Controller
{
    private $api;
    private $userData;

    public function __construct(AlmaApi $api, AlmaUserData $userData)
    {
        $this->api = $api;
        $this->userData = $userData;
    }

    /**
     * @Route("/history", name="history")
     */
    public function index()
    {
        $userId = $this->getUser()->getUsername();
        $transactions = $this->getDoctrine()->getRepository(Transaction::class)->findBy(['user_id' => $userId]);
        return $this->render('views/history.html.twig', [
            'full_name' => $this->userData->getFullNameAsString($this->api->getUserById($userId)),
            'transactions' => $transactions
        ]);
    }
}
