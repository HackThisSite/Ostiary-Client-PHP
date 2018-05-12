<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ostiary\Session;
use Ostiary\Utilities as Util;

/**
 * @covers \Ostiary\Session
 */
class SessionTest extends TestCase {

  /**
   * @group base
   */
  public function testSuccessfullyCreateObject() {
    // Prep variables
    $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE1MjU2ODU2NTgsIm5iZiI6MTUyNTY4NTY1OCwiZXhwIjoxNTI1NzcyMDU4LCJzaWQiOiJmNTJkOGI5Mi1mYzc2LTQ1ZDctYTI4Ny0zYzc5ZTAwNjJiMTMifQ.S8g41Rvla3P-BfLR3jjD2YZxGFG-8B2YJeM4usUrjxs';
    $ttl = 30;
    $uuid = $this->_generateUUID();
    $time = time();

    // Create session
    $session = new Session(
      $uuid,
      $jwt,
      $time,
      $time + $ttl,
      $ttl
    );

    // Is proper class
    $this->assertInstanceOf(
      Session::class,
      $session
    );

    // Values are correct
    $this->assertEquals($uuid, $session->getSessionID());
    $this->assertEquals($jwt, $session->getJWT());
    $this->assertEquals($time, $session->getTimeStarted());
    $this->assertEquals($time + $ttl, $session->getTimeExpiration());
    $this->assertEquals($ttl, $session->getTTL());
    $this->assertEmpty($session->getIPAddress());
    $this->assertEmpty($session->getBucket('global'));
    $this->assertEmpty($session->getBucket('local'));
    $this->assertEmpty($session->getUser());
  }

  private function _generateUUID() {
    try {
      return Uuid::uuid4()->toString();
    } catch (UnsatisfiedDependencyException $e) {
      throw new \RuntimeException('Error generating UUID: '.$e->getMessage());
    }
  }

}

// EOF
