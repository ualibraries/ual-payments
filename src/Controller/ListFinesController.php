<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ListFinesController extends Controller
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
        $this->removePendingFees();
        $userId = $this->user->getUserId();
        $alma_user_exists = $this->userData->isValidUser($this->api->findUserById($userId));

        if ($userId === null || !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        $totalDue = 0;
        $userFines = $this->userData->listFines($this->api->getUserFines($userId));
        foreach ($userFines as $userFine) {
            $totalDue += $userFine['balance'];
        }

        return $this->render('list_fines/index.html.twig', [
            'full_name' => $this->userData->getFullNameAsString($this->api->getUserById($userId)),
            'user_id' => $this->user->getUserId(),
            'user_fines' => $userFines,
            'total_Due' => $totalDue
        ]);
    }

    private function removePendingFees()
    {
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $entityManager = $this->getDoctrine()->getManager();

        $transactions = $repository->findBy([
            'user_id' => $this->user->getUserId(),
            'status' => 'PENDING'
        ]);

        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }
        $entityManager->flush();
    }
}
