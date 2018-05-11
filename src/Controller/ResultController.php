<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\AlmaApi;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResultController extends Controller
{
    public function result(Request $request)
    {
        //No result code in the request
        $resultCode = $request->request->get('RESULT');
        if (is_null($resultCode)) {
            return new Response('Missing result code', Response::HTTP_BAD_REQUEST);
        }

        //Cannot find the transaction in the database
        $invoiceNumber = $request->request->get('INVOICE');
        $transaction = $this->getDoctrine()->getRepository(Transaction::class)->findOneBy(['invoice_number' => $invoiceNumber]);
        if (!$transaction) {
            return new Response('Cannot find the transaction', Response::HTTP_BAD_REQUEST);
        }

        //The transaction is already paid or updated.
        $status = $transaction->getStatus();
        if ($status == 'PAID' || $status == 'COMPLETED') {
            return new Response('The transaction is completed.', Response::HTTP_OK);
        }

        //Amount does not match.
        $entityManager = $this->getDoctrine()->getManager();
        if ($transaction->getTotalBalance() != $request->request->get('AMOUNT')) {
            $transaction->setStatus('FAILED');
            $entityManager->persist($transaction);
            $entityManager->flush();
            return new Response('Invalid amount', Response::HTTP_BAD_REQUEST);
        }

        //The transaction is declined on Payflow.
        if ($resultCode != 0) {
            $transaction->setStatus('DECLINED');
            $entityManager->persist($transaction);
            $entityManager->flush();
            return new Response('Declined by Payflow', Response::HTTP_OK);
        }

        $transaction->setStatus('PAID');

        $this->updateFeesOnAlma($transaction);

        $transaction->setStatus('COMPLETED');

        $entityManager->persist($transaction);
        $entityManager->flush();

        return new Response("Success", Response::HTTP_OK);
    }

    private function updateFeesOnAlma(Transaction $transaction)
    {
        $api = new AlmaApi();

        $fees = $transaction->getFees();
        foreach ($fees as $fee) {
            $api->payUserFee($transaction->getUserId(), $fee->getFeeId(), $fee->getBalance());
        }
    }
}
