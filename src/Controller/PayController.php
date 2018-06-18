<?php

namespace App\Controller;

use App\Entity\Fee;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PayController extends Controller
{
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

        $user_id = $request->request->get('user_id');
        $transaction = new Transaction($user_id);

        $entityManager = $this->getDoctrine()->getManager();
        $this->setUserFees($transaction, $feeIds);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->render('pay/index.html.twig', [
            'user_id' => $transaction->getUserId(),
            'invoice_number' => $transaction->getInvoiceNumber(),
            'total_balance' => $transaction->getTotalBalance(),
            'payflow_url' => getEnv("PAYFLOW_URL"),
            'payflow_login' => getEnv("PAYFLOW_LOGIN"),
            'payflow_partner' => getEnv("PAYFLOW_PARTNER"),
        ]);
    }

    /**
     * Use the fee id to get the information about the fee (including balance) from Alma, than add them to the transaction.
     * @param Transaction $transaction
     * @param $feeIds
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function setUserFees(Transaction $transaction, $feeIds)
    {
        $userData = new AlmaUserData();
        $api = new AlmaApi();

        $userId = $transaction->getUserId();
        $almaFees = $userData->listFees($api->getUserFees($userId));

        $total = 0;
        foreach ($almaFees as $almaFee) {
            if (in_array($almaFee['id'], $feeIds)) {
                $fee = new Fee($almaFee['id'], $almaFee['balance'], $almaFee['label']);

                $this->getDoctrine()->getManager()->persist($fee);
                $transaction->addFee($fee);
                $total += $almaFee['balance'];
            }
        }
        $transaction->setTotalBalance($total);
    }
}
