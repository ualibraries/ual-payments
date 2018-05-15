<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends Behat\MinkExtension\Context\MinkContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
    }

    /**
     * @BeforeFeature @fee
     */
    public static function createFee()
    {
        $api = new AlmaApi();
        $userId = getenv('TEST_ID');
        $testFeeBody = file_get_contents(__DIR__ . '/../../tests/Service/TestJSONData/fee1.json');
        $api->createUserFee($userId, json_decode($testFeeBody));
    }

    /**
     * @AfterFeature @fee
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function removeFees()
    {
        $api = new AlmaApi();
        $userData = new AlmaUserData();
        $userId = getenv('TEST_ID');
        $fees = $userData->listFines($api->getUserFines($userId));

        foreach ($fees as $fee) {
            $api->payUserFee($userId, $fee['id'], '7');
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
}
