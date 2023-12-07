<?php

namespace App\Controller;

use App\Entity\Transaction;
use Doctrine\Persistence\ManagerRegistry;
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
    public function index(ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        $transactions = $doctrine->getRepository(Transaction::class)->findBy(
            ['user_id' => $user->getUserIdentifier()],
            ['date' => 'DESC']
        );
        return $this->render('views/history.html.twig', [
            'full_name' => $user->getFullName(),
            'transactions' => $transactions
        ]);
    }
}
