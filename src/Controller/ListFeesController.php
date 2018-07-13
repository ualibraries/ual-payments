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
        $user = $this->getUser();
        $transactionToNotify = $this->processTransactions();

        $totalDue = 0.0;
        $userFees = $this->userData->listFees($this->api->getUserFees($user->getUsername()));
        foreach ($userFees as $userFee) {
            $totalDue += $userFee['balance'];
        }

        return $this->render('views/index.html.twig', [
            'full_name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'user_fees' => $userFees,
            'total_Due' => $totalDue,
            'transaction' => $transactionToNotify
        ]);
    }

    /**
     * Remove user's pending transactions and return the latest transaction if it has not been notified.
     * @return Transaction|null Return null if the latest transaction has been notified or in PENDING status.
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

        $latestTransaction = $repository->findBy(['user_id' => $userId], ['date' => 'DESC'], 1);
        if (empty($latestTransaction) or $latestTransaction[0]->getNotified() or ($latestTransaction[0]->getStatus() === Transaction::STATUS_PENDING)) {
            $entityManager->flush();
            return null;
        } else {
            $latestTransaction[0]->setNotified(true);
            $entityManager->persist($latestTransaction[0]);
            $entityManager->flush();
            return $latestTransaction[0];
        }
    }
}
