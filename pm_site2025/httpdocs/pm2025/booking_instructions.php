<?php 
// We are after booking cut-off date.
if (ALL_BOOKINGS_LOCKED) {
?>
<p align="justify">As it is now very close to the start of this year's Presidents' meeting, so it is no longer possible to make any changes to your booking.<br>
	You can still look at your booking overview and validate that we processed your payments.
</p>
<?php
} else {
	// Booking is unlocked, give full instructions.
?>
<p align="justify">Please read these instructions carefully before proceeding. They will tell you how the best complete your booking for the <?= PM_TITLE ?>.
	On this booking site you will enter all details for your booking for this year's Presidents' meeting and send them to the organizer, <?= PM_ORGANIZER ?>.
	Based on your entered information we will arrange your seat(s) in the meeting and if you like, for a fantastic party amongst friends, a room to sleep it off and a t-Shirt to remember it all.
</p>
<p align="justify">	You can change your booking details at any time, but all details must be complete and submitted <strong><em>before</em> <?=REGISTRATION_CLOSE_DATE?></strong>. 
	After this date it is no longer possible to change or adjust your details and you risk being excluded from this year&rsquo;s Presidents&rsquo; Meeting.
</p>
<p align="justify">Only one (1) booking can be created for your club. If you cannot enter all details, like number of guests, rooms, etc. please contact us and we will get you sorted.
</p>
<p align="justify"><strong>The system will log you out after 25 minutes of inactivity. Please make sure to save your booking frequently.</strong></p>
<p align="justify">We will use the details from your last submitted booking. When you just save an update, we will not see the change untill you click submit. 
	So please make sure the status line at the top of the page says <em>Submitted</em> if you are done.
</p>
<p align="justify"><strong>Menu</strong>:<br>
	The <span class="instructions-menu-large"> menu shown at the left of the page</span><span class="instructions-menu-small">hamburger menu at the top</span>
	has the following options to make it easier to create and complete your booking successfully.
</p>
<ul>
	<li><strong>Instructions</strong>: will show you this page.</li>
	<li><strong>Save</strong>: save the details you entered thus far. Use as often as you like.</li>
	<li><strong>Validate</strong>: check your booking data to be complete.</li>
	<li><strong>Overview</strong>: shows an overview of your booking including the costs and any payments we received thus far.</li>
	<li><strong>Submit</strong>: will send your booking to us. You will also receive a confirmation email with your booking and payment details.</li>
	<li><strong>Logout</strong>: logs you out of this booking system. Make sure you have saved your details first.</li>
</ul>
<?php
	if ($booking->getLocked()) {
	// Booking is locked. Show those instructions.
	?>
<p align="justify"><strong>Your booking is locked</strong>.<br>You can only make changes that do not change the booking costs.<br>
	You can still change some of your club details, the names of attendees, hotel room assignments (if applicable), and flight and arrival time details (if applicable).
</p>
<p align="justify">If you make any changes that are still allowed, please do not forget to submit your updated booking again.
</p>
<p align="justify">It is not possible to change the number of delegates, guests, t-Shirts, party attendance, number of hotel rooms and types (if applicable) or number of people for airport transfer (if applicable).
</p>
<p align="justify">You can still look at your booking overview. This can be especially useful to look at the payment details or double check your booking.
</p>
<p align="justify">Should you have a need to make changes to the items not locked, please contact us. <a href="mailto:<?=SITE_EMAIL?>">Click here to email us.</a><br>
	We can temporary grand access to all items again to accommodate you.
</p>
<?php
	} else {
	// Booking is unlocked, give full instructions.
?>
<p align="justify">Before you start it is best to have the names of all attending and any flight details at hand.<br>
	You will start by entering your club's details and those of the main contact for the booking.<br>
	Next enter the details of your first delegate. When more delegates will attend the meeting click <strong>+ Add Delegate</strong>. You can add up to <?= $maximum_delegates ?> delegates. 
	To remove a delegate, click <strong>- Remove Delegate</strong>.<br>
	You can bring more members of your club to the party. Click <strong>+ Add Guest</strong> for all additional people that will join. You can add up to <?= $maximum_guests ?> guests.
	To remove a guest, click <strong>- Remove Guest</strong>.<br>
	For each delegate and guest you can indicate if they will join the dinner party at Saturday night or not, and select the desired t-Shirt size.<br>
  On Saturday during the day we will organize a City Tour in the city of Utrecht walking past special monuments, artistic places, cozy hotspots and the cities hidden gems. Of course we also go a little off the beaten track. 
  Please indicate if you are interested so we have an idea of how many plan to join. It is not a commitment.
</p>
<p align="justify">We have arranged a good deal with the hotels for the rooms. If you are staying at the PM Hotels, <?= PM_LOCATION ?> in <?= PM_LOCATION_CITY ?> or Houten,
	Click on <strong>+Add Room</strong>, select from the allowed arrival and departure days, the room type; Double, Twin or Singe, and enter the names of the persons staying in the room. 
	Please note the number of single rooms is limited. You can add up to <?= $maximum_rooms ?> rooms. 
	To remove a room, click <strong>- Remove Room</strong>.<br>
	Please note that you will need to report to the PM organizer at check-in and <u>not</u> with the hotel reception.
</p>
<p align="justify">For people staying at the PM Hotels we can arrange transfer from the airport to the hotels and back after the event. For that click <strong>+Add Travel</strong>
	and select "Airport with Transfer". then select the airport and your flight details. We will then pick you up at the selected airport to bring you to the PM Hotels. 
	If you want, we can also arrange a transfer from the PM Hotels to the airport after the event.<br>
	When you are staying at the PM Hotels, but do not need the transfer service please select "Own transport to/from hotel" and enter your expected time of arrival at the hotel. 
	This will help us to make your arrival a smooth experience.<br>
	You can add up to <?= $maximum_travels ?> travels. To remove a travel, click <strong>- Remove Travel</strong>.
	Please do not specify Travels if you are not staying at the PM Hotels.
</p>
<p align="justify">At the end of the page, you will find the Comments section. Please use this for anything you could not specify above. You can use it to order more t-Shirts, etc.
	In case you want to join with more people than you are allowed to enter you can request addition. Please indicate in the comments how many people and we will reach out to you to 
	look at the possibilities.
</p>
<p align="justify">If you have not done so click the "Save" button or select "Save" from the menu to store all information your entered. 
	To see if you have entered all required information use the Validate menu option. This will tell you if it is complete or not. If not, the missing information will be flagged with a red marker.
	To get a quick overview of your booking select the "Overview" menu item. This gives you a good view of your booking and the Charges section will show the complete costs of your booking.
	If you have made any payments, you will see them in the Overview as well.
</p>
<p align="justify">When your booking is complete click on the "Submit" button. This will save your details, do a validation and then send you an email with the booking confirmation and 
	further instructions.<br>
	You can make any changes to your booking at any time until <?=REGISTRATION_CLOSE_DATE?>. 
	Once you made your payment, we kindly ask you to limit any changes to names and or t-Shirt sizes only.<br>
	Note that the last number of your booking reference will increase each time you submit your booking to us and made changes.
</p>
<p align="justify">Should you have any questions regarding the booking form, the rules
	stated above or if you encounter any difficulties in completing the
	form, please contact us <a href="mailto:<?=SITE_EMAIL?>">(Click here to email us.)</a>
</p>
<?php
	}
}
	// Instrucitons below apply to locked and unlocked.
?>
<p align="justify">If one week before the booking closure date you have not submitted your booking, we will send you a reminder email. 
	We will also remind you if we did not receive full payments one week before the cut-ff date.
</p>
<p align="justify"><strong>Rates</strong>:<br>
	The following rates apply to this year's Presidents' meeting. The meeting is mandatory for at least one delegate from your club. The rest is optional. The rates shown are including VAT.
</p>
<ul>
	<li>Meeting attendance: <?= format_charge(MEETING_PRICE['price']) ?> per delegate.</li>
	<li>Saturday dinner &amp; Party: <?= format_charge(PARTY_PRICE['price']) ?> per person.</li>
	<li>Presidents' meeting t-Shirt: <?= format_charge(TSHIRT_PRICE['price']) ?> each.</li>
	<li>Twin/double room at hotel: <?= format_charge(TWIN_ROOM_PRICE['price']) ?> per room per night.</li>
	<li>Single room at hotel: <?= format_charge(SINGLE_ROOM_PRICE['price']) ?> per room per night.</li>
	<li>Transfer from selected airports to/from PM Hotel: <?= format_charge(TRANSFER_PRICE['price']) ?> per person per trip.</li>
	<li>Tourist Tax PM Hotel: <?= format_charge(TOURIST_TAX_PPPD['price']) ?> per person per night.</li>
</ul>
<p align="justify"><strong>Payments</strong>:<br>
	The full amount as shown in your booking confirmation will have to be transferred to the organizers bank account by <?= PAYMENT_DATE ?>. Your payment must be made in Euro's using the following details:<br>
	&nbsp;&nbsp;&nbsp;IBAN: <?= IBAN_BANK_ACCOUNT ?><br>
	&nbsp;&nbsp;&nbsp;BIC Code: <?= IBAN_BANK_BIC ?><br>
	&nbsp;&nbsp;&nbsp;Account holder: <?= IBAN_ACCOUNT_NAME ?><br>
<?php 
	if (PAYPAL_ID != null) {
?>
		When not in the Euro zone you can use the PayPal friend and family option to make the payment on:<br>
		&nbsp;&nbsp;&nbsp;PayPal email ID: <?= PAYPAL_ID ?><br>
<?php 
	}
?>
	Put the following in your payment description: <strong>"<?= SITE_TITLE_SHORT ?> <span id='instructions_club'>TBD</span>, booking ref <?= $booking->getReference() ?>"</strong>.<br>
	If you made a payment it will show in your booking Overview for you to check. Please allow a few days for us to process your payments.
</p>
<p align="justify">We will see you at a Presidents' meeting to remember in November!</p>

