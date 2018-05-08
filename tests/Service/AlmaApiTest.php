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
    private $uaid;

    public function setUp()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        $this->api = new AlmaApi();
        $this->uaid = getenv('TEST_UAID');
        $this->userdata = new AlmaUserData($this->uaid);

        $testFeeBody = file_get_contents(__DIR__ . '/TestJSONData/fee1.json');
        $response = $this->api->createUserFee($this->uaid, json_decode($testFeeBody));
        $this->testFee = new SimpleXMLElement($response->getBody());

        parent::setUp();
    }

    /**
    * Test that a 200 response code is provided in the response object returned by getUserFines.
    */
    public function testGetUserFines()
    {
        $userfines = $this->api->getUserFines($this->uaid);
        $this->assertEquals(200, $userfines->getStatusCode());
    }

    /**
    * Test that a 200 response code is provided in the response object returned by getUsersFullName.
    */
    public function testGetUserById()
    {
        $user = $this->api->getUserById($this->uaid);

        $this->assertEquals(200, $user->getStatusCode());
    }

    public function testFindUserById()
    {
        $user = $this->api->findUserById($this->uaid);

        $this->assertEquals(200, $user->getStatusCode());
    }

    public function testPayUserFee()
    {
        try {
            $this->api->payUserFee($this->uaid, $this->testFee->id, (float)$this->testFee->balance);
            $response = $this->api->getUserFines($this->uaid);
            $userfines = $this->userdata->listFines($response);
            foreach ($userfines as $fine) {
                $this->assertNotEquals($this->testFee->id, $fine['id']);
            }
        } catch (\Exception $e) {
            $this->fail("Unable to pay user test fee: " . $e->getMessage());
        }
    }

}
