<?php

// Inputs field length limitations
const USER_NAME_MIN_LENGTH = 5;
const USER_NAME_MAX_LENGTH = 55;
const USER_PWD_MIN_LENGTH = 8;
const USER_PWD_MAX_LENGTH = 55;
const USER_EMAIL_MAX_LENGTH = 100;

const SESSION_NAME = 'FHDCE_PM_REG_user_id';
 
// Details about the site
const SITE_NAME = 'https://pm2025.hdc-centrum.nl/';
const SITE_LOGON_PAGE = SITE_NAME;  // Page to send not logged in user
const SITE_REGISTER_PAGE = SITE_NAME . 'register.php';  // Page to send not logged in user
const SITE_BOOK_PAGE = SITE_NAME . 'booking.php'; // Page to redirect users to after login
const SITE_ADMIN_PAGE = SITE_NAME . 'admin.php'; // Page to redirect admin users to after login
const SITE_TITLE = 'H-DC &rsquo;t Centrum PM 2025'; // Name of this site, used in email and headers
const SITE_TITLE_SHORT = 'PM 2025'; // Name of this site, used pages headers
const SITE_EMAIL = 'pm2025@hdc-centrum.nl'; // email of sender, used in email
const USE_TEST_SMTP = false;  // If true, site email address is excluded in emails send. Use for testing.

// Details about the PM
const PM_ORGANIZER = 'Harley-Davidson Club &rsquo;t Centrum';
const PM_ORGANIZER_URL = 'https://www.hdc-centrum.nl';
const PM_COUNTRY = 'the Netherlands';
const PM_TITLE = 'FH-DCE Presidents&rsquo; meeting 2025';
const PM_LOCATION = 'Hotel van der Valk';
const PM_LOCATION_CITY = 'Vianen';
const PM_LOCATION_COUNTRY = 'the Netherlands';
const PM_LOCATION_ADDRESS = 'Prins Bernhardstraat 75, 4132XE Vianen';
const PM_CURRENCY = '&euro;';  // HTML for used currency symbol
const PM_LOGO = 'PM2025sitelogo.png'; // logo to use
const PM_FHDCE_LOGO = 'FHDCElogo.png'; // Federation logo to use

// Minimums and maximums for booking items. Can be overruled for a booking in the table. 
const MINIMUM_DELEGATES = 1;
const MINIMUM_GUESTS = 0;
const MINIMUM_ROOMS = 0;
const MINIMUM_TRAVELS = 0;
const MAXIMUM_DELEGATES = 3;
const MAXIMUM_GUESTS = 10;
const MAXIMUM_ROOMS = 6;
const MAXIMUM_TRAVELS = 4;

// Cut off dates and bank details.
const ACCOUNT_REGISTRATION_LOCKED = false;      // When true no new accounts can be created.
const ALL_BOOKINGS_LOCKED = false;              // When true no changes to any booking can be made. Users can only see overview.
const LAST_CHANGE_DATE = 'August 8th 2025';
const REGISTRATION_CLOSE_DATE = 'August 8th 2025';
const PAYMENT_DATE = 'AUGUST 15th 2025';
const IBAN_BANK_ACCOUNT = 'NL51RABO0108112233';
const IBAN_ACCOUNT_NAME = 'Friends of H-DCC, Van Teylingenweg 188 3471 GK Kamerik, the Netherlands';
const IBAN_BANK_BIC = 'RABONL2U';
const PAYPAL_ID = null; // 'paypal@sfo.hdc-centrum.nl tnv Stichting Friends of H-DCC';

// Prices including VAT.
const SINGLE_ROOM_PRICE = array ('price' => 175, 'vat' => 0.09);
const TWIN_ROOM_PRICE = array ('price' => 185, 'vat' => 0.09);
const TRIPLE_ROOM_PRICE = array ('price' => 285, 'vat' => 0.09);
const TSHIRT_PRICE = array ('price'=> 25 , 'vat' => 0.21);
const MEETING_PRICE = array ('price' => 50, 'vat' => 0.21);
const PARTY_PRICE = array('price'=> 99.50, 'vat' => 0.21);
const TRANSFER_PRICE = array ('price' => 5, 'vat' => 0.21);
const EXTRA_PRICE = array ('price' => 0, 'vat' => 0.21);
const TOURIST_TAX_PPPD = array ('price' => 2.30, 'vat' => 0.0);  // https://www.vijfheerenlanden.nl/toeristenbelasting

// If true we use the room details defined in hotel_rooms for room selection. If false it is a free value to enter in Rooms admin form.
const USE_ROOMS_TABLE = true;
// If true we use the club names as defined in fhdce_clubs as contact club_name selection. If false it is a free value to enter in the booking form.
const USE_CLUBS_TABLE = true;
const OTHER_CLUB_NAME = 'Other (enter in comments)';

// List values
const OPTIONS_TSHIRTS_SIZES = array('No shirt'=>'No shirt', 'Male S'=>'Male S', 'Male M'=>'Male M', 'Male L'=>'Male L', 'Male XL'=>'Male XL', 'Male XXL'=>'Male XXL', 'Male 3XL'=>'Male 3XL', 
        'Male 4XL'=>'Male 4XL', 'Female S'=>'Female S', 'Female M'=>'Female M', 'Female L'=>'Female L', 'Female XL'=>'Female XL', 'Female 2XL'=>'Female 2XL', 'Female 3XL'=>'Female 3XL');
const OPTIONS_GENDERS = array('M'=>'Male','F'=>'Female','X'=>'X');
const OPTIONS_YESNO = array('Y'=>'Yes','N'=>'No');
const OPTIONS_ROOM_LOCATIONS = array('Vianen'=>'Vianen', 'Houten' => 'Houten');
const OPTIONS_ROOM_TYPES = array('single'=>'Single', 'double' => 'Double', 'twin'=>'Twin');
const OPTIONS_COUNTRIES = array('at'=>'Austria','be'=>'Belgium','bg'=>'Bulgaria','cz'=>'Czech Republic','cy'=>'Cyprus','dk'=>'Denmark','ee'=>'Estonia','eu'=>'Europe','fi'=>'Finland','fr'=>'France',
        'de'=>'Germany','gi'=>'Gibraltar','gr'=>'Greece','is'=>'Iceland','ie'=>'Ireland','it'=>'Italy','lt'=>'Lithuania','lu'=>'Luxembourg','nl'=>'Netherlands','no'=>'Norway',
        'mt'=>'Malta','mc'=>'Monaco','pl'=>'Poland','ru'=>'Russia','sk'=>'Slovakia','ch'=>'Switzerland','se'=>'Sweden','es'=>'Spain','gb-eng'=>'England','gb-sct'=>'Scotland','gb-nir'=>'Northern Ireland','gb-wls'=>'Wales',
        'eu'=>'Europe','xx'=>'Other, enter in comments');


// Travel options
const OPTIONS_TRAVEL_TYPES = array('PLANE'=>'Airport with transfer', 'OTH'=>'Own transport to/from hotel');
const OPTIONS_ARR_DATES = array('2025-11-12'=>'Wednesday Nov 12th', '2025-11-13'=>'Thursday Nov 13th', '2025-11-14'=>'Friday Nov 14th', '2025-11-15'=>'Saturday Nov 15th');
const OPTIONS_DEP_DATES = array('2025-11-15'=>'Saturday Nov 15th', '2025-11-16'=>'Sunday Nov 16th', '2025-11-17'=>'Monday Nov 17th');
const OPTIONS_AIRPORTS = array('AMS'=>'Amsterdam Schiphol (AMS)', 'RTM'=>'Rotterdam The Hague', 'EIN'=>'Eindhoven');
const OPTIONS_ARR_TRANF_DATES = array('2025-11-13'=>'Thursday Nov 13th', '2025-11-14'=>'Friday Nov 14th', '2025-11-15'=>'Saturday Nov 15th');
const OPTIONS_DEP_TRANF_DATES = array('2025-11-15'=>'Saturday Nov 15th', '2025-11-16'=>'Sunday Nov 16th', '2025-11-17'=>'Monday Nov 17th');

// Prefixes used for form field names and html id's. Also the DB table names used except for comment, removed, party and tshirt.
const USERS_PREFIX = 'users';
const BOOKING_PREFIX = 'booking';
const CONTACT_PREFIX = 'contact';
const DELEGATE_PREFIX = 'delegate';
const GUEST_PREFIX = 'guest';
const ROOM_PREFIX = 'room';
const TRAVEL_PREFIX = 'travel';
const PAYMENT_PREFIX = 'payment';
const HOTEL_ROOMS_PREFIX = 'hotel_rooms';
const FHDCE_CLUBS_PREFIX = 'fhdce_clubs';
const COMMENT_PREFIX = 'comment';
const REMOVED_PREFIX = 'removed';
const PARTY_PREFIX = 'party';
const TSHIRT_PREFIX = 'tshirt';
const EXTRA_PREFIX = 'extra';
const OVERVIEW_PREFIX = 'overview';
const SUBMITTED_POSTFIX = '_submitted';

// Database table definitions. Excludes the table id primary key that each table has unless mentioned.
// To store a submitted booking we have copies of the tables booking, contact, delegate, guest, room and travel.
const USERS_FIELDS = array('id', 'name', 'password', 'status', 'email', 'active', 'actcode', 'salt', 'joined', 'last_active', 'admin');
const BOOKING_FIELDS = array('id','user_id', 'creation_date', 'modified_date', 'submitted_date', 'submitted_count', 'additional_descr', 'additional_costs','min_delegates','max_delegates', 'max_guests', 'max_rooms', 'max_travels', 'comments', 'is_locked');
const CONTACT_FIELDS = array('id','booking_id', 'club_name', 'club_address', 'club_zip', 'club_city', 'club_country', 'first_name', 'last_name', 'address', 'zip', 'city', 'email', 'phone');
const DELEGATE_FIELDS = array('id','booking_id', 'first_name', 'last_name', 'position', 'shirt_size', 'party');
const GUEST_FIELDS = array('id','booking_id', 'first_name', 'last_name', 'shirt_size', 'party', 'city_tour');
const ROOM_FIELDS = array('id','booking_id', 'arr_date', 'dep_date', 'type', 'room_no', 'guest1', 'guest2');
const TRAVEL_FIELDS = array('id','booking_id', 'arr_type', 'arr_other', 'arr_airport', 'arr_airp_other', 'arr_flight_no', 'arr_date', 'arr_time', 'arr_amount', 'dep_type', 'dep_other', 'dep_airport', 'dep_airp_other', 'dep_flight_no', 'dep_date', 'dep_time', 'dep_amount');
const PAYMENT_FIELDS = array('id','booking_id', 'date', 'description', 'amount');
const HOTEL_ROOMS_FIELDS = array('id','room_no', 'type', 'location', 'available');
const FHDCE_CLUBS_FIELDS = array('id','name', 'cc');
// Map in mutidimentional table for easy use in code.
const DB_FIELDS = array(BOOKING_PREFIX=>BOOKING_FIELDS, CONTACT_PREFIX=>CONTACT_FIELDS, DELEGATE_PREFIX=>DELEGATE_FIELDS, GUEST_PREFIX=>GUEST_FIELDS, 
                        ROOM_PREFIX=>ROOM_FIELDS, TRAVEL_PREFIX=>TRAVEL_FIELDS, PAYMENT_PREFIX=>PAYMENT_FIELDS, HOTEL_ROOMS_PREFIX=>HOTEL_ROOMS_FIELDS);

?>