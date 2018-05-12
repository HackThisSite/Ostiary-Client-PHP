<?php
/**
 * Ostiary\Client
 */

namespace Ostiary;

define('OSTIARY_VERSION', '0.1.2');
if (getenv('OSTIARY_DEBUG') && !defined('OSTIARY_DEBUG')) {
  define('OSTIARY_DEBUG', true);
}

use Ostiary\Client\Utilities as Util;
use Ostiary\Client\Model\Redis as RedisDriver;
use Ostiary\Client\Model\Ostiary as OstiaryDriver;


/**
 * Ostiary\Client interacts either directly with an Ostiary Redis environment, or with an Ostiary server
 */
class Client {


  /**
   * Default options for all functions with options
   * @ignore
   */
  private $default_options = array(
    '__construct' => array(
      'driver'  => 'ostiary',
      'ttl'     => 86400,
      'ostiary' => array(
        'server'  => 'http://localhost:1563',
        'timeout' => 3,
      ),
    ),
    'createSession' => array(
      'ttl' => -1,
    ),
    'getSession' => array(
      'update_expiration' => true,
      'ttl' => -1,
    ),
    'getAllSessions' => array(
      'update_expiration' => false,
      'ttl' => -1,
      'count_only' => false,
    ),
    'setBucket' => array(
      'update_expiration' => true,
      'ttl' => -1,
    ),
    'touchSession' => array(
      'ttl' => -1,
    ),
  );


  /**
   * Options for this Ostiary\Client object
   * @ignore
   */
  private $options;


  /**
   * Driver object to send calls to
   * @ignore
   */
  private $driver;


  /**
   * Construct an Ostiary client.
   *
   * @param array $options Configuration options for this Ostiary client
   * @param callback $debug_callback [optional] Callback function for debug output. Automatically enables debug output. Provides one parameter: (string) Debug message
   * @throws InvalidArgumentException Thrown if $options is invalid
   */
  public function __construct($options, $debug_callback = null) {
    // Set debug callback if defined
    if (!empty($debug_callback)) {
      $this->setDebugCallback($debug_callback);
    }

    Util::debug('Instantiating Ostiary\Client'.($debug_callback ? ' with debug callback' : ''));

    // Validate options
    try {
      $this->options = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Load the driver
    if ($this->options['driver'] == 'redis') {
      $this->driver = new RedisDriver($this->options);
    } else {
      $this->driver = new OstiaryDriver($this->options);
    }
  }


  /**
  * Set the debug callback to be used for logging
  *
  * @param callback $debug_callback Callback function for debug output. Automatically enables debug output. Provides one parameter: (string) Debug message
  * @return boolean True on success
  * @throws InvalidArgumentException Thrown if $debug_callback is not callable
  */
  public function setDebugCallback($debug_callback) {
    if (!is_callable($debug_callback))
      throw new \InvalidArgumentException('Debug callback is not callable');
    Util::$debug_callback = $debug_callback;
    if (!defined('OSTIARY_DEBUG')) define('OSTIARY_DEBUG', true);
    return true;
  }


  /**
   * Return the raw driver object in use.
   *
   * If this client is configured to use Ostiary, this will return a `\GuzzleHttp\Client`
   * object. If configured to use Redis, this will return a `\Predis\Client` object.
   *
   * @return \GuzzleHttp\Client|\Predis\Client Driver object in use for this Ostiary\Client
   */
  public function getDriver() {
    return $this->driver->getDriver();
  }


  /**
   * Create a new Ostiary session
   *
   * @param array $bucket_data [optional] Array of bucket data. Allowed indices: "global" and "local". Default: empty array
   * @param Ostiary\User|null $user [optional] An Ostiary\User object, or null. Default: null
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    ttl  (int)   Override the TTL value for this Ostiary client. Default: -1
   *       Allowed values: -1 = use TTL setting for this client, 0 = never expire, >0 = expire in X seconds
   * @return bool|\Ostiary\Session A populated Ostiary\Session object, or false on failure
   * @throws InvalidArgumentException Thrown if $bucket_data is not an array or if $options is invalid
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function createSession($bucket_data = array(), $user = null, $options = array()) {
    Util::debug('createSession called');

    // Validate user
    if ($user !== null && !is_a($user, 'Ostiary\User'))
      throw new \InvalidArgumentException('User object must be null or an instance of Ostiary\User');

    // Validate options
    $opts = array();
    try {
      $opts = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Set TTL
    $ttl = ($opts['ttl'] < 0 ? $this->options['ttl'] : $opts['ttl']);

    // Validate and set buckets
    if (!is_array($bucket_data)) {
      throw new \InvalidArgumentException('Bucket data must be an array');
      return false;
    }
    $bkt_global = (isset($bucket_data['global']) ? $bucket_data['global'] : null);
    $bkt_local = (isset($bucket_data['local']) ? $bucket_data['local'] : null);

    Util::debug('Creating session with TTL of '.$ttl);

    // Create and return an Ostiary\Session
    return $this->driver->createSession($ttl, $bkt_global, $bkt_local, $user);
  }


  /**
   * Get an Ostiary session by the JSON Web Token identifier
   *
   * @param string $jwt JSON Web Token identifier of the session
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true
   *    ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: -1
   *       Values: -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *       Setting ttl >= 0 will update the TTL setting on record to match this.
   * @return null|\Ostiary\Session A populated Ostiary\Session object, or null on failure
   * @throws InvalidArgumentException Thrown if $bucket_data is not an array or if $options is invalid
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getSession($jwt, $options = array()) {
    Util::debug('getSession called');
    // Validate options
    $opts = array();
    try {
      $opts = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Get the session and return an Ostiary\Session
    return $this->driver->getSession($jwt, $opts['update_expiration'], $opts['ttl']);
  }


  /**
   * Get an Ostiary session from the contents of a cookie
   *
   * @param string $cookie_name Name of the cookie
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true
   *    ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: -1
   *       Values: -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *       Setting ttl >= 0 will update the TTL setting on record to match this.
   * @return null|\Ostiary\Session A populated Ostiary\Session object, or null on failure
   * @throws InvalidArgumentException Thrown if specified cookie doesn't exist or if $options is invalid
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getSessionFromCookie($cookie_name, $options = array()) {
    Util::debug('getSessionFromCookie called');
    if (!isset($_COOKIE[$cookie_name])) return null;
    return $this->getSession($_COOKIE[$cookie_name], $options);
  }


  /**
   * Get all sessions in Ostiary
   *
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    count_only (bool)   Only give the count of sessions, not full details. Default: false
   *    update_expiration  (bool)   Update the expiration time of all sessions to now + TTL (stored TTL or overridden). Warning: This can be a very heavy operation! Default: false
   *    ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: -1
   *       Values: -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *       Setting ttl >= 0 will update the TTL setting on record to match this.
   * @return int|array If `count_only` is true, will return an integer count, otherwise an array of Ostiary\Session objects with their UUIDs as array indices.
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getAllSessions($options = array()) {
    Util::debug('getAllSessions called');
    // Validate options
    $opts = array();
    try {
      $opts = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Get all sessions and return the count or an array of Ostiary\Session
    return $this->driver->getAllSessions($opts['count_only'], $opts['update_expiration'], $opts['ttl']);
  }


  /**
   * Set an Ostiary session to the values of an Ostiary\Session object
   *
   * Warning: This will overwrite all contents of the session in Ostiary or Redis!
   * There is no option provided for updating the expiration. To do that, use the
   * `touchTimeExpiration()` method in the Ostiary\Session object or change the
   * TTL using the `setTTL()` method.
   *
   * @param \Ostiary\Session $session A populated Ostiary\Session object
   * @return bool|\Ostiary\Session The Ostiary\Session object, false on failure
   * @throws InvalidArgumentException Thrown if $session is not an Ostiary\Session object
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setSession(\Ostiary\Session $session) {
    Util::debug('setSession called');
    // Check that we received an Ostiary\Session object
    if (!is_a($session, 'Ostiary\Session'))
      throw new \InvalidArgumentException('session must be an Ostiary\Session object');

    // Overwrite the session and return the current Ostiary\Session
    return $this->driver->setSession($session);
  }


  /**
   * Set the data for a specific bucket
   *
   * Warning: This will overwrite all existing contents of the specified bucket in Ostiary or Redis!
   *
   * @param string $jwt JSON Web Token identifier of the session
   * @param string $bucket Must be either "global" or "local"
   * @param mixed $data Data to set for the bucket
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true
   *    ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: -1
   *       Values: -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *       Setting ttl >= 0 will update the TTL setting on record to match this.
   * @return bool|\Ostiary\Session An updated Ostiary\Session object, or false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setBucket($jwt, $bucket, $data, $options = array()) {
    Util::debug('setBucket called');
    // Validate options
    if (!in_array($bucket, array('global', 'local')))
      throw new \InvalidArgumentException('bucket must be set to only "global" or "local"');
    $opts = array();
    try {
      $opts = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Set the bucket and return an Ostiary\Session object
    return $this->driver->setBucket($jwt, $bucket, $data, $opts['update_expiration'], $opts['ttl']);
  }


  /**
   * Update the expiration of a Session to now + TTL (stored value or overridden)
   *
   * @param string $jwt JSON Web Token identifier of the session
   * @param array $options [optional] Array of optional settings. Allowed key/values:
   *    ttl  (int)   Override the TTL value for this Ostiary client. Default: -1
   *       Values: -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *       Setting ttl >= 0 will update the TTL setting on record to match this.
   * @return \Ostiary\Session An updated Ostiary\Session object
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function touchSession($jwt, $options = array()) {
    Util::debug('touchSession called');
    // Validate options
    $opts = array();
    try {
      $opts = $this->_validateAndMergeOptions($options);
    } catch (\InvalidArgumentException $e) {
      throw new \InvalidArgumentException('Invalid options: '.$e->getMessage());
    }

    // Touch session and return updated Ostiary\Session object
    return $this->driver->touchSession($jwt, $opts['ttl']);
  }


  /**
   * Delete an Ostiary session
   *
   * @param string $jwt JSON Web Token identifier of the session
   * @return bool True on success, false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function deleteSession($jwt) {
    Util::debug('deleteSession called');
    return $this->driver->deleteSession($jwt);
  }


  /**
   * Validate options of a function and merge with defaults. Function is auto-discovered.
   *
   * @ignore
   * @param array $options Options to validate
   * @return array Merged and validated options
   * @throws InvalidArgumentException Thrown if $options is invalid
   */
  private function _validateAndMergeOptions($options) {
    // Check that options is an array
    if (!is_array($options)) {
      throw new \InvalidArgumentException('options must be an array');
    }

    // Get function name
    $dbg_back = debug_backtrace();
    $function = $dbg_back[1]['function'];

    // Merge with defaults, if set
    $opts = array_key_exists($function, $this->default_options) ?
      array_merge($this->default_options[$function], $options) :
      $options;

    //
    // __construct
    //
    if ($function == '__construct') {

      if (empty($opts['id']) || !preg_match('/^[a-z0-9._-]+$/i', $opts['id']))
        throw new \InvalidArgumentException('id must be set and contain only letters, numbers, dots, dashes, or underscores');

      if (!is_int($opts['ttl']) || $opts['ttl'] < 0)
        throw new \InvalidArgumentException('ttl must be a positive integer >= 0');

      if (empty($opts['driver']) || !in_array($opts['driver'], array('ostiary', 'redis')))
        throw new \InvalidArgumentException('driver must be set to only: ostiary, redis');

      if ($opts['driver'] == 'ostiary') {

        if (empty($opts['secret']))
          throw new \InvalidArgumentException('secret must be set if driver = ostiary');

        if (empty($opts['ostiary']))
          throw new \InvalidArgumentException('ostiary settings must be set if driver = ostiary');
        if (empty($opts['ostiary']['server']))
          throw new \InvalidArgumentException('ostiary.server must be set to an Ostiary server endpoint');
        if (empty($opts['ostiary']['timeout']) || (!is_int($opts['ostiary']['timeout']) && !is_float($opts['ostiary']['timeout'])))
          throw new \InvalidArgumentException('ostiary.timeout must be an integer or float');

      } elseif ($opts['driver'] == 'redis') {

        if (empty($opts['redis']))
          throw new \InvalidArgumentException('redis settings must be set if driver = redis');

      }

    //
    // createSession
    //
    } elseif ($function == 'createSession') {

      if (!isset($opts['ttl']) || !is_int($opts['ttl']))
        throw new \InvalidArgumentException('ttl must be an integer');

    //
    // getSession
    //
    } elseif ($function == 'getSession') {

      if (!isset($opts['update_expiration']) || !is_bool($opts['update_expiration']))
        throw new \InvalidArgumentException('update_expiration must be a boolean');

      if (!isset($opts['ttl']) || !is_int($opts['ttl']))
        throw new \InvalidArgumentException('ttl must be an integer');

    //
    // getAllSessions
    //
    } elseif ($function == 'getAllSessions') {

      if (!isset($opts['update_expiration']) || !is_bool($opts['update_expiration']))
        throw new \InvalidArgumentException('update_expiration must be a boolean');

      if (!isset($opts['ttl']) || !is_int($opts['ttl']))
        throw new \InvalidArgumentException('ttl must be an integer');

      if (!isset($opts['count_only']) || !is_bool($opts['count_only']))
        throw new \InvalidArgumentException('count_only must be a boolean');

    //
    // setBucket
    //
    } elseif ($function == 'setBucket') {

      if (!isset($opts['update_expiration']) || !is_bool($opts['update_expiration']))
        throw new \InvalidArgumentException('update_expiration must be a boolean');

      if (!isset($opts['ttl']) || !is_int($opts['ttl']))
        throw new \InvalidArgumentException('ttl must be an integer');

    //
    // touchSession
    //
    } elseif ($function == 'touchSession') {

      if (!isset($opts['ttl']) || !is_int($opts['ttl']))
        throw new \InvalidArgumentException('ttl must be an integer');

    }

    // Return merged options
    return $opts;
  }

}

// EOF
