<?php
// Process form data from booking.php, store in database adn submit if needed.
require_once 'config.php';
require_once 'Input.php';
require_once 'Redirect.php';
require_once 'User.php';
require_once 'BookingClass.php';
require_once 'email_functions.php';

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
if (!$booking->loadDBData(user_id:$user->id(), full_load: true)) {
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
$booking->travels->setShow($booking->rooms->getNumItems() > 0);
$booking->setSubmitedCount(Input::get('booking_submitted_count'));

// Save and submit booking data
$booking->saveData(Input::get(REMOVED_PREFIX), true);

// Calculate and get charges details email body
$charges_body = $booking->getChargesOverview(true);
if ($charges_body == 'ERROR') {
    postReturn('Could not generate charges overview.', 'error');
    exit;
}
$overview_body = $booking->getDetailsOverview(true);
if ($overview_body == 'ERROR') {
    postReturn('Could not generate details overview.', 'error');
    exit;
}
$styles = get_html_email_styles(true);

// Create and send the confirmation email.
$body  =  '<p>Thank you for submitting your club booking for the '.PM_TITLE.'. Please read this email carefully.</p>';
$body .=  '<p>Start with checking all your booking details as listed below. You can make changes at '.SITE_NAME.' and submit again until '.LAST_CHANGE_DATE.'. After this date no more changes are possible.</p>';
$body .=  '<p>When you are happy with your booking please make your payment(s) to:<br>';
$body .=  '&nbsp;&nbsp;&nbsp;IBAN: '.IBAN_BANK_ACCOUNT.'<br>';
$body .=  '&nbsp;&nbsp;&nbsp;BIC Code: '.IBAN_BANK_BIC.'<br>';
$body .=  '&nbsp;&nbsp;&nbsp;Account holder: '.IBAN_ACCOUNT_NAME.'<br>';
if (PAYPAL_ID != null) {
    $body .=  '<p>When not in the Euro zone you can use the PayPal friend and family option to make the payment on:<br>';
    $body .=  '&nbsp;&nbsp;&nbsp;PayPal email ID: '.PAYPAL_ID.'<br>';
}
$body .=  '<p>Put the following in your payment description: <strong>"'.SITE_TITLE_SHORT.' '.$booking->contacts->getClubName().', booking ref '.$booking->getReference().'"</strong>.</p>';
$body .=  '<p>The total amount to pay for your booking is '.format_charge($booking->getBookingTotalCharges()).'. The details can be found below. If we received payment(s) earlier you will find them below too. Please allow a few days for us to process your payment.</p>';
$body .=  '<p><strong>ALL PAYMENTS MUST BE RECEIVED BEFORE '.PAYMENT_DATE.'</strong>!!</p>';
$body .=  '<p>Additional money transfer-costs will need to be paid by your club.</p>';
$body .=  '<p>Please note: If you entered any chargeable requests in the Comments, e.g. extra t-Shirts, transfers, etc., hold your payment until we reached out to you to update your booking.</p>';
$body .=  '<p>See you at '.PM_LOCATION.', '.PM_LOCATION_ADDRESS.' for a memorable FH-DCE Presidents&rsquo; meeting.</p>';
$body .=  '<p>Kind regards,';
$body .=  '<p>'.PM_ORGANIZER.'</p>';

// Details header to make it look like an invoice.
$body .= '<h1 '.$styles['h1'].'>Booking details for Booking reference: '.$booking->getReference().'</h1>';

// Add Booking details.
$body .=  '<h2 style="'.$styles['h2'].'">Booking Details</h2>';
$body .=  $overview_body;
// We first specify payment details
$body .=  $charges_body;
$body .=  '<br>';
// Send the email.
$subject = 'Booking confirmation for the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$booking->getReference();
[$mail_res, $mail_msg] = send_email($subject, $booking->contacts->getFirstName(), $body, Input::get('submit_email'), $booking->contacts->getEmail(), SITE_EMAIL);
if ($mail_res) {
  // Report success.
  postReturn('Your booking is submitted. Check your inbox for the confirmation email.<br>Make sure to check you spam folder as well!<p>See you at the Presidents&rsquo; meeting 2025!</p>');
} else {
  postReturn('Your booking is submitted. Howeer sending the confimation email failed!<br>Check your email address and try to submit again.</p>Mailer error : '.$mail_msg);
}  
exit;

?>
