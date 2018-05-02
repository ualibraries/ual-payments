<?php

namespace App\Tests\Service;

use App\Service\AlmaUserData;
use PHPUnit\Framework\TestCase;
use App\Service\AlmaApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Dotenv\Dotenv;

class AlmaUserDataTest extends TestCase
{
    private $userdata;

    public function setUp()
    {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        $uaid = getenv('TEST_UAID');
        $this->userdata = new AlmaUserData($uaid);

        parent::setUp();
    }

    /**
     * Test getting fees when the user has multiple fees.
     */
    public function testListFinesWithMultipleFees()
    {
        $body = file_get_contents(__DIR__ . '/TestXMLData/fees_test_data.xml');
        $mock = new MockHandler([
            new Response(200, [], $body)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        try {
            $response = $client->request('/');
        } catch (GuzzleException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        $givenListOfFines = $this->userdata->listFines($response);
        $expectedListOfFines = [
            [
                'id' => '1599882100003843',
                'label' => 'Lost item replacement fee',
                'balance' => '5.0',
                'title' => 'Dinosaur / by Carl E. Baugh, with Clifford A. Wilson.'
            ],
            [
                'id' => '1603983790003843',
                'label' => 'Card renewal',
                'balance' => '2.0',
                'title' => ''
            ]
        ];

        $this->assertEquals($expectedListOfFines, $givenListOfFines);
    }

    /**
     * Test getting the users full name from Alma
     */
    public function testGetFullNameAsString()
    {
        $body = file_get_contents(__DIR__ . '/TestXMLData/user_test_data.xml');
        $mock = new MockHandler([
            new Response(200, [], $body)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        try {
            $response = $client->request('/');
        } catch (GuzzleException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        $givenName = $this->userdata->getFullNameAsString($response);
        $expectedName = 'Hank Hill';
        $this->assertEquals($expectedName, $givenName);
    }

    /**
     * Test that a user not found in Alma returns false
     */
    public function testCheckInvalidUser()
    {
        $body = file_get_contents(__DIR__ . '/TestXMLData/no_user_found_data.xml');

        $mock = new MockHandler([
            new Response(200, [], $body)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        try {
            $response = $client->request('/');
        } catch (GuzzleException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        $given = $this->userdata->isValidUser($response);
        $expected = false;

        $this->assertEquals($expected, $given);
    }

    /**
     * Test that the Shib-uaId matches a valid primary_id in Alma
     */
    public function testCheckValidUser()
    {
        $body = file_get_contents(__DIR__ . '/TestXMLData/find_user_data.xml');

        $mock = new MockHandler([
            new Response(200, [], $body)
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        try {
            $response = $client->request('/');
        } catch (GuzzleException $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

        $given = $this->userdata->isValidUser($response);
        $expected = true;

        $this->assertEquals($expected, $given);
    }
}
