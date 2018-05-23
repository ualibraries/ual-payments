<?php
/**
 * Created by PhpStorm.
 * User: cao89
 * Date: 5/1/18
 * Time: 11:42 AM
 */

namespace App\Tests\Service;

use App\Service\AlmaApi;
use App\Service\AlmaUserData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use \SimpleXMLElement;

class AlmaApiTest extends TestCase
{
    private $api;
    private $testFee;
    private $userId;
    private $userdata;

    public function setUp()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        $this->api = new AlmaApi();
        $this->userId = getenv('TEST_ID');
        $this->userdata = new AlmaUserData($this->userId);
        parent::setUp();
    }

    /**
    * Test that a 200 response code is provided in the response object returned by getUserFees.
    */
    public function testGetUserFees()
    {
        $userFees = $this->api->getUserFees($this->userId);
        $this->assertEquals(200, $userFees->getStatusCode());
    }

    /**
    * Test that a 200 response code is provided in the response object returned by getUsersFullName.
    */
    public function testGetUserById()
    {
        $user = $this->api->getUserById($this->userId);

        $this->assertEquals(200, $user->getStatusCode());
    }

    public function testFindUserById()
    {
        $user = $this->api->findUserById($this->userId);

        $this->assertEquals(200, $user->getStatusCode());
    }

    public function testPayUserFee()
    {
        try {
            $testFeeBody = file_get_contents(__DIR__ . '/TestJSONData/fee1.json');
            $testFee = $this->createFeeForTesting($testFeeBody);
            $this->api->payUserFee($this->userId, $testFee->id, (float)$testFee->balance);
            $response = $this->api->getUserFees($this->userId);
            $userFees = $this->userdata->listFees($response);

            $feeNotRemoved = false;
            foreach ($userFees as $fee) {
                if($fee['id'] == $testFee->id) {
                    $feeNotRemoved = true;
                }
            }
        } catch (\Exception $e) {
            $this->fail("Unable to pay user test fee: " . $e->getMessage());
        }

        $this->assertFalse($feeNotRemoved);
    }

    private function createFeeForTesting($testFeeBody) {
        $response = $this->api->createUserFee($this->userId, json_decode($testFeeBody));
        return new SimpleXMLElement($response->getBody());
    }

}
