<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\Client as OstiaryClient;

/**
 * @covers \Ostiary\Client
 */
class BasicTest extends TestCase {

  /**
   * @expectedException        Ostiary\Client\Exception\InvalidOptionsException
   * @expectedExceptionMessage Debug callback is not callable
   */
  public function testDebugCallbackBad() {
    $this->assertNotInstanceOf(
      OstiaryClient::class,
      new OstiaryClient(array(
        'driver' => 'redis',
        'redis' => 'tcp://localhost',
      ), array(uniqid())));
  }

  public function testDebugCallbackOkay() {
    $ostiary = new OstiaryClient(array(
      'driver' => 'redis',
      'redis' => 'tcp://localhost',
    ), array(
      $this,
      'debugCallback'
    ));
  }

  public function debugCallback($message) {
    $this->assertNotEmpty($message);
  }

}

// EOF
