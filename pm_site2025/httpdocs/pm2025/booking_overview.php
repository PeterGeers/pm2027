<?php
/*
Generate the html for the booking overview based on the submitted data.
*/
require_once 'config.php';
require_once 'Input.php';
require_once 'User.php';
require_once 'BookingClass.php';

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive()) {
	// We do not have the user_id in the session, redirect to logon page and exit
    postReturn('Session expired. Logon again.', 'error', true);
	exit;
}
if(!Input::exists()) {
    // We must have form data else exit.
    postReturn('Unknown error (no form data).', 'error');
    exit;
}
$booking = new Booking();
if (Input::get('isAdminReq') != '1') {
	// This is a form post, get data from POST. First make sure we know this booking.
	if (!$booking->loadDBData(user_id:$user->id(), full_load:true)) {
		postReturn('Could not find booking for user.', 'error');
		exit;
	}	
	$booking->loadFormData(Input::get(CONTACT_PREFIX), Input::get(DELEGATE_PREFIX), Input::get(GUEST_PREFIX), Input::get(ROOM_PREFIX), Input::get(TRAVEL_PREFIX), Input::get(COMMENT_PREFIX));
} else {
	// This is a admin request, get data from database.
	if (!$user->isAdmin()) {
		postReturn('Not authorized (notadmin user).', 'error');
		exit;
	}
	$booking_id = Input::get('booking_id');
	if ($booking_id == '') {
		postReturn('No data (booking_id)', 'error');
		exit;
	}
	if (!$booking->loadDBData(booking_id:$booking_id, full_load:true)) {
		// We must have a booking
		postReturn('Could not find booking with ID '.$booking_id.'.', 'error');
		exit;
	}	
}

$booking->travels->setShow($booking->rooms->getNumItems() > 0);
// Generate booking overview html
$overview_body = $booking->getDetailsOverview();
if ($overview_body == 'ERROR') {
	postReturn('Could not generate details overview. Please retry later.', 'error');
    exit;
}
// Calculate and get charges details html
$charges_body = $booking->getChargesOverview();
if ($charges_body == 'ERROR') {
	postReturn('Could not generate charges overview. Please retry later.', 'error');
    exit;
}

$html  = '<h4>Booking reference : <span id="overview_booking_ref">'.$booking->getReference().'</span></h4>';
$html .= '<div class="details container">';
// Add body of overview.
$html .= $overview_body;
// Add body of charges.
$html .= $charges_body;
$html .= '</div>';
postReturn($html);

?>
