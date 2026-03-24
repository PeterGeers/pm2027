<?php
/* 
Objects to make data base and session manipulation simpler for booking tables. 

You have the copy the data to a variable immediately after a find as a next find will overview this class data.

Tables supported: See config.php for field details.
*/
require_once 'config.php';
require_once 'Database.php';
require_once 'Session.php';


class BookingDB {
    private $_db,
            $_data;

    // Class construct. 
	public function __construct() {
        $this->_db = Database::getInstance();
    }
    // Create empty object with given fields. Pre-populate id and booking_id, rest is null.
    public function new_object($fields, $booking_id) {
        $res = array('id'=>-1);
        foreach($fields as $field) {
            $res[$field] = ($field == 'booking_id') ? $booking_id: null; 
        }
        return (object) $res;
    }
    // Purge all obsolete items from a table for the booking.
    function purge($table, $booking_id, $list) {
        if ($list != '') {
            if ($this->find($table, array('booking_id', '=', $booking_id))) {
                if ($list != '*') {
                    $ids_to_remove = explode(',', $list);
                    if ($ids_to_remove[0]) {
                        foreach ($this->_data as $idx => $item) {
                            if (in_array($item->id, $ids_to_remove)) {
                                $this->remove($table, array('id', '=', $item->id));
                            }
                        }
                    }
                } else {
                    // Remove all items for booking.
                    $this->remove($table, array('booking_id', '=', $booking_id));
                }
            }    
        }
    }
    // Update if existing, create if not.
    function store($table, $id, $fields) {
        if ($this->find($table, array('id', '=', $id))) {
            return $this->update($table, $fields, $id);
        } else {
            return $this->create($table, $fields);
        }
    }
    // Update an existing table entry
    public function update($table, $fields = array(), $id = null) {
        if(!$this->_db->update($table, $id, $fields)) {
          throw new Exception('There was a problem updating.');
        }
    }
    // Create a new table entry.
    public function create($table, $fields = array()) {
        if(!$this->_db->insert($table, $fields) ) {
          throw new Exception('There was a problem inserting');
        }
    }
    // Remove table entry(ies).
    public function remove($table, $where = array()) {
        if(!$this->_db->delete($table, $where) ) {
          throw new Exception('There was a problem removing');
        }
    }
    // Find existing table entry(ies).
    public function find($table, $where = array()) {
        $data = $this->_db->get($table, $where);
        if($data->count()) {
            $this->_data = $data->results();
            return true;
        }
        return false;
    }
    public function find1($table, $where = array()) {
        $data = $this->_db->get($table, $where);
        if($data->count()) {
            $this->_data = $data->first();
            return true;
        }
        return false;
    }
    public function count($table, $where = array()) {
        return $this->_db->count_in_db($table, $where);
    }

    // Run a free format query. Use code controlled only.
    public function query($sql, $params = array()) {
        $data = $this->_db->query($sql, $params);
        if($data->count()) {
            $this->_data = $data->results();
            return true;
        }
        return false;
    }
    
    public function exists() {
        return (!empty($this->_data)) ? true : false;
    }
    public function data() {
        return $this->_data;
    }

/*
    // Booking table actions
    public function find_booking($user_id) {
        return $this->find1(BOOKING_PREFIX, array('user_id', '=', $user_id));
    }
    public function find_booking_by_id($id) {
        return $this->find1(BOOKING_PREFIX, array('id', '=', $id));
    }
    public function find_submitted_booking($user_id) {
        return $this->find1(BOOKING_PREFIX.SUBMITTED_POSTFIX, array('user_id', '=', $user_id));
    }
    public function find_submitted_booking_by_id($id) {
        return $this->find1(BOOKING_PREFIX.SUBMITTED_POSTFIX, array('id', '=', $id));
    }
    public function store_booking($id, $fields) {
        return $this->store(BOOKING_PREFIX, $id, $fields);
    }
    public function purge_booking($booking_id) {
        $this->remove(BOOKING_PREFIX, array('id', '=', $booking_id));
        $this->remove(CONTACT_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(DELEGATE_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(GUEST_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(ROOM_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(TRAVEL_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(PAYMENT_PREFIX, array('booking_id', '=', $booking_id));
        $this->remove(BOOKING_PREFIX.SUBMITTED_POSTFIX, array('id', '=', $booking_id));
        $this->remove(CONTACT_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(GUEST_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(ROOM_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    // Contact table actions
    public function find_contact($booking_id) {
        return $this->find1(CONTACT_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function find_submitted_contact($booking_id) {
        return $this->find1(CONTACT_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    public function store_contact($id, $fields) {
        return $this->store(CONTACT_PREFIX, $id, $fields);
    }

    // Delegates table actions
    public function find_delegates($booking_id) {
        return $this->find(DELEGATE_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function find_submitted_delegates($booking_id) {
        return $this->find(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    public function store_delegate($id, $fields) {
        return $this->store(DELEGATE_PREFIX, $id, $fields);
    }
    public function purge_delegates($booking_id, $ids) {
        $this->purge(DELEGATE_PREFIX, $booking_id, $ids);
    }

    // Guests table actions
    public function find_guests($booking_id) {
        return $this->find(GUEST_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function find_submitted_guests($booking_id) {
        return $this->find(GUEST_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    public function store_guest($id, $fields) {
        return $this->store(GUEST_PREFIX, $id, $fields);
    }
    public function purge_guests($booking_id, $ids) {
        $this->purge(GUEST_PREFIX, $booking_id, $ids);
    }

    // Rooms table actions
    public function find_rooms($booking_id) {
        return $this->find(ROOM_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function find_submitted_rooms($booking_id) {
        return $this->find(ROOM_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    public function store_room($id, $fields) {
        return $this->store(ROOM_PREFIX, $id, $fields);
    }
    public function purge_rooms($booking_id, $ids) {
        $this->purge(ROOM_PREFIX, $booking_id, $ids);
    }

    // Travels table actions
    public function find_travels($booking_id) {
        return $this->find(TRAVEL_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function find_submitted_travels($booking_id) {
        return $this->find(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }
    public function store_travel($id, $fields) {
        return $this->store(TRAVEL_PREFIX, $id, $fields);
    }
    public function purge_travels($booking_id, $ids) {
        $this->purge(TRAVEL_PREFIX, $booking_id, $ids);
    }

    // Copy the data of a booking to submitted tables
    public function store_booking_in_submitted($booking_id) {
        // Remove any currently stored data
        $this->remove(BOOKING_PREFIX.SUBMITTED_POSTFIX, array('id', '=', $booking_id));
        $this->remove(CONTACT_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(DELEGATE_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(GUEST_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(ROOM_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->remove(TRAVEL_PREFIX.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        // Now copy data
        $this->query('INSERT INTO '.BOOKING_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.BOOKING_PREFIX.' WHERE id='.$booking_id);
        $this->query('INSERT INTO '.CONTACT_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.CONTACT_PREFIX.' WHERE booking_id='.$booking_id);
        $this->query('INSERT INTO '.DELEGATE_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.DELEGATE_PREFIX.' WHERE booking_id='.$booking_id);
        $this->query('INSERT INTO '.GUEST_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.GUEST_PREFIX.' WHERE booking_id='.$booking_id);
        $this->query('INSERT INTO '.ROOM_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.ROOM_PREFIX.' WHERE booking_id='.$booking_id);
        $this->query('INSERT INTO '.TRAVEL_PREFIX.SUBMITTED_POSTFIX.' SELECT * FROM '.TRAVEL_PREFIX.' WHERE booking_id='.$booking_id);
    }

    // Payments table actions
    public function find_payments($booking_id) {
        return $this->find(PAYMENT_PREFIX, array('booking_id', '=', $booking_id));
    }
    public function store_payment($id, $fields) {
        return $this->store(PAYMENT_PREFIX, $id, $fields);
    }

    // Make new Contact object
    public function new_contact($booking_id) {
        return $this->new_object(CONTACT_FIELDS, $booking_id);
    }

    // Make new Delegate object
    public function new_delegate($booking_id) {
        return $this->new_object(DELEGATE_FIELDS, $booking_id);
    }
    
    // Make new Guest object
    public function new_guest($booking_id) {
        return $this->new_object(GUEST_FIELDS, $booking_id);
    }

    // Make new Room object
    public function new_room($booking_id) {
        return $this->new_object(ROOM_FIELDS, $booking_id);
    }
    // Make new Travel object
    public function new_travel($booking_id) {
        return $this->new_object(TRAVEL_FIELDS, $booking_id);
    }
*/  
}
?>
