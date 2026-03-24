<?php
use Shuchkin\SimpleXLSXGen;

require_once 'config.php';
require_once 'Redirect.php';
require_once 'Input.php';
require_once 'User.php';
require_once 'BookingClass.php';
require_once 'SimpleXLSXGen.php';

$total_booking_charges = 0;
$total_sub_booking_charges = 0;

$user = new User();
if(!$user->isLoggedIn() OR !$user->isActive() OR !$user->isAdmin()) {
	// We do not have the user_id in the session, redirect to logon page
    postReturn('Session expired. Logon again.', 'error', true);
	exit;
}
if(!Input::exists()) {
    postReturn('Unknown error (no form data).', 'error');
	exit;
}
$booking = new Booking();
$prefix = Input::get('prefix');
$clubs = $booking->getContactClubNames();

// generate html
$html  = '<div id="admin_data" class="form-section-primary mt-3 tabel-responsive">';
$html .= '<input type="hidden" name="data_type" id="data_type" value="'.$prefix.'" />';
switch ($prefix) {
	case USERS_PREFIX:
		$columns = array('id','name', 'email', 'active', 'joined', 'last_active', 'admin');
		$table_header = '';
		$chk_all = '<input type="checkbox" class="all" id="select_all" value="all" />';
		foreach($columns as $column) {
			$table_header .= '<th>'.$chk_all.(($column == 'id') ? ucfirst($prefix).' ': '').ucfirst(str_replace('_', ' ', $column)).'</th>';
			$chk_all = '';
		}
		$all_users = $user->getAllUsers($columns);
		if (!$all_users) {
			$html .= '<h3>Could not find data</h3></div>';
			exit;
		}
		$html .= '<h3 class="section_header">'.ucfirst($prefix).' Details</h3>';
		$html .= '<table class="table table-striped table-sm">';
		$html .=  '<thead class="thead-dark">';
		$html .=    "<tr>$table_header</tr>";
		$html .=   '</thead>';
		$html .=  '<tbody>';
		foreach ($all_users as $idx => $item) {
			$itm = get_object_vars($item);
			$html .= '<tr>';
			foreach($columns as $column) {
				$html .= ' <td>';
				if ($column == 'id') {
					$html .= '<input type="checkbox" class="item" name="select_id'.$item->id.'" value="'.$item->id.'" /> ';
				}
				$html .= $itm[$column].'</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		// For users add extra funciton buttons
		$html .= '<p>You can send a reminder email to inactive users you selected to activate their user ID.<br>';
		$html .= 'To remove a user, select the user, and click the Remove button.</p>';
		$html .= '<div id="main-input" class="container overflow-hidden">';
		$html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
		$html .=   '<input type="hidden" name="prefix" id="prefix" value="'.$prefix.'" />';
		$html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
		$html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
		$html .=  '</form>';
		$html .=  '<div class="row gy-5 mt-3">';
		$html .=   '<div class="d-grid col-sm-4 col-12 center">';
		$html .=    '<button id="btn_mail_inactive" class="menu-item btn btn-primary btn-inputFormMail">Send Email</button>';
		$html .=   '</div>';
		$html .=   '<div class="d-grid col-sm-4 col-12 center">';
		$html .=    '<button id="btn_remove" class="menu-item btn btn-primary btn-inputFormRemove">Remove</button>';
		$html .=   '</div>';
		$html .=  '</div>';
		$html .=  '<p>&nbsp;</p>';
		$html .= '</div>';
		break;
	case OVERVIEW_PREFIX:
		// Calculate total charges.
		$booking_data = $booking->collectOverviewDetails();
		$html .= '<h3 class="section_header">All bookings summary overview</h3>';
		$html .= '<table class="table table-striped table-sm">';
		$html .=  '<thead class="thead-dark">';
		$html .=    "<tr><th>Item</th><th>Total</th><th>Total of submitted</th></tr>";
		$html .=   '</thead>';
		$html .=  '<tbody>';
		$html .= '<tr><td>'.$booking_data['total_charges'][0].'</td><td>'.format_charge($booking_data['total_charges'][1]).'</td><td>'.format_charge($booking_data['total_charges'][2]).'</td></tr>';
		$html .= '<tr><td>'.$booking_data[USERS_PREFIX][0].'</td><td>'.$booking_data[USERS_PREFIX][1].'</td><td></td></tr>';
		$html .= '<tr><td>'.$booking_data[USERS_PREFIX.'act'][0].'</td><td>'.$booking_data[USERS_PREFIX.'act'][1].'</td><td></td></tr>';
		$html .= '<tr><td>'.$booking_data[BOOKING_PREFIX][0].'</td><td>'.$booking_data[BOOKING_PREFIX][1].'</td><td>'.$booking_data[BOOKING_PREFIX][2].'</td></tr>';
		$html .= '<tr><td>'.$booking_data[DELEGATE_PREFIX][0].'</td><td>'. $booking_data[DELEGATE_PREFIX][1].'</td><td>'.$booking_data[DELEGATE_PREFIX][2].'</td></tr>';
		$html .= '<tr><td>'.$booking_data[GUEST_PREFIX][0].'</td><td>'.$booking_data[GUEST_PREFIX][1].'</td><td>'.$booking_data[GUEST_PREFIX][2].'</td></tr>';
		$html .= '<tr><td>'.$booking_data[PARTY_PREFIX][0].'</td><td>'.$booking_data[PARTY_PREFIX][1].'</td><td>'.$booking_data[PARTY_PREFIX][2].'</td></tr>';
		$html .= '<tr><td>'.$booking_data[GUEST_PREFIX.'ct'][0].'</td><td>'.$booking_data[GUEST_PREFIX.'ct'][1].'</td><td>'.$booking_data[GUEST_PREFIX.'ct'][2].'</td></tr>';
		if ($booking->rooms->getEnabled()) {
			foreach(OPTIONS_ROOM_TYPES as $id => $name) {
				$idx = ROOM_PREFIX.$id;
				$html .= '<tr><td>'.$booking_data[$idx][0].'</td><td>'.$booking_data[$idx][1].'</td><td>'.$booking_data[$idx][2].'</td></tr>';
			}
		}
		if ($booking->travels->getEnabled()) {
			$html .= '<tr><td>'.$booking_data[TRAVEL_PREFIX.'arr'][0].'</td><td>'.$booking_data[TRAVEL_PREFIX.'arr'][1].'</td><td>'.$booking_data[TRAVEL_PREFIX.'arr'][2].'</td></tr>';
			$html .= '<tr><td>'.$booking_data[TRAVEL_PREFIX.'dep'][0].'</td><td>'.$booking_data[TRAVEL_PREFIX.'dep'][1].'</td><td>'.$booking_data[TRAVEL_PREFIX.'dep'][2].'</td></tr>';
			$html .= '<tr><td>'.$booking_data[TSHIRT_PREFIX.'tot'][0].'</td><td>'.$booking_data[TSHIRT_PREFIX.'tot'][1].'</td><td>'.$booking_data[TSHIRT_PREFIX.'tot'][2].'</td></tr>';
		}
		foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
			if ($id != 'No shirt') {
				$idx = TSHIRT_PREFIX.str_replace(' ', '', $id);
				$html .= '<tr><td>'.$booking_data[$idx][0].'</td><td>'.$booking_data[$idx][1].'</td><td>'.$booking_data[$idx][2].'</td></tr>';
			}
		}
		$html .= '</tbody></table><p />';
		break;
	case BOOKING_PREFIX:
		$html .= $booking->getAdminOverviewHTML(clubs: $clubs);
		break;
	case CONTACT_PREFIX:
		$html .= $booking->contacts->getAdminOverviewHTML();
		break;
	case DELEGATE_PREFIX:
		$html .= $booking->delegates->getAdminOverviewHTML(clubs: $clubs);
		break;
	case GUEST_PREFIX:
		$html .= $booking->guests->getAdminOverviewHTML(clubs: $clubs);
		break;
	case ROOM_PREFIX:
        // Special case for rooms
		$hotel_rooms = null;
		if (USE_ROOMS_TABLE) {
			$hotel_rooms = $booking->hotel_rooms->getHotelRoomNumbers();
		}
		if ($booking->rooms->getEnabled()) {
			$html .= $booking->rooms->getAdminOverviewHTML(2, $hotel_rooms,clubs: $clubs);
		} else {
			$html .= 'Not enabled';
		}
	break;
	case HOTEL_ROOMS_PREFIX:
		if ($booking->rooms->getEnabled()) {
			$html .= $booking->hotel_rooms->getAdminOverviewHTML(2,clubs: $clubs);
		} else {
			$html .= 'Not enabled';
		}
	break;
	case TRAVEL_PREFIX:
		if ($booking->travels->getEnabled()) {
			$html .= $booking->travels->getAdminOverviewHTML(clubs: $clubs);
		} else {
			$html .= 'Not enabled';
		}
		break;
	case PAYMENT_PREFIX:
		$html .= $booking->payments->getAdminOverviewHTML(1,$booking->getSubmittedIDs(),clubs: $clubs);
		break;
	case EXTRA_PREFIX:
		$html .= $booking->getAdminExtrasOverviewHTML(clubs: $clubs);
		break;
	default:
		postReturn('Unknown error (unknown prefix '.$prefix.')', 'error');
		exit;
}
$html .= '</div>';
postReturn($html);

?>	
