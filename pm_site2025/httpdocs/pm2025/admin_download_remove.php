<?php

require_once 'config.php';
require_once 'Session.php';
require_once 'User.php';
require_once 'Input.php';


$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive() OR !$user->isAdmin()) {
	// We do not have the user_id in the session, redirect to logon page
	echo 'Could not find user, redirect to logon.';
	Redirect::to(SITE_LOGON_PAGE);
	exit;
}
if(!Input::exists()) {
	echo '{"result":"error", "msg": "Unknown error (no data)"}';
	exit;
}
$file_name = Input::Get('file_name');
if (file_exists($file_name)) {
	unlink($file_name);
} else {
	// File not found.
}
?>	
