<?php
/* 
Objects to make data base and session manipulation simpler for user.
*/
require_once("config.php");
require_once("Database.php");
require_once("Hash.php");
require_once("Session.php");


class User {
    private $_db,
            $_data,
            $_sessionName,
            $_isLoggedIn;
    /* Are table fields for the user table.        
        id, name,  password, status, email, active, actcode, salt, joined, last_active, admin
    */

    // Class construct. If user is specified, pull details from database.
	public function __construct($user = null) {
        $this->_db = Database::getInstance();
        $this->_sessionName = SESSION_NAME;
        if(!$user) {
            if(Session::exists($this->_sessionName)) {
              $user = Session::get($this->_sessionName);
              if($this->find($user)) {
                $this->_isLoggedIn = true;
              } else {
                $this->logout(); // logout for unknown user id.
              }
            }
        } else {
            $this->find($user);
        }
    }
    // Update an existing user id table entry
    public function update( $fields = array(), $id = null) {
        if(!$id && $this->isLoggedIn()) {
          $id = $this->data()->id;
        }
        if(!$this->_db->update(USERS_PREFIX, $id, $fields)) {
          throw new Exception('There was a problem updating.');
        }
    }
    // Create a new user table entry.
    public function create($username, $password, $email) {
        $salt =  Hash::salt(32);
        $fields = array(
            'name' => $username,
            'password' => Hash::make($password, $salt),
            'email' => $email,
            'salt' => $salt,
            'joined' => date('Y-m-d H:i:s'),
            'actcode' => Hash::salt(20)
        );
        if(!$this->_db->insert(USERS_PREFIX, $fields) ) {
            throw new Exception('There was a problem creatin an account');
        }
        if(!$this->find($username)) {
            throw new Exception('New user not found in database. User : '.htmlspecialchars($username));
        }
    }
    // Find an existing user table entry.
    public function find( $user = null ) {
        if( $user ) {
            $field = ( is_numeric($user)) ? 'id' : 'name';
            $data = $this->_db->get(USERS_PREFIX, array($field, '=', $user));
    
            if($data->count()) {
                $this->_data = $data->first();
                return true;
            }
        }
        return false;
    }
    // Logon the user.
    public function login($username = null, $password = null) {

        if(!$username && !$password && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {
    
          $user = $this->find($username);
    
          if($user) {
            if($this->data()->password === Hash::make($password, $this->data()->salt)) {
              Session::put($this->_sessionName, $this->data()->id);
              $this->_isLoggedIn = true;
              return true;
            }
          }
        }
        return false;
    }
    public function logout(){
        Session::delete($this->_sessionName);
        $this->_isLoggedIn = false;
    }
    
    public function exists() {
        return (!empty($this->_data)) ? true : false;
    }

    public function data() {
        return $this->_data;
    }
    public function remove($id) {
        if ($id != $this->_data->id) {
            // Do not remove self.
            if(!$this->_db->delete(USERS_PREFIX,array('id', '=', $id)) ) {
                throw new Exception("There was a problem removing account $id.");
            }
        }
    }
    // Get all users from the database. If specified, only get given columns
    public function getAllUsers($columns) {
        $this->_db->query_columns('SELECT * FROM '.USERS_PREFIX.' ORDER BY `name`;', $columns);
        if ($this->_db->error() OR $this->_db->count() == 0) {
            return false;
        }
        return $this->_db->results();
    }
    
    public function name() {
        return $this->_data->name;
    }
    public function email() {
        return $this->_data->email;
    }
    public function isValidActivationCode($actcode) {
        return $this->_data->actcode === $actcode;
    }
    public function isActive() {
        return $this->_data->active =='1';
    }
    public function activation_code() {
        return $this->_data->actcode;
    }
    public function joined() {
        return $this->_data->joined;
    }
    public function last_active() {
        return $this->_data->last_active;
    }
    public function id() {
        return $this->_data->id;
    }
    public function isLoggedIn() {
        // Session no longer exists, so assume logged off
        if(!Session::exists($this->_sessionName)) {
            $this->_isLoggedIn = false;
        }
        return $this->_isLoggedIn;
    }
    public function isAdmin() {
        return $this->_data->admin == 1;
    }
    // Functions for activation and password management
    public function generateActivationCode() {
        return Hash::salt(20);
    }
    public function generateSalt() {
        return Hash::salt(32);
    }

    public function set_last_active() {
        $this->update(array('last_active' => date('Y-m-d H:i:s')), $this->id());
    }
    public function activate() {
        // Set user to deactivated so they can do a password reset using emailed link
        $this->update(array('active' => 1, 'actcode' => ''), $this->_data->id);
    }
    public function deactivate($actcode) {
        // Set user to deactivated so they can do a password reset using emailed link
        $this->update(array('active' => 0, 'actcode' => $actcode), $this->_data->id);
    }
    public function resetPassword($password) {
        $this->update(array('active' => 1, 'actcode' => '', 'password' => Hash::make($password, $this->_data->salt)), $this->_data->id);

    }
}
?>
