# Ostiary PHP Client Documentation

See also:

* [Class API](api/) - Documentation for the end-user classes of the Ostiary PHP client

## Usage

```php
require 'vendor/autoload.php';

// Using an Ostiary server
$ostiary = new \Ostiary\Client(array(
  'driver' => 'ostiary', // This is optional and default
  'server' => 'http://ostiary.server.local',
  'id' => 'client-id',
  'secret' => 'client-secret',
));

// Direct against Redis
$ostiary = new \Ostiary\Client(array(
  'driver' => 'redis',
  'redis'  => 'unix:/path/to/redis.sock',
  'id' => 'client-id',
));

// Create a session
$data_buckets = array(
  'global' => array(
    'username' => 'foobar',
    'email' => 'foo@bar.com',
  ),
);
$session = $ostiary->createSession($data_buckets);

// Get a session
$session = $ostiary->getSession($json_web_token);

// Get a session from a cookie
$session = $ostiary->getSessionFromCookie('cookie_name');

// Modify session data
$bucket_global = $session->getBucket('global');
$bucket_global['email'] = 'bar@foo.com';
$session->setBucket('global', $bucket_global);

// Overwrite a session
$result = $ostiary->setSession($session);

// Update a session's expiration
$session = $ostiary->touchSession($json_web_token);
```
