<?php namespace Ostiary\Client\Tests;

use PHPUnit\Framework\TestCase;
use Ostiary\User;

/**
 * @covers \Ostiary\User
 */
class UserTest extends TestCase {

  /**
   * @group base
   */
  public function testBlankUser() {
    $user = new User();
    $this->assertInstanceOf(
      User::class,
      $user
    );
    $this->assertEmpty($user->getUsername());
    $this->assertEmpty($user->getDisplayName());
    $this->assertEmpty($user->getEmail());
    $this->assertEmpty($user->getRoles());
    $this->assertEmpty($user->getAllParameters());
  }

  /**
   * @group base
   */
  public function testPopulatedUser() {
    $user = new User('user', 'gecos', 'foo@bar.com', array('USER'), array('key' => 'value'));
    $this->assertInstanceOf(
      User::class,
      $user
    );
    $this->assertEquals('user', $user->getUsername());
    $this->assertEquals('gecos', $user->getDisplayName());
    $this->assertEquals('foo@bar.com', $user->getEmail());
    $this->assertEquals(array('USER'), $user->getRoles());
    $this->assertEquals(array('key' => 'value'), $user->getAllParameters());
  }

}

// EOF
