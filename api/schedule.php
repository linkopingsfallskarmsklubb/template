<?php
// Insecure proxy to bypass CORS protection
$url = 'https://docs.google.com/spreadsheets/d/1puIrlni8fju25UwijhCjeIUiU95STRIYJsgsXe6cr6U/pub?gid=1143829030&single=true&output=csv';

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

header('Content-Type: text/csv');
echo curl_exec($ch);
?>
