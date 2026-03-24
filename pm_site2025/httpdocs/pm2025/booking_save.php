<?php
// Process form data from booking.php, store in database.
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
if (!$booking->loadDBData(user_id:$user->id())) {
	// We must have a booking
    postReturn('No booking found for user.', 'error');
	exit;
}	

$booking_id = $booking->getBookingID();
$frm_booking_id = Input::get('booking_id');
if ($booking_id != $frm_booking_id) {
    postReturn('Booking refrence does not match (db '.$booking_id.' frm '.$frm_booking_id.')', 'error');
	exit;
}
// Get data from form POST
$booking->loadFormData(Input::get(CONTACT_PREFIX), Input::get(DELEGATE_PREFIX), Input::get(GUEST_PREFIX), Input::get(ROOM_PREFIX), Input::get(TRAVEL_PREFIX), Input::get(COMMENT_PREFIX));
// Save and submit booking data
$booking->saveData(Input::get(REMOVED_PREFIX));

postReturn('Your booking data is updated.');
?>
