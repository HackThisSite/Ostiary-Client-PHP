<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\Client as OstiaryClient;

/**
 * @covers \Ostiary\Client
 */
class DriverTypeTest extends TestCase {

  /**
   * @group base
   */
  public function testDriverIsPredis() {
    $ost = new OstiaryClient(array(
      'id' => 'test',
      'secret' => 'secret',
      'driver' => 'redis',
      'redis' => 'tcp://localhost',
    ));
    $this->assertInstanceOf(
      \Predis\Client::class,
      $ost->getDriver()
    );
  }

  /**
   * @group base
   */
  public function testDriverIsGuzzle() {
    $ost = new OstiaryClient(array(
      'id' => 'test',
      'secret' => 'secret',
      'driver' => 'ostiary',
    ));
    $this->assertInstanceOf(
      \GuzzleHttp\Client::class,
      $ost->getDriver()
    );
  }

}

// EOF
