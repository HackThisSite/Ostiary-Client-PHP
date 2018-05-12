<?php
/**
 * Ostiary\User
 */

 namespace Ostiary;

/**
 * Ostiary\User stores data for a user in an Ostiary session
 */
class User {

  /**
   * Username
   * @ignore
   */
  private $username;

  /**
   * Display name (gecos)
   * @ignore
   */
  private $display_name;

  /**
   * Email address
   * @ignore
   */
  private $email;

  /**
   * Authorization roles
   * @ignore
   */
  private $roles;

  /**
   * User parameters
   * @ignore
   */
  private $parameters = array();


  /**
   * Construct an Ostiary user object
   *
   * @param string $username [optional] Username. Default: ""
   * @param string $display_name [optional] Display name (also known as 'gecos'). Default: ""
   * @param string $email [optional] Email address. Default: ""
   * @param array $roles [optional] Array of authorization role. Default: empty array
   * @param array $parameters [optional] Array of additional parameters. Default: empty array
   * @throws InvalidArgumentException Thrown if any param is invalid
   */
  public function __construct($username = '', $display_name = '', $email = '', $roles = array(), $parameters = array()) {
    $this->setUsername($username);
    $this->setDisplayName($display_name);
    $this->setEmail($email);
    $this->setRoles($roles);
    $this->setParameters($parameters, true);
  }


  /**
   * Get the username
   *
   * @return string Username
   */
  public function getUsername() {
    return $this->username;
  }


  /**
   * Set the username
   *
   * @param string $username Username to set
   * @return bool Always true
   */
  public function setUsername($username) {
    $this->username = $username;
    return true;
  }


  /**
   * Get the display name (also known as gecos)
   *
   * @return string Display name
   */
  public function getDisplayName() {
    return $this->display_name;
  }


  /**
   * Alias of `getDisplayName()`
   *
   * @return string Display name
   */
  public function getGecos() {
    return $this->getDisplayName();
  }


  /**
   * Set the display name
   *
   * @param string $display_name Display name to set
   * @return bool Always true
   */
  public function setDisplayName($display_name) {
    $this->display_name = $display_name;
    return true;
  }


  /**
   * Alias of `setDisplayName()`
   *
   * @param string $gecos Display name to set
   * @return bool Always true
   */
  public function setGecos($gecos) {
    return $this->setDisplayName($gecos);
  }


  /**
   * Get the email address
   *
   * @return string Email address
   */
  public function getEmail() {
    return $this->email;
  }


  /**
   * Set the email address
   *
   * @param string $email Email address
   * @return bool Always true
   */
  public function setEmail($email) {
    $this->email = $email;
    return true;
  }


  /**
   * Get the authorization roles
   *
   * @return array Array of authorization role
   */
  public function getRoles() {
    return $this->roles;
  }


  /**
   * Set the authorization roles
   *
   * This does not merge with existing settings, but will overwrite the roles array.
   *
   * @param array $roles Array of authorization role. Set to blank array to flush roles.
   * @return bool Always true
   */
  public function setRoles($roles) {
    if (!is_array($roles))
      throw new \InvalidArgumentException('Roles must be an array');

    $this->roles = $roles;
    return true;
  }


  /**
   * Test if a parameter exists by name
   *
   * @param string $name Name of the parameter
   * @return bool True if exists, false if not
   */
  public function parameterExists($name) {
    return array_key_exists($name, $this->parameters);
  }


  /**
   * Get a single parameter by name
   *
   * @param string $name Name of the parameter
   * @return null|mixed Null if the parameter doesn't exist, otherwise the value of the parameter. Because a parameter could be set to null, this should not be used to test for parameter existence. Use `parameterExists()` instead.
   */
  public function getParameter($name) {
    if (!array_key_exists($name, $this->parameters)) return null;
    return $this->parameters[$name];
  }


  /**
   * Get all parameters
   *
   * @return array Array of all parameters and values
   */
  public function getAllParameters() {
    return $this->parameters;
  }


  /**
   * Get all parameter names
   *
   * @return array List of all parameter names
   */
  public function getParameterNames() {
    return array_keys($this->parameters);
  }


  /**
   * Overwrite parameters, either specified or flush all and reset
   *
   * This will let you overwrite a list of specific parameters, or optionally flush all values and overwrite with a clean list.
   *
   * @param array $parameters Array of parameters to set
   * @param bool $flush [optional] Flush all parameters and overwrite with value of `$parameters`
   * @return bool True on success, false on failure
   * @throws InvalidArgumentException Thrown if any parameter name is invalid
   */
  public function setParameters($parameters, $flush = false) {
    // Validate $parameters is an array
    if (!is_array($parameters))
      throw new \InvalidArgumentException('Parameters must be an array');

    // Validate key names
    foreach (array_keys($parameters) as $name)
      if (!$this->_validateParameterName($name))
        throw new \InvalidArgumentException('Parameter name is invalid. Must contain only letters, numbers, and underscores.');

    // Flush and overwrite all
    if ($flush) {
      $this->parameters = $parameters;
    // Set specific parameters
    } else {
      foreach ($parameters as $name => $value) {
        $this->parameters[$name] = $value;
      }
    }
    return true;
  }


  /**
   * Set a specific parameter to a value
   *
   * @param string $name Name of parameter
   * @param string $value Value to set for the parameter
   * @return bool True on success
   * @throws InvalidArgumentException Thrown if the parameter name is invalid
   */
  public function setParameter($name, $value) {
    if (!$this->_validateParameterName($name))
      throw new \InvalidArgumentException('Parameter name is invalid. Must contain only letters, numbers, and underscores.');

    $this->parameters[$name] = $value;
    return true;
  }


  /**
   * Delete a parameter
   *
   * @param string $name Name of parameter
   * @return bool True on success, false if parameter did not exist
   */
  public function deleteParameter($name) {
    if (!array_key_exists($name, $this->parameters)) return false;
    unset($this->parameters[$name]);
    return true;
  }


  /**
   * Return all data for this class in an associative array
   *
   * @return array Array of data for this Ostiary\User object
   */
  public function toArray() {
    return array(
      'username'     => $this->username,
      'display_name' => $this->display_name,
      'email'        => $this->email,
      'roles'        => $this->roles,
      'parameters'   => $this->getAllParameters(),
    );
  }


  /**
   * Return all data for this class in a JSON-encoded string
   *
   * @return string JSON-encoded string of data for this Ostiary\User object
   */
  public function toJSON() {
    return json_encode($this->toArray());
  }


  /**
   * Validate a parameter name
   *
   * @param string $name Name of parameter
   * @return bool True if valid, false if not
   * @ignore
   */
  private function _validateParameterName($name) {
    return (preg_match('/^[a-zA-Z0-9_]+$/', $name));
  }

}

// EOF
