<?php
// Insecure proxy to show the old booking calendar for tandems
$url = 'http://old.skydivelfk.com/moduler/tandembokning/Boka1.aspx';

$ch = curl_init($url);

// Random user-agent to make Aspx generate a functioning form *sigh*
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
if (count($_POST) > 0) {
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

echo str_replace('Boka1.aspx', 'book_proxy.php', curl_exec($ch));
?>
