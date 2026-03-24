<!DOCTYPE html>
<?php
require_once 'config.php';
require_once 'Input.php';
require_once 'Redirect.php';
require_once 'User.php';
require_once 'booking_functions.php';

const PAGE_TITLE = 'Account Activation';
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

if(Input::exists('post')) {
    // Get here via password change form
    $id = Input::get('id');
    $code = Input::get('code');
    $user = new User($id);
    if($user->exists()) { 
        // User submitted the password change request
        if(Input::get('pass1') === Input::get('pass2')) {
            try {
                $user->resetPassword(Input::get('pass1'));
                echo "<p>Your your password has been changed and your account is now active for use.</p><br> <br><a href=\"index.php\">&laquo; Go to logon page</a></p>";
            } catch (Exception $e) {    
                echo "<p>An error has occurred while changing your password, please try again</p>";
                echo $e;
            }
        } else {
            echo "<p>The passwords you entered don't match.<br> <br><a href=\"javascript:history.back()\">&laquo; Try again</a></p>";
        }
    } else {
        // We should not get here. Redirect to logon page.
//        echo 'User unknown, redirect to '.SITE_LOGON_PAGE;
	    Redirect::to(SITE_LOGON_PAGE);  
    }
} elseif(Input::exists('get')) {
    // Got here via URL.
    $id = Input::get('id');
    $code = Input::get('code');
    if($id != '' AND $code != '') {
        $user = new User($id);
        if($user->exists()) { 
            if(!$user->isActive()) {
                if($user->isValidActivationCode($code)) {
                    if(Input::get('activate')) {
                        // Activate and keep pwd
                        try {
                            $user->activate();
                            echo '<p>Your account has successfully been re-activated, you can now use your old password again.</p><br><br><a href="index.php">&laquo; Go to logon page</a></p>';
                        } catch (Exception $e) {    
                            echo '<p>An error has occurred while activating you account, please try again.</p>';
                            echo $e;
                        }
                    } elseif (Input::get('register')) {
                        // Activate for registration
                        try {
                            $user->activate();
                            echo '<p>Your account has successfully been activated, you can now log on and start your booking.</p><br><br><a href="index.php">&laquo; Go to logon page</a></p>';
                        } catch (Exception $e) {    
                            echo '<p>An error has occurred while activating you account, please try again.</p>';
                            echo $e;
                        }
                    } elseif (Input::get('reset')) {
                        // Requesting password reset. Show form.
?>
    <p>Enter the new password details for user : <?= $user->name() ?></p>
    <form method="post" action="activate.php?id=<?= $id ?>&code=<?= $code ?>" accept-charset="utf-8" class="gy-2 align-items-center">
        <div class="row mb-3">
            <label for="pass1" class="col-lg-3 col-md-4 col-form-label">Password:</label> 
            <div class="col-lg-5 col-md-4">
                <input type="password" id="pass1" name="pass1" class="form-control" autocomplete="off" required placeholder="Choose password.." minlength="<?=USER_PWD_MIN_LENGTH?>" maxlength="<?=USER_PWD_MAX_LENGTH?>" />
        	</div>
		    <div class="col-lg-3 col-md-3 col-sm-8">
       			<span id="passwordHelpInline" class="form-text">Must be 8-20 characters long.</span>
	        </div>  
		</div>
		<div class="row mb-3">
       		<label for="pass2" class="col-lg-3 col-md-4 col-form-label">Re-enter password:</label> 
            <div class="col-lg-5 col-md-4">
           		<input type="password" id="pass2" name="pass2" class="form-control" autocomplete="off" required placeholder="Confirm password.." minlength="<?=USER_PWD_MIN_LENGTH?>" maxlength="<?=USER_PWD_MAX_LENGTH?>" />
			</div>  
            <div class="col-lg-3 col-md-3 col-sm-8">
                <span id="passwordHelpInline" class="form-text">Must be 8-20 characters long.</span>
            </div>  
        </div>
        <div class="row mb-3">
            <label for="submit" class="col-lg-3 col-md-4 col-form-label"></label> 
            <div class="d-grid col-lg-5 col-md-4">
                <input type="submit" name="submit" class="btn btn-primary" value="Change password" /> 
            </div>
        </div>
    </form>
<?php
                    } else {
                        // Unknown activation option
//                        echo 'Unknown activation option, redirect to '.SITE_LOGON_PAGE;
		                Redirect::to(SITE_LOGON_PAGE);  
                    }
                } else {
                    // Activation code does not match.
                    echo '<p><br>Your activation code is not valid. In case you have lost your activation code, go to "forgot password" at the logonpage.</p>';
                    echo '<p><br><a href="index.php">&laquo; Go to logon page</a></p>';
                }
            } else {
                // User not inactive
                echo '<p><br>Your account is already active. You can login. In case you lost your password, go to "forgot password" at the logonpage.</p>';
                echo '<p><br><br><a href="index.php">&laquo; Go to logon page</a></p>';
            }
        } else {
            // User id not in database
//            echo 'User unknown, redirect to '.SITE_LOGON_PAGE;
		    Redirect::to(SITE_LOGON_PAGE);  
        }
    } else {
        // missing form input
//        echo 'Missing input data, redirect to '.SITE_LOGON_PAGE;
		Redirect::to(SITE_LOGON_PAGE);  
    }
} else {
    // No form input
//    echo 'No input data, redirect to '.SITE_LOGON_PAGE;
		Redirect::to(SITE_LOGON_PAGE);  
}
?>
            </div> <!-- id="main-contents" -->
        </div> <!-- class row -->
    </div> <!-- class .container -->
</body>
</html>