<?php

namespace App\Controller;

use App\Entity\Fee;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PayController extends Controller
{
    public function index(Request $request)
    {
        $transaction = new Transaction();
        $transaction->setUserId($request->request->get('user_id'));
        $transaction->setInvoiceNumber(uniqid());
        $transaction->setStatus('PENDING');
        $transaction->setDate(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $feeIds = $request->request->get('fee');
        $this->setUserFees($transaction, $feeIds);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->render('pay/index.html.twig', [
            'invoice_number' => $transaction->getInvoiceNumber(),
            'total_balance' => $transaction->getTotalBalance()
        ]);
    }

    /**
     * Use the fee id to get the information about the fee (including balance) from Alma, than add them to the transaction.
     * @param Transaction $transaction
     * @param $feeIds
     */
    private function setUserFees(Transaction $transaction, $feeIds)
    {
        $userData = new AlmaUserData();
        $api = new AlmaApi();

        $userId = $transaction->getUserId();
        $almaFees = $userData->listFines($api->getUserFines($userId));

        $total = 0;
        foreach ($almaFees as $almaFee) {
            if (in_array($almaFee['id'], $feeIds)) {
                $fee = new Fee();
                $fee->setFeeId($almaFee['id']);
                $fee->setBalance($almaFee['balance']);
                $fee->setLabel($almaFee['label']);

                $this->getDoctrine()->getManager()->persist($fee);
                $transaction->addFee($fee);
                $total += $almaFee['balance'];
            }
        }
        $transaction->setTotalBalance($total);
    }
}
