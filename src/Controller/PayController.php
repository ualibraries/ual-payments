<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Transaction;
use App\Entity\Fee;
use GuzzleHttp\Client;

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
        $fees = $request->request->get('fee');

        $this->entityManager = $this->getDoctrine()->getManager();

        $this->setUserFees($fees);

        $this->entityManager->persist($this->transaction);
        $this->entityManager->flush();


        return $this->render('pay/index.html.twig');
    }

    public function setUserFees($fees) {
        foreach($fees as $fee_json) {
            $fee_properties = json_decode($fee_json);
            $fee = new Fee();
            $fee->setFeeId($fee_properties->id);
            $fee->setTransaction($this->transaction->getId());
            $this->entityManager->persist($fee);
            $this->transaction->addFee($fee);
            $this->total += $fee_properties->balance;
        }
    }
}
