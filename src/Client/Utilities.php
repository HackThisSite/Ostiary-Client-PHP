<?php namespace Ostiary\Client;

use RandomLib\Factory as RandFactory;

class Utilities {


  /**
   * Callback to invoke for debug logging
   */
  public static $debug_callback = null;


  /**
   * @param string $message Log message
   * @return mixed False if debug is disabled, result of call_user_func(), or true otherwise
   */
  public static function debug($message) {
    if (!defined('OSTIARY_DEBUG')) return false;
    $dbt = debug_backtrace();
    $msg = sprintf("[%s::%s] %s\n", $dbt[1]['class'], $dbt[1]['function'], $message);
    if (!empty(static::$debug_callback) && is_callable(static::$debug_callback)) {
      return call_user_func(static::$debug_callback, $msg);
    } else {
      $ms = explode('.', microtime(true));
      $ts = str_replace('%', $ms[1], date('D, d M Y H:i:s.% O', $ms[0]));
      printf('[%s] %s', $ts, $msg);
    }
    return true;
  }


  /**
   * Generate URL-safe base64-encoded string
   *
   * @source http://us1.php.net/manual/en/function.base64-encode.php#103849
   *
   * @return string URL-safe base64-encoded string
   */
   public static function base64_urlencode($data) {
     return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
   }

   /**
    * Decode a URL-safe base64-encoded string
    *
    * @source http://us1.php.net/manual/en/function.base64-encode.php#103849
    *
    * @return string Decoded URL-safe base64-encoded string
    */
   function base64_urldecode($data) {
     return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
   }


  /**
   * @param integer Length of string in bytes
   * @param boolean [optional] Use lowercase and uppercase letters (default: true)
   * @return string Random string
   */
  public static function rand_alnum($length = 8, $mixed_case = true) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    if ($mixed_case) $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $factory = new RandFactory();
    $generator = $factory->getLowStrengthGenerator();
    return $generator->generateString($length, $characters);
  }


  /**
   * Backwards-compatible alphanumeric string test. Uses ctype_alnum if available,
   * but will fall back to preg_match if not.
   * @param string String to test
   * @return boolean True on pass, False on fail
   */
  public static function is_alnum($value, $use_preg = false) {
    if ($use_preg === false && function_exists('ctype_alnum')) {
      return ctype_alnum($value);
    } else {
      return (preg_match('/^[a-zA-Z0-9]+$/', $value) ? true : false);
    }
  }


  /**
   * Test if a string is a valid URL
   * @param string String to test
   * @return boolean True on pass, False on fail
   */
  public static function is_url($value) {
    return !empty(filter_var($value, FILTER_VALIDATE_URL));
  }


  /**
   * Test if a string is a valid email
   * @param string String to test
   * @return boolean True on pass, False on fail
   */
  public static function is_email($value) {
    return !empty(filter_var($value, FILTER_VALIDATE_EMAIL));
  }


  /**
   * Decode a string with URL-safe Base64.
   * @param string $input A Base64 encoded string
   * @return string A decoded string
   * @source https://github.com/firebase/php-jwt
   */
  public static function urlsafeB64Decode($input) {
    $remainder = strlen($input) % 4;
    if ($remainder) {
      $padlen = 4 - $remainder;
      $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
  }


}

// EOF
