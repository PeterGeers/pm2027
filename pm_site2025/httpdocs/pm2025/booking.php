<!DOCTYPE html>
<?php
require_once 'config.php';
require_once 'Redirect.php';
require_once 'User.php';
require_once 'BookingClass.php';

const PAGE_TITLE = 'Booking Details';

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive()) {
	// We do not have the user_id in the session, redirect to logon page
//	echo 'Could not find active user, redirect to logon.';
	Redirect::to(SITE_LOGON_PAGE);
	exit;
} 
// See if we have a booking
$initial_message = "Existing";
$booking = new Booking();
$booking_new = FALSE;
if (!$booking->loadDBData(user_id:$user->id())) {
	// Make new booking entry
	$booking->createNewBooking($user->id());
	$booking_new = TRUE;
	$initial_message = "New";
}
$booking_id = $booking->getBookingID();
if ($booking->isSubmitted()) { $initial_message = "Submitted"; }
if ($booking->getLocked()) { $initial_message = "Locked"; }
if (ALL_BOOKINGS_LOCKED) { 
	$initial_message = "No more changes allowed"; 
	$booking->setLocked(true);
}

//to-do
// Check if modified date is after submitted date.

// Check submission and payment status.

// Set limits
$maximum_delegates = $booking->getMaxDelegates();
$maximum_guests = $booking->getMaxGuests();
$maximum_rooms = $booking->getMaxRooms();
$maximum_travels = $booking->getMaxTravels();
?>
<html lang="en">
<head>
	<meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
	<meta http-equiv="content-type" content="text/html;charset=UTF-8">
	<meta http-equiv="Content-Language" content="en">
	<meta http-equiv="pragma" content="cache">
	<meta http-equiv="imagetoolbar" content="no">
	<meta name="robots" content="noindex,nofollow">
	<meta name="creation-date" content="10/01/2025">
	<meta name="revisit-after" content="50 days">
	<meta name="author" content="H-DC t Centrum">
	<meta name="description" content="H-D Club 't Centrum, de Harley-Davidson club in het centrum van nederland : Utrecht.">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/css/intlTelInput.css">
	<link rel="stylesheet" href="HDCCPM2025.css" type="text/css"/> 
	<title><?php echo SITE_TITLE_SHORT.' '.PAGE_TITLE; ?></title>
</head>
<body>
<div id="viewport" class="container-fluid" >
<!-- Navigation bar locked at top of page. -->
<nav class="navbar fixed-top bg-body-tertiary" >
  <div class="container-fluid">
  	<div class="navbar-brand">
		<span><img class="pm-logo-navbar" alt="PM Logo" src="<?= PM_LOGO ?>"></span>
		<span class="navbar-ref" id="navbar-ref">Booking Ref # <?=$booking->getReference()?></span>
		<span class="navbar-message" id="navbar-msg1"><?=$initial_message?></span>
		<span class="message2 navbar-message" id="navbar-msg2"></span>
  	</div>
  	<!-- Dropdown menu small screens -->
  	<div class="dropdown dropdown-horizontal show border-end" id="dropdown-menu">
		<a href="#" class="icon" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-bars menu-bar"></i></a>
  		<div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
  			<a href="#" id="instructions" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-info-square"></i> <span>Instructions</span> </a>
<?php 
	if (!ALL_BOOKINGS_LOCKED) {
?>
			<a href="#" id="save" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-save"></i> <span>Save</span></a>
        	<a href="#" id="validate" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-check-circle"></i> <span>Validate</span></a>
<?php 
	}
?>
        	<a href="#" id="overview" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-check-circle"></i> <span>Overview</span></a>
<?php
	if (!ALL_BOOKINGS_LOCKED) {
?>
        	<a href="#" id="submit" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-upload"></i> <span>Submit</span></a>
<?php 
	}
?>
        	<a href="#" id="logout" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
		</div>
  	</div>
  </div>
</nav>
<div class="row flex-nowrap">
	<!-- Sidebar menu large screens -->
	<div class="col-auto px-0">
		<div id="sidebar" class="sidebar sticky-top collapse collapse-horizontal show border-end">
			<div class="sidebar-nav list-group border-0 rounded-0 text-sm-start min-vh-100">
				<div><h3>&nbsp;Menu</h3></div>
				<a href="#" id="instructions" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-info-square"></i> <span>Instructions</span> </a>
<?php 
	if (!ALL_BOOKINGS_LOCKED) {
?>
				<a href="#" id="save" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-save"></i> <span>Save</span></a>
				<a href="#" id="validate" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-check-circle"></i> <span>Validate</span></a>
<?php 
	}
?>
				<a href="#" id="overview" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-check-circle"></i> <span>Overview</span></a>
<?php 
	if (!ALL_BOOKINGS_LOCKED) {
?>
				<a href="#" id="submit" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-upload"></i> <span>Submit</span></a>
<?php 
	}
?>
				<a href="#" id="logout" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
			</div>
		</div>
	</div>
	<!-- Instructions -->
	<div id="canvas-instructions" class="instructions offcanvas offcanvas-end" tabindex="-1" data-bs-scroll="true" aria-labelledby="offcanvasRightLabel">
		<div class="offcanvas-header">
			<h5 id="offcanvasRightLabel">Instructions</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="instructions-canvas-body">
		  <?php include 'booking_instructions.php'; ?>
		</div>
		<div class="instructions-canvas-footer mt-3 mb-3">
			<center><button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas" aria-label="Close">Close</button></center>
		</div>
	</div>
	<!-- booking overview id="canvas-booking_overview" id="offcanvasBottom"-->
	<div id="canvas-booking_overview" class="overview offcanvas offcanvas-bottom" tabindex="-1" aria-labelledby="offcanvasBottomLabel">
  		<div class="offcanvas-header">
    		<h3 class="offcanvas-title" id="offcanvasBottomLabel">Booking overview for <?= PM_TITLE ?></h3>
    		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  		</div>
  		<div id="booking_overview" class="overview offcanvas-body">
			Loading...
  		</div>
		<div class="instructions-canvas-footer mt-3 mb-3">
			<center><button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas" aria-label="Close">Close</button></center>
		</div>
	</div>
	<!-- page main contents section -->
	<main class="main-contents col ps-md-2 pt-2">
	  	<div class="container-fluid">
		  <?php echo page_header(PAGE_TITLE); ?>
		  <div id="main">
		  	<div id="main-start" class="container">
 <?php 
	if (!ALL_BOOKINGS_LOCKED) {
?>
				<p><em>Before you start please read the instructions carefully by clicking on the <strong id="instructions" class="menu-item">Instructions</strong> menu.</em>
				Also note the system will log you out after 25 minutes of inactivity, so do save your booking.</p>
<?php 
	} else {
?>
				<p><em>It is no longer possible to change your booking detials</em>.</p>
<?php 
	}
?>
			</div>
			<p>&nbsp;</p>
			<div id="main-form" class="container">
				<form id="bookingform" autocomplete="off" accept-charset="utf-8" class="align-items-center requires-validation" novalidate>
					<div id="booking_data">
						<!-- Details loaded by booking_load.php -->
						Loading.....
					</div>
					<div id="comments_section" class="form-section-primary mt-3">
						<h3><center>Comments and special wishes</center></h3>
						<textarea id="<?php echo COMMENT_PREFIX ?>" class="col-12" name="<?php echo COMMENT_PREFIX ?>" rows="5"><?=htmlspecialchars($booking->getComments())?></textarea>
					</div>
					<input type="hidden" id="booking_id" name="booking_id" value="<?=$booking_id?>" />
					<input type="hidden" id="booking_submitted_count" name="booking_submitted_count" value="<?=$booking->getSubmittedCount()?>" />
					<input type="hidden" id="booking_ref" name="booking_ref" value="<?=$booking->getReference()?>" />
					<input type="hidden" id="submit_email" name="submit_email" value="<?=$user->email()?>" />
					<input type="hidden" id="maximum_delegates" name="maximum[<?php echo DELEGATE_PREFIX ?>]" value="<?=$maximum_delegates?>" />
					<input type="hidden" id="maximum_guests" name="maximum[<?php echo GUEST_PREFIX ?>]" value="<?=$maximum_guests?>" />
					<input type="hidden" id="maximum_rooms" name="maximum[<?php echo ROOM_PREFIX ?>]" value="<?=$maximum_rooms?>" />
					<input type="hidden" id="maximum_travels" name="maximum[<?php echo TRAVEL_PREFIX ?>]" value="<?=$maximum_travels?>" />
					<!-- Must have a removed for each booking item -->
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.CONTACT_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.CONTACT_PREFIX ?>]" value="" />
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.DELEGATE_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.DELEGATE_PREFIX ?>]" value="" />
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.GUEST_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.GUEST_PREFIX ?>]" value="" />
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.ROOM_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.ROOM_PREFIX ?>]" value="" />
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.TRAVEL_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.TRAVEL_PREFIX ?>]" value="" />
					<input type="hidden" id="<?php echo REMOVED_PREFIX.'_'.PAYMENT_PREFIX ?>" name="<?php echo REMOVED_PREFIX.'['.PAYMENT_PREFIX ?>]" value="" />
				</form>
			</div> <!-- id main-form -->
			<p>&nbsp;</p>
			<div id="main-footer" class="container overflow-hidden">
				<div class="row gy-5">
					<div class="d-grid col-sm-4 col-12">
						<button id="validate" class="menu-item btn btn-outline-primary">Validate</button>
					</div>
					<div class="d-grid col-sm-4 col-12">
						<button id="save" class="menu-item btn btn-outline-primary">Save</button>
					</div>
					<div class="d-grid col-sm-4 col-12">
						<button id="submit" class="menu-item btn btn-primary">Submit</button>
					</div>
				</div>
				<p>&nbsp;</p>
			</div> <!-- id main-footer -->
		</div> <!-- id main -->
	  </div> <!-- class container-fluid -->
	</main>
  </div> <!-- id content -->
</div> <!-- class container -->
<!-- List to hold names for autocompleteGuestNames -->
</div> <!-- viewport -->
<datalist id="listGuestNames">
    <option value="Enter delegate or guest name">
</datalist>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>
<script>
	// Transfer some constants from PHP to js.
	const SITE_NAME = "<?php echo SITE_NAME ?>";
	const CONST_MAX_DELEGATES = "<?php echo $maximum_delegates ?>";
	const CONST_MAX_GUESTS = "<?php echo $maximum_guests ?>";
	const CONST_MAX_ROOMS = "<?php echo $maximum_rooms ?>";
	const CONST_MAX_TRAVELS = "<?php echo $maximum_travels ?>";
	const OPTIONS_ARR_DATES = <?php echo json_encode(OPTIONS_ARR_DATES); ?>;
	const OPTIONS_DEP_DATES = <?php echo json_encode(OPTIONS_DEP_DATES); ?>;
	const OPTION_CC = <?php echo json_encode(array_keys(OPTIONS_COUNTRIES)); ?>;
	const DB_FIELDS = <?php echo json_encode(DB_FIELDS); ?>;
	const MIN_MAX_ITEMS = <?php 
		$min_max = array(CONTACT_PREFIX=>array(1,1), DELEGATE_PREFIX=>array(1,$maximum_delegates), GUEST_PREFIX=>array(0,$maximum_guests), ROOM_PREFIX=>array(0, $maximum_rooms), TRAVEL_PREFIX=>array(0,$maximum_travels));
		echo json_encode($min_max); 
	?>;
	const BOOKING_PREFIX = "<?php echo BOOKING_PREFIX ?>";
	const CONTACT_PREFIX = "<?php echo CONTACT_PREFIX ?>";
	const DELEGATE_PREFIX = "<?php echo DELEGATE_PREFIX ?>";
	const GUEST_PREFIX = "<?php echo GUEST_PREFIX ?>";
	const ROOM_PREFIX = "<?php echo ROOM_PREFIX ?>";
	const TRAVEL_PREFIX = "<?php echo TRAVEL_PREFIX ?>";
	const COMMENT_PREFIX = "<?php echo COMMENT_PREFIX ?>";
	const PAYMENT_PREFIX = "<?php echo PAYMENT_PREFIX ?>";
	const REMOVED_PREFIX = "<?php echo REMOVED_PREFIX ?>";
	const USE_CLUBS_TABLE = <?php echo (USE_CLUBS_TABLE) ? 1 : 0; ?>;
	const OTHER_CLUB_NAME = "<?php echo OTHER_CLUB_NAME ?>";
	var booking_submitted_count = <?php echo $booking->getSubmittedCount()?>;
	var booking_locked = <?php echo ($booking->getLocked()) ? 1 : 0; ?>;
	var booking_complete_locked = <?php echo (ALL_BOOKINGS_LOCKED) ? 1 : 0; ?>;
	var booking_new = <?php echo ($booking_new) ? 1 : 0; ?>;
</script>
<script src="booking.1.2.js"></script>
</body>
</html>