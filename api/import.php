<?php
/*
 Accepts a Gziped MySQL dump and imports it.
 Since this takes a while we stage it in skywin_stage
 both for error handling but also so we can later do
 the much faster RENAME TABLE to make this all fast.
 
 This is all super-ugly and should be replaced with a
 replicated setup. */

/* TODO: authentication */

set_time_limit(0);
ignore_user_abort(true);

isset($_FILES['dump']) || die('No file attached');

// Try to stage it first
$cmd = '(echo "DROP DATABASE IF EXISTS skywin_stage;";';
$cmd .= 'echo "CREATE DATABASE skywin_stage;";';
$cmd .= 'echo "USE skywin_stage;";';
$cmd .= 'zcat '. $_FILES['dump']['tmp_name'];
$cmd .= ') | mysql -h 127.0.0.1 -u skywin';
exec($cmd, $output, $ret);
if ($ret !== 0) {
  die('Staging failed');
}

// Staging OK, rename staging to production
mysql_connect('127.0.0.1', 'skywin', '');
$result = mysql_query('SHOW TABLES FROM skywin_stage');

$tables = array();
while ($row = mysql_fetch_row($result)) {
    $tables[] = $row[0];
}
mysql_free_result($result);

mysql_query('DROP DATABASE IF EXISTS skywin');
mysql_query('CREATE DATABASE skywin');

foreach ($tables as $table) {
  mysql_query('RENAME TABLE skywin_stage.' . $table . ' TO skywin.' . $table);
}

?>
