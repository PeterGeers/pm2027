<?php
require_once 'config.php';
require_once 'Session.php';
require_once 'User.php';
require_once 'BookingClass.php';

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive()) {
	// We do not have the user_id in the session, redirect to logon page
    postReturn('Session expired. Logon again.', 'error', true);
	exit;
}
// See if we have a booking
$booking = new Booking();
if (!$booking->loadDBData(user_id:$user->id(), full_load:true)) {
    postReturn('No booking found for user.', 'error');
	exit;
}
// See if we should show the travels section
if ($booking->travels->getEnabled()) {
	$booking->travels->setShow($booking->rooms->getNumItems() > 0);
}

// echo detail sections
postReturn($booking->getItemsFormHTML());

?>	
