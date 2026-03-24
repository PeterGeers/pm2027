<?php
	require_once 'config.php';
	require_once 'Session.php';
	require_once 'Input.php';
	require_once 'Redirect.php';
	require_once 'User.php';
  	require_once 'booking_functions.php';

  	const PAGE_TITLE = 'Sign-on';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
  <meta http-equiv="content-type" content="application/x-www-form-urlencoded;charset=utf-8">
  <meta http-equiv="Content-Language" content="en">
  <meta http-equiv="pragma" content="cache">
  <meta http-equiv="imagetoolbar" content="no">
  <meta name="robots" content="noindex,nofollow">
  <meta name="creation-date" content="10/01/2025">
  <meta name="revisit-after" content="50 days">
  <meta name="author" content="H-DC t Centrum">
  <meta name="description" content="H-D Club 't Centrum, de Harley-Davidson club in het centrum van nederland : Utrecht.">
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
$destination_page = SITE_BOOK_PAGE;
$user = new User();
if($user->isLoggedIn()) {
  // See if we have an admin user, then redirect to admin page
  if ($user->isAdmin()) {
    $destination_page = SITE_ADMIN_PAGE;
  }
  // We have the user_id in the session, redirect to expected page.
	Redirect::to($destination_page);
} else {	
	if(Input::exists()) {
		if(Input::get('username') != '' AND Input::get('pass') != '') {
			// Validate login details, load user details from DB.
			$user->login(Input::get('username'), Input::get('pass'));
			if($user->isLoggedIn()) { 
				if($user->isActive()) {
          // See if we have an admin user, then redirect to admin page
          if ($user->isAdmin()) {
            $destination_page = SITE_ADMIN_PAGE;
          }
          // Update last active date and time
          $user->set_last_active();
          Redirect::to($destination_page);
				} else {
          $user->logout();
					echo '<p><br><br>Your account is not yet activated. Please activate your account by clicking the link that was sent to you by email.</p>';
					echo '<p>Do not forget to look in your spam folder!</p>';
					echo '<p><br><br><a href="javascript:history.back()">&laquo; Go back.</a></p>';
				}
			} else {
				echo '<p><br><br>Unknown user name and/or password combination.</p>';
				echo '<p><br><br><a href="javascript:history.back()">&laquo; Go back.</a></p>';
			}
		} else {
			// No inputs, redirect back to login page.
			Redirect::to(SITE_LOGON_PAGE);
		}
	} else {
?>
  <p>The <?= PM_TITLE ?> is organized by <?= PM_ORGANIZER ?>. </p>
  <p>Log on to create or update the booking for your club.
<?php
if (!ACCOUNT_REGISTRATION_LOCKED) { 
?>
     Make sure to register for an account first.
<?php
}
?>
  </p>
  <form method="post" action="index.php" accept-charset="utf-8" class="gy-2 align-items-center">
    <div class="row mb-3">
      <label for="username" class="col-md-3 col-form-label">User name:</label>
      <div class="col-md-6">
        <input type="text" id="username" name="username" class="form-control" autofocus autocomplete="off" required placeholder="Your user name.." minlength="<?=USER_NAME_MIN_LENGTH?>" maxlength="<?=USER_NAME_MAX_LENGTH?>" />
      </div>
    </div>
    <div class="row mb-3">
      <label for="pass" class="col-md-3 col-form-label">Password:</label> 
      <div class="col-md-6">
        <input type="password" id="pass" name="pass" class="form-control" autocomplete="off" required placeholder="Enter password.." minlength="<?=USER_PWD_MIN_LENGTH?>" maxlength="<?=USER_PWD_MAX_LENGTH?>" />
      </div>
    </div>
    <div class="row mb-3">
   		<label for="submit" class="col-md-3 col-form-label"></label> 
   		<div class="d-grid col-md-6">
        <input type="submit" id="submit" name="submit" class="btn btn-primary" value="Log in" /> 
		  </div>
   	</div>
  </form>
  <div class="row">
    <div class="col-auto">
      <p><br><small><a href="forgotpass.php" title="Forgot password">Forgot password</a></small></P>
    </div>  
  </div>  
<?php
if (!ACCOUNT_REGISTRATION_LOCKED) { 
?>
  <div class="row">
    <div class="col-9">
    <p><br><br>Before you can log on you must first create an account. Note that there can be only one account per club.</p>
    </div>
  </div>  
  <div class="row mb-3">
    <label class="col-md-3 col-form-label"></label> 
 		<div class="d-grid col-md-6">
      <button class="btn btn-primary" onclick="location.href='register.php';" >Create new account</button>
    </div>
  </div>
<?php
}
?>
  <div class="row mb-3">
    <div class="col-6">
      <center>
        <p>&nbsp;<br></p>
        <p>&nbsp;<small><a href="<?= PM_ORGANIZER_URL ?>"><?= PM_ORGANIZER_URL ?> go to our club website.</a></small></p>
        <p>&nbsp;</p>
      </center>
    </div>  
  </div>
<?php
  }
}
?>
        </div> <!-- id="main-contents" -->
      </div> <!-- class row -->
    </div> <!-- class .container -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    (() => {
	    'use strict'
      // Do some animation for PM logo, grow parent too to avoid screen movement.
      let image = $('#page_header_img2');
      let duration = 700;
      let factor = 1.5;
      let ini_height = image.parent().height();
      let o_w = image.width();
      let o_h = image.height();
      function pulse(id) {
        if (id < 5) {
          image.animate({
            width: o_w, 
            opacity: 0.5
            }, duration, function() {
              image.animate({
                  width: o_w*factor, 
                  opacity: 1
              }, duration, function() {
                image.parent().height(ini_height*factor);
                pulse(id+1);
              });
          });
        } else {
          image.animate({ width: o_w, opacity: 1 }, duration);
          image.parent().animate({height: ini_height}, duration)
        }
      };
      pulse(1);
    })() // end of functions that need to wait on document load
  </script>
  </body>
</html>
