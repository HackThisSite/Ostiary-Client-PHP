<?php
/**
 * Ostiary\Session
 */

 namespace Ostiary;

/**
 * Ostiary\Session stores data for an Ostiary session
 */
class Session {

  /**
   * Session ID
   * @ignore
   */
  private $session_id;

  /**
   * JSON Web Token identifier
   * @ignore
   */
  private $jwt;

  /**
   * Unix timestamp of when the session was started
   * @ignore
   */
  private $time_started;

  /**
   * Unix timestamp of when the session will expire
   * @ignore
   */
  private $time_expiration;

  /**
   * Time To Live in seconds for this session
   * @ignore
   */
  private $ttl;

  /**
   * Data buckets, global (accessible to all clients) and local (accessible only to this client)
   * @ignore
   */
  private $buckets = array(
    'global' => null,
    'local'  => null,
  );

  /**
   * An associated Ostiary\User object, if defined
   * @ignore
   */
  private $user;


  /**
   * Construct an Ostiary session.
   *
   * @param string $session_id UUID of the session
   * @param string $jwt JSON Web Token identifier of the session
   * @param int $time_started Unix timestamp of when the session was started
   * @param int $time_expiration Unix timestamp of when the session will expire
   * @param int $ttl Time To Live in seconds for this session
   * @param array $buckets [optional] Data buckets, global (accessible to all clients)
   *    and local (accessible only to this client). Array indices must be only
   *    "global" and "local". Values can be any data type allowed by json_encode().
   * @param Ostiary\User|null $user [optional] An Ostiary\User object, or null
   * @throws InvalidArgumentException Thrown if any param is invalid
   */
  public function __construct($session_id, $jwt, $time_started, $time_expiration, $ttl, $buckets = array(), $user = null) {
    $this->setSessionID($session_id);
    $this->setJWT($jwt);
    $this->setTimeStarted($time_started);
    $this->setTimeExpiration($time_expiration);
    $this->setTTL($ttl);

    if (!is_array($buckets))
      throw new \InvalidArgumentException('Buckets must be an array');
    $bkt_glb = (isset($buckets['global']) ? $buckets['global'] : null);
    $bkt_loc = (isset($buckets['local']) ? $buckets['local'] : null);
    $this->setBucket('global', $bkt_glb);
    $this->setBucket('local', $bkt_loc);

    $this->setUser($user);
  }

  /**
   * Write the JWT to a cookie
   *
   * @param string $name Name of the cookie
   * @param int $expiry [optional] Unix timestamp of expiration. Default: 0
   * @param string $path [optional] URI path for the cookie. Default: "/"
   * @param string $domain [optional] Domain for the cookie. Default: ""
   * @param bool $secure [optional] Indicates that the cookie should be
   *    transmitted only over a secure HTTPS connection. Default: false
   * @param bool $http [optional] When true, the cookie will be made accessible
   *    only through the HTTP protocol. Default: false
   * @return bool Result of PHP setcookie() function
   */
  public function setCookie($name, $expiry = 0, $path = '/', $domain = '', $secure = false, $http = false) {
    return setcookie($name, $this->jwt, $expiry, $path, $domain, $secure, $http);
  }


  /**
   * Get the Session UUID
   *
   * @return string UUID of the session
   */
  public function getSessionID() {
    return $this->session_id;
  }


  /**
   * Set the UUID of the session
   *
   * @param string $uuid UUID of the session
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if invalid UUID syntax is provided
   */
  public function setSessionID($uuid) {
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid))
      throw new \InvalidArgumentException('Invalid UUID syntax');

    $this->session_id = $uuid;
    return true;
  }


  /**
   * Get the JSON Web Token of the session
   *
   * @return string Encoded JSON Web Token of this session
   */
  public function getJWT() {
    return $this->jwt;
  }


  /**
   * Set the JSON Web Token of this session
   *
   * @param string $jwt Encoded JSON Web Token
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if the JWT fails a basic syntax check
   */
  public function setJWT($jwt) {
    $jwt_split = explode('.', $jwt);
    if (count($jwt_split) != 3)
      throw new \InvalidArgumentException('Invalid JWT syntax');

    $this->jwt = $jwt;
    return true;
  }


  /**
   * Get the contents of a data bucket
   *
   * @param string $bucket [optional] Bucket type. Allowed types are "global", "local", and "all". Default: "all"
   * @return mixed Contents of the specified data bucket
   */
  public function getBucket($bucket='all') {
    switch ($bucket) {
      case 'global':
        return $this->buckets['global'];
        break;
      case 'local':
        return $this->buckets['local'];
        break;
      default:
        return $this->buckets;
        break;
    }
  }


  /**
   * Set the contents of a bucket
   *
   * @param string $bucket Bucket type to set data for. Allowed types are "global" or "local".
   * @param mixed $data Contents for the specified data bucket
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if invalid bucket type is specified
   */
  public function setBucket($bucket, $data) {
    if (!in_array($bucket, array('global', 'local')))
      throw new \InvalidArgumentException('Bucket type must be "global" or "local"');

    $this->buckets[$bucket] = $data;
    return true;
  }


  /**
   * Return an Ostiary\User for this session, if defined
   *
   * @return Ostiary\User The Ostiary\User object, if defined
   */
  public function getUser() {
    return $this->user;
  }


  /**
   * Set the Ostiary\User object
   *
   * @param Ostiary\User|null The Ostiary\User object to set, or null to unset
   * @return bool Always true
   */
  public function setUser($user) {
    if ($user !== null && !is_a($user, 'Ostiary\User'))
      throw new \InvalidArgumentException('User object must be null or an instance of Ostiary\User');
    $this->user = $user;
    return true;
  }


  /**
   * Get the unix timestamp of when this session was started
   *
   * @return int Unix timestamp of when this session was started
   */
  public function getTimeStarted() {
    return $this->time_started;
  }


  /**
   * Set the unix timestamp for when this session was started
   *
   * @param int $timestamp_started Unix timestamp for when this session was started
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if $timestamp_started is not an integer
   */
  public function setTimeStarted($timestamp_started) {
    if (!is_int($timestamp_started))
      throw new \InvalidArgumentException('Starting timestamp must be an integer value');

    $this->time_started = $timestamp_started;
    return true;
  }


  /**
   * Get the unix timestamp of when this session will expire
   *
   * @return int Unix timestamp of when this session will expire
   */
  public function getTimeExpiration() {
    return $this->time_expiration;
  }


  /**
   * Set the unix timestamp for when this session will expire
   *
   * @param int $timestamp_started Unix timestamp for when this session will expire
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if $timestamp_expiration is not an integer
   */
  public function setTimeExpiration($timestamp_expiration) {
    if (!is_int($timestamp_expiration))
      throw new \InvalidArgumentException('Expiration timestamp must be an integer value');

    $this->time_expiration = $timestamp_expiration;
    return true;
  }


  /**
   * Update the expiration to now + value of TTL stored in this object (or overridden)
   *
   * @param int $ttl [optional] Override the stored TTL value. This will also update the stored TTL value.
   * @return int Updated unix timestamp of when this session will expire
   */
  public function touchTimeExpiration($ttl = null) {
    if ($ttl !== null) $this->ttl = $ttl;
    $time = intval(gmdate('U'));
    $this->time_expiration = $time + $this->ttl;
    return $this->time_expiration;
  }


  /**
   * Get the Time To Live (in seconds) for this session
   *
   * @return int Time To Live (in seconds)
   */
  public function getTTL() {
    return $this->ttl;
  }


  /**
   * Set the Time To Live (in seconds) for this session
   *
   * @param int $ttl Time To Live (in seconds) for this session
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if $ttl is not an integer
   */
  public function setTTL($ttl) {
    if (!is_int($ttl))
      throw new \InvalidArgumentException('TTL must be an integer value');

    $this->ttl = $ttl;
    return true;
  }


  /**
   * Return all data for this class in an associative array
   *
   * @return array Array of data for this Ostiary\Session object
   */
  public function toArray() {
    return array(
      'session_id'      => $this->session_id,
      'jwt'             => $this->jwt,
      'time_started'    => $this->time_started,
      'time_expiration' => $this->time_expiration,
      'ttl'             => $this->ttl,
      'buckets'         => array(
        'global' => $this->buckets['global'],
        'local'  => $this->buckets['local'],
      ),
      'user'            => ($this->user === null ? null : $this->user->toArray()),
    );
  }


  /**
   * Return all data for this class in a JSON-encoded string
   *
   * @return string JSON-encoded string of data for this Ostiary\Session object
   */
  public function toJSON() {
    return json_encode($this->toArray());
  }

}

// EOF
