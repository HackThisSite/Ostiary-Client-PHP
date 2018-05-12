# Ostiary\User  

Ostiary\User stores data for a user in an Ostiary session





## Methods

| Name | Description |
|------|-------------|
|[__construct](#user__construct)|Construct an Ostiary user object|
|[deleteParameter](#userdeleteparameter)|Delete a parameter|
|[getAllParameters](#usergetallparameters)|Get all parameters|
|[getDisplayName](#usergetdisplayname)|Get the display name (also known as gecos)|
|[getEmail](#usergetemail)|Get the email address|
|[getGecos](#usergetgecos)|Alias of `getDisplayName()`|
|[getParameter](#usergetparameter)|Get a single parameter by name|
|[getParameterNames](#usergetparameternames)|Get all parameter names|
|[getRoles](#usergetroles)|Get the authorization roles|
|[getUsername](#usergetusername)|Get the username|
|[parameterExists](#userparameterexists)|Test if a parameter exists by name|
|[setDisplayName](#usersetdisplayname)|Set the display name|
|[setEmail](#usersetemail)|Set the email address|
|[setGecos](#usersetgecos)|Alias of `setDisplayName()`|
|[setParameter](#usersetparameter)|Set a specific parameter to a value|
|[setParameters](#usersetparameters)|Overwrite parameters, either specified or flush all and reset|
|[setRoles](#usersetroles)|Set the authorization roles|
|[setUsername](#usersetusername)|Set the username|
|[toArray](#usertoarray)|Return all data for this class in an associative array|
|[toJSON](#usertojson)|Return all data for this class in a JSON-encoded string|




### User::__construct  

**Description**

```php
public __construct (string $username, string $display_name, string $email, array $roles, array $parameters)
```

Construct an Ostiary user object 

 

**Parameters**

* `(string) $username`
: [optional] Username. Default: ""  
* `(string) $display_name`
: [optional] Display name (also known as 'gecos'). Default: ""  
* `(string) $email`
: [optional] Email address. Default: ""  
* `(array) $roles`
: [optional] Array of authorization role. Default: empty array  
* `(array) $parameters`
: [optional] Array of additional parameters. Default: empty array  

**Return Values**



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if any param is invalid


### User::deleteParameter  

**Description**

```php
public deleteParameter (string $name)
```

Delete a parameter 

 

**Parameters**

* `(string) $name`
: Name of parameter  

**Return Values**

`bool`

> True on success, false if parameter did not exist  




### User::getAllParameters  

**Description**

```php
public getAllParameters (void)
```

Get all parameters 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> Array of all parameters and values  




### User::getDisplayName  

**Description**

```php
public getDisplayName (void)
```

Get the display name (also known as gecos) 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> Display name  




### User::getEmail  

**Description**

```php
public getEmail (void)
```

Get the email address 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> Email address  




### User::getGecos  

**Description**

```php
public getGecos (void)
```

Alias of `getDisplayName()` 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> Display name  




### User::getParameter  

**Description**

```php
public getParameter (string $name)
```

Get a single parameter by name 

 

**Parameters**

* `(string) $name`
: Name of the parameter  

**Return Values**

`null|mixed`

> Null if the parameter doesn't exist, otherwise the value of the parameter. Because a parameter could be set to null, this should not be used to test for parameter existence. Use `parameterExists()` instead.  




### User::getParameterNames  

**Description**

```php
public getParameterNames (void)
```

Get all parameter names 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> List of all parameter names  




### User::getRoles  

**Description**

```php
public getRoles (void)
```

Get the authorization roles 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> Array of authorization role  




### User::getUsername  

**Description**

```php
public getUsername (void)
```

Get the username 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> Username  




### User::parameterExists  

**Description**

```php
public parameterExists (string $name)
```

Test if a parameter exists by name 

 

**Parameters**

* `(string) $name`
: Name of the parameter  

**Return Values**

`bool`

> True if exists, false if not  




### User::setDisplayName  

**Description**

```php
public setDisplayName (string $display_name)
```

Set the display name 

 

**Parameters**

* `(string) $display_name`
: Display name to set  

**Return Values**

`bool`

> Always true  




### User::setEmail  

**Description**

```php
public setEmail (string $email)
```

Set the email address 

 

**Parameters**

* `(string) $email`
: Email address  

**Return Values**

`bool`

> Always true  




### User::setGecos  

**Description**

```php
public setGecos (string $gecos)
```

Alias of `setDisplayName()` 

 

**Parameters**

* `(string) $gecos`
: Display name to set  

**Return Values**

`bool`

> Always true  




### User::setParameter  

**Description**

```php
public setParameter (string $name, string $value)
```

Set a specific parameter to a value 

 

**Parameters**

* `(string) $name`
: Name of parameter  
* `(string) $value`
: Value to set for the parameter  

**Return Values**

`bool`

> True on success  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if the parameter name is invalid


### User::setParameters  

**Description**

```php
public setParameters (array $parameters, bool $flush)
```

Overwrite parameters, either specified or flush all and reset 

This will let you overwrite a list of specific parameters, or optionally flush all values and overwrite with a clean list. 

**Parameters**

* `(array) $parameters`
: Array of parameters to set  
* `(bool) $flush`
: [optional] Flush all parameters and overwrite with value of `$parameters`. Default: false  

**Return Values**

`bool`

> True on success, false on failure  



**Throws Exceptions**


`\InvalidArgumentException`
> Thrown if any parameter name is invalid


### User::setRoles  

**Description**

```php
public setRoles (array $roles)
```

Set the authorization roles 

This does not merge with existing settings, but will overwrite the roles array. 

**Parameters**

* `(array) $roles`
: Array of authorization role. Set to blank array to flush roles.  

**Return Values**

`bool`

> Always true  




### User::setUsername  

**Description**

```php
public setUsername (string $username)
```

Set the username 

 

**Parameters**

* `(string) $username`
: Username to set  

**Return Values**

`bool`

> Always true  




### User::toArray  

**Description**

```php
public toArray (void)
```

Return all data for this class in an associative array 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`

> Array of data for this Ostiary\User object  




### User::toJSON  

**Description**

```php
public toJSON (void)
```

Return all data for this class in a JSON-encoded string 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string`

> JSON-encoded string of data for this Ostiary\User object  



