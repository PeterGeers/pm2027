<?php
require_once "config.php" ;
require_once "Session.php";
require_once "Redirect.php";
require_once "User.php";

// Get active user and log it out.
$user = new User();
if($user->isLoggedIn()) {
    $user->logout();
} else{
    // Nobody logged on.
}
?>