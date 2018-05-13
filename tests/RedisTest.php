<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\Client as OstiaryClient;
use Ostiary\User;

/**
 * @covers \Ostiary\Client
 * @covers \Ostiary\User
 * @covers \Ostiary\Session
 */
class RedisTest extends TestCase {

  /**
   * @group redis
   */
  public function testRedisDriverComplete() {
    $ost = new OstiaryClient(array(
      'id' => 'test',
      'secret' => 'secret',
      'driver' => 'redis',
      'redis' => 'tcp://localhost',
      'ttl' => 10,
    ));
    $this->assertInstanceOf(
      \Ostiary\Client::class,
      $ost
    );
    $this->assertInstanceOf(
      \Predis\Client::class,
      $ost->getDriver()
    );

    // Create a session

    $user = new User('user', 'gecos', 'foo@bar.com', array('USER'), array('key' => 'value'));
    $values = array(
      'bucket_global' => 'global',
      'bucket_local' => 'local',
      'ip_address' => '1.2.3.4',
      'user' => $user,
    );
    $time = intval(gmdate('U'));
    $session = $ost->createSession($values, array('ttl' => 30));

    $this->assertInstanceOf(
      \Ostiary\Session::class,
      $session
    );
    $this->assertInstanceOf(
      \Ostiary\User::class,
      $session->getUser()
    );
    $this->assertNotEmpty($session->getJWT());
    $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $session->getSessionID());
    // Use >= in case of rare occurance of $time < getTimeStarted
    $this->assertGreaterThanOrEqual($time, $session->getTimeStarted());
    $this->assertGreaterThanOrEqual($time + 30, $session->getTimeExpiration());
    $this->assertEquals(30, $session->getTTL());
    $this->assertEquals('global', $session->getBucket('global'));
    $this->assertEquals('local', $session->getBucket('local'));
    $this->assertEquals('user', $session->getUser()->getUsername());
    $this->assertEquals('gecos', $session->getUser()->getDisplayName());
    $this->assertEquals('foo@bar.com', $session->getUser()->getEmail());
    $this->assertEquals(array('USER'), $session->getUser()->getRoles());
    $this->assertEquals(array('key' => 'value'), $session->getUser()->getAllParameters());

    // Wait 2 seconds so we can bump the expiration
    sleep(2);

    // Get a session

    $time2 = intval(gmdate('U'));
    $get = $ost->getSession($session->getJWT(), array('update_expiration' => true, 'ttl' => 60));

    $this->assertInstanceOf(
      \Ostiary\Session::class,
      $get
    );
    $this->assertInstanceOf(
      \Ostiary\User::class,
      $get->getUser()
    );
    // $session and $get should not be identical, but should hold mostly same values
    $this->assertNotEquals($session, $get);
    $this->assertEquals($session->getSessionID(), $get->getSessionID());
    $this->assertNotEmpty($get->getJWT());
    $this->assertNotEquals($session->getJWT(), $get->getJWT());
    $this->assertEquals($session->getTimeStarted(), $get->getTimeStarted());
    $this->assertGreaterThan($session->getTimeExpiration(), $get->getTimeExpiration());
    $this->assertGreaterThanOrEqual($time, $get->getTimeStarted());
    $this->assertGreaterThanOrEqual($time2 + 60, $get->getTimeExpiration());
    $this->assertEquals(60, $get->getTTL());
    $this->assertEquals('global', $get->getBucket('global'));
    $this->assertEquals('local', $get->getBucket('local'));
    $this->assertEquals('user', $get->getUser()->getUsername());
    $this->assertEquals('gecos', $get->getUser()->getDisplayName());
    $this->assertEquals('foo@bar.com', $get->getUser()->getEmail());
    $this->assertEquals(array('USER'), $get->getUser()->getRoles());
    $this->assertEquals(array('key' => 'value'), $get->getUser()->getAllParameters());
  }

}

// EOF
