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
    private $fee_ids;
    private $total;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->total = 0;
        // To keep track of the fees that the user has selected to pay
        $this->fee_ids = [];
    }

    public function index(Request $request)
    {
        $this->transaction->setUserId($request->request->get('user_id'));
        $this->transaction->setInvoiceNumber(uniqid());
        $this->transaction->setStatus('PENDING');
        $this->transaction->setDate(new \DateTime());
        $fees = $request->request->get('fee');

        $this->entityManager = $this->getDoctrine()->getManager();

        $this->setUserFees($fees);

        $this->transaction->setTotalBalance($this->total);

        if(!$this->totalIsValid($this->transaction->getUserId()))
            $this->transaction->setStatus('FAILED');


        $this->entityManager->persist($this->transaction);
        $this->entityManager->flush();

        if($this->transaction->getStatus() == 'FAILED') {
            return $this->render('pay/error.html.twig');
        }

        return $this->render('pay/index.html.twig', [
            'invoice_number' => $this->transaction->getInvoiceNumber(),
            'total_balance' => $this->transaction->getTotalBalance()
        ]);
    }

    /**
     * Parse through the fees passed from user submitted form and set them in the database.
     * @param $fees - The users fees that they have selected to pay for this transaction.
     */
    public function setUserFees($fees)
    {
        foreach($fees as $fee_json) {
            $fee_properties = json_decode($fee_json);
            $fee = new Fee();
            $fee->setFeeId($fee_properties->id);
            $fee->setBalance($fee_properties->balance);
            $fee->setLabel($fee_properties->label);
            $fee->setTransaction($this->transaction->getId());
            $this->entityManager->persist($fee);
            $this->transaction->addFee($fee);
            $this->total += $fee->getBalance();
            $this->fee_ids[] = $fee->getFeeId();
        }
    }

    /**
     * Get the the users fees from Alma, and check the expected total that the user is going to pay based on the
     * Fee id's that the user selected in the form. This should catch if users have tampered with the form.
     * @param $uaid - The users id
     * @return bool
     */
    public function totalIsValid($userId)
    {
        $userdata = new AlmaUserData();
        $api = new AlmaApi();
        $expected_total = 0;

        $fees = $userdata->listFines($api->getUserFines($userId));
        foreach($fees as $fee) {
            if(in_array($fee['id'], $this->fee_ids)) {
                $expected_total += $fee['balance'];
            }
        }

        if($expected_total !== $this->total)
            return false;
        return true;
    }
}
