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

//  /**
//   * Test that a 200 response code is provided in the response object returned by getUsersFullName.
//   */
//  public function testGetUsersFullName()
//  {
//    $user_full_name = $this->api->getUsersFullName($this->uaid);
//
//    $this->assertEquals(200, $user_full_name->getStatusCode());
//  }
//
//  /**
//   * Test that a 200 response code is provided in the response object returned by getAlmaUser.
//   */
//  public function testGetAlmaUser()
//  {
//    $userfines = $this->api->getAlmaUser($this->uaid);
//
//    $this->assertEquals(200, $userfines->getStatusCode());
//  }

}
