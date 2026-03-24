<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * List the Alma fees for a particular user.
 */
class ListFeesController extends AbstractController {
    private $api;
    private $userData;
    private $doctrine;

    public function __construct(AlmaApi $api, AlmaUserData $userData, ManagerRegistry $doctrine) {
        $this->api = $api;
        $this->userData = $userData;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/", name="index")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index() {
        $user = $this->getUser();
        $transactionToNotify = $this->processTransactions();

        $totalDue = 0.0;
        $userFees = $this->userData->listFees($this->api->getUserFees($user->getUserIdentifier()));
        foreach ($userFees as $userFee) {
            $totalDue += $userFee['balance'];
        }

        try {
            $hasTransferFees = $this->userData->responseHasFees($this->api->getUserFees($user->getUserIdentifier(), 'EXPORTED'));
        } catch (\Exception $e) {
            // If there is an error with the API call to check for transfer fees, we log the error and assume that there are no transfer fees so that the user can still pay their other fees.
            $hasTransferFees = false;
        }

        return $this->render('views/index.html.twig', [
            'full_name' => $user->getFullName(),
            'user_fees' => $userFees,
            'total_Due' => $totalDue,
            'has_transfer_fees' => $hasTransferFees,
            'transaction' => $transactionToNotify
        ]);
    }

    /**
     * Remove user's pending transactions and return the latest transaction if it has not been notified.
     * @return Transaction|null Return null if the latest transaction has been notified or in PENDING status.
     */
    private function processTransactions() {
        $userId = $this->getUser()->getUserIdentifier();
        $repository = $this->doctrine->getRepository(Transaction::class);
        $entityManager = $this->doctrine->getManager();

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
