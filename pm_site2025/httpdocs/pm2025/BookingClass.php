<?php
// Classes to handle booking items: Contacts, Delegates, Guests, Rooms, Travels, Payments, Extras
require_once 'config.php';
require_once 'Database.php';
require_once 'BookingItemsClass.php';
require_once 'Charge.php';
require_once 'booking_functions.php';

// Defines a booking
class Booking {
    private $_db, $_data, $numItems = 0, $total_amount = 0, $prefix = BOOKING_PREFIX, $booking_items, $locked;
    public $contacts, $delegate, $guest, $rooms, $travels; 
    public function __construct() {
        $this->locked = false; // Not working yet for overview and save/submit as the disabled field are not send over in the post.
        $this->_db = Database::getInstance();
        // Set classes
        $this->booking_items = array();
        $this->contacts = new Contacts(true);
        $this->delegates = new Delegates(true);
        $this->guests = new Guests(true);
        $this->rooms = new Rooms(true);
        $this->hotel_rooms = new HotelRooms(true);
        $this->travels = new Travels(true);
        $this->payments = new Payments(true);
        // Add all enabled classes to list to process
        if ($this->contacts->getEnabled()) { $this->booking_items[$this->contacts->getPrefix()] = $this->contacts; }
        if ($this->delegates->getEnabled()) { $this->booking_items[$this->delegates->getPrefix()] = $this->delegates; }
        if ($this->guests->getEnabled()) { $this->booking_items[$this->guests->getPrefix()] = $this->guests;}
        if ($this->rooms->getEnabled()) { $this->booking_items[$this->rooms->getPrefix()] = $this->rooms;}
        if ($this->travels->getEnabled()) { $this->booking_items[$this->travels->getPrefix()] = $this->travels;}
    }
    public function getLocked() {
        return ($this->_data->is_locked == 1);
    }
    public function setLocked($locked) {
        // Set in DB as this is only called from admin interface
		$id = $this->getBookingID();
        $this->_db->update($this->prefix, $id, array('is_locked'=>($locked) ? 1 : 0));
        if ($this->_db->count_in_db($this->prefix.SUBMITTED_POSTFIX, array('id', '=', $id))) {
            $this->_db->update($this->prefix.SUBMITTED_POSTFIX, $id, array('is_locked'=>($locked) ? 1 : 0));
        }
        $this->locked = $locked;
    }
    public function getData() {
        return $this->_data;
    }
    public function getBookingID() {
        if (empty($this->_data)) { return -1; }
        return $this->_data->id;
    }
    public function getUserID() {
        if (empty($this->_data)) { return -1; }
        return $this->_data->user_id;
    }
    public function getBookingTotalCharges() {
        return $this->total_amount;
    }
    // Get the maximums for the given items that can be overruled for a booking.
    public function getMinDelegates() {
        if (empty($this->_data)) { return MINIMUM_DELEGATES; }
        return $this->_data->min_delegates;
    }
    public function getMaxDelegates() {
        if (empty($this->_data)) { return MAXIMUM_DELEGATES; }
        $max = $this->_data->max_delegates;
        if ($max == 0) return MAXIMUM_DELEGATES;
        return $max;
    }
    public function getMaxGuests() {
        if (empty($this->_data)) { return MAXIMUM_GUESTS; }
        $max = $this->_data->max_guests;
        if ($max == 0) return MAXIMUM_GUESTS;
        return $max;
    }
    public function getMaxRooms() {
        if (empty($this->_data)) { return MAXIMUM_ROOMS; }
        $max = $this->_data->max_rooms;
        if ($max == 0) return MAXIMUM_ROOMS;
        return $max;
    }
    public function getMaxTravels() {
        if (empty($this->_data)) { return MAXIMUM_TRAVELS; }
        $max = $this->_data->max_travels;
        if ($max == 0) return MAXIMUM_TRAVELS;
        return $max;
    }
    // Get/Add/Update/Remove optionaly extra charges.
    public function getExtras() {
        // Get for this booking from retrieved data
        if (!empty($this->_data) AND $this->_data->additional_costs != 0) {
            return [$this->_data->additional_descr, $this->_data->additional_costs];
        } else {
            return ['', 0];
        }
    }
    // Update extras. Also update modified date and bump submited_count as user should resubmit as requested.
    public function storeExtras($descr, $costs, $id = null) {
        if ($id == null) { $id = $this->getBookingID(); }
        $sc = $this->getSubmitedCount()+1;
        $this->setSubmitedCount($sc);
        $this->_db->update($this->prefix, $id, array('additional_descr'=>$descr, 'additional_costs'=>$costs, 'submitted_date' => date('Y-m-d H:i:s'), 'submitted_count'=>$sc));
    }
    public function deleteExtras($id = null) {
        $this->storeExtras('', 0, $id);
        // Also remove from submitted as we do not ask user to resubmit.
        if ($this->_db->count_in_db($this->prefix.SUBMITTED_POSTFIX, array('id', '=', $id))) {
            $this->_db->update($this->prefix.SUBMITTED_POSTFIX, $id, array('additional_descr'=>'', 'additional_costs'=>0));
        }
    }

    // Get optionaly comments.
    public function getComments() {
        if (empty($this->_data)) { return ''; }
        return $this->_data->comments;
    }
    // Get/set submitted_count in memory.
    public function setSubmitedCount($count) {
        if (empty($this->_data)) { return 0; }
        $this->_data->submitted_count = $count;
    }
    public function getSubmitedCount() {
        if (empty($this->_data)) { return 0; }
        return $this->_data->submitted_count;
    }
    // Get the formatted booking reference.
    public function getReference($id = null, $cnt = null) {
        if ($id == null OR $cnt == null) {
            if (empty($this->_data)) { return 'UNKNOWN'; }
            $id = $this->_data->id;
            $cnt = $this->_data->submitted_count;
        }
        return number_format($id) . '-' . number_format($cnt);
    }

    // Get the posted data from the form and store in booking items
    public function loadFormData($contacts, $delegates, $guests, $rooms, $travels, $comments) {
        $this->comments = $comments;
        if ($this->contacts->getEnabled()) {$this->contacts->loadFormData($contacts);}
        if ($this->delegates->getEnabled()) {$this->delegates->loadFormData($delegates);}
        if ($this->guests->getEnabled()) {$this->guests->loadFormData($guests);}
        if ($this->rooms->getEnabled()) {$this->rooms->loadFormData($rooms);}
        if ($this->travels->getEnabled()) {$this->travels->loadFormData($travels);}
    }
    // Get data from database
    public function loadDBData($booking_id = null, $user_id = null, $full_load = false, $submitted = false) {
        if (isset($booking_id)) { 
            $where = array('id', '=', $booking_id); 
        } elseif (isset($user_id)) { 
            $where = array('user_id', '=', $user_id); 
        } else {
            return false;
        }
        $tab_name = $this->prefix;
        if ($submitted) { $tab_name .= SUBMITTED_POSTFIX;}
        if ($this->_db->count_in_db($tab_name, $where) > 0) {
            $this->_db->get($tab_name, $where);
            $this->_data = $this->_db->first();
            $this->numItems = 1;
            // Set min and max that can be overruled in booking table
            $this->delegates->setMinItems($this->getMinDelegates());
            $this->delegates->setMaxItems($this->getMaxDelegates());
            $this->guests->setMaxItems($this->getMaxGuests());
            $this->rooms->setMaxItems($this->getMaxRooms());
            $this->travels->setMaxItems($this->getMaxTravels());
            // See if we should load all item data as well
            if ($full_load) {
                $booking_id = $this->getBookingID();
                $locked = $this->getLocked();
                foreach($this->booking_items as $id => $item) {
                    $item->setLocked($locked);
                    $item->loadDBData($booking_id, $submitted);
                }
            }
            return true;
        } else {
            $this->_data = array();
            $this->numItems = 0;
            return false;
        }
    }
    // Create a new booking in database and load the newly created data.
    public function createNewBooking($user_id) {
        $this->_db->insert($this->prefix, array('user_id' => $user_id, 'creation_date' => date('Y-m-d H:i:s')));
		return $this->loadDBData(user_id:$user_id);
    }
    // Return true if booking has been submitted
    public function isSubmitted() {
        if (empty($this->_data)) { return false; }
        return ($this->_data->submitted_date != null);
    }
    // Return submitted count
    public function getSubmittedCount() {
        if (empty($this->_data)) { return 0; }
        return $this->_data->submitted_count;
    }
    public function getSubmittedDate() {
        if (empty($this->_data)) { return false; }
        return $this->_data->submitted_date;
    }
    public function getModifiedDate() {
        if (empty($this->_data)) { return false; }
        return $this->_data->modified_date;
    }
    public function getClubNames() {
        if (USE_CLUBS_TABLE) {
            $this->_db->query('SELECT id, name, cc FROM '.FHDCE_CLUBS_PREFIX.' ORDER BY name;');
            return $this->_db->results();
        }
        return array();
    }
    public function getContactClubNames() {
      $clubs = array();
      $this->_db->query('SELECT booking_id, club_name FROM '.CONTACT_PREFIX.';');
      if ($this->_db->error() OR $this->_db->count() == 0) {
        return $clubs;
      }
      foreach($this->_db->results() as $id => $item) {
        $clubs[$item->booking_id] = $item->club_name;
      }
      return $clubs;
    }
    
    // Save the booking from submitted form to the database
    public function saveData($removed, $submit = false) {
        $booking_id = $this->getBookingID();
        foreach ($this->booking_items as $id => $item) {
            $item->saveData($booking_id, $removed[$item->getPrefix()]);
        }
        if ($this->rooms->getNumItems() == 0) {
            // No rooms, purge all travel data 
            $this->_db->delete($this->travels->getPrefix(), array('booking_id', '=', $booking_id));
        }

        // Update booking data it self.
        $this->_db->update($this->prefix, $booking_id, array('comments'=>$this->comments));
        // The booking needs to be sumitted after saving
        if ($submit) {
            $this->_db->update($this->prefix, $booking_id, array('submitted_date' => date('Y-m-d H:i:s'), 'submitted_count' => $this->_data->submitted_count));
            // Remove any currently stored submitted data
            $this->_db->delete($this->prefix.SUBMITTED_POSTFIX, array('id', '=', $booking_id));
            // Now copy new data
            $this->_db->query('INSERT INTO '.$this->prefix.SUBMITTED_POSTFIX.' SELECT * FROM '.$this->prefix.' WHERE id='.$booking_id);
            foreach ($this->booking_items as $id => $item) {
                $item->saveToSubmitted($booking_id);
            }
        } else {
            // Set modified date on save only
            $this->_db->update($this->prefix, $booking_id, array('modified_date' => date('Y-m-d H:i:s')));
        }
    }
    // Delete the booking from the database
    public function deleteFromDB($id = null) {
        $booking_id = ($id != null) ? $id : $this->getBookingID();
        $this->_db->delete($this->prefix, array('id', '=', $booking_id));
        $this->_db->delete($this->prefix.SUBMITTED_POSTFIX, array('id', '=', $booking_id));
        foreach ($this->booking_items as $id => $item) {
            $item->deleteFromDB($booking_id);
        }
    }
    // See if club name is not also used in other booking
    public function checkClubNameInuse($booking_id, $club_name) {
        if (USE_CLUBS_TABLE AND $club_name != OTHER_CLUB_NAME) {
            if ($this->_db->count_in_db(CONTACT_PREFIX, array('club_name', '=', $club_name)) > 0) {
                $this->_db->query('SELECT booking_id, first_name, last_name FROM '.CONTACT_PREFIX.' WHERE club_name = "'.$club_name.'";');
                $data = $this->_db->first();
                if ($data->booking_id != $booking_id) {
                    // Club name used on other booking. Return false and name of other booking contact.
                    return [false, $data->first_name.' '.$data->last_name, '', ''];
                }
            }
            $this->_db->query('SELECT * FROM '.FHDCE_CLUBS_PREFIX.' WHERE name = "'.$club_name.'";');
            $data = $this->_db->first();
            return [true, $data->name, $data->cc, $data->id];
        }
        // We have no data.
        return [true, '', '', ''];
    }
    // get the input form HTML for all items.
    public function getItemsFormHTML() {
        $html = '';
        $stripe = true;
        foreach ($this->booking_items as $id => $item) {
            $html .= $item->getFormHTML($stripe);
            $stripe = !$stripe;
        }
        return $html;
    }
    public function getPayments() {
        $this->payments->loadDBData($this->getBookingID());
        if ($this->payments->getNumItems() > 0) {
            return $this->payments->getData();
        } else {
            return array();
        }

    }
    // Functions for html or email booking overview
    public function getChargesOverview($email = false) {
        $meeting = new MeetingCharge($this->delegates);
        $party = new PartyCharge($this->delegates, $this->guests);
        $tshirt  = new TshirtCharge($this->delegates, $this->guests);
        $transfer = new TransferCharge($this->travels);
        $single_room = new SingleRoomCharge($this->rooms);
        $twin_room = new TwinRoomCharge($this->rooms);
        $tourist_tax = new TouristTaxCharge($this->rooms);
        $extra = new ExtrasCharge($this);

        $this->total_amount = $meeting->getTotal() + $party->getTotal() + $tshirt->getTotal() + $transfer->getTotal() + $single_room->getTotal() + $twin_room->getTotal() + $tourist_tax->getTotal() + $extra->getTotal();
        return $this->generate_charges_overview($meeting, $party, $tshirt, $transfer, $single_room, $twin_room, $tourist_tax, $extra, $email);
    }

    // Generate one row for payment overview. Use email_stripe for email format.
    private function generate_charge_row($description, $quantity, $price, $total, $vat, $email, $stripe = null) {
        $styles = get_html_email_styles($email);
        $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
	    $html  = "<tr$strcl><td>$description</td>";
	    $html .=  '<td'.$styles['al_right'].">$quantity</td>";
	    $html .=  '<td'.$styles['al_right'].'>'.format_charge($price).'</td>';
	    $html .=  '<td'.$styles['al_right'].'>'.format_charge($total).'</td>';
	    $html .=  '<td'.$styles['al_right'].">$vat%</td>";
	    $html .= '</tr>';
	    return $html;
    }

    // Generate the charges section. Use $email for email format
    private function generate_charges_overview(&$meeting, &$party, &$tshirt, &$transfer, &$single_room, &$twin_room, &$tourist_tax, &$extra, $email) {
        $html = '';
        $styles = get_html_email_styles($email);

        // Should find a better way to calc VAT.
        $total_vatl = ($single_room->getTotal() + $twin_room->getTotal()) * $single_room->getVATPercentage() / 100;
        $total_vath = ($meeting->getTotal() + $party->getTotal() + $tshirt->getTotal() + $transfer->getTotal() + $extra->getTotal()) * $meeting->getVATPercentage() / 100;
        if (!$email) {
            $html .= '<div class="charges container mt-5">';
        }
        $html .=  '<h3'.$styles['h3'].'>Charges Overview</h3>';
        $html .=  '<p>Prices are including local VAT.</p>';
        $html .=  '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
        $html .=    '<tr><th'.$styles['al_left'].'>Description</th><th'.$styles['al_right'].'>Quantity</th><th'.$styles['al_right'].'>Unit Price</th><th'.$styles['al_right'].'>Price</th><th'.$styles['al_right'].'>VAT%</th></tr>';
        $html .=   '</thead><tbody>';
        $stripe = true;
        $html .= $this->generate_charge_row('Meeting attendance', $meeting->getQuantity(), $meeting->getPrice(), $meeting->getTotal(), $meeting->getVATPercentage(), $email, $stripe);
        if ($party->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('Saturday party attendance', $party->getQuantity(), $party->getPrice(), $party->getTotal(), $party->getVATPercentage(), $email, $stripe);
        }
        if ($tshirt->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('t-Shirts', $tshirt->getQuantity(), $tshirt->getPrice(), $tshirt->getTotal(), $tshirt->getVATPercentage(), $email, $stripe);
        }
        if ($transfer->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('Airport with transfer', $transfer->getQuantity(), $transfer->getPrice(), $transfer->getTotal(), $transfer->getVATPercentage(), $email, $stripe);
        }
        if ($single_room->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('Single room at hotel', $single_room->getQuantity(), $single_room->getPrice(), $single_room->getTotal(), $single_room->getVATPercentage(), $email, $stripe);
        }
        if ($twin_room->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('Twin/double room at hotel', $twin_room->getQuantity(), $twin_room->getPrice(), $twin_room->getTotal(), $twin_room->getVATPercentage(), $email, $stripe);
        }
        if ($tourist_tax->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row('Tourist Tax', $tourist_tax->getQuantity(), $tourist_tax->getPrice(), $tourist_tax->getTotal(), $tourist_tax->getVATPercentage(), $email, $stripe);
        }
        if ($extra->getQuantity() > 0) {
            $stripe = !$stripe;
            $html .= $this->generate_charge_row($this->getExtras()[0], $extra->getQuantity(), $extra->getPrice() , $extra->getTotal() , $extra->getVATPercentage(), $email, $stripe);
        }
        $stripe = !$stripe;
        $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
        $html .=    "<tr$strcl><td><strong>Total charges</stong></td>";
        $html .=     '<td>&nbsp;</td><td>&nbsp;</td>';
        $html .=     '<td'.$styles['al_right'].'><strong>'.format_charge($this->total_amount).'</strong></td>';
        $html .=     '<td'.$styles['al_right'].'>9%&nbsp;'.format_charge($total_vatl).'<br>21%&nbsp;'.format_charge($total_vath).'</td>';
        $html .=    '</tr>';
        $html .=   '</tbody></table>';
        if (!$email) {
            $html .= '</div>';
            $html .= '<div class="payments container col-md-6 col-sm-12 mt-5">';
        }
        // payments details 
        $html .= '<h3'.$styles['h3'].'>Payments Overview</h3>';
        $total_received = 0;
        $payments = $this->getPayments();
        if (count($payments) > 0) {
            $html .= '<h4'.$styles['h4'].'>Payments received</h4>';
            $html .=  '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
            $html .=  '<tr><th'.$styles['al_left'].'>Date</th><th'.$styles['al_left'].'>Description</th><th'.$styles['al_right'].'>Amount received</th></tr>';
            $html .=  '</thead><tbody>';
            $stripe = true;
            foreach ($payments as $idx => $payment) {
                $total_received += $payment->amount;
                $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
                $html .=   '<tr'.$strcl.'>';
                $html .=    '<td>'.format_date($payment->date).'</td>';
                $html .=    '<td>'.$payment->description.'</td>';
                $html .=    '<td'.$styles['al_right'].'>'.format_charge($payment->amount).'</td>';
                $html .=   '</tr>';
                $stripe = !$stripe;
            }
            $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
            $html .= '<tr'.$strcl.'>';
            $html .=  '<td></td><td><strong>Total paid</strong></td>';
            $html .=  '<td'.$styles['al_right'].'><strong>'.format_charge($total_received).'</strong></td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
        }
//        if ($total_received < $this->total_amount) {
        if ($total_received != 0 and $this->total_amount !=0) {
            $html .= '<h4'.$styles['h4'].'>Payment balance</h4>';
            $html .=  '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
            $html .=   '<tr><th'.$styles['al_left'].'>Last pay date</th><th'.$styles['al_right'].'>Amount due</th></tr>';
            $html .=  '</thead><tbody>';
            $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
            $html .=   '<tr'.$strcl.'>';
            $html .=    '<td>'.PAYMENT_DATE.'</td>';
            if ($total_received != $this->total_amount) {
              $html .=    '<td'.$styles['al_right'].'>'.format_charge($this->total_amount - $total_received).'</td>';
            } else {
              $html .=    '<td'.$styles['al_right'].'>No outstanding balance. Thank you.</td>';
            }
            $html .=   '</tr>';
            $html .= '</tbody></table>';
        }
        if (!$email) {
            $html .= '</div>';
        }
        return $html;
    }
    // Generate the html for the web or email overview.
    public function getDetailsOverview($email = false) {
        $styles = get_html_email_styles($email);
        $html = '';
        // Items details
        foreach ($this->booking_items as $id => $item) {
            $html .= $item->getOverviewHTML($styles, $email);
        }
        // Show any comments
        $comments = $this->_data->comments;
        if ($comments != '') {
            if (!$email) {
                $html .= '<div class="'.$this->prefix.' container mt-5">';
            }
            $html .= '<h3'.$styles['h3'].'>Comments and wishes Details</h3>';
            $html .= '<p'.$styles['comment'].'>'.htmlspecialchars($comments).'</p>';
            if (!$email) {
                $html .= '</div>';
            }
        }
    
        return $html;
    }
    // Get contact details for booking. Also get email of user as alternate.
    public function getContactEmails($id) {
        $this->contacts->loadDBData($id);
        $email = $this->contacts->getEmail();
        $name = $this->contacts->getFirstName();
        $email2 = null;
        $t_user = new User();
        if ($t_user->find($this->getUserID())) {
            if ($email) {
                $email2 = $t_user->email();
                if (strcasecmp($email, $email2) == 0) {
                    $email2 = null;
                }
            } else {
                $email = $t_user->email();
                $name = $t_user->name();
            }
        }
        return [$name, $email, $email2];
    }
    // Start of ADMIN specific functions.
    // Get the list of IDs of submitted bookings
    public function getSubmittedIDs() {
        $ids = array();
        if ($this->_db->query('SELECT id FROM '.$this->prefix.SUBMITTED_POSTFIX)) {
            foreach($this->_db->results() as $id => $item) {
                $ids[] = $item->id;
            }
        }
        return $ids;
    }
    // Get the list of IDs of saved bookings
    public function getSavedIDs() {
        $ids = array();
        if ($this->_db->query('SELECT id FROM '.$this->prefix)) {
            foreach($this->_db->results() as $id => $item) {
                $ids[] = $item->id;
            }
        }
        return $ids;
    }
    // Add up the payments for a given booking
    public function totalPayments($booking_id) {
        if (!$this->payments->loadDBData($booking_id) OR $this->payments->getNumItems() == 0) {
            // No payments found
            return 0;
        }
        $total = 0;
        foreach($this->payments->getData() as $id => $item) {
            $total += $item->amount;
        }
        return $total;
    
    }
    // Ad up the total charges for a saved or submitted booking.
    public function totalCharges($booking_id, $submitted = false) {
        // Load the data of a booking
        if (!$this->loadDBData(booking_id:$booking_id, submitted:$submitted, full_load:true)) {
            // Looking for not yet submitted booking.
            return 0;
        }
        // Add up totals
        $meeting = new MeetingCharge($this->delegates);
        $party = new PartyCharge($this->delegates, $this->guests);
        $tshirt  = new TshirtCharge($this->delegates, $this->guests);
        $transfer = new TransferCharge($this->travels);
        $single_room = new SingleRoomCharge($this->rooms);
        $twin_room = new TwinRoomCharge($this->rooms);
        $tourist_tax = new TouristTaxCharge($this->rooms);
        $extra = new ExtrasCharge($this);
        return  $meeting->getTotal() + $party->getTotal() + $tshirt->getTotal() + $transfer->getTotal() + $single_room->getTotal() + $twin_room->getTotal() + $tourist_tax->getTotal() + $extra->getTotal();
    }
    // Collect summary data for bookings, saved and/or submitted.
    public function collectOverviewDetails($submitted_only = null) {
        $result = array();
        // Count users data
        $result[USERS_PREFIX] = array('Number of users', $this->_db->count_in_db(USERS_PREFIX));
        $result[USERS_PREFIX.'act'] = array('Number of users active', $this->_db->count_in_db(USERS_PREFIX, array('active', '=', '1')));
        // Setup rest of fields
        $result[BOOKING_PREFIX] = array('Number of bookings',0,0);
        $result[DELEGATE_PREFIX] = array('Number of delegates',0,0);
        $result[GUEST_PREFIX] = array('Number of guests',0,0);
        $result[PARTY_PREFIX] = array('Number of party attendees',0,0);
        $result[GUEST_PREFIX.'ct'] = array('Number of city tour attendees',0,0);
        if ($this->rooms->getEnabled()) {
            foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                $result[ROOM_PREFIX.$id] = array("Number of $name rooms",0,0);
            }
        }
        if ($this->travels->getEnabled()) {
            $result[TRAVEL_PREFIX.'arr'] = array('Number of arrival transfers',0,0);
            $result[TRAVEL_PREFIX.'dep'] = array('Number of departure transfers',0,0);
            $result[TSHIRT_PREFIX.'tot'] = array('Number of t-Shirts total',0,0);
        }
        foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
            if ($id != 'No shirt') {
                $result[TSHIRT_PREFIX.str_replace(' ', '', $id)] = array('Number of t-Shirts '.$size,0,0);
            }
        }
        $result['total_charges'] = array('Total charges',0,0);;
        // Count up for saved data
        if ($submitted_only != 'Y') {
            $ids_all = $this->getSavedIDs();
            if (count($ids_all) > 0) {
                // Count up data
                $result[BOOKING_PREFIX][1] = $this->_db->count_in_db(BOOKING_PREFIX);
                $result[DELEGATE_PREFIX][1] = $this->_db->count_in_db(DELEGATE_PREFIX);
                $result[GUEST_PREFIX][1] = $this->_db->count_in_db(GUEST_PREFIX);
                $result[PARTY_PREFIX][1] = $this->_db->count_in_db(DELEGATE_PREFIX,array('party', '=', 'Y')) + $this->_db->count_in_db(GUEST_PREFIX,array('party', '=', 'Y'));
                $result[GUEST_PREFIX.'ct'][1] = $this->_db->count_in_db(GUEST_PREFIX,array('city_tour', '=', 'Y'));
                if ($this->rooms->getEnabled()) {
                    foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                        $result[ROOM_PREFIX.$id][1] = $this->_db->count_in_db(ROOM_PREFIX,array('type', '=', $id));
                    }
                }
                if ($this->travels->getEnabled()) {
                    $result[TRAVEL_PREFIX.'arr'][1] = $this->_db->count_in_db(TRAVEL_PREFIX,array('arr_type', '=', 'PLANE'));
                    $result[TRAVEL_PREFIX.'dep'][1] = $this->_db->count_in_db(TRAVEL_PREFIX,array('dep_type', '=', 'PLANE'));
                    $result[TSHIRT_PREFIX.'tot'][1] = $this->_db->count_in_db(DELEGATE_PREFIX,array('shirt_size', '!=', 'No shirt')) + $this->_db->count_in_db(GUEST_PREFIX,array('shirt_size', '!=', 'No shirt'));
                }
                foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                    if ($id != 'No shirt') {
                        $result[TSHIRT_PREFIX.str_replace(' ', '', $id)][1] = $this->_db->count_in_db(DELEGATE_PREFIX,array('shirt_size', '=', $id)) + $this->_db->count_in_db(GUEST_PREFIX,array('shirt_size', '=', $id));
                    }
                }
                $total = 0;
                foreach ($ids_all as $id) {
                    $total += $this->totalCharges($id);
                }
                $result['total_charges'][1] = $total;
            }
        }
        // Count up for submitted data
        if ($submitted_only != 'N') {
            $ids_sub = $this->getSubmittedIDs();
            if (count($ids_sub) > 0) {
                $result[BOOKING_PREFIX][2] = $this->_db->count_in_db(BOOKING_PREFIX.SUBMITTED_POSTFIX);
                $result[DELEGATE_PREFIX][2] = $this->_db->count_in_db(DELEGATE_PREFIX.SUBMITTED_POSTFIX);
                $result[GUEST_PREFIX][2] = $this->_db->count_in_db(GUEST_PREFIX.SUBMITTED_POSTFIX);
                $result[PARTY_PREFIX][2] = $this->_db->count_in_db(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('party', '=', 'Y')) + $this->_db->count_in_db(GUEST_PREFIX.SUBMITTED_POSTFIX, array('party', '=', 'Y'));
                $result[GUEST_PREFIX.'ct'][2] = $this->_db->count_in_db(GUEST_PREFIX.SUBMITTED_POSTFIX,array('city_tour', '=', 'Y'));
                if ($this->rooms->getEnabled()) {
                    foreach(OPTIONS_ROOM_TYPES as $id => $name) {
                        $result[ROOM_PREFIX.$id][2] = $this->_db->count_in_db(ROOM_PREFIX.SUBMITTED_POSTFIX,array('type', '=', $id));
                    }
                }
                if ($this->travels->getEnabled()) {
                    $result[TRAVEL_PREFIX.'arr'][2] = $this->_db->count_in_db(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('arr_type', '=', 'PLANE'));
                    $result[TRAVEL_PREFIX.'dep'][2] = $this->_db->count_in_db(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('dep_type', '=', 'PLANE'));
                    $result[TSHIRT_PREFIX.'tot'][2] = $this->_db->count_in_db(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '!=', 'No shirt')) + $this->_db->count_in_db(GUEST_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '!=', 'No shirt'));
                }
                foreach(OPTIONS_TSHIRTS_SIZES as $id => $size) {
                    if ($id != 'No shirt') {
                        $result[TSHIRT_PREFIX.str_replace(' ', '', $id)][2] = 
                            $this->_db->count_in_db(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '=', $id)) +
                            $this->_db->count_in_db(GUEST_PREFIX.SUBMITTED_POSTFIX, array('shirt_size', '=', $id));
                    }
                }
                $total = 0;
                foreach ($ids_sub as $id) {
                    $total += $this->totalCharges($id, true);
                }
                $result['total_charges'][2] = $total;
            }
        }
        return $result;
    }

    // Create HTML for Admin load screen
  public function getAdminOverviewHTML($clubs = array()) {
    $html = '';
    $sql = 'SELECT * FROM '.$this->prefix.' ORDER BY `user_id`;';
		$columns = DB_FIELDS[$this->prefix];
		// Build sql column names 
		$table_header = '';
		foreach($columns as $column) {
      // Add Club Name
      if ($column == 'id') { 
        $table_header .= '<th><input type="checkbox" id="select_all" value="all" /> Booking ID - Club Name</th>'; 
      } else { 
        $table_header .= '<th>'.ucfirst(str_replace('_', ' ', $column)).'</th>';
      }
		}
		$this->_db->query_columns($sql, $columns);
    if ($this->_db->error() OR $this->_db->count() == 0) {
      $html .= '<h3>Could not find data</h3>';
      return $html;
		}	
		$html .= '<h3 class="section_header">'.ucfirst($this->prefix).' Details</h3>';
		$html .= '<table class="table table-striped table-sm table_adm">';
		$html .=  '<thead class="thead-dark">';
		$html .=    "<tr>$table_header<th>Charges</th><th>Paid</th><th>Due</th></tr>";
		$html .=   '</thead>';
		$html .=  '<tbody>';
		foreach ($this->_db->results() as $idx => $item) {
			$itm = get_object_vars($item);
			$html .= '<tr>';
			foreach($columns as $column) {
				$html .= '<td id ="'.$this->prefix.$itm['id'].'">';
				if ($column == 'id') {
					$html .= '<input type="checkbox" class="item" name="select_id'.$item->id.'" value="'.$item->id.'" /> ';
					$html .= $itm[$column];
					$html .= ' - ';
					$html .= getSaveArray($clubs,$item->id);
				} elseif ($column == 'additional_costs') {
					$html .= format_charge($itm[$column]);
				} else {
					$html .= $itm[$column];
				}
				$html .= '</td>';
			}
			// Get total charge and payments of booking.
      $charges = $this->totalCharges($itm['id']);
      $payments = $this->totalPayments($itm['id']);
			$html .= ' <td id ="'.$this->prefix.$itm['id'].'">'.format_charge($charges).'</td>';
			$html .= ' <td id ="'.$this->prefix.$itm['id'].'">'.format_charge($payments).'</td>';
			$html .= ' <td id ="'.$this->prefix.$itm['id'].'">'.format_charge($charges-$payments).'</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		// For booking add extra funciton buttons
		$html .= '<p>You can see the booking details by clicking in the row of the booking.</p>';
		$html .= '<p>You can send a reminder email to contacts of not yet submitted or paid bookings.<br>';
		$html .= 'To remove a booking, select the booking, and click the Remove button. All data related to the booking will be deleted!</p>';
		$html .= '<div id="main-input" class="container overflow-hidden">';
		$html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
		$html .=   '<input type="hidden" name="prefix" id="prefix" value="'.$this->prefix.'" />';
		$html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
		$html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
		$html .=   '<div class="pt-1 row  mt-2">';
		$html .=    '<div class="form-label col-auto">';
		$html .=     '<input type="checkbox" class="form-check-input" name="notify_contact" value="1" checked />';
		$html .=     '<label for="notify_contact" class="form-check-label">&nbsp;Send confirmation email to booking contact (Lock/unlock only).</label>';
		$html .=    '</div>';
		$html .=   '</div>';
		$html .=  '</form>';
		$html .=  '<div class="row gy-5 mt-3">';
		$html .=   '<div class="d-grid col-sm-3 col-12 center">';
		$html .=    '<button id="btn_mail_pay" class="menu-item btn btn-primary btn-inputFormMail">Email Payment</button>';
		$html .=   '</div>';
		$html .=   '<div class="d-grid col-sm-3 col-12 center">';
		$html .=    '<button id="btn_mail_submit" class="menu-item btn btn-primary btn-inputFormMail">Email Submit</button>';
		$html .=   '</div>';
    $html .=   '<div class="d-grid col-sm-3 col-12 center">';
		$html .=    '<button id="btn_toggle" class="menu-item btn btn-primary btn-inputFormToggle">Lock/unlock</button>';
		$html .=   '</div>';
		$html .=   '<div class="d-grid col-sm-3 col-12 center">';
		$html .=    '<button id="btn_remove" class="menu-item btn btn-primary btn-inputFormRemove">Remove</button>';
		$html .=   '</div>';
		$html .=  '</div>';
		$html .=  '<div class="row gy-5 mt-3">';
		$html .=   '<div class="d-grid col-sm-6 col-12 center">';
		$html .=    '<button id="btn_remove" class="menu-item btn btn-primary btn-inputFormLock">Lock All bookings</button>';
		$html .=   '</div>';
		$html .=  '</div>';
		$html .=  '<p>&nbsp;</p>';
		$html .= '</div>';
    return $html;
  }
  public function getAdminExtrasOverviewHTML($clubs = null) {
    $html = '';
    $sql = 'SELECT id, additional_descr, additional_costs FROM '.$this->prefix.' WHERE additional_descr IS NOT NULL ORDER BY `id`;';
		$this->_db->query($sql);
    if ($this->_db->error() OR $this->_db->count() == 0) {
      $html .= '<h3>Could not find data</h3>'; 
    } else {
		  $html .= '<h3 class="section_header">Current extra`s Details</h3>';
	  	$html .= '<table class="table table-striped table-sm">';
      $html .=  '<thead class="thead-dark">';
	   	$html .=    '<tr><th>Booking ID - Club name</th><th>Description</th><th>Amount</th></tr>';
    	$html .=   '</thead>';
		  $html .=  '<tbody>';
	   	foreach ($this->_db->results() as $idx => $item) {
    		if ($item->additional_costs != 0) {
		    	$html .= '<tr>';
		   		$html .= '<td><input type="radio" name="select_id" value="'.$item->id.'" /> '. ($item->id).' - '.getSaveArray($clubs, $item->id).'</td>';
	   			$html .= ' <td>'.$item->additional_descr.'</td>';
    			$html .= ' <td>'.format_charge($item->additional_costs).'</td>';
			    $html .= '</tr>';
		    }
		  }
		  $html .= '</tbody></table>';
    }
		$booking_options = array();
		foreach($this->getSavedIDs() as $id) {
			$booking_options[$id] = $id.' - '.getSaveArray($clubs,$id);
		}
		$html .= '<p>To add or change extra charges, select the Booking ID above or below if not listed enter the Description and amount, and click the Add button.<br>';
		$html .= 'If selecting a listed extra, clicking Add will overwrite the existing.</p>';
		$html .= 'To remove extra charges, select the Booking ID above, and click the Remove button.</p>';
		$html .= '<div id="main-input" class="container overflow-hidden">';
		$html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
		$html .=   '<input type="hidden" name="prefix" id="prefix" value="'.EXTRA_PREFIX.'" />';
		$html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
		$html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
		$html .=   '<div class="row">';
		$html .=    generate_form_row('Booking ID', "booking_id", type:'select', width:2, options: $booking_options);
		$html .=    generate_form_row('Description', "additional_descr", width:8, max_len:150);
		$html .=    generate_form_row('Amount', "additional_costs", type:'number', width:2, options: '0.01', max_len:5000);
		$html .=   '</div>';
		$html .=   '<div class="pt-1 row  mt-2">';
		$html .=    '<div class="form-label col-auto">';
		$html .=     '<input type="checkbox" class="form-check-input" name="notify_contact" value="1" checked />';
		$html .=     '<label for="notify_contact" class="form-check-label">&nbsp;Send confirmation email to booking contact (add only).</label>';
		$html .=    '</div>';
		$html .=   '</div>';
		$html .=  '</form>';
		$html .=  '<div class="row gy-5 mt-3">';
		$html .=   '<div class="d-grid col-sm-4 col-12 center">';
		$html .=    '<button id="btn_add" class="menu-item btn btn-primary btn-inputFormAdd">Add</button>';
		$html .=   '</div>';
		$html .=   '<div class="d-grid col-sm-4 col-12 center">';
		$html .=    '<button id="btn_remove" class="menu-item btn btn-primary btn-inputFormRemove">Remove</button>';
		$html .=   '</div>';
		$html .=  '</div>';
		$html .=  '<p>&nbsp;</p>';
		$html .= '</div>';
    return $html;
  }
}

?>