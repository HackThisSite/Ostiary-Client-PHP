<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\Client as OstiaryClient;

/**
 * @covers \Ostiary\Client
 */
class DebugCallbackTest extends TestCase {

  /**
   * @group base
   * @expectedException        InvalidArgumentException
   * @expectedExceptionMessage Debug callback is not callable
   */
  public function testDebugCallbackBad() {
    $this->assertNotInstanceOf(
      OstiaryClient::class,
      new OstiaryClient(array(
        'id' => 'test',
        'secret' => 'secret',
      ), array(uniqid())));
  }

  /**
   * @group base
   */
  public function testDebugCallbackOkay() {
    $ostiary = new OstiaryClient(array(
      'id' => 'test',
      'secret' => 'secret',
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
