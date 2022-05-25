<?php

namespace App\Controller;

use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Displays previous payments the user has made.
 */
class HistoryController extends AbstractController
{
    /**
     * @Route("/history", name="history")
     */
    public function index()
    {
        $user = $this->getUser();
        $transactions = $this->getDoctrine()->getRepository(Transaction::class)->findBy(
            ['user_id' => $user->getUsername()],
            ['date' => 'DESC']
        );
        return $this->render('views/history.html.twig', [
            'full_name' => $user->getFullName(),
            'transactions' => $transactions
        ]);
    }
}
