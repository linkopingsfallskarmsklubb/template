<?php
// Insecure proxy to show the old booking calendar for tandems
$url = 'http://old.skydivelfk.com/moduler/tandembokning/Export.aspx';

$ch = curl_init($url);

// Random user-agent to make Aspx generate a functioning form *sigh*
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
if (count($_POST) > 0) {
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

header('Content-Type: application/javascript');
$data = explode("\n", trim(curl_exec($ch)));
sort($data);
echo json_encode($data);
?>
