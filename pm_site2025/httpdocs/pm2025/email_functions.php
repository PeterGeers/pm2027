<?php
// Emailer function.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';
require_once 'Exception.php';
require_once 'PHPMailer.php';
require_once 'SMTP.php';

// Wrap the email body text, add closure and send to contact(s)
function send_email($subject, $name, $msg_body, $email1, $email2 = null, $bcc = null) {
	$body = '<!DOCTYPE html>';
	$body .= '<html><head>';
	$body .= '</head><body style="background-color:#ffffff; font-family:Verdana, sans-serif">';
	$body .=  '<p style="margin-bottom: -0.1rem;">Dear '.htmlspecialchars($name).',</p>';
	$body .= $msg_body;
	$body .= '<br>';
	$body .= 'Kind regards,<br>';
	$body .= PM_ORGANIZER.'<br>';
	$body .= '<img src="'.SITE_NAME.PM_LOGO.'" height="120">';
	$body .= '<p><br><i>Disclaimer:</i><br>';
	$body .= '<i><small>';
	$body .= 'This message is sent on behalf of '.PM_ORGANIZER.' and is intended for ';
	if ($email2 AND strcasecmp($email1, $email2) != 0) {
		$body .=  $email1 . ' and '. $email2;
	} else {
		$body .=  $email1;
	}
	$body .= '. If you are not the intended recipient delete this email and you are notified that disclosing, copying, distributing or taking any action in reliance on the contents of this information is strictly prohibited. ';
	$body .= PM_ORGANIZER.' accepts no liability for the content of this email, or for the consequences of any actions taken on the basis of the information provided. ';
	$body .= PM_ORGANIZER.' takes no responsibility for any miscalculations or misspells.';
	$body .= '</small></i></p>';
	$body .= '</body></html>';

	$mail = new PHPMailer(true);
	$mail->setFrom(SITE_EMAIL, html_entity_decode(SITE_TITLE));
	if ($email1 != '') {
		$mail->addAddress($email1);
	}
	if ($email2 AND strcasecmp($email1, $email2) != 0) {
		$mail->addAddress($email2);
	}
	// We can have an array with many bcc emails, or a single for copy to our selfs
	if (is_array($bcc)) {
		foreach($bcc as $email) {
			$mail->AddBcc ($email);
		}
	} elseif (!USE_TEST_SMTP AND $bcc) {
		$mail->AddBcc ($bcc);
	}
	$mail->isHTML(true);
	$mail->CharSet = 'UTF-8';
	$mail->Subject = $subject;
	$mail->Body = $body;
  try {
    $mail->send();
    return array(true, 'OK');
  } catch (Exception $e) {
    return array(false, $mail->ErrorInfo);
  }
}

