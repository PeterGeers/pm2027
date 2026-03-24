<!DOCTYPE html>
<?php
require_once "config.php" ;
require_once "Input.php";
require_once "Redirect.php";
require_once "User.php";
require_once 'booking_functions.php';
require_once 'email_functions.php';

const PAGE_TITLE = 'User Registration';
?>
<html lang="en">
<head>
	<meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
	<meta http-equiv="content-type" content="text/html;charset=UTF-8">
	<meta http-equiv="Content-Language" content="en">
	<meta http-equiv="Pragma" content="no-cache">
 	<meta http-equiv="Expires" content="-1">
 	<meta http-equiv="imagetoolbar" content="no">
	<meta charset="UTF-8">
 	<meta name="robots" content="noindex,nofollow">
 	<meta name="creation-date" content="10/01/2025">
 	<meta name="revisit-after" content="50 days">
 	<meta name="author" content="H-DC t Centrum">
 	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  	<link href="HDCCPM2025.css" rel="stylesheet" type="text/css"> 
    <title><?php echo SITE_TITLE_SHORT.' '.PAGE_TITLE; ?></title>
</head>
<body>
	<div class="container body-background">
      <div class="row g-5" style="margin-left: auto;">
        <div class="col-auto" id="main-contents">
<?php
echo page_header(PAGE_TITLE);

/* if we have a user_id, we are logged on. Do not register! */
$user = new User();
if($user->isLoggedIn()) {	
	// We have the user_id in the session, load details from DB
	echo '<p>You are currently logged in as '.htmlspecialchars($user->name()).'. You cannot register while you are logged in so we logged you out to retry!</p>';
	echo '<p>&nbsp;<br></p>';
	echo '<p><br><br<br><a href="index.php">&laquo; Go to the login page</a></p>';
	$user->logout();
} else {	
	if(Input::exists()) {
		// We got a POST, check and register if valid.
		if(Input::get('username') != '' AND Input::get('pass1') != '' AND Input::get('pass2') != '' AND Input::get('email') != '') {
			$user = new User(Input::get('username'));
			if(!$user->exists()) { 
				// User should not yet exist.
				if(Input::get('pass1') == Input::get('pass2')) {
					try {
						$user->create(Input::get('username'), Input::get('pass1'), Input::get('email'));
						$body  = '<p>You have created an account to registrer for the '.PM_TITLE.'. Using the link in this email you will activate your account to start the registration.</p>';
						$body .= '<p>Please keep this email secure and do not give anybody your details. Remember: the details are personal and for the '.PM_TITLE.' organized by '.PM_ORGANIZER.' only!</p>';
						$body .= '<p>To complete your account activation; <a href="'.SITE_NAME.'activate.php?id='.$user->id().'&code='.$user->activation_code().'&register=true">click here</a> or paste this in your browser '.SITE_NAME.'activate.php?id='.$user->id().'&code='.$user->activation_code().'&register=true.</p>';
						$body .= '<p>Account details:<br>';
						$body .= '&nbsp;&nbsp;User name: '.htmlspecialchars($user->name()).'<br>';
//								$body .= '(to be removed from email?)&nbsp;&nbsp;Password: '.htmlspecialchars(Input::get('pass1')).'</p><br>';
						$body .= '<p>If you found this email in your spam folder we recommend to mark the email address '.SITE_EMAIL.' as not spam to avoid this for future emails from us.</p>';
						$body .= '<p>** This is an automatic generated message and cannot be responded to. **</p>';
						$subject =  html_entity_decode(SITE_TITLE).' Account activation';
						[$mail_res, $mail_msg] = send_email($subject, $user->name(), $body, $user->email());
            if ($mail_res) {
							echo '<p>An account activation email will be send to you by <em><b>"'.html_entity_decode(SITE_TITLE).'&lt;'.SITE_EMAIL.'&gt;"</b></em> with the subject line <em><b>"'.$subject.'"</b></em> within minutes. Please click on the link in the email to activate your account. ';
							echo 'You cannot login until you have activated your account.</p>';
						  	echo '<p><br>If you do not see the email, make sure to check your email spam folder, especially if you have a gmail.com, or outlook.com email address. When you did not receive the email, contact '.SITE_EMAIL.'.</p>';
						 	echo '<p>&nbsp;<br></p>';
							echo '<p><br><br<br><a href="index.php">&laquo; Go to the login page</a></p>';
						} else {
							echo '<p><br><br>An error occurred while trying to send the email:'.$mail_msg.'</p>';
							echo '<p><br><br>Is your email address correct? '.htmlspecialchars($user->email()).'</p>';
							echo '<p><br><br>Please contact <a href="mailto:'.SITE_EMAIL.'">'.SITE_EMAIL.'</a>.</p>';
						}
					} catch (Exception $e) {
						echo '<p><br><br>Error "'.htmlspecialchars($e).'" occurred while trying to add your account. Please try again later.</p>';
						echo '<p><br><br><a href="javascript:history.back()">&laquo; Go back</a></p>';
					}
				} else {
					echo '<p><br><br>The passwords entered by you do not match.</p><p><br><br><a href="javascript:history.back()">&laquo; Go back</a></p>';
				}
		    } else {
				echo '<p><br><br>The user name "'.htmlspecialchars(Input::get('username')).'" is all ready being used. Please choose a different user name.<br><br></p>';
				echo '<p><a href="javascript:history.back()">&laquo; Go back</a></p>';
			}
		} else {
			echo '<p>br><br>You forgot to fill in more or multiple fields.</p><p><br>\n<a href="javascript:history.back()">&laquo; Go back</a></p>';
		}
	} else {
		if (ACCOUNT_REGISTRATION_LOCKED) { 
			echo '<p><br><br>Account registration is closed.</p>';
			echo '<p><br><br><a href="javascript:history.back()">&laquo; Go back</a></p>';
		} else {
			// No input data, show form to collect.
?>
	<p><br>Use a user name of your choosing to create your account to use for the <?= PM_TITLE ?> registration.</p>
 	<form method="post" action="register.php" accept-charset="utf-8" class="gy-2 align-items-center">
		<div class="row mb-3">
			<label for="username" class="col-lg-3 col-md-4 col-form-label">User name:</label>
   			<div class="col-md-4">
				<input type="text" id="username" name="username" class="form-control" autofocus autocomplete="off" required placeholder="Choose your user name.." minlength="<?=USER_NAME_MIN_LENGTH?>" maxlength="<?=USER_NAME_MAX_LENGTH?>" />
			</div>
    		<div class="col-lg-3 col-md-3 col-sm-8">
    			<span id="usernameHelpInline" class="form-text">Must be 5-50 characters long.</span>
			</div>
		</div>
		<div class="row mb-3">
    		<label for="pass1" class="col-lg-3 col-md-4 col-form-label">Password:</label> 
    		<div class="col-md-4">
				<input type="password" id="pass1" name="pass1" class="form-control" autocomplete="off" required placeholder="Choose password.." minlength="<?=USER_PWD_MIN_LENGTH?>" maxlength="<?=USER_PWD_MAX_LENGTH?>" />
			</div>
	        <div class="col-lg-3 col-md-3 col-sm-8">
    			<span id="passwordHelpInline" class="form-text">Must be 8-20 characters long.</span>
    		</div>  
    	</div>
    	<div class="row mb-3">
    		<label for="pass2" class="col-lg-3 col-md-4 col-form-label">Re-enter password:</label> 
    		<div class="col-md-4">
				<input type="password" id="pass2" name="pass2" class="form-control" autocomplete="off" required placeholder="Confirm password.." minlength="<?=USER_PWD_MIN_LENGTH?>" maxlength="<?=USER_PWD_MAX_LENGTH?>" />
			</div>  
	        <div class="col-lg-3 col-md-3 col-sm-8">
   				<span id="passwordHelpInline" class="form-text">Must be 8-20 characters long.</span>
			</div>  
        </div>
		<div class="row mb-3">
			<label for="email" class="col-lg-3 col-md-4 col-form-label">Email address:</label> 
			<div class="col-md-4">
				<input type="email" id="email" name="email" class="form-control"  autocomplete="off" required placeholder="Enter your email address.." maxlength="<?=USER_EMAIL_MAX_LENGTH?>" />
			</div>
	        <div class="col-lg-3 col-md-3 col-sm-8">
   				<span id="emailHelpInline" class="form-text">Enter a valid email address.</span>
			</div>  
		</div>
		<div class="row mb-3">
			<label for="submit" class="col-lg-3 col-md-4 col-form-label"></label> 
			<div class="d-grid col-md-4">
   				<input type="submit" name="submit" class="btn btn-primary" value="Register Account" /> 
			</div>
    	</div>
	</form>
<?php
		}
	}
}
?>
       		</div> <!-- id="main-contents" -->
   		</div> <!-- class row -->
   	</div> <!-- class .container -->
</body>
</html>