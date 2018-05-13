# Changelog

## v0.2.0 - 2018-05-13

* Breaking Changes:
  * `createSession()` in Ostiary\Client has changed its parameters

* Added:
  * Ostiary\User object
  * Supporting methods in Ostiary\Session for the new User object
  * IP address in Ostiary\Session
  * A lot of testing

* Changed:
  * `createSession()` parameters changed to reflect new User object
  * Documentation for new User object changes

* Fixed:
  * Bad namespaces in models
  * Util::base64_urldecode() was not properly set to static
  * Backwards compatibility for `hash_equals()`

## v0.1.2 - 2018-05-10

* Added
  * Added `getAllSessions()` call to Ostiary\Client
  * Better UUID generator
  * More debug logging

* Fixed
  * Made TTL values work properly (0 = no expiry)
  * Bad `setex` Redis calls
  * Typoed `del` Redis call
  * Debug log formatting

## v0.1.1 - 2018-05-06

* Added
  * Ostiary\Client method `getAllSessions()`

* Fixed
  * phpUnit tests
  * Composer definitions
  * Travis-CI settings

## v0.1.0 - 2018-05-05

* Initial release
