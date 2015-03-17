<?php
defined( '_LFK_API' ) or die( 'Restricted access' );
require_once('secret.php');

// Initialize Joomla framework for database access.
define('_JEXEC', '');
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../' ) );
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');


/**
 * Validate that the data has fields that we can use.
 */
function validate_data($data) {
  $nonEmpty = array(
    'name', 'weight', 'height', 'date', 'payment', 'phone', 'email');
  foreach($nonEmpty as $field) {
    if (!isset($data[$field]) || $data[$field] == '') {
      die('No ' . $field . ' supplied');
    }
  }

  if (strpos($data['email'], '@') === false) {
    die('Invalid email provided');
  }

  if ($data['payment'] == 'giftcard' && (
    !isset($data['cardid']) || $data['cardid'] == '')) {
    die('No giftcard supplied');
  }
}

/**
 * Send an error dump to the administrator via email.
 */
function send_error_report($id, $text) {
  $msg = "Hi,\n$text - sorry!\n";
  ob_start();
  var_dump($_SERVER);
  var_dump($_GET);
  var_dump($_POST);
  $msg .= ob_get_contents();
  ob_end_clean();

  mail(ERROR_EMAIL, "LFK website error: $id", $msg);
}

/**
 * Save the user provided info from a booking in persistent storage
 * and return a unique reference ID (booking ID) to it.
 */
function suspend_booking($data) {
  // Track the "going to sleep" time
  $data['time'] = time();
  $state = json_encode($data);
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $query
    ->insert($db->quoteName('#__booking_suspended'))
    ->columns($db->quoteName(array('state')))
    ->values($db->quote($state));
  $db->setQuery($query);
  try {
    $db->execute();
  } catch (Exception $e) {
    send_error_report('Database suspend exception', $e->getMessage());
    return false;
  }
  return $db->insertid();
}

/**
 * Retrieve a booking and save the payment information
 * in the database next to it for auditing.
 */
function resume_booking($booking_id, $payment_info) {
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $query->select(array('state'))
    ->from($db->quoteName('#__booking_suspended'))
    ->where(array($db->quoteName('num') . ' = ' . $db->quote($booking_id)));
  $db->setQuery($query);

  try {
    $db->execute();
  } catch (Exception $e) {
    send_error_report('Database resume exception', $e->getMessage());
    return false;
  }

  $results = $db->loadAssocList();
  if (count($results) != 1) {
    return false;
  }

  $state_encoded = $results[0]['state'];

  // Save payment info with timestamp
  $payment_info['time'] = time();
  $payment_encoded = json_encode($payment_info);
  $query = $db->getQuery(true);
  $query->update($db->quoteName('#__booking_suspended'))
    ->set(array(
      $db->quoteName('payment') . ' = ' . $db->quote($payment_encoded)))
    ->where(array($db->quoteName('num') . ' = ' . $db->quote($booking_id)));
  $db->setQuery($query);

  try {
    $db->execute();
  } catch (Exception $e) {
    send_error_report('Database state update exception', $e->getMessage());
    return false;
  }

  $state = json_decode($state_encoded, true);
  if ($state === null) {
    return false;
  }
  return $state;
}

/**
 * Read the request state (GET/POST) and return
 * our custom magic value (suspended booking ID).
 */
function confirm_payment() {

  require_once 'payson/lib/paysonapi.php';

  $data = file_get_contents('php://input');
  $credentials = new PaysonCredentials(PAYSON_AGENT_ID, PAYSON_API_KEY);
  $api = new PaysonApi($credentials, IS_TEST);
  $response = $api->validate($data);

  if (!$response->isVerified()) {
    return false;
  }

  $details = $response->getPaymentDetails();
  $is_complete = (
    $details->getType() == 'TRANSFER' && $details->getStatus() == 'COMPLETED');

  return array(
    'payson' => array(
      'purchase_id' => $details->getPurchaseId(),
      'type' => $details->getType(),
      'status' => $details->getStatus()),
    'note' => 'Payson: ' . $details->getPurchaseId(),
    'is_complete' => $is_complete,
    'custom' => $details->getCustom());
}

/**
 * Create a new giftcard for the products specified.
 */
function new_giftcard($data, $payment) {
  $columns = array();
  $values = array();

  $db = JFactory::getDBO();

  $columns[] = 'expire';
  $values[] = 'DATE_ADD(CURDATE(), INTERVAL 1 YEAR)';

  $dataColumnMap = array(
    'name' => 'person',
    'email' => 'email',
    'contact' => 'contact',
    'phone' => 'phone');

  foreach($dataColumnMap as $key => $value) {
    $columns[] = $value;
    $values[] = $db->quote($data[$key]);
  }

  $columns[] = 'note';
  $values[] = $db->quote($payment['note']);

  $columns[] = 'product_jump';
  $values[] = 1;

  if ($data['media'] != null && in_array('photo', $data['media'])) {
    $columns[] = 'product_photo';
    $values[] = 1;
  }
  if ($data['media'] != null && in_array('video', $data['media'])) {
    $columns[] = 'product_video';
    $values[] = 1;
  }

  $columns[] = 'product_credit';
  $values[] = 0;

  $query = $db->getQuery(true);
  $query
    ->insert($db->quoteName('#__giftcards'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
  $db->setQuery($query);

  try {
    $db->execute();
  } catch (Exception $e) {
    send_error_report('Database insert giftcard exception', $e->getMessage());
    return false;
  }
  return $db->insertid();
}

/**
 * Create a new payment request, return the URL
 * to redirect the user to.
 */
function new_payment($is_giftcard, $media, $email, $custom,
  $return_url, $cancel_url, $ipn_url, $is_test) {

  require_once 'payson/lib/paysonapi.php';

  // Assume that the user always want to buy a jump
  // Amount to send to receiver
  $amount = 2990;

  $prefix = $is_giftcard ? 'Presentkort: ' : '';

   // Set the list of products.
  $order_items = array();
  $order_items[] = new OrderItem($prefix . 'Tandemhopp', 2392, 1, 0.25, 'Hopp');

  if ($media != null) {
    if (in_array('photo', $media) && in_array('video', $media)) {
      $order_items[] = new OrderItem(
        $prefix . 'Video & Foto', 960, 1, 0.25, 'Foto+Video');
      $amount += 1200;
    } else if (in_array('photo', $media) || in_array('video', $media)) {
      $order_items[] = new OrderItem(
        $prefix . 'Video eller Foto', 720, 1, 0.25, 'FotoEllerVideo');
      $amount += 900;
    }
  }

  $credentials = new PaysonCredentials(PAYSON_AGENT_ID, PAYSON_API_KEY);
  $api = new PaysonApi($credentials, IS_TEST);

  /*
   * To initiate a direct payment the steps are as follows
   *  1. Set up the details for the payment
   *  2. Initiate payment with Payson
   *  3. Verify that it suceeded
   *  4. Forward the user to Payson to complete the payment
   */

  // Step 1: Set up details

  // Details about the receiver
  $receiver = new Receiver(PAYSON_RECEIVER, $amount);
  $receivers = array($receiver);

  // Details about the user that is the sender of the money
  $sender = new Sender($email, '', '');

  $expire = date('Y-m-d', strtotime('+1 years'));
  $order_description = "Notera att din order måste nyttjas senast $expire.";

  $pay_data = new PayData($return_url, $cancel_url, $ipn_url,
    $order_description, $sender, $receivers);

  $pay_data->setOrderItems($order_items);

  // Set the payment method
  $constraints = array(FundingConstraint::BANK, FundingConstraint::CREDITCARD);
  $pay_data->setFundingConstraints($constraints);
  $pay_data->setFeesPayer(FeesPayer::PRIMARYRECEIVER);
  $pay_data->setCurrencyCode(CurrencyCode::SEK);
  $pay_data->setLocaleCode(LocaleCode::SWEDISH);
  $pay_data->setGuaranteeOffered(GuaranteeOffered::NO);
  $pay_data->setCustom($custom);
  $pay_data->setShowReceiptPage(false);

  // Step 2: initiate payment
  $pay_response = $api->pay($pay_data);

  // Step 3: verify that it suceeded
  if (!$pay_response->getResponseEnvelope()->wasSuccessful()) {
    return false;
  }

  // Step 4: forward user
  return $api->getForwardPayUrl($pay_response);
}

/**
 * Persist a booking. At this point everything should
 * be validated and it's just a matter of saving it.
 *
 * Currently this is posting to our two Google Forms
 * as a form of database. This will later be connected
 * to the tandem booking system when that's done.
 */
function new_booking($input, $is_test) {
  $formMap = array(
    'name' => 'entry.275431443', /* Namn */
    'email' => 'entry.1987986258', /* E-post */
    'contact' => 'entry.1385175204', /* Kontaktperson */
    'height' => 'entry.1188499459', /* Längd */
    'weight' => 'entry.79438309', /* Vikt */
    'date' => 'entry.1085938945', /* Datum (2015-01-01) */
    'phone' => 'entry.1533462447', /* Telefonnummer */
    'cardid' => 'entry.328907933', /* Presentkort (empty if cash) */
    'media' => 'entry.1504688742', /* (P)hoto (V)ideo */
    'city' => 'entry.509359054'); /* Ort */

  if (!$is_test) {
    $form = 'https://docs.google.com/a/skydivelfk.com/forms/d/' .
      '1f2sfLt13HFbASkjBpqduaare5AsYM40l9781noY3jqE' .
      '/formResponse';
  } else {
    $form = 'https://docs.google.com/a/skydivelfk.com/forms/d/' .
      '19-U3cf_m4ZV2qdsXOFAUFkd6f1g5W3draroWvwrxeKk' .
      '/formResponse';
  }

  $token = 'SUCCESS_TOKEN_DO_NOT_CHANGE';

  $data = array();
  // Default media to 'none'
  $data[$formMap['media']] = 'none';

  foreach($formMap as $key => $map) {
    if (isset($input[$key])) {
      if ($key == 'cardid') {
        if ($input['payment'] == 'later') {
          $data[$map] = 'Pay at jump';
        } else if ($input['payment'] == 'now') {
          $data[$map] = 'Online: ' . $input[$key];
        } else {
          $data[$map] = $input[$key];
        }
      } else if ($key == 'media') {
        sort($input[$key]);
        $data[$map] = implode('+', $input[$key]);
      } else if ($key == 'phone') {
        $data[$map] = '# ' . $input[$key];
      } else {
        $data[$map] = $input[$key];
      }
    }
  }

  // Testing name to use to test failure flow
  if ($input['name'] == 'CRASH_ME') {
    return false;
  }

  $ch = curl_init($form);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
  curl_close($ch);

  return $result !== false && strpos($result, $token) !== false;
}

/**
 * Create a redirection page that will register with
 * Google Analytics. Used for conversion tracking.
 */
function success($redirect, $payment) {
?>
<script>
function redirect() {window.location = '<?php echo $redirect; ?>';}
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
var page = window.location.pathname + '?payment=<?php echo urlencode($payment); ?>';
ga('create', 'UA-60538721-1', 'auto');
ga('send', 'pageview', {'page': page, 'hitCallback': redirect});
setTimeout(redirect, 5000);
</script>
Redirecting you to the next page, <a href="<?php echo $redirect; ?>">click here</a> if you're not redirected.
<?php
}
?>
