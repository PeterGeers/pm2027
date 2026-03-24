<?php
// Create has for storing passwords.
class Hash {
  public static function make( $string, $salt = '') {
    return hash('sha256', $string . $salt);
  }

  public static function salt($length) {
//    return utf8_encode(mcrypt_create_iv($length));
    return utf8_encode(bin2hex(random_bytes(($length/2))));
  }

  public static function unique() {
    return self::make(uniqid());
  }
}