<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\Client\Utilities as Util;

/**
 * @covers \Ostiary\Client\Utilities
 */
class UtilitiesTest extends TestCase {

  /**
   * @group base
   */
  public function testRandAlnum() {
    // Is 8 bytes
    $this->assertEquals(8, strlen(Util::rand_alnum(8)));
    // Is lowercase alphanumeric
    $this->assertRegExp('/^[a-z0-9]+$/', Util::rand_alnum(64, false));
    // Is mixed-case alphanumeric
    $this->assertRegExp('/^[a-zA-Z0-9]+$/', Util::rand_alnum(64, true));
  }

  /**
   * @group base
   */
  public function testIsAlnum() {
    // Use ctype
    $this->assertTrue(Util::is_alnum('abc'));
    $this->assertTrue(Util::is_alnum('ABC'));
    $this->assertTrue(Util::is_alnum('123'));
    $this->assertTrue(Util::is_alnum('abcABC123'));
    $this->assertFalse(Util::is_alnum('abcABC-123'));
    // Use preg
    $this->assertTrue(Util::is_alnum('abc', true));
    $this->assertTrue(Util::is_alnum('ABC', true));
    $this->assertTrue(Util::is_alnum('123', true));
    $this->assertTrue(Util::is_alnum('abcABC123', true));
    $this->assertFalse(Util::is_alnum('abcABC-123', true));
  }

  /**
   * @group base
   */
  public function testIsUrl() {
    $this->assertTrue(Util::is_url('http://localhost'));
    $this->assertFalse(Util::is_url('host'));
  }

  /**
   * @group base
   */
  public function testIsEmail() {
    $this->assertTrue(Util::is_email('foo@bar.com'));
    $this->assertFalse(Util::is_url('email'));
  }

}

// EOF
