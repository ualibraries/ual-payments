<?php

namespace App\Tests\Service;

use App\Service\AlmaUserData;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class AlmaApiTest extends KernelTestCase
{
    private $api;
    private $userId;
    private $userData;

    public function setUp()
    {
        $kernel = self::bootKernel();
        $api = $kernel->getContainer()->get('test.App\Service\AlmaApi');
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        $this->api = $api;
        $this->userId = getenv('TEST_ID');
        $this->userData = new AlmaUserData();
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
            $userFees = $this->userData->listFees($response);

            $feeNotRemoved = false;
            foreach ($userFees as $fee) {
                if ($fee['id'] == $testFee->id) {
                    $feeNotRemoved = true;
                }
            }
            $this->assertFalse($feeNotRemoved);
        } catch (\Exception $e) {
            $this->fail('Unable to pay user test fee: ' . $e->getMessage());
        }
    }

    public function testAuthenticateUser()
    {
        //Test correct password
        try {
            $response = $this->api->authenticateUser($this->userId, getenv('TEST_PASS'));
            $this->assertEquals(204, $response->getStatusCode());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        //Test wrong password
        try {
            $this->api->authenticateUser($this->userId, uniqid());
            $this->fail('Test wrong password failed');
        } catch (\GuzzleHttp\Exception\TransferException $te) {
            $this->assertEquals(400, $te->getCode());
        }

        //Test random userId
        try {
            $this->api->authenticateUser(uniqid(), uniqid());
            $this->fail('Test random userId failed');
        } catch (\GuzzleHttp\Exception\TransferException $te) {
            $this->assertEquals(400, $te->getCode());
        }
    }

    private function createFeeForTesting($testFeeBody)
    {
        $response = $this->api->createUserFee($this->userId, json_decode($testFeeBody));
        return new SimpleXMLElement($response->getBody());
    }
}
