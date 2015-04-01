<?php
define('_LFK_API', '');
require_once('api.inc.php');

assert_user_has_view_level('Schedule Admin');

header('Content-Type: application/javascript');
echo '[]';
?>
