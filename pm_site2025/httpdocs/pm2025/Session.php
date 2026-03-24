<?php
/* Class for session wrapper
    https://github.com/rickardd/PHP-oop-account-template/blob/master/classes/Session.php
*/
// (re)Start session.
session_start(['cookie_samesite' => 'Lax',]);

class Session {

    // Check if name existis in session.
    public static function exists($name) {
        return (isset($_SESSION[$name])) ? true : false;
    }
    // Set value for name in session
    public static function put($name, $value) {
        return $_SESSION[$name] = $value;
    }
    // Get value for name in session
    public static function get($name){
        return $_SESSION[$name];
    }
    // Delete name from session
    public static function delete($name) {
        if(self::exists($name)){
            unset($_SESSION[$name]);
        }
    }
    // Destroy complete session (needed, or will flash do??)
    public static function destroy() {
        unset($_SESSION);
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"],$params["httponly"]);
        }
        session_destroy();
    }
    // set a flash message (??).
    public static function flash($name, $string = '') {
      if(self::exists($name)){
            $session = self::get($name);
            self::delete($name);
            return $session;
        } else {
            self::put($name, $string);
        }
    }
}
?>