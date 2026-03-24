<?php
// Text to show in instructions canvas
require_once("config.php");
?>
<p align="justify">Please read these instructions for admin carefully before proceeding. 
</p>
<p align="center"><strong>The system will log you out after 25 minutes of inactivity.</strong>
</p>
<p align="justify"><strong>Menu</strong>:<br>
	The <span class="instructions-menu-large"> menu shown at the left of the page</span><span class="instructions-menu-small">hamburger menu at the top</span>
	has the following options to make it easier to manage PM bookings.
</p>
<ul>
	<li><strong>Instructions</strong>: will show you this page.</li>
	<li><strong>Overview</strong>: summry counts of all users and bookings.</li>
	<li><strong>Users</strong>: list of all registered users.</li>
	<li><strong>Bookings</strong>: list of all saved bookings.</li>
	<li><strong>Contacts</strong>: list of all club and contact details.</li>
	<li><strong>Delegates</strong>: list of all delegate details.</li>
	<li><strong>Guests</strong>: list of all guest details.</li>
	<li><strong>Rooms</strong>: list of all room details.</li>
	<li><strong>Travels</strong>: list of all travel details.</li>
	<li><strong>Extras</strong>: list of all extras details.</li>
	<li><strong>Payments</strong>: list of all payments details.</li>
	<li><strong>Hotel Rooms</strong>: list of all hotel rooms availabe for bookings details.</li>
	<li><strong>Download Submitted</strong>: download excel with details of all submitted bookings.</li>
	<li><strong>Download Saved</strong>: download excel with details of all saved bookings.</li>
	<li><strong>Logout</strong>: logout of system.</li>
</ul>
<p align="justify">Besides the overviews als listed above some of the lists give extra functions to manage the bookings.<br>
</p>
<p align="justify">On the <strong>Users</strong> page you can select any user(s) and send a predefined email to the users that are not active. If you selected a user that is already active, they will not be send an email.<br>
    You can also remove a user, please use with care there is no check against wrong use.
</p>
<p align="justify">On the <strong>Bookings</strong> page you can select any booking(s) and send a predefined email to the booking contacts that have not yet submitted their booking or have not yet paid.<br>
	Emails will only be send if the booking is indeed not submitted, or changed after submission, or if there is an open payment balance.<br>
	You can changed the locked status for all bookings, or individual bookings. Locking all is to be used when the last change date <?=REGISTRATION_CLOSE_DATE?>, all the booking contacts will receive an email about the new status.
	When using Lock/Unlock, select the applicable bookings (less than 20 please) and then clikc the button.<br>
    You can also remove a booking and all booking data, please use with care there is no check against wrong use.
</p>
<p align="justify">On the <strong>Rooms</strong> page it is possible to assign an hotel room number. Room numbers already assigned will not be avaible to select again. 
	No emails will be send using option.
</p>
<p align="justify">On the <strong>Extras</strong> page it is possible to add or update an extra chargable item for a booking. E.g. for extra t-Shirts. Select the booking number in the dropdown, enter a description and amount and click Add.
	When the email options is checked the booking contact will receive a predefined email that the extra is added to their booking and the (re)submit their final booking details.<br>
	You can also select an extra entered earier and remove it. No emails will be send on remove.
</p>
<p align="justify">On the <strong>Payments</strong> page club booking payments must be added. Select the booking number the payment is received for, enter the date and amount and click Add.
	When the email options is checked the booking contact will receive a predefined email we processed their payment. The email will state if the payment is in full, or if there is still an amount open.<br>
	You can also select a payment entered earier and remove it. No emails will be send on remove.
</p>
<p align="justify">The two <strong>Download</strong> menu options will generate an Excel file for you to download and analyze.
</p>
<p align="justify">We are making a Presidents' meeting to remember in November!</p>

