<?php

use App\Entity\Fee;
use App\Entity\Transaction;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use GuzzleHttp\Client;
use Symfony\Component\Dotenv\Dotenv;
use Webmozart\Assert\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends Behat\MinkExtension\Context\MinkContext
{
    use Behat\Symfony2Extension\Context\KernelDictionary;

    private $testTransaction;
    private $paymentResponse;
    private $api;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     * @param AlmaApi $api
     */
    public function __construct(AlmaApi $api)
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        $this->testTransaction = null;
        $this->paymentResponse = null;
        $this->api = $api;
    }

    /**
     * @Given I have a fee
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createFee($event)
    {
        $userId = getenv('TEST_ID');
        $testFeeBody = file_get_contents(__DIR__ . '/../../tests/Service/TestJSONData/fee1.json');
        $this->api->createUserFee($userId, json_decode($testFeeBody));
    }

    /**
     * @AfterScenario @fee, @transactions
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function removeFees($event)
    {
        $userData = new AlmaUserData();
        $userId = getenv('TEST_ID');
        $fees = $userData->listFees($this->api->getUserFees($userId));

        foreach ($fees as $fee) {
            $this->api->payUserFee($userId, $fee['id'], $fee['balance']);
        }
    }

    /**
     * @Then the element with class :arg1 should equal the element with class :arg2
     * @throws Exception
     */
    public function theElementWithClassShouldEqualTheElementWithClass($arg1, $arg2)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $element1 = $page->find('css', $arg1)->getText();
        $element2 = $page->find('css', $arg2)->getText();

        if ($element1 != $element2) {
            throw new Exception(
                $element1 . " does not equal " . $element2
            );
        }
    }

    /**
     * @When I check all fees
     */
    public function iCheckAllFees()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $fees = $page->findAll('named', ['checkbox', 'fee[]']);
        foreach ($fees as $fee) {
            $fee->check();
        }
    }

    /**
     * @Given I submit the :arg1 form
     */
    public function iSubmitTheForm($arg1)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $form = $page->findById('chargesList');
        $form->submit();
    }

    /**
     * @Given I have a fee of amount :amount
     */
    public function iHaveAFee($amount)
    {
        $userId = getenv('TEST_ID');
        $testFee = file_get_contents(__DIR__ . '/../../tests/Service/TestJSONData/fee1.json');
        $testFee = json_decode($testFee);
        $testFee->original_amount = $amount;
        $response = $this->api->createUserFee($userId, $testFee);
    }


    /**
     * @Given I have a transaction with :numFees fees of amount :amount
     */
    public function iHaveATransaction($numFees, $amount)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $testFee = file_get_contents(__DIR__ . '/../../tests/Service/TestJSONData/fee1.json');
        $testFee = json_decode($testFee);
        $testFee->original_amount = $amount;
        $userId = getenv('TEST_ID');
        $transaction = new Transaction($userId);
        $total = 0;

        for ($i = 0; $i < $numFees; $i++) {
            $response = $this->api->createUserFee($userId, $testFee);
            $sxml = new SimpleXMLElement($response->getBody());
            $fee = new Fee((int)$sxml->id, (float)$sxml->original_amount, (string)$sxml->type);
            $transaction->addFee($fee);
            $em->persist($fee);
            $total += $amount;
        }
        $transaction->setTotalBalance($total);
        $em->persist($transaction);
        $em->flush();
        $this->testTransaction = $transaction;
    }

    /**
     * @Given I successfully pay for the transaction in Payflow Link
     */

    public function iSuccessfullyPayForTheTransactionInPayflowLink()
    {
        $form_params = $this->getPayFlowSuccessPostArray();
        $this->testPayflowLinkResult($form_params);
    }

    /**
     * @Then I should receive a response from the results endpoint with status code :status and body :body
     */

    public function iShouldReceiveAMessageFromTheResultsEndpoint($status, $body)
    {
        Assert::eq($this->paymentResponse->getBody(), $body);
        Assert::eq($this->paymentResponse->getStatusCode(), $status);
    }

    /**
     * @Given my transaction in Payflow Link is declined
     */

    public function myTransactionInPayflowLinkIsDeclined()
    {
        $form_params = $this->getPayFlowDeclinedPostArray();
        $this->testPayflowLinkResult($form_params);
    }

    /**
     * @Given my transaction in Payflow Link is AVS declined
     */

    public function myTransactionInPayflowLinkIsAvsDeclined()
    {
        $form_params = $this->getPayFlowAvsDeclinedPostArray();
        $this->testPayflowLinkResult($form_params);
    }

    /**
     * @Given my transaction in Payflow Link is CSC declined
     */

    public function myTransactionInPayflowLinkIsCscDeclined()
    {
        $form_params = $this->getPayFlowCscDeclinedPostArray();
        $this->testPayflowLinkResult($form_params);
    }

    protected function testPayflowLinkResult($form_params)
    {
        if ($this->testTransaction === null) {
            throw new \Exception("No test transaction id set.");
        }


        $url = rtrim($this->getMinkParameter('base_url'), '/');
        $url .= $this->getContainer()->get('router')->generate('result');
        $userId = getenv('TEST_ID');

        $form_params['INVOICE'] = $this->testTransaction->getInvoiceNumber();
        $form_params['AMOUNT'] = $this->testTransaction->getTotalBalance();
        $form_params['CUSTID'] = $userId;

        $client = new Client();
        $this->paymentResponse = $client->request('POST', $url, [
            'form_params' => $form_params
        ]);
    }

    /**
     * @Then the fees checklist should not contain the ids of the test transaction fees
     */
    public function theFeesChecklistShoudNotContainTheIdsOfTheTestTransactionFees()
    {
        $this->checkFeesListForTestTransactionFeeIds("false");
    }

    /**
     * @Then the fees checklist should contain the ids of the test transaction fees
     */
    public function theFeesChecklistShoudContainTheIdsOfTheTestTransactionFees()
    {
        $this->checkFeesListForTestTransactionFeeIds();
    }

    protected function checkFeesListForTestTransactionFeeIds($assertMethod = "true")
    {
        $page = $this->getSession()->getPage();
        $nodes = $page->findAll('css', "[name='fee[]']");
        if (count($nodes) === 0 && $assertMethod === "true") {
            throw new \Exception("No fees found.");
        }
        $fees = $this->testTransaction->getFees();
        $feeIds = [];
        foreach ($nodes as $node) {
            $feeIds[] = $node->getAttribute("value");
        }

        foreach ($fees as $fee) {
            Assert::{$assertMethod}(in_array($fee->getFeeId(), $feeIds));
        }
    }

    protected function getPayFlowSuccessPostArray()
    {
        return [
            "STATE" => "",
            "CITYTOSHIP" => "",
            "COUNTRYTOSHIP" => "",
            "AVSDATA" => "YYY",
            "AUTHCODE" => "010101",
            "PHONE" => "",
            "NAMETOSHIP" => "",
            "RESULT" => "0",
            "ZIP" => "23059",
            "EMAILTOSHIP" => "",
            "EMAIL" => "foo@mailinator.com",
            "RESPMSG" => "Approved",
            "INVOICE" => "5aff688b7a291",
            "PHONETOSHIP" => "",
            "FAX" => "",
            "TYPE" => "S",
            "FAXTOSHIP" => "",
            "STATETOSHIP" => "",
            "TAX" => "",
            "CSCMATCH" => "Y",
            "PONUM" => "",
            "NAME" => "",
            "DESCRIPTION" => "",
            "ORIGMETHOD" => "",
            "COUNTRY" => "",
            "ADDRESS" => "123 Fake St",
            "CUSTID" => "test1234",
            "USER10" => "",
            "PNREF" => "A10EAAE47E1D",
            "AMOUNT" => "5.00",
            "ZIPTOSHIP" => "",
            "USER4" => "",
            "ADDRESSTOSHIP" => "",
            "USER3" => "",
            "TRXTYPE" => "",
            "USER6" => "",
            "USER5" => "",
            "USER8" => "",
            "USER7" => "",
            "USER9" => "",
            "METHOD" => "CC",
            "CITY" => "",
            "HOSTCODE" => "00",
            "USER2" => "",
            "USER1" => ""
        ];
    }

    protected function getPayFlowDeclinedPostArray()
    {
        return [
            "STATE" => "",
            "CITYTOSHIP" => "",
            "COUNTRYTOSHIP" => "",
            "AVSDATA" => "YYY",
            "PHONE" => "",
            "NAMETOSHIP" => "",
            "RESULT" => "12",
            "ZIP" => "23059",
            "EMAILTOSHIP" => "",
            "EMAIL" => "wsimpson@email.arizona.edu",
            "RESPMSG" => "Declined",
            "INVOICE" => "5aff6a6919053",
            "PHONETOSHIP" => "",
            "FAX" => "",
            "TYPE" => "S",
            "FAXTOSHIP" => "",
            "STATETOSHIP" => "",
            "TAX" => "",
            "CSCMATCH" => "Y",
            "PONUM" => "",
            "NAME" => "",
            "DESCRIPTION" => "",
            "ORIGMETHOD" => "",
            "COUNTRY" => "",
            "ADDRESS" => "123 Fake St",
            "CUSTID" => "test1234",
            "USER10" => "",
            "PNREF" => "A70EA94C2731",
            "AMOUNT" => "2002.00",
            "ZIPTOSHIP" => "",
            "USER4" => "",
            "ADDRESSTOSHIP" => "",
            "USER3" => "",
            "TRXTYPE" => "",
            "USER6" => "",
            "USER5" => "",
            "USER8" => "",
            "USER7" => "",
            "USER9" => "",
            "METHOD" => "CC",
            "CITY" => "",
            "HOSTCODE" => "05",
            "USER2" => "",
            "USER1" => ""
        ];
    }

    protected function getPayFlowCscDeclinedPostArray()
    {
        return [
            "STATE" => "AZ",
            "CITYTOSHIP" => "",
            "COUNTRYTOSHIP" => "",
            "AVSDATA" => "YNY",
            "AUTHCODE" => "010101",
            "PHONE" => "",
            "NAMETOSHIP" => "",
            "RESULT" => "0",
            "ZIP" => "85721",
            "EMAILTOSHIP" => "",
            "EMAIL" => "foo%40mailinator.com",
            "RESPMSG" => "CSCDECLINED",
            "INVOICE" => "5b58c517d9e1e",
            "PHONETOSHIP" => "",
            "FAX" => "",
            "TYPE" => "S",
            "FAXTOSHIP" => "",
            "STATETOSHIP" => "",
            "TAX" => "",
            "CSCMATCH" => "N",
            "PONUM" => "",
            "NAME" => "William+S",
            "DESCRIPTION" => "",
            "ORIGMETHOD" => "",
            "COUNTRY" => "",
            "ADDRESS" => "123",
            "CUSTID" => "Circleci",
            "USER10" => "",
            "PNREF" => "A70EAA268924",
            "AMOUNT" => "7.00",
            "ZIPTOSHIP" => "",
            "USER4" => "",
            "ADDRESSTOSHIP" => "",
            "USER3" => "",
            "TRXTYPE" => "",
            "USER6" => "",
            "USER5" => "",
            "USER8" => "",
            "USER7" => "",
            "USER9" => "",
            "METHOD" => "CC",
            "CITY" => "Tucson",
            "HOSTCODE" => "00",
            "USER2" => "",
            "USER1" => ""
        ];
    }

    protected function getPayFlowAvsDeclinedPostArray()
    {
        return [
            "STATE" => "AZ",
            "CITYTOSHIP" => "",
            "COUNTRYTOSHIP" => "",
            "AVSDATA" => "NNN",
            "AUTHCODE" => "010101",
            "PHONE" => "",
            "NAMETOSHIP" => "",
            "RESULT" => "0",
            "ZIP" => "85721",
            "EMAILTOSHIP" => "",
            "EMAIL" => "foo%40mailinator.com",
            "RESPMSG" => "AVSDECLINED",
            "INVOICE" => "5b58c517d9e1e",
            "PHONETOSHIP" => "",
            "FAX" => "",
            "TYPE" => "S",
            "FAXTOSHIP" => "",
            "STATETOSHIP" => "",
            "TAX" => "",
            "CSCMATCH" => "Y",
            "PONUM" => "",
            "NAME" => "William+S",
            "DESCRIPTION" => "",
            "ORIGMETHOD" => "",
            "COUNTRY" => "",
            "ADDRESS" => "49354+Main",
            "CUSTID" => "Circleci",
            "USER10" => "",
            "PNREF" => "A70EAA26896D",
            "AMOUNT" => "7.00",
            "ZIPTOSHIP" => "",
            "USER4" => "",
            "ADDRESSTOSHIP" => "",
            "USER3" => "",
            "TRXTYPE" => "",
            "USER6" => "",
            "USER5" => "",
            "USER8" => "",
            "USER7" => "",
            "USER9" => "",
            "METHOD" => "CC",
            "CITY" => "Tucson",
            "HOSTCODE" => "00",
            "USER2" => "",
            "USER1" => ""
        ];
    }

    /**
     * @Given I fill in :arg1 with the ENV variable :arg2
     */
    public function iFillInWithTheEnvVariable($arg1, $arg2)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $form = $page->findById('login_form');
        try {
            $form->fillField($arg1, getenv($arg2));
        } catch (\Behat\Mink\Exception\ElementNotFoundException $e) {
            print($e);
            return;
        }
    }
}
