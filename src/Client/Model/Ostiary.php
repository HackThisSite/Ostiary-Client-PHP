<?php namespace Ostiary\Client\Model;

use \GuzzleHttp\Client as Guzzle;
use Ostiary\Client\Utilities as Util;
use Ostiary\Client\Model\ModelInterface;
use Ostiary\Session;
use Ostiary\Client\Exception\OstiaryServerException;

class Ostiary implements ModelInterface {


  private $options;


  private $guzzle;


  public function __construct($options) {
    $this->options = $options;
    // Create Guzzle object
    $this->guzzle = new Guzzle(array(
      'base_uri' => $this->options['ostiary']['server'],
      'timeout'  => $this->options['ostiary']['timeout'],
    ));
    // Set User-Agent
    $this->guzzle->getConfig()->setPath('headers/User-Agent', sprintf('Ostiary-Client-PHP/%s', OSTIARY_VERSION));
    // Set Basic auth
    $this->guzzle->getConfig()->setPath('request.options/auth', array(
      $this->options['id'],
      $this->options['secret'],
      'Basic',
    ));
  }


  public function getDriver() {
    return $this->guzzle;
  }


  public function createSession($ttl, $bucket_global, $bucket_local) {
    $body = json_encode(array(
      'ttl' => $ttl,
      'bkt' => array(
        'glb' => $bucket_global,
        'loc' => $bucket_local,
      ),
    ));

    $request = $this->guzzle->put('/v1/createSession');
    $request->setBody($body, 'application/json');

    // Process request and get JSON
    $json = $this->_processRequest($request);
    if ($json['res'] != 'ok') return false;

    // Create Ostiary\Session object
    $session = $this->_generateSessionObject($json['ses']);

    // Return Ostiary\Session object
    return $session;
  }


  public function getSession($jwt, $update_expiration) {
    $request = $this->guzzle->get('/v1/getSession', array(), array(
      'query' => array(
        'jwt' => $jwt,
        'tch' => $update_expiration,
      ),
    ));

    // Process request and get JSON
    $json = $this->_processRequest($request);
    if ($json['res'] != 'ok') return null;

    // Create Ostiary\Session object
    $session = $this->_generateSessionObject($json['ses']);

    // Return Ostiary\Session object
    return $session;
  }


  public function setSession($session, $update_expiration) {
    $body = json_encode(array(
     'sid' => $session->getSessionID(),
     'str' => $session->getTimeStarted(),
     'exp' => $session->getTimeExpiration(),
     'ttl' => $session->getTTL(),
     'bkt' => array(
       'glb' => $session->getBucket('global'),
       'loc' => $session->getBucket('local'),
     ),
    ));

    $request = $this->guzzle->put('/v1/setSession');
    $request->setBody($body, 'application/json');

    // Process request and get JSON
    $json = $this->_processRequest($request);

    // Return result
    return ($json['res'] == 'ok');
  }


  public function setBucket($jwt, $bucket, $data, $update_expiration) {
    $body = json_encode(array(
      'jwt' => $jwt,
      'bkt' => ($bucket == 'global' ? 'glb' : 'loc'),
      'dat' => $data,
      'tch' => $update_expiration,
    ));

    $request = $this->guzzle->put('/v1/setBucket');
    $request->setBody($body, 'application/json');

    // Process request and get JSON
    $json = $this->_processRequest($request);
    if ($json['res'] != 'ok') return false;

    // Create Ostiary\Session object
    $session = $this->_generateSessionObject($json['ses']);

    // Return Ostiary\Session object
    return $session;
  }


  public function touchSession($jwt, $ttl) {
    $request = $this->guzzle->post('/v1/touchSession');
    $request->setBody(array('jwt' => $jwt, 'ttl' => $ttl), 'application/json');

    // Process request and get JSON
    $json = $this->_processRequest($request);
    if ($json['res'] != 'ok') return false;

    // Create Ostiary\Session object
    $session = $this->_generateSessionObject($json['ses']);

    // Return Ostiary\Session object
    return $session;
  }


  public function deleteSession($jwt) {
    $request = $this->guzzle->delete('/v1/deleteSession');
    $request->setBody(array('jwt' => $jwt), 'application/json');

    // Process request and get JSON
    $json = $this->_processRequest($request);

    // Return result
    return ($json['res'] == 'ok');
  }


  private function _processRequest($response) {
    try {
      $response = $request->send();
    } catch (Exception $e) {
      throw new OstiaryServerException('An error occurred with the Guzzle driver: '.$e->getMessage());
    }

    $code = $response->getStatusCode();
    $body = $response->getBody();

    // Test HTTP codes
    if ($code >= 500) {
      throw new OstiaryServerException(sprintf('The Ostiary server had a failure: HTTP %d - %s', $code, $body));
    } elseif ($code >= 400 && <= 499) {
      throw new OstiaryServerException(sprintf('Access denied to the Ostiary server: HTTP %d - %s', $code, $body));
    } elseif ($code != 200) {
      throw new OstiaryServerException(sprintf('There was an error interacting with the Ostiary server: HTTP %d - %s', $code, $body));
    }

    // Process JSON
    $json = json_decode($body, true);
    if (empty($json))
      throw new OstiaryServerException(sprintf('Invalid data returned from the Ostiary server: HTTP %d - %s', $code, $body))

    // Return JSON
    return $json;
  }


  private function _generateSessionObject($data) {
    return new Session(
      $data['sid'],
      $data['jwt'],
      $data['str'],
      $data['exp'],
      $data['ttl'],
      array(
        'global' => $data['bkt']['glb'],
        'local' => $data['bkt']['loc'],
      )
    );
  }

}
