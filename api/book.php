<?php
/**
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
require_once('secret.php');
require_once('book.inc.php');

$payment = $_POST['payment'];
$is_payment_done = isset($_GET['TOKEN']);
$ipn_callback = isset($_GET['ipn_secret']);

// User wants to pay now
if ($payment == 'now') {
  $buying_giftcard = !isset($_POST['cardid']);
  $booking_id = suspend_booking($_POST);
  if ($booking_id === false) {
    header("Location: /bokningsfel.html");
    exit();
  }

  // Assume that the user always want to buy a jump
  $products = array('jump');

  // Add media as a product
  sort($_POST['media']);
  $extra_product = implode('+', $_POST['media']);
  if ($extra_product != '') {
    $products[] = $extra_product;
  }

  $return_url = SITE_URL . "/templates/lfk/api/book.php";
  $cancel_url = SITE_URL . "/avbruten-bokning.html";
  $ipn_url = SITE_URL . "/templates/lfk/api/book.php?ipn_secret=" . IPN_SECRET;

  $url = new_payment($buying_giftcard, $products, $_POST['email'],
    $booking_id, $return_url, $cancel_url, $ipn_url, IS_TEST);

  if ($url === false) {
    header("Location: /bokningsfel.html");
    exit();
  }

  header("Location: " . $url);
  exit();

// Handle return of the user from the payment processor
} else if ($is_payment_done) {
  // Note: Bookings are done in the IPN callback below
  success('/tack-foer-din-bokning.html', 'now');

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

  // TODO: create giftcard
  $giftcard = 'TODO';

  // Use the newly created gift card
  $data['cardid'] = $giftcard;

  if (!new_booking($data, IS_TEST)) {
    send_error_report('new_booking failed', 'Failed to store new booking');
    exit();
  }

// User wants to pay later or has a gift card, just book him
} else if ($payment == 'later' || $payment == 'giftcard') {
  if (!new_booking($_POST, IS_TEST)) {
    header("Location: /bokningsfel.html");
    exit();
  }
  success('/tack-foer-din-bokning.html', $payment);
} else {
  // Unknown payment type :(
  header("Location: /bokningsfel.html");
  exit();
}
?>
