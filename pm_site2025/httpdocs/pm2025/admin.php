<!DOCTYPE html>
<?php
require_once 'config.php';
require_once 'Redirect.php';
require_once 'User.php';
require_once 'BookingClass.php';

const PAGE_TITLE = 'Admin';

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive() or !$user->isAdmin()) {
	// We do not have the user_id in the session, redirect to logon page
//	echo 'Could not find active user, redirect to logon.';
	Redirect::to(SITE_LOGON_PAGE);
	exit;
} 
?>
<html lang="en">
<head>
	<meta http-equiv="Page-Enter" content="blendTrans(Duration=1.0)">
	<meta http-equiv="content-type" content="text/html;charset=UTF-8">
	<meta http-equiv="Content-Language" content="en">
	<meta http-equiv="pragma" content="cache">
	<meta http-equiv="imagetoolbar" content="no">
	<meta charset="UTF-8">
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
		<span><img class="pm-logo-navbar" src="<?= PM_LOGO ?>"></span>
		<span class="navbar-ref"></span>
		<span class="navbar-message" id="navbar-msg1"></span>
		<span class="message2 navbar-message" id="navbar-msg2"></span>
  	</div>
  	<!-- Dropdown menu small screens -->
  	<div class="dropdown dropdown-horizontal show border-end" id="dropdown-menu">
		<a href="#" class="icon" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-bars menu-bar"></i></a>
  		<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
          <a href="#" id="instructions" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-info-square"></i> <span>Instructions</span> </a>
          <a href="#" id="overview" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person-rolodex"></i> <span>Overview</span></a>
          <a href="#" id="users" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-list-ul"></i> <span></span>Users</a>
          <a href="#" id="booking" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-bag-check"></i> <span>Bookings</span></a>
        	<a href="#" id="contact" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person"></i> <span>Club &amp; Contact</span></a>
        	<a href="#" id="delegate" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person"></i> <span>Delegates</span></a>
        	<a href="#" id="guest" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-incognito"></i> <span>Guests</span></a>
        	<a href="#" id="room" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-building"></i> <span>Rooms</span></a>
        	<a href="#" id="travel" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-bus-front"></i> <span>Travels</span></a>
        	<a href="#" id="extra" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-plus"></i> <span>Extras</span></a>
        	<a href="#" id="payment" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-credit-card"></i> <span>Payments</span></a>
        	<a href="#" id="hotel_rooms" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-building"></i> <span>Hotel Rooms</span></a>
        	<a href="#" id="download_sub" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-download"></i> <span>Download Submitted</span></a>
        	<a href="#" id="download_sav" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-download"></i> <span>Download Saved</span></a>
        	<a href="#" id="logout" class="menu-item dropdown-item list-group-item border-end-0 text-truncate" data-bs-parent="#sidebar"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
  		</ul>
  	</div>
  </div>
</nav>
<div class="row flex-nowrap">
	<!-- Sidebar menu large screens -->
	<div class="col-auto px-0">
		<div id="sidebar" class="sidebar sticky-top collapse collapse-horizontal show border-end">
			<div class="sidebar-admin-nav list-group border-0 rounded-0 text-sm-start min-vh-100">
				<div><h3>&nbsp;Menu</h3></div>
				<a href="#" id="instructions" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-info-square"></i> <span>Instructions</span> </a>
				<a href="#" id="overview" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-list-ul"></i> <span>Overview</span></a>
				<a href="#" id="users" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person-rolodex"></i> <span>Users</span></a>
				<a href="#" id="booking" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-bag-check"></i> <span>Bookings</span></a>
				<a href="#" id="contact" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person"></i> <span>Club &amp; Contact</span></a>
				<a href="#" id="delegate" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-person"></i> <span>Delegates</span></a>
				<a href="#" id="guest" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-incognito"></i> <span>Guests</span></a>
				<a href="#" id="room" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-building"></i> <span>Rooms</span></a>
				<a href="#" id="travel" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-bus-front"></i> <span>Travels</span></a>
				<a href="#" id="extra" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-plus"></i> <span>Extras</span></a>
				<a href="#" id="payment" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-credit-card"></i> <span>Payments</span></a>
				<a href="#" id="hotel_rooms" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-building"></i> <span>Hotel Rooms</span></a>
				<a href="#" id="download_sub" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-download"></i> <span>Download Submitted</span></a>
				<a href="#" id="download_sav" class="menu-item sidebar-item list-group-item border-end-0 d-inline-block text-truncate" data-bs-parent="#sidebar"><i class="bi bi-download"></i> <span>Download Saved</span></a>
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
		  <?php include 'admin_instructions.php'; ?>
		</div>
		<div class="instructions-canvas-footer mt-3 mb-3">
			<center><button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas" aria-label="Close">Close</button></center>
		</div>
	</div>
	<!-- booking overview id="offcanvasBottom" -->
	<div id="canvas-booking_overview" class="overview offcanvas offcanvas-bottom" tabindex="-1"  aria-labelledby="offcanvasBottomLabel">
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
	<main class="col ps-md-2 pt-2">
	  	<div class="container-fluid">
		  <?php echo page_header(PAGE_TITLE); ?>
		  <div id="main">
			<div id="main-body" class="container">
				<p><em>This is the site admin page, select one of the menu options to continue.</em></p>
			</div> <!-- id main-body -->
			<p>&nbsp;</p>
			<div id="main-footer" class="container overflow-hidden">
			</div> <!-- id main-footer -->
		</div> <!-- id main -->
	  </div> <!-- class container-fluid -->
	</main>
  </div> <!-- id content -->
</div> <!-- class container -->
<!-- List to hold names for autocompleteGuestNames -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>
<script>
	// Transfer some constants from PHP to js.
	const SITE_NAME = "<?php echo SITE_NAME ?>";
	const DB_FIELDS = <?php echo json_encode(DB_FIELDS); ?>;
	const BOOKING_PREFIX = "<?php echo BOOKING_PREFIX ?>";
	const CONTACT_PREFIX = "<?php echo CONTACT_PREFIX ?>";
	const DELEGATE_PREFIX = "<?php echo DELEGATE_PREFIX ?>";
	const GUEST_PREFIX = "<?php echo GUEST_PREFIX ?>";
	const ROOM_PREFIX = "<?php echo ROOM_PREFIX ?>";
	const TRAVEL_PREFIX = "<?php echo TRAVEL_PREFIX ?>";
	const COMMENT_PREFIX = "<?php echo COMMENT_PREFIX ?>";
	const PAYMENT_PREFIX = "<?php echo PAYMENT_PREFIX ?>";
	const USERS_PREFIX = "<?php echo USERS_PREFIX ?>";
	const EXTRA_PREFIX = "<?php echo EXTRA_PREFIX ?>";
	const OVERVIEW_PREFIX = "<?php echo OVERVIEW_PREFIX ?>";
	const HOTEL_ROOMS_PREFIX = "<?php echo HOTEL_ROOMS_PREFIX ?>";
	const FHDCE_CLUBS_PREFIX = "<?php echo FHDCE_CLUBS_PREFIX ?>";
</script>
<script src="admin.1.0.js"></script>
</body>
</html>