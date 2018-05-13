<?php namespace Ostiary\Client\Model;

use \Firebase\JWT\JWT;
use \Predis\Client as Predis;
use \Ramsey\Uuid\Uuid;
use \Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ostiary\Client\Utilities as Util;
use Ostiary\Client\Model\ModelInterface;
use Ostiary\Session;
use Ostiary\User;
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
    Util::debug('Instantiating Ostiary\Client\Model\Redis');
    $this->options = $options;
    $this->redis = new Predis($this->options['redis']);
  }


  public function getDriver() {
    return $this->redis;
  }


  public function createSession($ttl, $ip_address, $bucket_global, $bucket_local, $user) {
    // Try 5 times to find a unique UUID for this session (should always happen on first try)
    $uuid = null;
    $iter = 0;
    while (empty($uuid) && $iter < 5) {
      try {
        $uuid_tmp = Uuid::uuid4()->toString();
      } catch (UnsatisfiedDependencyException $e) {
        throw new \RuntimeException('Error generating UUID: '.$e->getMessage());
      }
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
    if ($ttl < 0) $ttl = 0;
    $key = Util::rand_alnum(32);
    $jwt = $this->_generateJWT($uuid, $ttl, $key);

    // Set the user data array
    $user_data = ($user === null ? null : $user->toArray());

    // Prep data for Redis
    $time = intval(gmdate('U'));
    $exp = ($ttl == 0 ? 0 : $time + $ttl);
    $redis_data = array(
      'sid' => $uuid,
      'key' => $key,
      'str' => $time,
      'exp' => $exp,
      'ttl' => $ttl,
      'ipa' => $ip_address,
      'bkt' => array(
        'glb' => $bucket_global,
        'loc' => array(
          $this->options['id'] => $bucket_local,
        ),
      ),
      'usr' => $user_data,
    );
    $json = json_encode($redis_data);

    // Insert into Redis
    if ($ttl == 0) {
      $this->redis->set($uuid, $json);
    } else {
      $this->redis->setex($uuid, $ttl, $json);
    }

    // Create Ostiary\Session object
    $session = new Session(
      $uuid,
      $jwt,
      $time,
      $exp,
      $ttl,
      $ip_address,
      array(
        'global' => $bucket_global,
        'local' => $bucket_local,
      ),
      $user
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function getSession($jwt, $update_expiration, $ttl) {
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
    if (!Util::hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Bump Redis expiration, if set
    if ($update_expiration) {
      $new_ttl = ($ttl < 0 ? $r_json['ttl'] : $ttl);
      $time = intval(gmdate('U'));
      $jwt = $this->_generateJWT($uuid, $new_ttl, $r_json['key']);
      $r_json['exp'] = ($ttl == 0 ? 0 : $time + $new_ttl);
      $r_json['ttl'] = $new_ttl;
      if ($new_ttl == 0) {
        $this->redis->set($uuid, json_encode($r_json));
      } else {
        $this->redis->setex($uuid, $new_ttl, json_encode($r_json));
      }
    }

    // Create Ostiary\Session object
    $session = new Session(
      $uuid,
      $jwt,
      $r_json['str'],
      $r_json['exp'],
      $r_json['ttl'],
      $r_json['ipa'],
      array(
        'global' => $r_json['bkt']['glb'],
        'local' => $bkt_local,
      ),
      $this->_generateUserObject($r_json['usr'])
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function getAllSessions($count_only, $update_expiration, $ttl) {
    // Get all Redis keys
    $keys = $this->redis->keys('*');

    // Returning count only with no expiration bump
    if ($count_only && !$update_expiration) {
      return count($keys);
    }

    // Generate Ostiay\Session objects for all keys, and optionally bump all expirations
    $sessions = array();
    foreach ($keys as $key) {
      // Get Redis data for the key
      $r_data = $this->redis->get($key);
      $r_json = json_decode($redis_data, true);
      if (empty($r_json)) {
        Util::debug('Error on getAllSessions: Corrupted session: '.$key);
        continue;
      }
      $jwt = $this->_generateJWT($r_json['sid'], $r_json['ttl'], $r_json['key']);
      $bkt_local = (isset($r_json['bkt']['loc'][$this->options['id']]) ? $r_json['bkt']['loc'][$this->options['id']] : null);

      // Bump expiration
      if ($update_expiration) {
        $new_ttl = ($ttl < 0 ? $r_json['ttl'] : $ttl);
        $time = intval(gmdate('U'));
        $jwt = $this->_generateJWT($r_json['sid'], $new_ttl, $r_json['key']);
        $r_json['exp'] = ($new_ttl == 0 ? 0 : $time + $new_ttl);
        $r_json['ttl'] = $new_ttl;
        if ($new_ttl == 0) {
          $this->redis->set($r_json['sid'], json_encode($r_json));
        } else {
          $this->redis->setex($r_json['sid'], $new_ttl, json_encode($r_json));
        }
      }

      // Generate Ostiary\Session object
      $sessions[$r_json['sid']] = new Session(
        $r_json['sid'],
        $jwt,
        $r_json['str'],
        $r_json['exp'],
        $r_json['ttl'],
        $r_json['ipa'],
        array(
          'global' => $r_json['bkt']['glb'],
          'local' => $bkt_local,
        ),
        $this->_generateUserObject($r_json['usr'])
      );
    }

    // Return sessions array
    return $sessions;
  }


  public function setSession(\Ostiary\Session $session) {
    // Get key from Redis
    $r_data = $this->redis->get($session->getSessionID());
    if (empty($r_data)) return false;
    $r_json = json_decode($r_data, true);
    if (empty($r_json)) return false;

    // Validate JWT
    $jwt_decoded = (array) JWT::decode($session->getJWT(), $r_json['key'], array('HS256'));
    if (empty($jwt_decoded)) return false;
    if (!Util::hash_equals($jwt_decoded['sid'], $session->getSessionID())) return false;

    // Set the user data array
    $user_data = (empty($session->getUser()) ? null : $session->getUser()->toArray());

    // Prep data for Redis
    $redis_data = array(
     'sid' => $session->getSessionID(),
     'key' => $r_json['key'],
     'str' => $session->getTimeStarted(),
     'exp' => $session->getTimeExpiration(),
     'ttl' => $session->getTTL(),
     'ipa' => $session->getIPAddress(),
     'bkt' => array(
       'glb' => $session->getBucket('global'),
       'loc' => $r_json['bkt']['loc'],
     ),
     'usr' => $user_data,
    );
    $redis_data['bkt']['loc'][$this->options['id']] = $session->getBucket('local');
    $json = json_encode($redis_data);

    // Insert into Redis
    if ($redis_data['ttl'] == 0) {
      $this->redis->set($redis_data['sid'], $json);
    } else {
      $this->redis->setex($redis_data['sid'], $redis_data['ttl'], $json);
    }

    // All done
    return true;
  }


  public function setBucket($jwt, $bucket, $data, $update_expiration, $ttl) {
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
    if (!Util::hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Bump Redis expiration, if set
    if ($update_expiration) {
      $new_ttl = ($ttl < 0 ? $r_json['ttl'] : $ttl);
      $time = intval(gmdate('U'));
      $jwt = $this->_generateJWT($uuid, $new_ttl, $r_json['key']);
      $r_json['exp'] = ($new_ttl == 0 ? 0 : $time + $new_ttl);
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
    if ($r_json['ttl'] == 0) {
      $this->redis->set($uuid, $json);
    } else {
      $this->redis->setex($uuid, $r_json['ttl'], $json);
    }

    // Create Ostiary\Session object
    $bkt_local = (isset($r_json['bkt']['loc'][$this->options['id']]) ? $r_json['bkt']['loc'][$this->options['id']] : null);
    $session = new Session(
      $uuid,
      $jwt,
      $r_json['str'],
      $r_json['exp'],
      $r_json['ttl'],
      $r_json['ipa'],
      array(
        'global' => $r_json['bkt']['glb'],
        'local' => $bkt_local,
      )
    );

    // Return Ostiary\Session object
    return $session;
  }


  public function touchSession($jwt, $ttl) {
    return $this->getSession($jwt, true, $ttl);
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
    if (!Util::hash_equals($jwt_decoded['sid'], $uuid)) return null;

    // Delete record
    return $this->redis->del($uuid);
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


  private function _generateUserObject($data) {
    if (empty($data)) {
      $user = null;
    } else {
      $user = new User(
        $data['username'],
        $data['display_name'],
        $data['email'],
        $data['roles'],
        $data['parameters']
      );
    }
    return $user;
  }

}
