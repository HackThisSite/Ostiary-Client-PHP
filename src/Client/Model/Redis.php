<?php namespace Ostiary\Client\Model;

use \Firebase\JWT\JWT;
use \Predis\Client as Predis;
use Ostiary\Client\Utilities as Util;
use Ostiary\Client\Model\ModelInterface;
use Ostiary\Session;
use Ostiary\Client\Exception\ServerErrorException;

/*

{
  // Session ID (same as Redis key name)
  "sid": "e699e8f7-5046-48b6-9cf0-c919d67061f3",

  // JWT key
  "key": "gdQwZ7WLDYJZjcOYKX7uwP6y3GAt6P3C",

  // Unix timestamp of when session was originally started
  "str": 1525192225,

  // Unix timestamp when session will expire
  "exp": 1526211756,

  // Time to live in seconds
  "ttl": 86400,

  // Data buckets
  "bkt": {

    // Global bucket (accessible to all clients)
    "glb": {},

    // Local client buckets (accessible only to named client)
    "loc": {
      "client-1": {},
      "client-2": {}
    }
  }
}

*/


class Redis implements ModelInterface {

  private $options;

  private $redis;


  public function __construct($options) {
    $this->options = $options;
    $this->redis = new Predis($this->options['redis']);
  }


  public function getDriver() {
    return $this->redis;
  }


  public function createSession($ttl, $bucket_global, $bucket_local) {
    // Try 5 times to find a unique UUID for this session (should always happen on first try)
    $uuid = null;
    $iter = 0;
    while (empty($uuid) && $iter < 5) {
      $uuid_tmp = Util::gen_uuid_v4();
      $exists = $this->redis->get($uuid_tmp);
      if (empty($exists)) {
        $uuid = $uuid_tmp;
      } else {
        $iter++;
      }
    }
    // This should never happen
    if (empty($uuid)) {
      Util::debug('The world is broken. Five UUIDs caused a collision.');
      throw new \RuntimeException('Unable to define a unique UUID for new session');
    }

    // Create the JWT
    $key = Util::rand_alnum(32);
    $jwt = $this->_generateJWT($uuid, $ttl, $key);

    // Prep data for Redis
    $time = intval(gmdate('U'));
    $redis_data = array(
      'sid' => $uuid,
      'key' => $key,
      'str' => $time,
      'exp' => $time + $ttl,
      'ttl' => $ttl,
      'bkt' => array(
        'glb' => $bucket_global,
        'loc' => array(
          $this->options['id'] => $bucket_local,
        ),
      ),
    );
    $json = json_encode($redis_data);

    // Insert into Redis
    $this->redis->setex($uuid, $ttl, $json);

    // Create Ostiary\Session object
    $session = new Session(
      $uuid,
      $jwt,
      $time,
      $time + $ttl,
      $ttl,
      array(
        'global' => $bucket_global,
        'local' => $bucket_local,
      )
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function getSession($jwt, $update_expiration) {
    // Extract UUID from JWT
    $uuid = $this->_extractUUIDFromJWT($jwt);
    if (empty($uuid)) return null;

    // Get Redis record
    $redis_data = $this->redis->get($uuid);
    if (empty($redis_data)) return null;
    $r_json = json_decode($redis_data, true);
    if (empty($r_json)) return null;
    $bkt_local = (isset($r_json['bkt']['loc'][$this->options['id']]) ? $r_json['bkt']['loc'][$this->options['id']] : null);

    // Validate JWT
    $jwt_decoded = (array) JWT::decode($jwt, $r_json['key'], array('HS256'));
    if (empty($jwt_decoded)) return null;
    if (!hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Bump Redis expiration, if set
    if ($update_expiration >= 0) {
      $ttl = ($update_expiration > 0 ? $update_expiration : $r_json['ttl']);
      $time = intval(gmdate('U'));
      $jwt = $this->_generateJWT($uuid, $ttl, $r_json['key']);
      $r_json['exp'] = $time + $ttl;
      $r_json['ttl'] = $ttl;
      $this->redis->setex($uuid, $ttl, json_encode($r_json));
    }

    // Create Ostiary\Session object
    $session = new Session(
      $uuid,
      $jwt,
      $r_json['str'],
      $r_json['exp'],
      $r_json['ttl'],
      array(
        'global' => $r_json['bkt']['glb'],
        'local' => $bkt_local,
      )
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function setSession($session) {
    // Get key from Redis
    $r_data = $this->redis->get($session->getSessionID());
    if (empty($r_data)) return false;
    $r_json = json_decode($r_data, true);
    if (empty($r_json)) return false;

    // Validate JWT
    $jwt_decoded = (array) JWT::decode($session->getJWT(), $r_json['key'], array('HS256'));
    if (empty($jwt_decoded)) return false;
    if (!hash_equals($jwt_decoded['sid'], $session->getSessionID())) return false;

    // Prep data for Redis
    $redis_data = array(
     'sid' => $session->getSessionID(),
     'key' => $r_json['key'],
     'str' => $session->getTimeStarted(),
     'exp' => $session->getTimeExpiration(),
     'ttl' => $session->getTTL(),
     'bkt' => array(
       'glb' => $session->getBucket('global'),
       'loc' => $r_json['bkt']['loc'],
     ),
    );
    $redis_data['bkt']['loc'][$this->options['id']] = $session->getBucket('local');
    $json = json_encode($redis_data);

    // Insert into Redis
    if ($redis_data['exp'] != $r_json['exp'] || $redis_data['ttl'] != $r_json['ttl']) {
     $this->redis->setex($redis_data['sid'], $redis_data['ttl'], $json);
    } else {
     $this->redis->set($redis_data['sid'], $json);
    }

    // All done
    return true;
  }


  public function setBucket($jwt, $bucket, $data, $update_expiration) {
    // Extract UUID from JWT
    $uuid = $this->_extractUUIDFromJWT($jwt);
    if (empty($uuid)) return false;

    // Get key from Redis
    $r_data = $this->redis->get($uuid);
    if (empty($r_data)) return false;
    $r_json = json_decode($r_data, true);
    if (empty($r_json)) return false;

    // Validate JWT
    $jwt_decoded = (array) JWT::decode($jwt, $r_json['key'], array('HS256'));
    if (empty($jwt_decoded)) return false;
    if (!hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Bump Redis expiration, if set
    if ($update_expiration >= 0) {
      $new_ttl = ($update_expiration > 0 ? $update_expiration : $r_json['ttl']);
      $time = intval(gmdate('U'));
      $jwt = $this->_generateJWT($uuid, $new_ttl, $r_json['key']);
      $r_json['exp'] = $time + $new_ttl;
      $r_json['ttl'] = $new_ttl;
    }

    // Prep data for Redis
    if ($bucket == 'global') {
      $r_json['bkt']['glb'] = $data;
    } else {
      $r_json['bkt']['loc'][$this->options['id']] = $data;
    }
    $json = json_encode($r_json);

    // Insert into Redis
    if ($update_expiration >= 0) {
     $this->redis->setex($uuid, $r_json['ttl'], $json);
    } else {
     $this->redis->set($uuid, $json);
    }

    // Create Ostiary\Session object
    $bkt_local = (isset($r_json['bkt']['loc'][$this->options['id']]) ? $r_json['bkt']['loc'][$this->options['id']] : null);
    $session = new Session(
      $uuid,
      $jwt,
      $r_json['str'],
      $r_json['exp'],
      $r_json['ttl'],
      array(
        'global' => $r_json['bkt']['glb'],
        'local' => $bkt_local,
      )
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function touchSession($jwt, $ttl) {
    return $this->getSession($jwt, $ttl);
  }


  public function deleteSession($jwt) {
    // Extract UUID from JWT
    $uuid = $this->_extractUUIDFromJWT($jwt);
    if (empty($uuid)) return null;

    // Get Redis record
    $redis_data = $this->redis->get($uuid);
    if (empty($redis_data)) return null;
    $r_json = json_decode($redis_data, true);
    if (empty($r_json)) return null;

    // Validate JWT
    $jwt_decoded = (array) JWT::decode($jwt, $r_json['key'], array('HS256'));
    if (empty($jwt_decoded)) return null;
    if (!hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Delete record
    return $this->redis->delete($uuid);
  }


  private function _extractUUIDFromJWT($jwt) {
    // Split JWT
    $jwt_split = explode('.', $jwt);
    if (count($jwt_split) != 3) return null;
    // Extract payload
    $payload = Util::base64_urldecode($jwt_split[1]);
    if (empty($payload)) return null;
    // Extract JSON
    $json = json_decode($payload, true);
    if (empty($json)) return null;
    // Get and return UUID
    if (!isset($json['sid'])) return null;
    return $json['sid'];
  }


  private function _generateJWT($uuid, $ttl, $key) {
    $time = intval(gmdate('U'));
    $payload = array(
      'iat' => $time,
      'nbf' => $time,
      'exp' => $time + $ttl,
      'sid' => $uuid,
    );
    return JWT::encode($payload, $key, 'HS256');
  }

}
