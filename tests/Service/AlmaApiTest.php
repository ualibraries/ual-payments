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

        $testFeeBody = file_get_contents(__DIR__ . '/TestJSONData/fee1.json');
        $response = $this->api->createUserFee($this->userId, json_decode($testFeeBody));
        $this->testFee = new SimpleXMLElement($response->getBody());

        parent::setUp();
    }

    /**
    * Test that a 200 response code is provided in the response object returned by getUserFines.
    */
    public function testGetUserFines()
    {
        $userfines = $this->api->getUserFines($this->userId);
        $this->assertEquals(200, $userfines->getStatusCode());
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
            $this->api->payUserFee($this->userId, $this->testFee->id, (float)$this->testFee->balance);
            $response = $this->api->getUserFines($this->userId);
            $userfines = $this->userdata->listFines($response);
            foreach ($userfines as $fine) {
                $this->assertNotEquals($this->testFee->id, $fine['id']);
            }
        } catch (\Exception $e) {
            $this->fail("Unable to pay user test fee: " . $e->getMessage());
        }
    }

}
