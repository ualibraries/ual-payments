<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Transaction;
use App\Entity\Fee;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;

class PayController extends Controller
{
    private $transaction;
    private $entityManager;
    private $total;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->total = 0;
    }

    public function index(Request $request)
    {
        $this->transaction->setUserId($request->request->get('user_id'));
        $this->transaction->setInvoiceNumber(uniqid());
        $this->transaction->setStatus('PENDING');
        $this->transaction->setDate(new \DateTime());
        $feeIds = $request->request->get('fee');

        $this->entityManager = $this->getDoctrine()->getManager();

        $this->setUserFees($this->transaction->getUserId(), $feeIds);

        $this->transaction->setTotalBalance($this->total);

        $this->entityManager->persist($this->transaction);
        $this->entityManager->flush();

        return $this->render('pay/index.html.twig', [
            'invoice_number' => $this->transaction->getInvoiceNumber(),
            'total_balance' => $this->transaction->getTotalBalance()
        ]);
    }

    /**
     * Use the fee id to get the information about the fee (including balance) from Alma
     * @param $fees - The users fees that they have selected to pay for this transaction.
     */
    private function setUserFees($userId, $feeIds)
    {
        $userdata = new AlmaUserData();
        $api = new AlmaApi();

        $almaFees = $userdata->listFines($api->getUserFines($userId));

        foreach($almaFees as $almaFee) {
            if(in_array($almaFee['id'], $feeIds)) {
                $fee = new Fee();
                $fee->setFeeId($almaFee['id']);
                $fee->setBalance($almaFee['balance']);
                $fee->setLabel($almaFee['balance']);
                $this->transaction->addFee($fee);
                $this->entityManager->persist($fee);
                $this->total += $fee->getBalance();
            }
        }
    }
}
