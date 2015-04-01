<?php
/*
SQL needed for booking_suspended:

CREATE TABLE jos_booking_suspended (
  num int(11) NOT NULL AUTO_INCREMENT,
  state text NOT NULL,
  payment text DEFAULT NULL,
  PRIMARY KEY (num),
  KEY num (num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

define('_LFK_API', '');
require_once('api.inc.php');
require_once('book.inc.php');

$payment = $_POST['payment'];
$is_payment_done = isset($_GET['TOKEN']);
$ipn_callback = isset($_GET['ipn_secret']);

if ($ipn_callback && $_GET['ipn_secret'] != IPN_SECRET) {
  send_error_report('IPN secret mismatch',
    'Got request but IPN secret invalid');
  die('Invalid IPN');
}

// User wants to pay now
if ($payment == 'now') {
  $buying_giftcard = !isset($_POST['cardid']);
  validate_data($buying_giftcard, $_POST);

  $booking_id = suspend_booking($_POST);
  if ($booking_id === false) {
    header("Location: /bokningsfel.html");
    exit();
  }

  $return_url = SITE_URL . "/templates/lfk/api/book.php";
  $cancel_url = SITE_URL . "/avbrutet-koep.html";
  $ipn_url = SITE_URL . "/templates/lfk/api/book.php?ipn_secret=" . IPN_SECRET;

  $url = new_payment($buying_giftcard, $_POST['media'], $_POST['email'],
    $booking_id, $return_url, $cancel_url, $ipn_url, IS_TEST);

  if ($url === false) {
    header("Location: /bokningsfel.html");
    exit();
  }

  header("Location: " . $url);
  exit();

// Handle return of the user from the payment processor
} else if ($is_payment_done) {
  // Verify that the payment actually succeeded
  $payment_info = get_payment($_GET['TOKEN']);
  if ($payment_info === false) {
    header("Location: /bokningsfel.html");
    exit();
  }

  if ($payment_info['is_complete'] !== true) {
    header("Location: /bokningsfel.html");
    exit();
  }

  // Note: Bookings are done in the IPN callback below
  if ($payment_info['is_giftcard']) {
    success('/tack-foer-ditt-koep.html', 'giftcard', 'now');
  } else {
    success('/tack-foer-din-bokning.html', 'jump', 'now');
  }

  // Purchase done!

// This is the callback from the payment provider, we're guaranteed
// that this will be attempted so we do the booking here.
// If we were to do it in the "is_payment_done" callback, the user could
// have closed their browser and thus ended up paying but not getting
// a booking.
} else if ($ipn_callback) {
  // Remember: This is all executed "backstage", so no reason to redirect
  // to a nice error page or anything.

  // This will read the payment processor information from POST/GET.
  $payment_info = confirm_payment();
  if ($payment_info === false) {
    // IPN is not verified
    exit();
  }

  $booking_id = $payment_info['custom'];
  if ($booking_id === false) {
    send_error_report('$booking_id === false', 'No custom booking field found');
    exit();
  }

  $data = resume_booking($booking_id, $payment_info);
  if ($data === false) {
    send_error_report('$data === false', 'No saved booking found to resume');
    exit();
  }

  if ($payment_info['is_complete'] !== true) {
    // Do not go futher if this is not a COMPLETED IPN.
    exit();
  }

  $giftcard = new_giftcard($data, $payment_info);
  if ($giftcard === false) {
    send_error_report('new_giftcard failed', 'Failed to create new giftcard');
    exit();
  }

  if ($payment_info['is_giftcard']) {
    mail_giftcard($data, $giftcard);
  } else {
    // Use the newly created gift card
    $data['cardid'] = $giftcard;

    if (!new_booking($data, IS_TEST)) {
      send_error_report('new_booking failed', 'Failed to store new booking');
      exit();
    }
  }

  // Payment done!

// User wants to pay later or has a gift card, just book him
} else if ($payment == 'later' || $payment == 'giftcard') {
  validate_data(false, $_POST);

  if (!new_booking($_POST, IS_TEST)) {
    header("Location: /bokningsfel.html");
    exit();
  }
  success('/tack-foer-din-bokning.html', 'jump', $payment);
} else {
  // Unknown payment type :(
  header("Location: /bokningsfel.html");
  exit();
}
?>
