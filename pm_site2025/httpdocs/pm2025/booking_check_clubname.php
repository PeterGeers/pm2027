<?php
// Process form data from booking.php, store in database adn submit if needed.
require_once 'config.php';
require_once 'Input.php';
require_once 'User.php';
require_once 'BookingClass.php';

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive()) {
    postReturn('Session expired. Logon again.', 'error', true);
	exit;
}
if(!Input::exists()) {
    postReturn('Unknown error (no form data).', 'error');
	exit;
}

$booking = new Booking();
$club_name = Input::get('club_name');
[$res, $contact, $cc, $club_id] = $booking->checkClubNameInuse(Input::get('booking_id'), $club_name);
if ($res) {
	// We are ok
    postReturn(array('name'=>$contact,'cc'=>$cc, 'id'=>$club_id));
} else {
	// We are not  to use club nameok
    postReturn('The Club name '.$club_name.' you selected is already used for another booking.<br>Please contact your club member <strong>'.$contact.'</strong> on that club\'s booking.', 'error');
}
?>
