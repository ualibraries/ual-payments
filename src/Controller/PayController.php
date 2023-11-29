<?php

namespace App\Controller;

use App\Entity\Fee;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Displays payments about to be sent to the payment processor.
 */
class PayController extends AbstractController
{
    private $api;
    private $userData;
    private $doctrine;

    public function __construct(AlmaApi $api, AlmaUserData $userData, ManagerRegistry $doctrine)
    {
        $this->api = $api;
        $this->userData = $userData;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/pay", name="payment_handler")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(Request $request)
    {
        $feeIds = $request->request->get('fee');
        if (!isset($feeIds) || !count($feeIds)) {
            return $this->redirectToRoute('index');
        }

        $transaction = new Transaction($this->getUser()->getUsername());

        $entityManager = $this->doctrine->getManager();
        if ($this->setUserFees($transaction, $feeIds) == 0) {
            return $this->redirectToRoute('index');
        }

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->render('views/pay.html.twig', [
            'user_id' => $transaction->getUserId(),
            'invoice_number' => $transaction->getInvoiceNumber(),
            'total_balance' => $transaction->getTotalBalance(),
            'payflow_url' => $_ENV['PAYFLOW_URL'],
            'payflow_login' => $_ENV['PAYFLOW_LOGIN'],
            'payflow_partner' => $_ENV['PAYFLOW_PARTNER'],
        ]);
    }

    /**
     * Use the fee id to get the information about the fee (including balance) from Alma, than add them to the transaction.
     * @param Transaction $transaction
     * @param $feeIds
     * @return float
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function setUserFees(Transaction $transaction, $feeIds)
    {
        $userId = $transaction->getUserId();
        $almaFees = $this->userData->listFees($this->api->getUserFees($userId));

        $total = 0.0;
        foreach ($almaFees as $almaFee) {
            if (in_array($almaFee['id'], $feeIds)) {
                $fee = new Fee($almaFee['id'], $almaFee['balance'], $almaFee['label']);

                $this->doctrine->getManager()->persist($fee);
                $transaction->addFee($fee);
                $total += $almaFee['balance'];
            }
        }
        $transaction->setTotalBalance($total);

        return $total;
    }
}
