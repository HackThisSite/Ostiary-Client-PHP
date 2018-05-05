# Ostiary\Client  

Ostiary\Client interacts either directly with an Ostiary Redis environment, or with an Ostiary server





## Methods

| Name | Description |
|------|-------------|
|[__construct](#client__construct)|Construct an Ostiary client.|
|[createSession](#clientcreatesession)|Create a new Ostiary session|
|[deleteSession](#clientdeletesession)|Delete an Ostiary session|
|[getDriver](#clientgetdriver)|Return the raw driver object in use.|
|[getSession](#clientgetsession)|Get an Ostiary session by the JSON Web Token identifier|
|[getSessionFromCookie](#clientgetsessionfromcookie)|Get an Ostiary session by the contents of a cookie|
|[setBucket](#clientsetbucket)|Set the data for a specific bucket|
|[setDebugCallback](#clientsetdebugcallback)|Set the debug callback to be used for logging|
|[setSession](#clientsetsession)|Set an Ostiary session to the values of an Ostiary\Session object|
|[touchSession](#clienttouchsession)|Update the expiration of a Session to now + TTL (stored value or overridden)|




### Client::__construct  

**Description**

```php
public __construct (array $options, callback $debug_callback)
```

Construct an Ostiary client. 

 

**Parameters**

* `(array) $options`
: Configuration options for this Ostiary client  
* `(callback) $debug_callback`
: [optional] Callback function for debug output. Automatically enables debug output. Provides one parameter: (string) Debug message  

**Return Values**



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $options is invalid


### Client::createSession  

**Description**

```php
public createSession (array $bucket_data, array $options)
```

Create a new Ostiary session 

 

**Parameters**

* `(array) $bucket_data`
: [optional] Array of bucket data. Allowed indices: "global" and "local"  
* `(array) $options`
: [optional] Array of optional settings. Allowed key/values:  
   ttl  (int)   Override the TTL value for this Ostiary client. Default: undefined  

**Return Values**

`bool|\Ostiary\Session`

> A populated Ostiary\Session object, or false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $bucket_data is not an array or if $options is invalid

`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::deleteSession  

**Description**

```php
public deleteSession (string $jwt)
```

Delete an Ostiary session 

 

**Parameters**

* `(string) $jwt`
: JSON Web Token identifier of the session  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::getDriver  

**Description**

```php
public getDriver (void)
```

Return the raw driver object in use. 

If this client is configured to use Ostiary, this will return a `\GuzzleHttp\Client`  
object. If configured to use Redis, this will return a `\Predis\Client` object. 

**Parameters**

`This function has no parameters.`

**Return Values**

`\GuzzleHttp\Client|\Predis\Client`

> Driver object in use for this Ostiary\Client  




### Client::getSession  

**Description**

```php
public getSession (string $jwt, array $options)
```

Get an Ostiary session by the JSON Web Token identifier 

 

**Parameters**

* `(string) $jwt`
: JSON Web Token identifier of the session  
* `(array) $options`
: [optional] Array of optional settings. Allowed key/values:  
   update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true  
   ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: undefined  

**Return Values**

`null|\Ostiary\Session`

> A populated Ostiary\Session object, or null on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $bucket_data is not an array or if $options is invalid

`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::getSessionFromCookie  

**Description**

```php
public getSessionFromCookie (string $cookie_name, array $options)
```

Get an Ostiary session by the contents of a cookie 

 

**Parameters**

* `(string) $cookie_name`
: Name of the cookie  
* `(array) $options`
: [optional] Array of optional settings. Allowed key/values:  
   update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true  
   ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: undefined  

**Return Values**

`null|\Ostiary\Session`

> A populated Ostiary\Session object, or null on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $bucket_data is not an array or if $options is invalid

`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::setBucket  

**Description**

```php
public setBucket (string $jwt, string $bucket, mixed $data, array $options)
```

Set the data for a specific bucket 

Warning: This will overwrite all existing contents of the specified bucket in Ostiary or Redis! 

**Parameters**

* `(string) $jwt`
: JSON Web Token identifier of the session  
* `(string) $bucket`
: Must be either "global" or "local"  
* `(mixed) $data`
: Data to set for the bucket  
* `(array) $options`
: [optional] Array of optional settings. Allowed key/values:  
   update_expiration  (bool)   Update the expiration time of a session to now + TTL (stored TTL or overridden). Default: true  
   ttl  (int)   Override the TTL value for this Ostiary client. Ignored if `update_expiration` is false. Default: undefined  

**Return Values**

`bool|\Ostiary\Session`

> An updated Ostiary\Session object, or false on failure  



**Throws Exceptions**


`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::setDebugCallback  

**Description**

```php
public setDebugCallback (callback $debug_callback)
```

Set the debug callback to be used for logging 

 

**Parameters**

* `(callback) $debug_callback`
: Callback function for debug output. Automatically enables debug output. Provides one parameter: (string) Debug message  

**Return Values**

`boolean`

> True on success  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $debug_callback is not callable


### Client::setSession  

**Description**

```php
public setSession (\Ostiary\Session $session)
```

Set an Ostiary session to the values of an Ostiary\Session object 

Warning: This will overwrite all contents of the session in Ostiary or Redis!  
There is no option provided for updating the expiration. To do that, use the  
`touchTimeExpiration()` method in the Ostiary\Session object or change the  
TTL using the `setTTL()` method. 

**Parameters**

* `(\Ostiary\Session) $session`
: A populated Ostiary\Session object  

**Return Values**

`bool|\Ostiary\Session`

> The Ostiary\Session object, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if $session is not an Ostiary\Session object

`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server


### Client::touchSession  

**Description**

```php
public touchSession ( $jwt, array $options)
```

Update the expiration of a Session to now + TTL (stored value or overridden) 

 

**Parameters**

* `() $jwt`
: JSON Web Token identifier of the session  
* `(array) $options`
: [optional] Array of optional settings. Allowed key/values:  
   ttl  (int)   Override the TTL value for this Ostiary client. Default: undefined  

**Return Values**

`\Ostiary\Session`

> An updated Ostiary\Session object  



**Throws Exceptions**


`\Ostiary\Client\Exception\OstiaryServerException`
> If the driver is Ostiary, this is thrown if there was an error interacting with the Ostiary server

