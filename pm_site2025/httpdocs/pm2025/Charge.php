<?php
/* Charge class.
    To store and calculate charges with VAT
*/
require_once 'config.php';
//require_once 'BookingClass.php';


class Charge {
    protected $price, $vatperc, $total, $vat, $quantity;
    public function __construct() {
        $this->price = 0;
        $this->quantity = 0;
        $this->vatperc = 0;
    }

    public function getPrice(){
        return $this->price;
    }
    public function getVATPercentage(){
        return ($this->vatperc * 100);
    }
    public function getTotal(){
        return $this->total;
    }
    public function getVAT(){
        return $this->vat;
    }
    public function getQuantity(){
        return $this->quantity;
    }
}

class RoomCharge extends Charge {
    protected $nights;
    public function getNights(){
        return $this->nights;
    }
}

class MeetingCharge extends Charge {
    public function __construct(&$delegates) {
        $this->price = MEETING_PRICE['price'];
        $this->vatperc = MEETING_PRICE['vat'];

        // Determine quantity.
        $quant = $delegates->countAttendMeeting();
    
        // Calculate the charges and VAT
        $this->total = $this->price * $quant;
        $this->quantity = $quant;
    }
}
class PartyCharge extends Charge {
    public function __construct(&$delegates, &$guests) {
        $this->price = PARTY_PRICE['price'];
        $this->vatperc = PARTY_PRICE['vat'];

        // Determine quantity.
	    $quant = $delegates->countAttentParty() + $guests->countAttentParty();

        // Calculate the charges and VAT
        $this->total = $this->price * $quant;
        $this->quantity = $quant;
    }
}
class TshirtCharge extends Charge {
    public function __construct(&$delegates, &$guests) {
        $this->price = TSHIRT_PRICE['price'];
        $this->vatperc = TSHIRT_PRICE['vat'];

        // Determine quantity.
	    $quant = $delegates->countTshirts() + $guests->countTshirts();
    
        // Calculate the charges and VAT
        $this->total = $this->price * $quant;
        $this->quantity = $quant;
    }
}
class TransferCharge extends Charge {
    public function __construct(&$travels) {
        $this->price = TRANSFER_PRICE['price'];
        $this->vatperc = TRANSFER_PRICE['vat'];

        // Determine quantity.
	    $quant = $travels->countTransefers();
        
        // Calculate the charges and VAT
        $this->total = $this->price * $quant;
        $this->quantity = $quant;
    }
}
class TouristTaxCharge extends Charge {
    public function __construct(&$rooms) {
        $this->price = TOURIST_TAX_PPPD['price'];
        $this->vatperc = TOURIST_TAX_PPPD['vat'];

        // Determine quantity.
        $quant = $rooms->countTaxNights();
            
        // Calculate the charges and VAT
        $this->total = $this->price * $quant;
        $this->quantity = $quant;
    }
}
class ExtrasCharge extends Charge {
    public function __construct(&$booking) {
        [$desc, $price] = $booking->getExtras();
        $this->price = $price;
        $this->vatperc = EXTRA_PRICE['vat'];
        $this->total = $this->price;
        $this->quantity = ($price != 0) ? 1 : 0;
    }
}

class SingleRoomCharge extends RoomCharge {
    public function __construct(&$rooms) {
        $this->price = SINGLE_ROOM_PRICE['price'];
        $this->vatperc = SINGLE_ROOM_PRICE['vat'];

        // Determine quantity.
        [$quant, $nights] = $rooms->countRoomNights(array('single'));
            
        // Calculate the charges and VAT
        $this->total = $this->price * $nights;
        $this->quantity = $quant;
        $this->nights = $nights;
    }
}
class TwinRoomCharge extends RoomCharge {
    public function __construct($rooms) {
        $this->price = TWIN_ROOM_PRICE['price'];
        $this->vatperc = TWIN_ROOM_PRICE['vat'];

        // Determine quantity.
        [$quant, $nights] = $rooms->countRoomNights(array('double', 'twin'));
            
        // Calculate the charges and VAT
        $this->total = $this->price * $nights;
        $this->quantity = $quant;
        $this->nights = $nights;
    }
}
class TripleRoomCharge extends RoomCharge {
    public function __construct($rooms) {
        $this->price = TRIPLE_ROOM_PRICE['price'];
        $this->vatperc = TRIPLE_ROOM_PRICE['vat'];

        // Determine quantity.
        [$quant, $nights] = $rooms->countRoomNights(array('triple'));
            
        // Calculate the charges and VAT
        $this->total = $this->price * $nights;
        $this->quantity = $quant;
        $this->nights = $nights;
    }
}

?>