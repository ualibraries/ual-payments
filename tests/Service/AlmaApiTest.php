<?php
/**
 * Created by PhpStorm.
 * User: cao89
 * Date: 5/1/18
 * Time: 11:42 AM
 */

namespace App\Tests\Service;

use App\Service\AlmaApi;
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
        $body = new \stdClass;
        $body->type = new \stdClass;
        $body->type->value = "OVERDUEFINE";
        $body->type->desc = "Overdue fine";
        $body->original_amount = 3.50;
        $body->comment = "Test fee";
        $body->owner = new \stdClass;
        $body->owner->value = "MAIN";
        $body->owner->desc = "Main Library";

        try {
            $response = $this->api->createUserFee($this->uaid, $body);
            $sxml = new SimpleXMLElement($response->getBody());
            $this->api->payUserFee($this->uaid, $sxml->id, 3.50);
            $this->assertEquals(200, $response->getStatusCode());
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

}
