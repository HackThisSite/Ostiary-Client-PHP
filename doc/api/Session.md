# Ostiary\Session  

Ostiary\Session stores data for an Ostiary session





## Methods

| Name | Description |
|------|-------------|
|[__construct](#session__construct)|Construct an Ostiary session.|
|[getBucket](#sessiongetbucket)|Get the contents of a data bucket|
|[getJWT](#sessiongetjwt)|Get the JSON Web Token of the session|
|[getSessionID](#sessiongetsessionid)|Get the Session UUID|
|[getTTL](#sessiongetttl)|Get the Time To Live (in seconds) for this session|
|[getTimeExpiration](#sessiongettimeexpiration)|Get the unix timestamp of when this session will expire|
|[getTimeStarted](#sessiongettimestarted)|Get the unix timestamp of when this session was started|
|[getUser](#sessiongetuser)|Return an Ostiary\User for this session, if defined|
|[setBucket](#sessionsetbucket)|Set the contents of a bucket|
|[setCookie](#sessionsetcookie)|Write the JWT to a cookie|
|[setJWT](#sessionsetjwt)|Set the JSON Web Token of this session|
|[setSessionID](#sessionsetsessionid)|Set the UUID of the session|
|[setTTL](#sessionsetttl)|Set the Time To Live (in seconds) for this session|
|[setTimeExpiration](#sessionsettimeexpiration)|Set the unix timestamp for when this session will expire|
|[setTimeStarted](#sessionsettimestarted)|Set the unix timestamp for when this session was started|
|[setUser](#sessionsetuser)|Set the Ostiary\User object|
|[toArray](#sessiontoarray)|Return all data for this class in an associative array|
|[toJSON](#sessiontojson)|Return all data for this class in a JSON-encoded string|
|[touchTimeExpiration](#sessiontouchtimeexpiration)|Update the expiration to now + value of TTL stored in this object (or overridden)|




### Session::__construct  

**Description**

```php
public __construct (string $session_id, string $jwt, int $time_started, int $time_expiration, int $ttl, array $buckets, \Ostiary\User $user)
```

Construct an Ostiary session. 

 

**Parameters**

* `(string) $session_id`
: UUID of the session  
* `(string) $jwt`
: JSON Web Token identifier of the session  
* `(int) $time_started`
: Unix timestamp of when the session was started  
* `(int) $time_expiration`
: Unix timestamp of when the session will expire  
* `(int) $ttl`
: Time To Live in seconds for this session  
* `(array) $buckets`
: Data buckets, global (accessible to all clients)  
   and local (accessible only to this client). Array indices must be only  
   "global" and "local". Values can be any data type allowed by json_encode().  
* `(\Ostiary\User) $user`
: [optional] An Ostiary\User object  

**Return Values**



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if any param is invalid


### Session::getBucket  

**Description**

```php
public getBucket (string $bucket)
```

Get the contents of a data bucket 

 

**Parameters**

* `(string) $bucket`
: [optional] Bucket type. Allowed types are "global", "local", and "all". Default: "all"  

**Return Values**

`mixed`

> Contents of the specified data bucket  




### Session::getJWT  

**Description**

```php
public getJWT (void)
```

Get the JSON Web Token of the session 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> Encoded JSON Web Token of this session  




### Session::getSessionID  

**Description**

```php
public getSessionID (void)
```

Get the Session UUID 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> UUID of the session  




### Session::getTTL  

**Description**

```php
public getTTL (void)
```

Get the Time To Live (in seconds) for this session 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`

> Time To Live (in seconds)  




### Session::getTimeExpiration  

**Description**

```php
public getTimeExpiration (void)
```

Get the unix timestamp of when this session will expire 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`

> Unix timestamp of when this session will expire  




### Session::getTimeStarted  

**Description**

```php
public getTimeStarted (void)
```

Get the unix timestamp of when this session was started 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`

> Unix timestamp of when this session was started  




### Session::getUser  

**Description**

```php
public getUser (void)
```

Return an Ostiary\User for this session, if defined 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Ostiary\User`

> The Ostiary\User object, if defined  




### Session::setBucket  

**Description**

```php
public setBucket (string $bucket, mixed $data)
```

Set the contents of a bucket 

 

**Parameters**

* `(string) $bucket`
: Bucket type to set data for. Allowed types are "global" or "local".  
* `(mixed) $data`
: Contents for the specified data bucket  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if invalid bucket type is specified


### Session::setCookie  

**Description**

```php
public setCookie (string $name, int $expiry, string $path, string $domain, bool $secure, bool $http)
```

Write the JWT to a cookie 

 

**Parameters**

* `(string) $name`
: Name of the cookie  
* `(int) $expiry`
: [optional] Unix timestamp of expiration. Default: 0  
* `(string) $path`
: [optional] URI path for the cookie. Default: "/"  
* `(string) $domain`
: [optional] Domain for the cookie. Default: ""  
* `(bool) $secure`
: [optional] Indicates that the cookie should be  
   transmitted only over a secure HTTPS connection. Default: false  
* `(bool) $http`
: [optional] When true, the cookie will be made accessible  
   only through the HTTP protocol. Default: false  

**Return Values**

`bool`

> Result of PHP setcookie() function  




### Session::setJWT  

**Description**

```php
public setJWT (string $jwt)
```

Set the JSON Web Token of this session 

 

**Parameters**

* `(string) $jwt`
: Encoded JSON Web Token  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if the JWT fails a basic syntax check


### Session::setSessionID  

**Description**

```php
public setSessionID (string $uuid)
```

Set the UUID of the session 

 

**Parameters**

* `(string) $uuid`
: UUID of the session  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if invalid UUID syntax is provided


### Session::setTTL  

**Description**

```php
public setTTL (int $ttl)
```

Set the Time To Live (in seconds) for this session 

 

**Parameters**

* `(int) $ttl`
: Time To Live (in seconds) for this session  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $ttl is not an integer


### Session::setTimeExpiration  

**Description**

```php
public setTimeExpiration (int $timestamp_started)
```

Set the unix timestamp for when this session will expire 

 

**Parameters**

* `(int) $timestamp_started`
: Unix timestamp for when this session will expire  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $timestamp_expiration is not an integer


### Session::setTimeStarted  

**Description**

```php
public setTimeStarted (int $timestamp_started)
```

Set the unix timestamp for when this session was started 

 

**Parameters**

* `(int) $timestamp_started`
: Unix timestamp for when this session was started  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $timestamp_started is not an integer


### Session::setUser  

**Description**

```php
public setUser (\Ostiary\User|null )
```

Set the Ostiary\User object 

 

**Parameters**

* `(\Ostiary\User|null) `
: The Ostiary\User object to set, or null to unset  

**Return Values**

`bool`

> Always true  




### Session::toArray  

**Description**

```php
public toArray (void)
```

Return all data for this class in an associative array 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> Array of data for this Ostiary\Session object  




### Session::toJSON  

**Description**

```php
public toJSON (void)
```

Return all data for this class in a JSON-encoded string 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> JSON-encoded string of data for this Ostiary\Session object  




### Session::touchTimeExpiration  

**Description**

```php
public touchTimeExpiration (int $ttl)
```

Update the expiration to now + value of TTL stored in this object (or overridden) 

 

**Parameters**

* `(int) $ttl`
: [optional] Override the stored TTL value. This will also update the stored TTL value.  

**Return Values**

`int`

> Updated unix timestamp of when this session will expire  



