<?php
// Shared functions for admin part.

require_once 'config.php';
require_once 'Charge.php';

// Get the ids of all submitted bookings
function get_submitted_booking_ids($db) {
    $ids = array();
    if ($db->query('SELECT id FROM '.BOOKING_PREFIX.SUBMITTED_POSTFIX)) {
        foreach($db->data() as $id => $item) {
            $ids[] = $item->id;
        }
    }
    return $ids;
}

// Get the ids of all bookings
function get_booking_ids($db) {
    $ids = array();
    if ($db->query('SELECT id FROM '.BOOKING_PREFIX)) {
        foreach($db->data() as $id => $item) {
            $ids[] = $item->id;
        }
    }
    return $ids;
}

// Collect summary data for booking, saved and submitted.
function collect_booking_overview_details($db, $submitted_only = null) {
    $result = array();
    // Count users data
    $result['num_'.USERS_PREFIX] = $db->count(USERS_PREFIX);
    $result['num_act_'.USERS_PREFIX] = $db->count(USERS_PREFIX ,array('active', '=', '1'));
    // Count up for saved data
    if ($submitted_only != 'Y') {
        $ids_all = get_booking_ids($db);
        if (count($ids_all) > 0) {
            // Count up data
            $result['num_'.BOOKING_PREFIX] = $db->count(BOOKING_PREFIX);
            $result['num_'.DELEGATE_PREFIX] = $db->count(DELEGATE_PREFIX);
            $result['num_'.GUEST_PREFIX] = $db->count(GUEST_PREFIX);
            $result['num_'.PARTY_PREFIX] = $db->count(DELEGATE_PREFIX,array('party', '=', 'Y')) + $db->count(GUEST_PREFIX,array('party', '=', 'Y'));
            foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                $result['num_'.$id.ROOM_PREFIX] = $db->count(ROOM_PREFIX,array('type', '=', $id));
            }
            $result['num_arr_'.TRAVEL_PREFIX] = $db->count(TRAVEL_PREFIX,array('arr_type', '=', 'PLANE'));
            $result['num_dep_'.TRAVEL_PREFIX] = $db->count(TRAVEL_PREFIX,array('dep_type', '=', 'PLANE'));
            $result['num_tot_'.TSHIRT_PREFIX] = $db->count(DELEGATE_PREFIX,array('shirt_size', '!=', 'No shirt')) + $db->count(GUEST_PREFIX,array('shirt_size', '!=', 'No shirt'));
            foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                if ($id != 'No shirt') {
                    $result['num_'.str_replace(' ', '', $id).'_'.TSHIRT_PREFIX] = $db->count(DELEGATE_PREFIX,array('shirt_size', '=', $id)) + $db->count(GUEST_PREFIX,array('shirt_size', '=', $id));
                }
            }
            $result['total_charges'] = 0;
            foreach ($ids_all as $id) {
                $result['total_charges'] += calculate_booking_total_charge($db, $id);
            }
        } else {
            // No saved bookings.
            $result['num_'.BOOKING_PREFIX] = 0;
            $result['num_'.DELEGATE_PREFIX] = 0;
            $result['num_'.GUEST_PREFIX] = 0;
            $result['num_'.PARTY_PREFIX] = 0;
            foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                $result['num_'.$id.ROOM_PREFIX] =0;
            }
            $result['num_arr_'.TRAVEL_PREFIX] = 0;
            $result['num_dep_'.TRAVEL_PREFIX] = 0;
            $result['num_tot_'.TSHIRT_PREFIX] = 0;
            foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                if ($id != 'No shirt') {
                    $result['num_'.str_replace(' ', '', $id).'_'.TSHIRT_PREFIX] = 0;
                }
            }
            $result['total_charges'] = 0;
        }
    }
    // Count up for submitted data
    if ($submitted_only != 'N') {
        $ids_sub = get_submitted_booking_ids($db);
        if (count($ids_sub) > 0) {
            $result['num_sub_'.BOOKING_PREFIX] = $db->count(BOOKING_PREFIX.SUBMITTED_POSTFIX);
            $result['num_sub_'.DELEGATE_PREFIX] = $db->count(DELEGATE_PREFIX.SUBMITTED_POSTFIX);
            $result['num_sub_'.GUEST_PREFIX] = $db->count(GUEST_PREFIX.SUBMITTED_POSTFIX);
            $result['num_sub_'.PARTY_PREFIX] = $db->count(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('party', '=', 'Y')) + $db->count(GUEST_PREFIX.SUBMITTED_POSTFIX, array('party', '=', 'Y'));
            foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                $result['num_'.$id.'_sub_'.ROOM_PREFIX] = $db->count(ROOM_PREFIX.SUBMITTED_POSTFIX,array('type', '=', $id));
            }
            $result['num_arr_sub_'.TRAVEL_PREFIX] = $db->count(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('arr_type', '=', 'PLANE'));
            $result['num_dep_sub_'.TRAVEL_PREFIX] = $db->count(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('dep_type', '=', 'PLANE'));
            $result['num_tot_sub'.TSHIRT_PREFIX] = $db->count(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '!=', 'No shirt')) + $db->count(GUEST_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '!=', 'No shirt'));
            foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                if ($id != 'No shirt') {
                    $result['num_'.str_replace(' ', '', $id).'_sub_'.TSHIRT_PREFIX] = 
                        $db->count(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '=', $id)) +
                        $db->count(GUEST_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '=', $id));
                }
            }
            $result['total_sub_charges'] = 0;
            foreach ($ids_sub as $id) {
                $result['total_sub_charges'] += calculate_booking_total_charge($db, $id, 'Y');
            }
        } else {
            $result['num_sub_'.BOOKING_PREFIX] = 0;
            $result['num_sub_'.DELEGATE_PREFIX] = 0;
            $result['num_sub_'.GUEST_PREFIX] = 0;
            $result['num_sub_'.PARTY_PREFIX] = 0;
            foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                $result['num_'.$id.'_sub_'.ROOM_PREFIX] = 0;
            }
            $result['num_arr_sub_'.TRAVEL_PREFIX] = 0;
            $result['num_dep_sub_'.TRAVEL_PREFIX] = 0;
            $result['num_tot_sub'.TSHIRT_PREFIX] = 0;
            foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                if ($id != 'No shirt') {
                    $result['num_'.str_replace(' ', '', $id).'_sub_'.TSHIRT_PREFIX] = 0;
                }
            }
            $result['total_sub_charges'] = 0;
        }
    }
    return $result;
}

// Add up all charges for a booking. If submitted then use the submitted tables as reference.
function calculate_booking_total_charge($db, $booking_id, $submitted = null) {
    if ($submitted and $submitted != '') {
        if (!$db->find_submitted_booking_by_id($booking_id)) {
            // We must have a submitted booking
            return -1;
        }
        $booking = $db->data();

        // get data from submitted tables
	    if ($db->find_submitted_delegates($booking_id)) {
		    $delegates = $db->data();
	    } else {
		    $delegates = '';
	    }
	    if ($db->find_submitted_guests($booking_id)) {
		    $guests = $db->data();
	    } else {
		    $guests = '';
    	}
	    if ($db->find_submitted_rooms($booking_id)) {
		    $rooms = $db->data();
    	} else {
	    	$rooms = '';
        }
	    if ($rooms == '') {
		    $travels = '';
    	} else {
	    	if ($db->find_submitted_travels($booking_id)) {
		    	$travels = $db->data();
    		} else {
	    		$travels = '';
		    }
        }
        $extra = $booking->additional_costs;
    } else {
        if (!$db->find_booking_by_id($booking_id)) {
            // We must have a booking
            return -1;
        }	
        $booking = $db->data();
            // Get data from working tables
	    if ($db->find_delegates($booking_id)) {
		    $delegates = $db->data();
	    } else {
		    $delegates = '';
	    }
	    if ($db->find_guests($booking_id)) {
		    $guests = $db->data();
	    } else {
		    $guests = '';
    	}
	    if ($db->find_rooms($booking_id)) {
		    $rooms = $db->data();
    	} else {
	    	$rooms = '';
        }
	    if ($rooms == '') {
		    $travels = '';
    	} else {
	    	if ($db->find_travels($booking_id)) {
		    	$travels = $db->data();
    		} else {
	    		$travels = '';
		    }
        }
        $extra = $booking->additional_costs;
	}

    $meeting = new MeetingCharge($delegates);
    $party = new PartyCharge($delegates, $guests);
    $tshirt  = new TshirtCharge($delegates, $guests);
    $transfer = new TransferCharge($travels);
    $single_room = new SingleRoomCharge($rooms);
    $twin_room = new TwinRoomCharge($rooms);
    $tourist_tax = new TouristTaxCharge($rooms);
    $total_amount = $meeting->getTotal() + $party->getTotal() + $tshirt->getTotal() + $transfer->getTotal() + $single_room->getTotal() + $twin_room->getTotal() + $tourist_tax->getTotal() + $extra;
    return $total_amount;
}

// Add up all payments for a given booking.
function calculate_booking_payments($db, $booking_id) {
    if (!$db->find_payments($booking_id)) {
		// No payments founs
		return 0;
	}
    $total = 0;
    $payment = $db->data();
    foreach($payment as $id => $item) {
        $total += $item->amount;
    }
    return $total;
}


?>