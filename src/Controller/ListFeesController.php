<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ListFeesController extends Controller
{
    private $userId;
    private $api;
    private $userData;

    public function __construct(AlmaApi $api, AlmaUserData $userData)
    {
        $user = new AlmaUser();
        $this->userId = $user->getUserId();
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

        $alma_user_exists = $this->userData->isValidUser($this->api->findUserById($this->userId));

        if ($this->userId === null || !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        $totalDue = 0;
        $userFees = $this->userData->listFees($this->api->getUserFees($this->userId));
        foreach ($userFees as $userFee) {
            $totalDue += $userFee['balance'];
        }

        return $this->render('list_fees/index.html.twig', [
            'full_name' => $this->userData->getFullNameAsString($this->api->getUserById($this->userId)),
            'user_id' => $this->userId,
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
        $repository = $this->getDoctrine()->getRepository(Transaction::class);
        $entityManager = $this->getDoctrine()->getManager();

        $transactions = $repository->findBy([
            'user_id' => $this->userId,
            'status' => Transaction::STATUS_PENDING
        ]);

        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }

        $latestTransaction = $repository->findOneBy(['user_id' => $this->userId], ['date' => 'DESC']);
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
