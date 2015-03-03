<?php
/*
 Accepts a weather station dump
 and saves it to vader/downld02.txt */

set_time_limit(0);
ignore_user_abort(true);

isset($_FILES['dump']) || die('No file attached');

define('_LFK_API', '');
require_once('secret.php');
if ($_POST['secret'] !== WEBSITE_SECRET) {
  die('Wrong secret');
}

$data = file_get_contents($_FILES['dump']['tmp_name']);
file_put_contents('../../../vader/downld02.txt', $data);

syslog(LOG_INFO, 'New weather uploaded');
?>
