<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Service\AlmaApi;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * This controller processes the "Silent POST" requests send back from Payflow Link
 * after a payment has been processed.
 */
class ResultController extends AbstractController
{
    private $api;

    public function __construct(AlmaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Process a "Silent POST" request from Payflow Link and updated the status of
     * the transaction within the Payments Application and the fees in Alma.
     *
     * See "Data Returned by the Post and Silent Post Features" on page 56 of the
     * Payflow Link User's Guide (https://www.paypalobjects.com/webstatic/en_US/developer/docs/pdf/pp_payflowlink_guide.pdf)
     * for more information.
     *
     * @Route("/result", name="result")
     * @param Request $request
     * @param ManagerRegistry $doctrine
     * @param LoggerInterface $logger
     * @return Response
     */
    public function result(Request $request, ManagerRegistry $doctrine, LoggerInterface $logger)
    {
        $struct_log = [];
        //No result code in the request
        $resultCode = $request->request->get('RESULT');
        $struct_log['result_code'] = $resultCode;
        if (is_null($resultCode)) {
            $msg = 'Missing result code';
            $response_status = Response::HTTP_BAD_REQUEST;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //Cannot find the transaction in the database
        $invoiceNumber = $request->request->get('INVOICE');
        $transaction = $doctrine->getRepository(Transaction::class)->findOneBy(['invoice_number' => $invoiceNumber]);
        $struct_log['invoice_number'] = $invoiceNumber;
        if (!$transaction) {
            $msg = 'Cannot find the transaction';
            $response_status = Response::HTTP_BAD_REQUEST;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //The transaction is already paid or updated.
        $status = $transaction->getStatus();
        $struct_log['transaction_status'] = $status;
        if ($status === Transaction::STATUS_PAID || $status === Transaction::STATUS_COMPLETED) {
            $msg = 'The transaction is completed.';
            $response_status = Response::HTTP_BAD_REQUEST;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //Amount does not match.
        $entityManager = $doctrine->getManager();
        if ($transaction->getTotalBalance() != $request->request->get('AMOUNT')) {
            $transaction->setStatus(Transaction::STATUS_ERROR);
            $entityManager->persist($transaction);
            $entityManager->flush();
            $msg = 'Invalid amount';
            $response_status = Response::HTTP_BAD_REQUEST;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //Communication error
        if ($resultCode < 0) {
            $msg = 'Communication error';
            $response_status = Response::HTTP_OK;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //The transaction is declined on Payflow.
        if ($resultCode > 0) {
            $transaction->setStatus(Transaction::STATUS_DECLINED);
            $entityManager->persist($transaction);
            $entityManager->flush();
            $msg = 'Declined by Payflow';
            $response_status = Response::HTTP_OK;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        //The transaction is declined by PayPal due to AVS or CSC check failed.
        $responseMessage = $request->request->get('RESPMSG');
        if ($resultCode == 0 && ($responseMessage == 'AVSDECLINED' || $responseMessage == 'CSCDECLINED')) {
            $transaction->setStatus(Transaction::STATUS_DECLINED);
            $entityManager->persist($transaction);
            $entityManager->flush();
            $msg = 'Declined by Payflow';
            $response_status = Response::HTTP_OK;
            $struct_log['response_message'] = $msg;
            $struct_log['response_status'] = $response_status;
            $logger->info(json_encode($struct_log));
            return new Response($msg, $response_status);
        }

        $transaction->setStatus(Transaction::STATUS_PAID);

        if ($this->updateFeesOnAlma($transaction)) {
            $status = Transaction::STATUS_COMPLETED;
            $transaction->setStatus($status);
            $struct_log['transaction_status'] = $status;
        } else {
            $status =Transaction::STATUS_FAILED;
            $transaction->setStatus($status);
            $struct_log['transaction_status'] = $status;
        }

        $entityManager->persist($transaction);
        $entityManager->flush();
        $msg = "Success";
        $response_status = Response::HTTP_OK;
        $struct_log['response_message'] = $msg;
        $struct_log['response_status'] = $response_status;
        $logger->info(json_encode($struct_log));
        return new Response($msg, $response_status);
    }

    /**
     * Update the fees in a given transaction using the Alma API.
     *
     * @param Transaction $transaction
     * @return $result -- true if the update succeeded, false otherwise.
     */
    private function updateFeesOnAlma(Transaction $transaction)
    {
        $result = false;

        $fees = $transaction->getFees();
        foreach ($fees as $fee) {
            try {
                $this->api->payUserFee($transaction->getUserId(), $fee->getFeeId(), $fee->getBalance());
                $result = true;
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
                echo $e->getCode() . $e->getMessage();
            }
        }

        return $result;
    }
}
