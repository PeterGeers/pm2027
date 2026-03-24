<?php
// Classes to handle booking items: Contact, Delegates, Guests, Rooms, Travels, Payments
// Is included by BookingClass.php, not to use without.

// Defines an item that can be part of a booking with all standard funcitions. This class is extended for each booking item and not used by other code directly.
class BookingItem {
    protected $enabled, $is_locked, $minItems, $maxItems, $prefix, $show, $title, $hasParty = false, $hasTshirts = false, $_db;
    private $_data;
    public function __construct($show=true, $enabled=true) {
        $this->_db = Database::getInstance();
        $this->show = $show; // If false do not show the whole section
        $this->enabled = $enabled; // If false we do not use this type at all
        $this->is_locked = false; // If true the form body will allow to make price impacting changes
        $this->minItems = 0;
        $this->maxItems = 1;
        $this->numItems = 0;
    }
    // Set/get enabled status. Disabled removed all functionality
    public function setEnabled($enabled = true) {
        $this->enabled = $enabled;
    }
    public function getEnabled() {
        return $this->enabled;
    }
    // Set locked status.
    public function setLocked($locked = true) {
        $this->is_locked = $locked;
    }
    // Set show status. Not shown will still include section, just not shown on screen.
    public function setShow($show = true) {
        $this->show = $show;
    }
    // Get items data.
    public function getData() {
        return $this->_data;
    }
    public function hasData() {
        return !empty($this->_data);
    }
    // Get number of data records loaded.
    public function getNumItems() {
        return $this->numItems;
    }
    // Set/Get maximum number of items suppored.
    public function setMaxItems($items) {
        $this->maxItems = $items;
    }
    public function getMaxItems() {
        return $this->maxItems;
    }
    // Set/Get minimum number of items suppored.
    public function setMinItems($items) {
        $this->minItems = $items;
    }
    public function getMinItems() {
        return $this->minItems;
    }
    public function getPrefix() {
        return $this->prefix;
    }
    
    // Optional clean up of collected data prior to storing in db.
    public function cleanData(&$data) {}

    // Save the data in the dabase
    public function saveData($booking_id, $removed = null) {
        if ($this->numItems > 0) {
            foreach ($this->_data as $key => $item) {
                // Data starts with booking_id for all items
                $data = array('booking_id'=>$booking_id);
                foreach ($item as $name => $value) {
                    // Add each set value if the name is known in the table.
                    if ($value != '' AND in_array($name, DB_FIELDS[$this->prefix])) { $data[$name] = $value;}
                }
                $this->cleanData($data);
                // Store only if we have more than just the booking_id
                if (count($data) > 1) {
                    if ($this->_db->count_in_db($this->prefix, array('id', '=', $item->id)) > 0) {
                        $this->_db->update($this->prefix, $item->id, $data);
                    } else {
                        // Only store if ID is set.
                        if ($item->id != -1) {
                            $this->_db->insert($this->prefix, $data);
                        }
                    }
                }
            }
        }
        // We may need to purge some data too.
        if ($removed != null AND $removed != '') {
            if ($removed == '*') {
                // Delete all data for booking
                $this->_db->delete($this->prefix, array('booking_id', '=', $booking_id)); 
            } else {
                // Delete list of id's
                $this->_db->delete($this->prefix, array('id', 'IN', $removed)); 
            }
        }
    }
    // Booking is submitted, so copy data to submitted table
    public function saveToSubmitted($booking_id){
        $this->_db->delete($this->prefix.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
        $this->_db->query('INSERT INTO '.$this->prefix.SUBMITTED_POSTFIX.' SELECT * FROM '.$this->prefix.' WHERE booking_id='.$booking_id);
    }
    // Delete the booking from the database
    public function deleteFromDB($booking_id) {
        $this->_db->delete($this->prefix, array('booking_id', '=', $booking_id));
        $this->_db->delete($this->prefix.SUBMITTED_POSTFIX, array('booking_id', '=', $booking_id));
    }

    // Get the table IDs submitted to DB
    public function getSubmittedIDs() {
        return $this->getSavedIDs(true);
    }
    // Get the table IDs saved to DB
    public function getSavedIDs($sub = false) {
        $ids = array();
        $table_name = $this->prefix;
        if ($sub) { $table_name .= SUBMITTED_POSTFIX; }
        $this->_db->query('SELECT id from '.$table_name.';');
		if ($this->_db->count() > 0) {
			foreach ($this->_db->results() as $id => $item) {
				$ids[] = $item->id;
			}
        }
        return $ids;
    }
    // Generate the html for the section details. This will be defined in each extended class
    public function getFormHTMLSection($id,$item,$disable) {
        return '';
    }
    public function getFormHTMLSectionTitle() {
        return $this->title;
    }
    // Generate the html for the input form
    public function getFormHTML(&$stripe) {
        $html = '';
        // Section header
        $cls = ($stripe) ? 'form-section-secondary' : 'form-section-primary';
        $cls .= ($this->show) ? ' visible collapse.show' : ' invisible collapse';
        $html .= '<div id="'.$this->prefix.'" class="'.$cls.' mt-3">';
        $html .= '<h3 class="section_header">'.$this->title.' Details</h3>';
        if (!$this->is_locked AND $this->minItems == 0) {
            // If we can have zero items, we need an inital button
            $cls = ($this->numItems == 0) ? 'visible collapse.show' : 'invisible collapse';
            $html .= '<div class="d-grid col-md-4 col-12">';
            $html .=  '<button class="btn btn-primary btn-inputFormAdd '.$cls.'" id="btn_add_'.$this->prefix.'" type="button">+ Add '.$this->title.'</button>';
            $html .= '</div>';
        }
        // loop over data
        foreach ($this->_data as $id => $item) {
            // Determine is details section needs to be shown or hidden. If hidden also disable for submit and validation
            if ($this->minItems > 0) {
                $show = ($id > 0 AND $item->id == -1);
            } else {
                $show = ($item->id == -1);
            }
            if ($show) { 
                $cls = 'invisible collapse';
                $disable = true; 
                $dis = ' disabled';
            } else {
                $cls = 'visible collapse.show';
                $disable = false;
                $dis= '';
            }
            $html .= '<div id="'.$this->prefix.$id.'" class="'.$cls.'">';
            if ($this->maxItems > 1) {
                $html .= ' <p class="pt-3">'.$this->getFormHTMLSectionTitle().' '.($id+1).' details:</p>';
            }
            $html .= ' <input type="hidden" name="'.$this->prefix.'['.$id.'][id]" id="'.$this->prefix.$id.'id" value="'.$item->id.'"'.$dis.' />';
            // section details
            $html .= $this->getFormHTMLSection($id,$item,$disable); 
            // closing buttons if we can have more than one item
            if (!$this->is_locked AND $this->maxItems > 1) {
                // generate_form_button_row($label, $prefix, $id, $next_id, $maximum_items, $show_remove=true)
                $next_id = ($id < $this->maxItems-1) ? $this->_data[$id+1]->id : 0;
                $show_remove = ($id > 0 OR $this->numItems > 1);
                $html .= '<div class="row buttons-row mt-2">';
                $html .=  '<div class="d-grid col-md-4 col-12">';
                $cls = ($show_remove) ? 'visible collapse.show' : 'invisible collapse';
                $html .=  '<button class="btn btn-primary btn-inputFormRemove '.$cls.'" type="button" id="btn_remove_'.$this->prefix.$id.'">- Remove '.$this->title.' '.($id+1).'</button>';
                $html .= '</div>';
                if ($id < $this->maxItems-1) {  // Put Add button on last item shown unless max reached
                    $cls = ($next_id == -1) ? 'visible collapse.show' : 'invisible collapse';
                    $html .= '<div class="d-grid col-md-4 col-12">';
                    $html .=  '<button class="btn btn-primary btn-inputFormAdd '.$cls.'" type="button" id="btn_add_'.$this->prefix.$id.'">+ Add '.$this->title.'</button>';
                    $html .= '</div>';
                } 
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        // Section close
        $html .= '</div>';
        return $html;
    }
    public function getOverviewHTMLSectionTitle($styles, $email) {
    }
    // Generate the html for the overviews if we have anything to show
    public function getOverviewHTML($styles, $email){
        if ($this->numItems == 0) { return '';}
        $html = '';
        if (!$email) {
            $html .= '<div class="'.$this->prefix.' container mt-5">';
        }
        $html .= '<h3'.$styles['h3'].'>'.$this->title.' Overview</h3>';
        $html .= '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
        $html .=  $this->getOverviewHTMLSectionTitle($styles, $email);
        $html .=  '</thead><tbody>';
        $stripe = true;
        foreach ($this->_data as $id => $item) {
            if ($item->id != -1) {
                $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
                $html .= "<tr$strcl>";
                $html .= $this->getOverviewHTMLSection($id,$item,$styles,$email); 
                $html .= '</tr>';
                $stripe = !$stripe;
            }
        }
        $html .=  '</tbody></table>';
        if (!$email) { $html .= '</div>'; }
        return $html;
    }
  // Generate the html for the Admin overviews
  // If $hasselect == 1 then add select input to ID column, if 2 add radio input.
  public function getAdminOverviewHTML($hasSelect = 0, $ids = null, $clubs = null){
    $html = '';
		$sql = 'SELECT * FROM '.$this->prefix.' ORDER BY `booking_id`;';
		$table_header = '';
		$columns = DB_FIELDS[$this->prefix];
		foreach($columns as $column) {
      if ($column == 'id') {
        $table_header .= '<th>';
        if ($hasSelect == 1) {
          $table_header .= '<input type="checkbox" id="select_all" value="all" /> ';
        }
        $table_header .= ucfirst($this->prefix).' ID</th>';
      } elseif ($column == 'booking_id') {
        // Insert Clubname after (booking_)id as applicable
        if ($clubs != null) {
          $table_header .= '<th>Booking ID - Club Name</th>';
        } else {
          $table_header .= '<th>Booking ID</th>';
        }
      } else {
        $table_header .= '<th>'.ucfirst(str_replace('_', ' ', $column)).'</th>';
      }
		}
		$this->_db->query_columns($sql, $columns);
      if ($this->_db->error() OR $this->_db->count() == 0) {
        return '<h3>Could not find data</h3>';
		}
		$html .=  '<h3 class="section_header">'.ucfirst($this->prefix).' Details</h3>';
		$html .=  '<table class="table table-striped table-sm table_adm">';
		$html .=   '<thead class="thead-dark">';
		$html .=     "<tr>$table_header</tr>";
		$html .=    '</thead>';
		$html .=   '<tbody>';
		foreach ($this->_db->results() as $idx => $item) {
			$itm = get_object_vars($item);
			$html .=  '<tr>';
			foreach($columns as $column) {
        if ($column == 'id') {
          // Add select option as applicable
          if ($hasSelect == 1) {
            $html .= '<td><input type="checkbox" class="item" name="select_id'.($item->id).'" value="'.($item->id).'" /> '.$itm[$column].'</td>';
          } elseif ($hasSelect == 2) {
            $html .= '<td><input type="radio" class="item" name="select_id" value="'.($item->id).'" /> '.$itm[$column].'</td>';
          } else {
            $html .= '<td>'.$itm[$column].'</td>';
          }
        } elseif ($clubs != null AND $column == 'booking_id') {
          // Insert Clubname after (booking_)id as applicable
          $html .= '<td>'.$itm[$column].' - '.getSaveArray($clubs, $itm[$column]).'</td>';
        } elseif (USE_ROOMS_TABLE AND $column == 'room_no') {
          // Lookup room details
          $room = null;
          if ($itm[$column] != '') {
            foreach($ids as $rm_id => $rm) {
              if ($itm[$column] == $rm->id) {
                $room = $rm;
                break;
              }
            }
          }
          if ($room != null) {
            $html .= '<td>'.$room->room_no.' '.$room->location.'</td>';
          } else {
            $html .=  '<td></td>';
          }
        } else {
          $html .= '<td>'.$itm[$column].'</td>';
        }
			}
			$html .=  '</tr>';
		}
		$html .=  '</tbody></table>';
    return $html;
  }

  // Store the data we got from a form POST.
  public function loadFormData($data) {
    if ($data == '') {
      // Data set from form is empty
      $this->_data = null;
      $this->numItems = 0;
      return true;
    }
    $cp_data = array();
    foreach ($data as $id => $item) {
      // Get only values that are in POST data if in locked status, other items are in _data already so preserve.
      if ($this->is_locked) {
        $it_data = (array)$this->_data[$id];
        foreach($item as $iid => $value) {
          $it_data[$iid] = $value;
        }
        $cp_data[$id] = (object)$it_data;
      } else {
        // Not locked, use only POST data.
        $cp_data[$id] = (object)$item;
      }
    }
    $this->_data = $cp_data;
    $this->numItems = count($this->_data);
    return true;
  }

  // Get data from database
  public function loadDBData($booking_id, $submitted=false) {
        $tab_name = $this->prefix;
        if ($submitted) { $tab_name .= SUBMITTED_POSTFIX; }
        $this->_db->query('SELECT * FROM '.$tab_name.' WHERE booking_id = '.$booking_id.' ORDER BY id;');
        $this->numItems = $this->_db->count();
        if ($this->numItems > 0) {
            $this->_data = $this->_db->results();
        } else {
            $this->_data = array();
        }
        // Add empty items so we can use the data in the form generation.
        for ($id = $this->numItems; $id < $this->maxItems; $id++) {
            $res = array();
            foreach(DB_FIELDS[$this->prefix] as $field) {
                if ($field == 'id') { $res['id'] = -1;}
                elseif ($field == 'booking_id') { $res['booking_id'] = $booking_id;}
                else { $res[$field] = null; }
            }
            $this->_data[$id] = (object)$res;
        }
        return true;
    }
    // Get counts of chargable items if they apply
    public function countAttentParty () {
        $num = 0;
        if (!empty($this->_data) AND $this->hasParty) {
            foreach($this->_data as $id => $item) {
                if (property_exists($item, 'party') AND $item->party == 'Y') {$num++;}
            }
        }
        return $num;
    }
    public function countTshirts () {
        $num = 0;
        if (!empty($this->_data) AND $this->hasTshirts) {
            foreach($this->_data as $id => $item) {
                if (property_exists($item, 'shirt_size') AND $item->shirt_size != null AND $item->shirt_size != 'No shirt') {$num++;}
            }
        }
        return $num;
    }

}

// Class for club & contact
class Contacts extends BookingItem {
    public function __construct($show=true, $enabled=true) {
        parent::__construct($show, $enabled);
        $this->title = 'Club';
        $this->prefix = CONTACT_PREFIX;
        $this->minItems = 1;
        $this->maxItems = 1;
    }
    public function getEmail() {
        if (!$this->hasData()) { return 'Unkn'; }
        return $this->getData()[0]->email;
    }
    public function getFirstName() {
        if (!$this->hasData()) { return 'Unkn'; }
        return $this->getData()[0]->first_name;
    }
    public function getClubName() {
        if (!$this->hasData()) { return 'Unkn'; }
        return $this->getData()[0]->club_name;
    }
    // Generate overview html
    public function getOverviewHTMLSection($id,$item,$styles,$email){
        $html = '';
        if ($email) {
            $html .= '<table'.$styles['tabst'].'>';
            $html .=  '<tbody>';
            $html .=   '<tr>';
            $html .=    '<td'.$styles['al_top'].'>'.htmlspecialchars($item->club_name).'<br>';
            $html .=     htmlspecialchars($item->club_address).'<br>';
            $html .=     htmlspecialchars($item->club_zip).' '.htmlspecialchars($item->club_city).'<br>';
            $html .=     getSaveArray(OPTIONS_COUNTRIES, $item->club_country).'<br><br>';
            $html .=     '<strong>Club Contact</strong><br>';
            $html .=     '&nbsp;&nbsp;'.htmlspecialchars($item->first_name).' '.htmlspecialchars($item->last_name).'<br>';
            $html .=     '&nbsp;&nbsp;'.htmlspecialchars($item->address).'<br>';
            $html .=     '&nbsp;&nbsp;'.htmlspecialchars($item->zip).' '.htmlspecialchars($item->city).'<br>';
            $html .=     '&nbsp;&nbsp;'.htmlspecialchars($item->phone).' '.htmlspecialchars($item->email) .'<br>';
            $html .=    '</td>';
            $html .=    '<td'.$styles['al_top'].'>';
            $html .=     'Organized for FH-DCE<br>';
            $html .=     'by '.PM_ORGANIZER.'<br>';
            $html .=     PM_LOCATION.'<br>';
            $html .=     PM_LOCATION_CITY.'<br>';
            $html .=     PM_LOCATION_COUNTRY;
            $html .=    '</td>';
            $html .=   '</tr>';
            $html .=  '</tbody>';
            $html .= '</table>'; 
            $html .= '<br>';
            
        } else {
            $html .= '<div class="row">';
            $html .=  '<div class="col-6">';
            $html .=   '<p>'.htmlspecialchars($item->club_name).'</p>';
            $html .=   '<p>'.htmlspecialchars($item->club_address).'</p>';
            $html .=   '<p>'.htmlspecialchars($item->club_zip).' '.htmlspecialchars($item->club_city).'</p>';
            $html .=   '<p>'.getSaveArray(OPTIONS_COUNTRIES,$item->club_country).'</p>';
            $html .=   '<p class="mt-1"><strong>Club Contact</strong></p>';
            $html .=   '<p>&nbsp;&nbsp;'.htmlspecialchars($item->first_name).' '.htmlspecialchars($item->last_name).'</p>';
            $html .=   '<p>&nbsp;&nbsp;'.htmlspecialchars($item->address).'</p>';
            $html .=   '<p>&nbsp;&nbsp;'.htmlspecialchars($item->zip).' '.htmlspecialchars($item->city).'</p>';
            $html .=   '<p>&nbsp;&nbsp;'.htmlspecialchars($item->phone).' '.htmlspecialchars($item->email) .'</p>';
            $html .=  '</div>';
            $html .=  '<div class="col-2">';
            $html .=   '<img class="overview_logo" src="PM2025sitelogo.png">';
            $html .=  '</div>';
            $html .=  '<div class="hdcc_details col-4">';
            $html .=   '<p>Organized for FH-DCE</p>';
            $html .=   '<p>by '.PM_ORGANIZER.'</p>';
            $html .=   '<p>'.PM_LOCATION.'</p>';
            $html .=   '<p>'.PM_LOCATION_CITY.'</p>';
            $html .=   '<p>'.PM_LOCATION_COUNTRY.'</p>';
            $html .=  '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    // Generate html form section details
    public function getFormHTMLSection($id,$item,$disable) {
        $html = '';
        if ($this->is_locked) {
            $disable = true;
        }
        if (USE_CLUBS_TABLE) {
            $this->_db->query('SELECT name FROM '.FHDCE_CLUBS_PREFIX.' ORDER BY name;');
            $clubs = array();
            foreach ($this->_db->results() as $idx => $club) {
                $clubs[$club->name] = $club->name;
            }
            $clubs[OTHER_CLUB_NAME] = OTHER_CLUB_NAME;
		    $html .= generate_form_row('Club Name', $this->prefix."[$id][club_name]", type:'select', options: $clubs, value: $item->club_name,disabled:$disable); 
        } else {
		    $html .= generate_form_row('Club Name', $this->prefix."[$id][club_name]", place_holder: "Your club name as known with FH-DCE...", max_len: 120, value: $item->club_name,disabled:$disable); 
        }
        $html .= generate_form_row('Club Address', $this->prefix."[$id][club_address]", place_holder: "Your club address. Can be a PO box...", value: $item->club_address); 
        $html .= '<div class="row">';
        $html .= generate_form_row('Club Post code', $this->prefix."[$id][club_zip]", place_holder: "Your club address Post/ZIP code...", width:3, max_len:20, value: $item->club_zip); 
        $html .= generate_form_row('Club city', $this->prefix."[$id][club_city]", place_holder: "Your club address city...", width:5, value: $item->club_city); 
        $html .= generate_form_row('Club country', $this->prefix."[$id][club_country]", type:'select', options: OPTIONS_COUNTRIES, width:4, value: $item->club_country,disabled:$disable); 
        $html .= '</div>';
        $html .= '<div class="col-lg-12"><div class="pt-3 row"></div></div>';
        $html .= '</div>';
        $html .= '<div id="clubContact">';
        $html .= '<h4 class="section_header">Club Contact Details</h4>';
        $html .= '<div class="row">';
        $html .= generate_form_row('First Name', $this->prefix."[$id][first_name]", place_holder: "Booking contact first name...", width:5, min_len:1, max_len:120, value: $item->first_name); 
        $html .= generate_form_row('Last Name', $this->prefix."[$id][last_name]", place_holder: "Booking contact last name...", width:7, max_len:120, value: $item->last_name); 
        $html .= '</div>';
        $html .= generate_form_row('Address', $this->prefix."[$id][address]", place_holder: "Booking contact address...", value: $item->address);
        $html .= '<div class="row">';
        $html .= generate_form_row('Post code', $this->prefix."[$id][zip]", place_holder: "Booking contact Post/ZIP code...", width:3, max_len:20, value: $item->zip); 
        $html .= generate_form_row('City', $this->prefix."[$id][city]", place_holder: "Booking contact city...", width:5, value: $item->city); 
        $html .= generate_form_row('Phone number', $this->prefix."[$id][phone]", width:4, value: $item->phone); 
        $html .= '</div>';
        $html .= generate_form_row('Email', $this->prefix."[$id][email]", type: 'email', place_holder: "Booking contact email address...", value: $item->email);
        return $html;
    }
}

// Class for delegates
class Delegates extends BookingItem {
    public function __construct($show=true, $enabled=true) {
        parent::__construct($show, $enabled);
        $this->title = 'Delegates';
        $this->prefix = DELEGATE_PREFIX;
        $this->hasParty = true;
        $this->hasTshirts = true;
        $this->minItems = MINIMUM_DELEGATES;
        $this->maxItems = MAXIMUM_DELEGATES;
    }

    public function countAttendMeeting () {
        return $this->numItems;
    }
    // Table headers used in overview htmls.
    public function getOverviewHTMLSectionTitle($styles,$email) {
        return '<th'.$styles['al_left'].'>Name</th><th'.$styles['al_left'].'>Position</th><th'.$styles['al_left'].'>t-Shirt</th><th'.$styles['al_left'].'>Attend Party</th>';
    }
    // Create overview html for item detail
    public function getOverviewHTMLSection($id,$item,$styles,$email){
        $html = '';
        $html .=  '<td>'.htmlspecialchars($item->first_name).' '.htmlspecialchars($item->last_name).'</td>';
        $html .=  '<td>'.htmlspecialchars($item->position).'</td>';
        $html .=  '<td>'.getSaveArray(OPTIONS_TSHIRTS_SIZES,$item->shirt_size).'</td>';
        $html .=  '<td>'.getSaveArray(OPTIONS_YESNO,isset($item->party)? $item->party : '').'</td>';
        return $html;
    }

    // Generate html form section details
    public function getFormHTMLSection($id,$item,$disable) {
        $html = '';
        $html .= '<div class="row">';
        $html .= generate_form_row('First Name', $this->prefix."[$id][first_name]", place_holder: 'Delegate first name...', width:5, min_len:1, max_len:120, value: $item->first_name,disabled:$disable); 
        $html .= generate_form_row('Last Name', $this->prefix."[$id][last_name]", place_holder: 'Delegate last name...', width:7, max_len:120, value: $item->last_name,disabled:$disable); 
        $html .= '</div>';
        $html .= '<div class="row">';
        $html .= generate_form_row('Position', $this->prefix."[$id][position]", width: 5, place_holder: 'Delegate position in your club...', max_len:120, value: $item->position,disabled:$disable); 
        if ($this->is_locked) {
            $disable = true;
        }
        $html .= generate_form_row('t-Shirt', $this->prefix."[$id][shirt_size]", type:'select', options: OPTIONS_TSHIRTS_SIZES, width:3, value: $item->shirt_size,disabled:$disable);
        $html .= '</div>';
        $html .= generate_form_row('Attend Saturday Dinner and Party', $this->prefix."[$id][party]", type:'radio', width:5, value: $item->party, options: OPTIONS_YESNO,disabled:$disable);
        return $html;
    }
}

// Class for guests
class Guests extends BookingItem {
    public function __construct($show=true, $enabled=true) {
        parent::__construct($show, $enabled);
        $this->title = 'Guests';
        $this->prefix = GUEST_PREFIX;
        $this->hasParty = true;
        $this->hasTshirts = true;
        $this->minItems = MINIMUM_GUESTS;
        $this->maxItems = MAXIMUM_GUESTS;
    }
    // Table headers used in overview htmls.
    public function getOverviewHTMLSectionTitle($styles, $email) {
        return '<th'.$styles['al_left'].'>Name</th><th'.$styles['al_left'].'>t-Shirt</th><th'.$styles['al_left'].'>Attend Party</th><th'.$styles['al_left'].'>Attend City Tour</th>';
    }
    // Create overview html for item detail
    public function getOverviewHTMLSection($id,$item,$styles,$email){
        $html = '';
        $html .=  '<td>'.htmlspecialchars($item->first_name).' '.htmlspecialchars($item->last_name).'</td>';
        $html .=  '<td>'.getSaveArray(OPTIONS_TSHIRTS_SIZES,$item->shirt_size).'</td>';
        $html .=  '<td>'.getSaveArray(OPTIONS_YESNO,isset($item->party)? $item->party : '').'</td>';
        $html .=  '<td>'.getSaveArray(OPTIONS_YESNO,isset($item->city_tour)? $item->city_tour : '').'</td>';
        return $html;
    }

    // Generate html form section details
    public function getFormHTMLSection($id,$item,$disable) {
        $html = '';
        $html .= '<div class="row">';
        $html .= generate_form_row('First Name', $this->prefix."[$id][first_name]", place_holder: 'Guest first name...', width:5, min_len:1, max_len:120, value: $item->first_name,disabled:$disable); 
        $html .= generate_form_row('Last Name', $this->prefix."[$id][last_name]", place_holder: 'Guest last name...', width:7, max_len:120, value: $item->last_name,disabled:$disable); 
        $html .= '</div>';
        $html .= '<div class="row align-items-center">';
        if ($this->is_locked) {
            $disable = true;
        }
        $html .= generate_form_row('t-Shirt', $this->prefix."[$id][shirt_size]", type:'select', options: OPTIONS_TSHIRTS_SIZES, width:3, value: $item->shirt_size,disabled:$disable);
        $html .= '<div class="col-md-2"></div>';
        $html .= '</div>';
        $html .= generate_form_row('Attend Saturday Dinner and Party', $this->prefix."[$id][party]", type:'radio', width:5, value: $item->party, options: OPTIONS_YESNO,disabled:$disable);
        if (!isset($item->city_tour)) { $item->city_tour = 'N';}
        $html .= generate_form_row('Attend Saturday City Tour', $this->prefix."[$id][city_tour]", type:'radio', width:5, value: $item->city_tour, options: OPTIONS_YESNO,disabled:$disable);
        return $html;
    }
}

// Class for rooms
class Rooms extends BookingItem {
    public function __construct($show=true, $enabled=true) {
        parent::__construct($show, $enabled);
        $this->title = 'Rooms';
        $this->prefix = ROOM_PREFIX;
        $this->minItems = MINIMUM_ROOMS;
        $this->maxItems = MAXIMUM_ROOMS;
    }

    // Clean up room data array prior to storing in db.
    public function cleanData(&$fields) {
        if (array_key_exists('type', $fields)) {
            // Only needed if booking is not locked.
            if ($fields['type'] == 'single') { 
                $fields['guest2'] = '';
//                $fields['guest3'] = '';
//            } elseif ($fields['type'] == 'twin' or $fields['type'] == 'double') { 
//                $fields['guest3'] = '';
            }
        }
    }

    // Add the room number to the database also to submitted when exists.
    public function storeRoomNumber($id, $room_no) {
        $this->_db->update($this->prefix, $id, array('room_no'=>$room_no));
        if ($this->_db->count_in_db($this->prefix.SUBMITTED_POSTFIX, array('id', '=', $id))) {
            $this->_db->update($this->prefix.SUBMITTED_POSTFIX, $id, array('room_no'=>$room_no));
        }
    }

    // Delete the room number from the database
    public function deleteRoomNumber($id) {
        storeRoomNumber($id, '');
    }
    public function getBookedRooms() {
        $booked_rooms = array();
        $this->_db->query('SELECT distinct room_no from '.ROOM_PREFIX.' WHERE room_no IS NOT NULL;');
        if ($this->_db->count() > 0) {
            foreach ($this->_db->results() as $id => $item) {
                $booked_rooms[] = $item->room_no;
            }
        }
        return $booked_rooms;
    }

    // Calculate the total number of staying in given room type(s).
    public function countRoomNights($types) {
        if ($this->numItems == 0) { return [0,0]; }
        $num = 0;
        $nights = 0;
        foreach($this->getData() as $id => $item) {
            if (in_array($item->type, $types)) { 
                $num++;
                $nights += calculate_nights($item->arr_date, $item->dep_date);
            }
        }
        return [$num, $nights];
    }
    // Calculate the number of nights times number of guests for tourist tax.
    public function countTaxNights() {
        if ($this->numItems == 0) { return 0; }
        $num = 0;
        foreach($this->getData() as $id => $item) {
            $nights = calculate_nights($item->arr_date, $item->dep_date);
            if (isset($item->guest1) and $item->guest1 != '') { $num += $nights; }
            if (isset($item->guest2) and $item->guest2 != '') { $num += $nights; }
            if (isset($item->guest3) and $item->guest3 != '') { $num += $nights; }
        }
        return $num;
    }

    // Table headers used in overview htmls.
    public function getOverviewHTMLSectionTitle($styles, $email) {
        return '<th'.$styles['al_left'].'>Arrival Date</th><th'.$styles['al_left'].'>Departure Date</th><th'.$styles['al_left'].'>Room Type</th><th'.$styles['al_left'].'>Nights</th>';
    }
    // Create overview html for item detail
    public function getOverviewHTMLSection($id,$item,$styles,$email){
        $html = '';
        $html .=  '<td style="'.$styles['tab_al_top'].'">'.getSaveArray(OPTIONS_ARR_DATES, $item->arr_date).'<br>';
        $html .=  '&nbsp;&nbsp;'.htmlspecialchars($item->guest1).'<br>';
        if (isset($item->guest2)) { $html .= '&nbsp;&nbsp;'.htmlspecialchars($item->guest2).'<br>'; }
//        if (isset($item->guest3)) { $html .= '&nbsp;&nbsp;'.htmlspecialchars($item->guest3); }
        $html .=  '</td>';
        $html .=  '<td style="'.$styles['al_top'].'">'.getSaveArray(OPTIONS_DEP_DATES,$item->dep_date).'</td>';
        $html .=  '<td style="'.$styles['al_top'].'">'.getSaveArray(OPTIONS_ROOM_TYPES,$item->type).'</td>';
        $html .=  '<td style="'.$styles['al_top'].'">'.calculate_nights($item->arr_date, $item->dep_date).'</td>';
        return $html;
    }

    // Generate html form section details
    public function getFormHTMLSection($id,$item,$disable) {
        if ($this->is_locked) {
            $dis = true;
        } else {
            $dis = $disable;
        }
        $html = '';
        $html .= '<div class="row">';
        $html .= generate_form_row('Arrival Date', $this->prefix."[$id][arr_date]", type:'select', options: OPTIONS_ARR_DATES, width:4, value: $item->arr_date, disabled:$dis);
        $html .= generate_form_row('Departure Date', $this->prefix."[$id][dep_date]", type:'select', options: OPTIONS_DEP_DATES, width:4, value: $item->dep_date, disabled:$dis);
        $html .= generate_form_row('Room Type', $this->prefix."[$id][type]", type:'select', options: OPTIONS_ROOM_TYPES, width:4, value: $item->type, disabled:$dis);
        $html .= '</div>';
        $html .= generate_form_row('Name 1', $this->prefix."[$id][guest1]", value: $item->guest1, options:"listGuestNames", disabled:$disable, pattern:"");
        $html .= generate_form_row('Name 2', $this->prefix."[$id][guest2]", value: $item->guest2, options:"listGuestNames", disabled:$disable, pattern:"");
//		$html .= generate_form_row('Name 3', $this->prefix."[$id][guest3]", value: $item->guest3, options:"listGuestNames", disabled:$disable, pattern:"");
        return $html;
    }
    // Generate the html for the Admin overviews
    public function getAdminOverviewHTML($hasSelect = 0, $ids = null, $clubs = null){
        $html = parent::getAdminOverviewHTML($hasSelect, $ids, $clubs);
        if (USE_ROOMS_TABLE) {
            $html .= '<p>To add a room assignment, select the Room ID in the table above, select the hotel room to assign below, and click the Add button.<br>';
        } else {
            $html .= '<p>To add a room assignment, select the Room ID in the table above enter the hotel room number below, and click the Add button.<br>';
        }
        $html .= 'To remove a room assignment, select the Room ID in the table above, and click the Remove button.</p>';
        $html .= '<div id="main-input" class="container overflow-hidden">';
        $html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
        $html .=   '<input type="hidden" name="prefix" id="prefix" value="'.$this->prefix.'" />';
        $html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
        $html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
        $html .=   '<div class="row">';
        if (USE_ROOMS_TABLE) {
            // Get list of avaible rooms.
            $booking_options = array();
            $booked_rooms = $this->getBookedRooms();
            foreach($ids as $id => $room) {
                if (!in_array($room->id ,$booked_rooms)) {
                    $booking_options[$room->id] = $room->room_no.' '.$room->type.' '.$room->location;
                }
            }
            $html .=    generate_form_row('Room number', "room_no", type:'select', width:2, options: $booking_options);
        } else {
            $html .=    generate_form_row('Room number', "room_no", width:2);
        }
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

// Class for travels
class Travels extends BookingItem {
    public function __construct($show=true, $enabled=true) {
        parent::__construct($show, $enabled);
        $this->title = 'Travels';
        $this->prefix = TRAVEL_PREFIX;
        $this->minItems = MINIMUM_TRAVELS;
        $this->maxItems = MAXIMUM_TRAVELS;
    }

    // Clean up travel data array prior to storing in db.
    public function cleanData(&$fields) {
        if (array_key_exists('arr_type', $fields)) {
            // Only needed if booking is not locked.
            if ($fields['arr_type'] == 'OTH') {
                $fields['arr_airport'] = '';
                $fields['arr_airp_other'] = '';
                $fields['arr_flight_no'] = '';
                $fields['arr_amount'] = 0;
            } elseif ($fields['arr_type'] == 'PLANE') {
                $fields['arr_other'] = '';
            }
        }
        if (array_key_exists('dep_type', $fields)) {
            if ($fields['dep_type'] == 'OTH') {
                $fields['dep_airport'] = '';
                $fields['dep_airp_other'] = '';
                $fields['dep_flight_no'] = '';
                $fields['dep_amount'] = 0;
            } elseif ($fields['dep_type'] == 'PLANE') {
                $fields['dep_other'] = '';
            }
        }
    }

    // We have two sections Arrival & Departure so we must insert Arrival into first header.
    public function getFormHTMLSectionTitle() {
        return $this->title . ' Arrival';
    }
    // Calculate the number of people for transfers 
    public function countTransefers () {
        if ($this->numItems == 0) { return 0; }
        $num = 0;
        foreach($this->getData() as $id => $item) {
            if ($item->arr_type == 'PLANE') { if (is_numeric($item->arr_amount)) { $num += $item->arr_amount; }}
            if ($item->dep_type == 'PLANE') { if (is_numeric($item->dep_amount)) {$num += $item->dep_amount; }}
        }
        return $num;
    }
    // Generate the html for the overview for Travels we cannot use base class function
    public function getOverviewHTML($styles, $email){
        if ($this->numItems == 0) { return ''; }
        $html = '';
        if (!$email) { $html .= '<div class="'.$this->prefix.' container mt-5">'; }
        $html .= '<h3'.$styles['h3'].'>'.$this->title.' Overview</h3>';
        // Arrivals
        $has_plane = 0;
        $has_oth = 0;
        foreach ($this->getData() as $id => $item) {
            if ($item->arr_type == 'OTH') { $has_oth++;
            } elseif ($item->arr_type == 'PLANE') { $has_plane++; }
        }
        if ($has_oth > 0 or $has_plane > 0) {
            $html .= '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
            $html .= '<tr><th'.$styles['al_left'].' colspan="2">Arrival Details</th></tr>';
            $html .= '</thead><tbody>';
            $stripe = true;
            // See arrival travel types we have
            if ($has_plane > 0) {
                foreach ($this->getData() as $id => $item) {
                    if ($item->arr_type == 'PLANE') {
                        $html .= '<tr><th'.$styles['al_left'].'>Travel type</th><th'.$styles['al_left'].'>Airport</th><th'.$styles['al_left'].'>Arrival Date &amp; Time</th><th'.$styles['al_left'].'>Flight No</th><th'.$styles['al_left'].'># People</th></tr>';
                        $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
                        $html .= '<tr'.$strcl.'>';
                        $html .= '<td>'.getSaveArray(OPTIONS_TRAVEL_TYPES,$item->arr_type).'</td><td>'.getSaveArray(OPTIONS_AIRPORTS,$item->arr_airport).'</td>';
                        $html .= '<td>'.getSaveArray(OPTIONS_ARR_DATES,$item->arr_date).'&nbsp;'.$item->arr_time.'</td>';
                        $html .= '<td>'.htmlspecialchars($item->arr_flight_no).'</td><td>'.$item->arr_amount.'</td>';
                        $html .= '</tr>';
                        $stripe = !$stripe;
                    }
                }
            }
            if ($has_oth > 0) {
                foreach ($this->getData() as $id => $item) {
                    if ($item->arr_type == 'OTH') {
                        $html .= '<tr><th'.$styles['al_left'].'>Travel type</th><th'.$styles['al_left'].'>Hotel Check-in Date &amp; Time</th></tr>';
                        $html .= '<tr'.$styles['trstrst'].'><td>'.getSaveArray(OPTIONS_TRAVEL_TYPES,$item->arr_type).'</td><td>'.getSaveArray(OPTIONS_ARR_DATES,$item->arr_date).'&nbsp;'.$item->arr_other.'</td></tr>';
                    }
                }
            }    
            $html .=  '</tbody></table>'; 
        }
        // Departures
        $has_plane = 0;
        $has_oth = 0;
        foreach ($this->getData() as $id => $item) {
            if ($item->dep_type == 'OTH') { $has_oth++;
            } elseif ($item->dep_type == 'PLANE') { $has_plane++; }
        }
        if ($has_oth > 0 or $has_plane > 0) {
            $html .= '<table'.$styles['tabst'].'><thead'.$styles['thdst'].'>';
            $html .= '<tr><th'.$styles['al_left'].' colspan="2">Depature Details</th></tr>';
            $html .= '</thead><tbody>';
            $stripe = true;
            // See arrival travel types we have
            if ($has_plane > 0) {
                foreach ($this->getData() as $id => $item) {
                    if ($item->dep_type == 'PLANE') {
                        $html .= '<tr><th'.$styles['al_left'].'>Travel type</th><th'.$styles['al_left'].'>Airport</th><th'.$styles['al_left'].'>Departure Date &amp; Time</th><th'.$styles['al_left'].'>Flight No</th><th'.$styles['al_left'].'># People</th></tr>';
                        $strcl = ($email AND $stripe) ? $styles['trstrst'] : '';
                        $html .= '<tr'.$strcl.'>';
                        $html .= '<td>'.getSaveArray(OPTIONS_TRAVEL_TYPES,$item->dep_type).'</td><td>'.getSaveArray(OPTIONS_AIRPORTS,$item->dep_airport).'</td>';
                        $html .= '<td>'.getSaveArray(OPTIONS_DEP_DATES,$item->dep_date).'&nbsp;'.$item->dep_time.'</td>';
                        $html .= '<td>'.htmlspecialchars($item->dep_flight_no).'</td><td>'.$item->dep_amount.'</td>';
                        $html .= '</tr>';
                        $stripe = !$stripe;
                    }
                }
            }
            if ($has_oth > 0) {
                foreach ($this->getData() as $id => $item) {
                    if ($item->dep_type == 'OTH') {
                        $html .= '<tr><th'.$styles['al_left'].'>Travel type</th><th'.$styles['al_left'].'>Hotel Check-out Date &amp; Time</th></tr>';
                        $html .= '<tr'.$styles['trstrst'].'><td>'.getSaveArray(OPTIONS_TRAVEL_TYPES,$item->dep_type).'</td><td>'.getSaveArray(OPTIONS_DEP_DATES,$item->dep_date).'&nbsp;'.$item->dep_other.'</td></tr>';
                    }
                }
            }    
            $html .=  '</tbody></table>'; 
        }
        $html .= ($email) ? '<br>' : '</div>'; 
        return $html;
    }
    
    // Generate html form section details
    public function getFormHTMLSection($id,$item,$disable) {
      if ($this->is_locked) {
        $dis = true;
      } else {
        $dis = $disable;
      }
      $html = '';
      $html .= '<div class="row">';
      $html .= generate_form_row('How will you travel', $this->prefix."[$id][arr_type]", type:'select', options: OPTIONS_TRAVEL_TYPES, width:5, value: $item->arr_type,disabled:$dis);
      $html .= generate_form_row('Arrival Date', $this->prefix."[$id][arr_date]", type:'select', options: OPTIONS_ARR_DATES, width:4, value: $item->arr_date,disabled:$dis);
      $html .= generate_form_row('Est. arr. time hotel', $this->prefix."[$id][arr_other]", type:'time', width:3, value: $item->arr_other,disabled:$disable);
      $html .= generate_form_row('Airport arr. Time', $this->prefix."[$id][arr_time]", type:'time', width:3, value: $item->arr_time,disabled:$disable);
      $html .= '</div>';
      $html .= '<div <div id="'.$this->prefix.$id.'arr_planeSection">';
      $html .= '<div class="row">';
      $html .= generate_form_row('Arrival Airport', $this->prefix."[$id][arr_airport]", type:'select', options: OPTIONS_AIRPORTS, width:5, value: $item->arr_airport,disabled:$disable);
      $html .= generate_form_row('Flight number', $this->prefix."[$id][arr_flight_no]", width:4, max_len:20, value: $item->arr_flight_no,disabled:$disable);
      $html .= generate_form_row('Number of people', $this->prefix."[$id][arr_amount]", type:'number', width:3, min_len:1, max_len:20, value: $item->arr_amount,disabled:$dis);
      $html .= ' </div>';
      $html .= '</div>';
      $html .= '<p>&nbsp;</p>';
      $html .= '<p class="pt-3">'.$this->title.' '.($id+1).' Departure details:</p>';
      $html .= '<div class="row">';
      $html .= generate_form_row('How will you travel', $this->prefix."[$id][dep_type]", type:'select', options: OPTIONS_TRAVEL_TYPES, width:5, value: $item->dep_type,disabled:$dis);
      $html .= generate_form_row('Departure Date', $this->prefix."[$id][dep_date]", type:'select', options: OPTIONS_DEP_DATES, width:4, value: $item->dep_date,disabled:$dis);
      $html .= generate_form_row('Est. dep. time hotel', $this->prefix."[$id][dep_other]", type:'time', width:3, value: $item->dep_other,disabled:$disable);
      $html .= generate_form_row('Airport dep. Time', $this->prefix."[$id][dep_time]", type:'time', width:3, value: $item->dep_time,disabled:$disable);
      $html .= '</div>';
      $html .= '<div <div id="'.$this->prefix.$id.'dep_planeSection">';
      $html .= ' <div class="row">';
      $html .= generate_form_row('Departure Airport', $this->prefix."[$id][dep_airport]", type:'select', options: OPTIONS_AIRPORTS, width:5, value: $item->dep_airport,disabled:$disable);
      $html .= generate_form_row('Flight number', $this->prefix."[$id][dep_flight_no]", width:4, max_len:20, value: $item->dep_flight_no,disabled:$disable);
      $html .= generate_form_row('Number of people', $this->prefix."[$id][dep_amount]", type:'number', width:3, min_len:1, max_len:20, value: $item->dep_amount,disabled:$dis);
      $html .= ' </div>';
      $html .= '</div>';
      return $html;
    }

  // Generate the html for the Admin overviews
  public function getAdminOverviewHTML($hasSelect = 0, $ids = null, $clubs = null){
    $html = '';
    $sql1 = 'SELECT * FROM '.$this->prefix.' WHERE `arr_type` = ? ORDER BY `booking_id`;';
    $sql2 = 'SELECT * FROM '.$this->prefix.' WHERE `dep_type` = ? ORDER BY `booking_id`;';
    $this->_db->query($sql1, array('OTH'));
    if ($this->_db->count() > 0) {
      $html .= '<h3 class="section_header">Travel Arrival Other Details</h3>';
      $html .= '<table class="table table-striped table-sm">';
      $html .=  '<thead class="thead-dark"><tr><th>Booking ID - Club name</th><th>Arrival ETA</th></tr></thead>';
      $html .=  '<tbody>';
      foreach ($this->_db->results() as $idx => $item) {
        $html .= '<tr><td>'.($item->booking_id).' - '.getSaveArray($clubs, $item->booking_id).'</td><td>'.($item->arr_other).'</td></tr>';
      }
      $html .= '</tbody></table><p />';
    }
    $this->_db->query($sql1, array('PLANE'));
    if ($this->_db->count() > 0) {
      $html .= '<h3 class="section_header">Travel Arrival Transfer Details</h3>';
      $html .= '<table class="table table-striped table-sm">';
      $html .=  '<thead class="thead-dark"><tr><th>Booking ID - Club name</th><th>Airport</th><th>Flight #</th><th>Arrival Date</th><th>Arrival Time</th><th>Number</th></tr></thead>';
      $html .=  '<tbody>';
      foreach ($this->_db->results() as $idx => $item) {
        $html .= '<tr><td>'.($item->booking_id).' - '.getSaveArray($clubs, $item->booking_id).'</td><td>'.($item->arr_airport).'</td><td>'.($item->arr_flight_no).'</td><td>'.($item->arr_date).'</td><td>'.($item->arr_time).'</td><td>'.($item->arr_amount).'</td></tr>';
      }
      $html .= '</tbody></table><p />';
    }
    $this->_db->query($sql2, array('OTH'));
    if ($this->_db->count() > 0) {
      $html .= '<h3 class="section_header">Travel Departure Other Details</h3>';
      $html .= '<table class="table table-striped table-sm">';
      $html .=  '<thead class="thead-dark"><tr><th>Booking ID - Club name</th><th>Departure ETD</th></tr></thead>';
      $html .=  '<tbody>';
      foreach ($this->_db->results() as $idx => $item) {
        $html .= '<tr><td>'.($item->booking_id).' - '.getSaveArray($clubs, $item->booking_id).'</td><td>'.($item->dep_other).'</td></tr>';
      }
      $html .= '</tbody></table><p />';
    } 
    $this->_db->query($sql2, array('PLANE'));
    if ($this->_db->count() > 0) {
      $html .= '<h3 class="section_header">Travel Departure Transfer Details</h3>';
      $html .= '<table class="table table-striped table-sm">';
      $html .=  '<thead class="thead-dark"><tr><th>Booking ID - Club name</th><th>Airport</th><th>Flight #</th><th>Departure Date</th><th>Departure Time</th><th>Number</th></tr></thead>';
      $html .=  '<tbody>';
      foreach ($this->_db->results() as $idx => $item) {
        $html .= '<tr><td>'.($item->booking_id).' - '.getSaveArray($clubs, $item->booking_id).'</td><td>'.($item->dep_airport).'</td><td>'.($item->dep_flight_no).'</td><td>'.($item->dep_date).'</td><td>'.($item->dep_time).'</td><td>'.($item->dep_amount).'</td></tr>';
      }
      $html .= '</tbody></table>';
    }
    return $html;
  }
}

// Class for payments
class Payments extends BookingItem {
    public function __construct($show=true, $enabled=true) {
      parent::__construct($show, $enabled);
      $this->title = 'Payment';
      $this->prefix = PAYMENT_PREFIX;
      $this->minItems = 1;
      $this->maxItems = 1;
    }
    // Store the payment in the database
    public function saveAdminFormData($booking_id, $date, $description, $amount) {
      if ($amount != 0) {
        $this->_db->insert($this->prefix,array('booking_id'=>$booking_id, 'date'=>$date, 'description'=>$description, 'amount'=>$amount));
      }
    }
    // Delete the payment from the database
    public function deleteFromDB($id) {
      $this->_db->delete($this->prefix, array('id', '=', $id));
    }

    // Generate the html for the Admin overviews
    public function getAdminOverviewHTML($hasSelect = 0, $ids = null, $clubs = null){
      $booking_options = array();
      foreach($ids as $id) {
        $booking_options[$id] = $id.' - '.getSaveArray($clubs,$id);
      }
      $html = parent::getAdminOverviewHTML($hasSelect, $ids, $clubs);
      $html .= '<p>To add a payment, select the Booking ID enter the payment date and amount, and click the Add button.<br>';
      $html .= 'To remove a payment, select the payment, and click the Remove button.</p>';
      $html .= '<div id="main-input" class="container overflow-hidden">';
      $html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
      $html .=   '<input type="hidden" name="prefix" id="prefix" value="'.$this->prefix.'" />';
      $html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
      $html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
      $html .=   '<div class="row">';
      $html .=    generate_form_row('Booking ID', "booking_id", type:'select', width:2, options: $booking_options);
      $html .=    generate_form_row('Date', "date", type:'date', width:2);
      $html .=    generate_form_row('Description', "description", width:6);
      $html .=    generate_form_row('Amount', "amount", type:'number', width:2, options: '0.01', max_len:15000);
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

// Class for rooms
class HotelRooms extends BookingItem {
    public function __construct($show=true, $enabled=true) {
      parent::__construct($show, $enabled);
      $this->title = 'Hotel Rooms';
      $this->prefix = HOTEL_ROOMS_PREFIX;
      $this->minItems = 1;
      $this->maxItems = 1;
    }

    // Add the room number to the database also to submitted when exists.
    public function storeRoom($id, $room_no, $type, $location,$avaible) {
      if ($type == '') { $type = array_key_first(OPTIONS_ROOM_TYPES); }
      if ($location == '') { $location = array_key_first(OPTIONS_ROOM_LOCATIONS); }
      if ($avaible == '') { $avaible = 1; }
      if ($id == '') {
        $this->_db->insert($this->prefix, array('room_no'=>$room_no, 'type'=>$type, 'location'=>$location, 'available'=>$avaible));
      } else {
        $this->_db->update($this->prefix, $id, array('room_no'=>$room_no, 'type'=>$type, 'location'=>$location, 'available'=>$avaible));
      }
    }

    // Delete the room number from the database
    public function deleteRoom($id) {
      $this->_db->delete($this->prefix, array('id', '=', $id));
    }

    // Load table of existing room numbers from DB.
    public function getHotelRoomNumbers() {
      $this->_db->query('SELECT * from '.$this->prefix.' WHERE available = 1 ORDER BY location, cast(room_no as unsigned);');
      if ($this->_db->count() > 0) {
        $this->_data = $this->_db->results();
        return $this->_data;
      }
      return false;
    }

    // Find teh details of an hotel room to display. Either just the room number, or the mapping hotel_rooms entry.
    public function getHotelRoomByID($id) {
      if (USE_ROOMS_TABLE) {
        // Fetch dat if not yet loaded.
        if (empty($this->_data)) {
          $this->getHotelRoomNumbers();
        }
        $room = null;
        if ($id != '') {
          foreach($this->_data as $rm_id => $rm) {
            if ($id == $rm->id) {
              $room = $rm;
              break;
            }
          }
        }
        if ($room != null) {
          return $room->room_no.' '.$room->location;
        } else {
          return null;
        }
      } else {
        return $id;
      }
    }

    // Generate the html for the Admin overviews
    public function getAdminOverviewHTML($hasSelect = 0, $ids = null, $clubs = null){
      // List hotel rooms known
      $html = '';
      $html .=  '<h3 class="section_header">Hotel Rooms Details</h3>';
      $html .=  '<table class="table table-striped table-sm table_adm">';
      $html .=   '<thead class="thead-dark">';
      $html .=     '<tr><th>Room Number</th><th>Type</th><th>Location</th><th>Available</th></tr>';
      $html .=    '</thead>';
      $html .=   '<tbody>';
      foreach($this->getHotelRoomNumbers() as $id => $room) {
        $html .= '<tr>';
        $html .=  '<td><input type="radio" class="item" name="select_id" value="'.($room->id).'" /> '.$room->room_no.'</td>';
        $html .=  '<td>'.$room->type.'</td>';
        $html .=  '<td>'.$room->location.'</td>';
        $html .=  '<td>'.(($room->available == 1) ? 'Yes':'No').'</td>';
        $html .= '</tr>';
      }
      $html .=  '</tbody></table>';

      // Add buttons and add form
      $html .= '<p>To add a room, enter the details below, and click the Add button.<br>';
      $html .= 'To change room details, select the Room ID enter in the table above, enter the new details below, and click the Add button.<br>';
      $html .= 'To remove a room number, select the Room ID in the table above, and click the Remove button.</p>';
      $html .= '<div id="main-input" class="container overflow-hidden">';
      $html .=  '<form id="adminform" autocomplete="off" accept-charset="utf-8" class="align-items-center">';
      $html .=   '<input type="hidden" name="prefix" id="prefix" value="'.$this->prefix.'" />';
      $html .=   '<input type="hidden" name="submit_action" id="submit_action" />';
      $html .=   '<input type="hidden" name="selected_ids" id="selected_ids" />';
      $html .=   '<div class="row">';
      $html .=    generate_form_row('Room number', "room_no", type:'number', width:2);
      $html .=    generate_form_row('Type', "type", type:'select', width:3, options: OPTIONS_ROOM_TYPES);
      $html .=    generate_form_row('Location', "location", type:'select', width:3, options: OPTIONS_ROOM_LOCATIONS);
      $html .=    generate_form_row('Available', "available", type:'select', width:2, options: OPTIONS_YESNO);
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