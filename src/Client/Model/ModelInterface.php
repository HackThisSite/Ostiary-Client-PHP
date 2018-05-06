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
  public function createSession($ttl, $bucket_global, $bucket_local);

  /**
   * Get a session by JWT
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param int $update_expiration Update expiration and/or TTL.
   *    -1 = don't update, 0 = update with TTL on record, >0 = update using this
   *    value as the TTL (and update the TTL on record to this)
   * @return \Ostiary\Session A populated Ostiary\Session object
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getSession($jwt, $update_expiration);


  /**
   * Get all sessions
   *
   * @param bool $count_only True to only return the number of sessions, false to return all session data
   * @param int $update_expiration Update expiration and/or TTL.
   *    -1 = don't update, 0 = update with TTL on record, >0 = update using this
   *    value as the TTL (and update the TTL on record to this)
   * @return int|array If $count_only is true, will return an integer count, otherwise an array of Ostiary\Session objects with their UUIDs as array indices.
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function getAllSessions($count_only, $update_expiration);


  /**
   * Set a session
   *
   * @param \Ostiary\Session $session Session to set data with
   * @return bool True on success, false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setSession($session);


  /**
   * Set the bucket data for a session
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param string $bucket Must be either "global" or "local"
   * @param mixed $data Data to set for the bucket
   * @param int $update_expiration Update expiration and/or TTL.
   *    -1 = don't update, 0 = update with TTL on record, >0 = update using this
   *    value as the TTL (and update the TTL on record to this)
   * @return bool|\Ostiary\Session An updated Ostiary\Session object, or false on failure
   * @throws \Ostiary\Client\Exception\OstiaryServerException If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server
   */
  public function setBucket($jwt, $bucket, $data, $update_expiration);


  /**
   * Update the expiration of a session to now + TTL (stored value or overridden)
   *
   * @param string $jwt JSON Web Token identifier for this session
   * @param int $ttl Overriding Time To Live for this session, or 0 to use TTL on record
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
