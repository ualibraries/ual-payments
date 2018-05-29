<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ListFeesController extends Controller
{
    private $api;
    private $userData;

    public function __construct(AlmaApi $api, AlmaUserData $userData)
    {
        $this->api = $api;
        $this->userData = $userData;
    }

    /**
     * @Route("/", name="index")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index()
    {
        $transactionToNotify = $this->processTransactions();

        $userId = $this->getUser()->getUsername();
        $alma_user_exists = $this->userData->isValidUser($this->api->findUserById($userId));

        if ($userId === null || !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        $totalDue = 0;
        $userFees = $this->userData->listFees($this->api->getUserFees($userId));
        foreach ($userFees as $userFee) {
            $totalDue += $userFee['balance'];
        }

        return $this->render('list_fees/index.html.twig', [
            'full_name' => $this->userData->getFullNameAsString($this->api->getUserById($userId)),
            'user_id' => $userId,
            'user_fees' => $userFees,
            'total_Due' => $totalDue,
            'transaction' => $transactionToNotify
        ]);
    }

    /**
     * Remove user's pending transactions and return the latest transaction if it has not been notified.
     * @return Transaction|null
     */
    private function processTransactions()
    {
        $userId = $this->getUser()->getUsername();
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $entityManager = $this->getDoctrine()->getManager();

        $transactions = $repository->findBy([
            'user_id' => $userId,
            'status' => Transaction::STATUS_PENDING
        ]);

        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }

        $latestTransaction = $repository->findOneBy(['user_id' => $userId], ['date' => 'DESC']);
        if (is_null($latestTransaction) or $latestTransaction->getNotified()) {
            return null;
        } else {
            $latestTransaction->setNotified(true);
            $entityManager->persist($latestTransaction);
        }

        $entityManager->flush();
        return $latestTransaction;
    }
}
