<?php

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

$form = 'https://docs.google.com/a/skydivelfk.com/forms/d/' .
  '1f2sfLt13HFbASkjBpqduaare5AsYM40l9781noY3jqE' .
  '/formResponse';

$token = 'SUCCESS_TOKEN_DO_NOT_CHANGE';

$redirect = '/tack-foer-din-bokning.html';

$data = array();
foreach($formMap as $key => $map) {
  if (isset($_POST[$key])) {
    if ($key == 'cardid' && $_POST['payment'] == 'later') {
      $data[$map] = 'Pay at jump';
    } else {
      $data[$map] = $_POST[$key];
    }
  }
}

// Testing name to use to test failure flow
if ($_POST['name'] == 'CRASH_ME') {
  $result = false;
} else {
  $ch = curl_init($form);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result = curl_exec($ch);
}

if ($result === false || strpos($result, $token) === false) {
  header("Location: /bokningsfel.html");
  exit();
}
curl_close($ch);

?>
<script>
function redirect() {window.location = '<?php echo $redirect; ?>';}
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-60538721-1', 'auto');
ga('send', 'pageview', {'hitCallback': redirect});
setTimeout(redirect, 5000);
</script>
Redirecting you to the next page, <a href="<?php echo $redirect; ?>">click here</a> if you're not redirected.
