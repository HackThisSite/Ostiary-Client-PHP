<?php namespace Ostiary\Client\Model;


interface ModelInterface {

  /**
   * Return the raw driver object
   *
   * @return \GuzzleHttp\Client|\Predis\Client Driver object in use for this model
   */
  public function getDriver();

  /**
   * Create a session
   *
   * @param int $ttl Time To Live for this session
   * @param mixed $bucket_global Data for the global bucket
   * @param mixed $bucket_local Data for the local bucket for this client
   * @return bool|\Ostiary\Session A populated Ostiary\Session object, or false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function createSession(int $ttl, $bucket_global, $bucket_local);

  /**
   * Get a session by JWT
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param bool $update_expiration Touch expiration for this session
   * @param int $ttl Use existing TTL setting or override and set new TTL. Ignored if $update_expiration = false.
   *    -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *    Setting $ttl >= 0 will update the TTL setting on record to match this.
   * @return \Ostiary\Session A populated Ostiary\Session object
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getSession(string $jwt, bool $update_expiration, int $ttl);


  /**
   * Get all sessions
   *
   * @param bool $count_only True to only return the number of sessions, false to return all session data
   * @param bool $update_expiration Touch expiration for this session
   * @param int $ttl Use existing TTL setting or override and set new TTL. Ignored if $update_expiration = false.
   *    -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *    Setting $ttl >= 0 will update the TTL setting on record to match this.
   * @return int|array If $count_only is true, will return an integer count, otherwise an array of Ostiary\Session objects with their UUIDs as array indices.
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getAllSessions(bool $count_only, bool $update_expiration, int $ttl);


  /**
   * Set a session
   *
   * @param \Ostiary\Session $session Session to set data with
   * @return bool True on success, false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setSession(Ostiary\Session $session);


  /**
   * Set the bucket data for a session
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param string $bucket Must be either "global" or "local"
   * @param mixed $data Data to set for the bucket
   * @param bool $update_expiration Touch expiration for this session
   * @param int $ttl Use existing TTL setting or override and set new TTL. Ignored if $update_expiration = false.
   *    -1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   *    Setting $ttl >= 0 will update the TTL setting on record to match this.
   * @return bool|\Ostiary\Session An updated Ostiary\Session object, or false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setBucket(string $jwt, string $bucket, $data, bool $update_expiration, int $ttl);


  /**
   * Update the expiration of a session to now + TTL (stored value or overridden)
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param int $ttl TTL for this session: 1 = use TTL on record, 0 = never expire, >0 = expire in X seconds
   * @return bool|\Ostiary\Session An updated Ostiary\Session object, or false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function touchSession($jwt, $ttl);


  /**
   * Delete a session
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @return bool True on success, false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function deleteSession($jwt);

}
