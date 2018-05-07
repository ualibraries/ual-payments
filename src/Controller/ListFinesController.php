<?php

namespace App\Controller;

use App\Entity\AlmaUser;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

use \SimpleXMLElement;

class ListFinesController extends Controller
{
    private $user;
    private $api;
    private $userdata;

    public function __construct(AlmaApi $api, AlmaUserData $userdata)
    {
        $this->user = new AlmaUser();
        $this->api = $api;
        $this->userdata = $userdata;
    }

    public function index()
    {
        $uaid = $this->user->getUaId();

        $alma_user_exists = $this->userdata->isValidUser($this->api->findUserById($uaid));

        if ($uaid === null || !$alma_user_exists) {
            return $this->render('unauthorized.html.twig');
        }

        return $this->render('list_fines/index.html.twig', [
            'full_name' => $this->userdata->getFullNameAsString($this->api->getUserById($uaid)),
            'user_fines' => $this->userdata->listFines($this->api->getUserFines($uaid))
        ]);
    }

    /**
     * @Route("/pay/{feeId}/{amount}", name="pay_fee")
     */
    public function payFee($feeId, $amount)
    {
        $uaid = $this->user->getUaId();
        try {
            $this->api->payUserFee($uaid, $feeId . 'fdasv', $amount);
            $this->addFlash('notice', 'Transaction complete.');
        } catch(\GuzzleHttp\Exception\TransferException $e) {
            $this->addFlash('error', 'We were unable to process your transaction.');
        }
        return new RedirectResponse("/");
    }
}
