<?php
require_once 'config.php';
require_once 'Session.php';
require_once 'Redirect.php';
require_once 'User.php';
require_once 'Input.php';
require_once 'BookingClass.php';
require_once 'email_functions.php';

$user = new User();

if(!$user->isLoggedIn() OR !$user->isActive() or !$user->isAdmin()) {
    postReturn('Session expired. Logon again.', 'error', true);
	exit;
} 
if(!Input::exists()) {
    postReturn('Unknown error (no form data).', 'error');
	exit;
}
$booking = new Booking();
$booking_id = Input::get('booking_id');
$prefix = Input::get('prefix');
$action = explode(',', Input::get('submit_action'));
$notify = Input::get('notify_contact');
$body = '';
$subject = '';
switch ($action[0]) {
case 'add':
	if ($prefix == PAYMENT_PREFIX) {
		if (!$booking->loadDBData($booking_id)) {
			postReturn('Booking '.$booking_id.' not found.', 'error');
			exit;
		}
		$date = Input::get('date');
		$description = Input::get('description');
		$amount = Input::get('amount');
		$booking->payments->saveAdminFormData($booking_id, $date, $description, $amount);
		$msg = 'Added '.$prefix.' to Booking '.$booking_id;
		if ($notify == '1') {
			$total_charge = $booking->totalCharges($booking_id, true);
			$total_paid = $booking->totalPayments($booking_id);
			$subject = 'Booking payment confirmation for the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$booking->getReference();
			$body =  '<p>Thank you for your booking payment for the '.PM_TITLE.'.</p>';
			$body .= '<p>We recorded your payment in the PM booking system. You can go in your booking at '.SITE_NAME.' and see your payment in the Booking Overview</p>';
			if ($total_charge == $total_paid) {
				$body .= '<p>You have payed your booking in full. Thank you for this. Please be carefull making any future changes to your booking.</p>';
			} elseif ($total_charge > $total_paid) {
				$body .= '<p>You have payed '.format_charge($total_paid).' of the total of '.format_charge($total_charge).' for your booking. ';
				$body .= 'Thank you for this. Please make sure you send the payment for the remaining '.format_charge($total_charge-$total_paid).' before '.PAYMENT_DATE.'.</p>';
			} else {
				// We have received more payments?
				$body .= '<p>You have payed '.format_charge($total_paid).' of the total of '.format_charge($total_charge).' for your booking. ';
				$body .= 'Thank you for this. However, it looks like you have overpaid. Please contact us at '.SITE_EMAIL.'</p>';
			}
		}
	} elseif ($prefix == ROOM_PREFIX) {
		$room_id = Input::get('selected_ids');
		$room_no = Input::get('room_no');
		if ($room_no != '' and $room_id != '') {
			$booking->rooms->storeRoomNumber($room_id, $room_no);
			$msg = 'Assigned room number to Room ID '.$room_id.'.';
		} else {
			$msg = 'Did nothing. Data not entered.';
		}
		$notify = '0';
	} elseif ($prefix == HOTEL_ROOMS_PREFIX) {
		$room_no = Input::get('room_no');
		if ($room_no != '') {
			$booking->hotel_rooms->storeRoom(Input::get('selected_ids'), $room_no, Input::get('type'), Input::get('location'),((Input::get('available') == 'Y') ? 1:0));
			$msg = 'Added hotel room number '.$room_no.'.';
		} else {
			$msg = 'Did nothing. Data not entered.';
		}
		$notify = '0';
	} elseif ($prefix == EXTRA_PREFIX) {
		if ($booking_id == '') {
			$booking_id = Input::get('selected_ids');
		}
		if (!$booking->loadDBData($booking_id)) {
			postReturn('Booking '.$booking_id.' not found.', 'error');
			exit;
		}
		$additional_descr = Input::get('additional_descr');
		$additional_costs = Input::get('additional_costs');
		if ($additional_descr != '' and $additional_costs != 0) {
			$booking->storeExtras($additional_descr, $additional_costs, $booking_id);
			if ($notify == '1') {
				$subject = 'Booking special request confirmation for the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$booking->getReference();
				$body  = '<p>On your special request we have added "'.$additional_descr.'" with a cost of '.format_charge($additional_costs).' to your booking.</p>';
				$body .= '<p>Please go into your booking at '.SITE_NAME.' and validate this addition by looking at the Booking Overview.</p>';
				$body .= '<p>If you agree with booking details please re-submit your booking and you will receive an updated confirmation email. ';
				$body .= 'Note that we will only include your special request when you submitted the booking again.</p>';
			}
			$msg = 'Added '.$prefix.' to Booking '.$booking_id;
		} else {
			$msg = 'Did nothing. Data not entered.';
		}
	}
	// Get contact data and send notification email
	if ($notify == '1') {
		[$name, $email, $email2] = $booking->getContactEmails($booking_id);
		if ($email != '') {
			[$mail_res, $mail_msg] = send_email($subject, $name, $body, $email, $email2);
      if ($mail_res) {
        $msg .= ', and email sent';
      } else {
        $msg .= ', and email send failed, error : '.$mail_msg;
      }
		}
	}
	postReturn($msg);
	break;
case 'toggle':
	$proc_msg = '';
	$errors_msg = '';
	if ($prefix == BOOKING_PREFIX) {
		$mails_send = 0;
		$mails_failed = 0;
		$ids = explode(',',Input::get('selected_ids'));
		if ($ids[0]) {
			$proc_msg = 'Processed ids: ';
			foreach($ids as $id) {
				// Toggel booking is_locked
				$t_booking = new Booking();
				if ($t_booking->loadDBData($id)) {
					$new_locked = !$t_booking->getLocked();
					$t_booking->setLocked($new_locked);
					$proc_msg .= $id.' ';
					if ($notify == '1') {
						$subject = 'Booking change optionsfor the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$t_booking->getReference();
						$body =  '<p>Thank you for your booking for the '.PM_TITLE.'.</p>';
						if ($new_locked) {
							$body .= '<p>Your booking is considered final and you can no longer make cost impacting changes. You can still change things like delegate and guest names, room occupants, or flight details and times.</p>';
							$body .= '<p>However, it is no longer possible to add or remove delegates, guests, rooms or travel options. Also, t-Shirt and party attendance cannot be changed.</p>';
							$body .= '<p>If, for some reason you must make changes contact us at '.SITE_EMAIL.'.</p>';
						} else {
							// We have received more payments?
							$body .= '<p>On your special request we have opened up your booking to make changes. Please make the required changes promptly and submit your updated booking.</p>';
						}
						// Get contact data and send notification email
						[$name, $email, $email2] = $t_booking->getContactEmails($id);
						if ($email != '') {
							[$mail_res, $mail_msg] = send_email($subject, $name, $body, $email, $email2);
              if ($mail_res) {
                $mails_send++;
              } else {
                $errors_msg .= '<br>Email send for '.$id.' failed. ';
              }  
						}
					}
				} else {
					$errors_msg .= '<br>Did not find booking '.$id.'. ';
				}
			}
		} else {
			postReturn('No items selected to remove.', 'error');
		}
		postReturn($proc_msg.$errors_msg.'<br>Number of emails send '.$mails_send.'.');
	} else {
		postReturn('Not a valid request.', 'error');
	}
	break;
case 'lock':
		$errors_msg = '';
		if ($prefix == BOOKING_PREFIX) {
			$mails_send = 0;
			// Get IDs of all bookings and lock them. Email is send with all on bcc.
			$ids = $booking->getSavedIDs();
			if (count($ids) > 0) {
				$email_list = array();
				foreach($ids as $id) {
					// Set booking is_locked and get contacts email
					$t_booking = new Booking();
					if ($t_booking->loadDBData($id)) {
						$t_booking->setLocked(true);
						[$name, $email, $email2] = $t_booking->getContactEmails($id);
						$email_list[] = $email;
						$mails_send++;
						if ($email2) {
							$email_list[] = $email2;
							$mails_send++;
						}
					} else {
						$errors_msg .= '<br>Did not find booking '.$id.'. ';
					}
				}
				if ($notify == '1' AND count($email_list) > 0) {
					$subject = 'Booking change options for the '.html_entity_decode(PM_TITLE).'.';
					$body =  '<p>Thank you for your booking for the '.PM_TITLE.'. The registrations are now closed.</p>';
					$body .= '<p>Your booking is considered final and you can no longer make cost impacting changes. You can still change things like delegate and guest names, room occupants, or flight details and times.</p>';
					$body .= '<p>However, it is no longer possible to add or remove delegates, guests, rooms or travel options. Also, t-Shirt and party attendance cannot be changed.</p>';
					$body .= '<p>If, for some reason you must make changes contact us at '.SITE_EMAIL.'.</p>';
					// Get contact data and send notification email
					[$mail_res, $mail_msg] = send_email($subject, 'Sir/Madam', $body, SITE_EMAIL, SITE_EMAIL, $email_list);
				}
			} else {
				postReturn('No bookings found to lock.', 'error');
			}
			postReturn('All bookings are now locked.'.$errors_msg.'<br>Number of contact emails on bcc '.$mails_send.'.');
		} else {
			postReturn('Not a valid request.', 'error');
		}
		break;
case 'remove':
	// Remove the selected items from DB
	$ids = explode(',',Input::get('selected_ids'));
	if ($ids[0]) {
		foreach($ids as $id) {
			switch ($prefix) {
			case PAYMENT_PREFIX:
				$booking->payments->deleteFromDB($id);
				break;
			case USERS_PREFIX:
				$user->remove($id);
				break;
			case BOOKING_PREFIX:
				$booking->deleteFromDB($id);
				break;
			case ROOM_PREFIX:
				$booking->rooms->deleteRoomNumber($id);
				break;
			case HOTEL_ROOMS_PREFIX:
				$booking->hotel_rooms->deleteRoom($id);
				break;
			case EXTRA_PREFIX:
				$booking->deleteExtras($id);
				break;
			}
		}
		postReturn('Removed '.count($ids).' '.$prefix.'s.');
	} else {
		postReturn('No items selected to remove.', 'error');
	}
	break;
case 'mail':
	$mails_send = 0;
	$proc_msg = '';
	$errors_msg = '';
	if ($prefix == BOOKING_PREFIX) {
		// Loop over the specified ids. 
		$ids = explode(',',Input::get('selected_ids'));
		$proc_msg = 'Processed ids: ';
		if ($ids[0]) {
			foreach($ids as $id) {
				if ($booking->loadDBData($id)) {
					[$name, $email, $email2] = $booking->getContactEmails($id);
					if($email) {
						switch ($action[1]) {
						case 'submit':
							$proc_msg .= $id.' ';
							$data = $booking->getData();
							$sub_dat = $booking->getSubmittedDate();
							$mod_dat = $booking->getModifiedDate();
							// Check for missing submitted date, or a modified date after submitted date
							if (!$sub_dat OR ($mod_dat AND $sub_dat < $mod_dat)) {
								$subject = 'Booking submission reminder for the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$booking->getReference();
								$body  = '<p>We have not yet received all details for you booking for the '.html_entity_decode(PM_TITLE).'.</p>';
								$body .= '<p>Please go into your booking at '.SITE_NAME.' to complete and submit your booking before '.LAST_CHANGE_DATE.'.</p>';
								$body .= '<p>You will not be able to attend the meeting failing to submit your booking before this date.</p>';
								[$mail_res, $mail_msg] = send_email($subject, $name, $body, $email, $email2);
                if ($mail_res) {
                  $mails_send++;
                } else {
                  $errors_msg .= '<br>Email send for '.$id.' failed. ';
                }  
							}
							break;
						case 'pay':
							$proc_msg .= $id.' ';
							// Check for open amount.
							$total_charge = $booking->totalCharges($id, true);
							$total_paid = $booking->totalPayments($id);
							if ($total_charge != $total_paid) {
								$subject = 'Booking payment reminder for the '.html_entity_decode(PM_TITLE).'. Booking ref #'.$booking->getReference();
								if ($total_paid == 0) {
									$body  = '<p>You have not yet paid for your booking. The total for your booking is '.format_charge($total_charge).'. ';
									$body .= '<p>You will not be able to attend the meeting failing to pay your booking before '.PAYMENT_DATE.'.</p>';
									$body .= '<p>Please see the email you received with your booking confirmation for the payment details and instructions.</p>';
									$body .= '<p>If your payment has crossed this email, we apologize and you can consider this email as not sent.</p>';
								} elseif ($total_charge > $total_paid) {
									$body  = '<p>You have paid '.format_charge($total_paid).' of the total of '.format_charge($total_charge).' for your booking. ';
									$body .= 'Please make sure you send the payment for the amount due of '.format_charge($total_charge-$total_paid).' before '.PAYMENT_DATE.'.</p>';
									$body .= '<p>Please see the email you received with your booking confirmation for the payment details and instructions.</p>';
									$body .= '<p>If your payment has crossed this email, we apologize and you can consider this email as not sent.</p>';
								} elseif ($total_charge < $total_paid) {
									$body  = '<p>You have paid '.format_charge($total_paid).' of the total of '.format_charge($total_charge).' for your booking. ';
									$body .= 'This is more than the amount you should have paid. Please contact us at '.SITE_EMAIL.' to resolve this.</p>';
								}
								[$mail_res, $mail_msg] = send_email($subject, $name, $body, $email, $email2);
                if ($mail_res) {
                  $mails_send++;
                } else {
                  $errors_msg .= '<br>Email send for '.$id.' failed. ';
                }  
							}
							break;
						}
					} else {
						$errors_msg .= '<br>No contact email for booking '.$id.'. ';
					}
				} else {
					$errors_msg .= '<br>Did not find booking '.$id.'. ';
				}
			}
		} else {
			postReturn('No bookings selected.', 'error');
		}
	} elseif ($prefix == USERS_PREFIX) {
		// loop over specified IDs and send email to not activated users.
		$ids = explode(',',Input::get('selected_ids'));
		if ($ids[0]) {
			$not_user = new User();
			foreach($ids as $id) {
				if ($not_user->find((int)$id)) {
					if (!$not_user->isActive()) {
						switch ($action[1]) {
						case 'activate':
							$subject = 'User activation reminder for the '.html_entity_decode(PM_TITLE);
							$body  = '<p>You created an account on the '.html_entity_decode(PM_TITLE).' web site on '.$not_user->joined().'.</p>';
							$body .= '<p>However, you never used this account for making a registration for the PM.</p>';
							$body .= '<p>You can still create a booking by looking up the email we have sent you on '.$not_user->joined().' and following the instructions.</p>';
							$body .= '<p>The user name you entered is "'.$not_user->name().'", in combination with the email address you recieved this email on.</p>';
							$body .= '<p>If you have lost your registration email, go to '.SITE_NAME.' and click the "Forgot password link".</p>';
							[$mail_res, $mail_msg] = send_email($subject, $not_user->name(), $body, $not_user->email());
              if ($mail_res) {
                $mails_send++;
              } else {
                $errors_msg .= '<br>Email send for '.$id.' failed. ';
              }  
							break;
						}
					} else {
						$errors_msg .= '<br>User is active '.$id.'. ';
					}
				} else {
					$errors_msg .= '<br>Did not find user '.$id.'. ';
				}
			}
		} else {
			postReturn('No users selected to email.', 'error');
		}
	}
	postReturn($proc_msg.$errors_msg.'<br>Number of emails send '.$mails_send.'.');
	break;
default:
	postReturn('Unknown action '.$action[0].'.', 'error');
	exit;
}
?>