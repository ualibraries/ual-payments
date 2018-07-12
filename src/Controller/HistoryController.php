<?php

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends Controller
{
    /**
     * @Route("/history", name="history")
     */
    public function index()
    {
        $user = $this->getUser();
        if (!$user->isValid()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

        $userId = $this->getUser()->getUsername();
        $transactions = $this->getDoctrine()->getRepository(Transaction::class)->findBy([
            'user_id' => $userId],
            ['date' => 'DESC']
        );
        return $this->render('views/history.html.twig', [
            'full_name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'transactions' => $transactions
        ]);
    }
}
