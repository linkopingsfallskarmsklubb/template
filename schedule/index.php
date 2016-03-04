<?php
// Initialize Joomla framework for database access.
define('_JEXEC', '');
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../' ) );
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

$app = JFactory::getApplication('site');
$user = JFactory::getUser();
if ($user->guest) {
	$app->redirect('/index.php?option=com_users&view=login');
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LFK Schema</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/dataTables.bootstrap.css" rel="stylesheet">
    <style>
      #table-container td {
        white-space: nowrap;
      }
    </style>
  </head>

  <body>
    <div class="container-fluid">
      <div id='table-container'></div>
    </div><!-- /.container -->

    <footer class='footer'>
      <div class='container-fluid'>
        <hr />
      </div>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.csv.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.js"></script>
    <script src='js/csv_to_html_table.js'></script>

    <script>
      init_table({
        csv_path: '/templates/lfk/api/schedule.php',
        element: 'table-container', 
        allow_download: false,
        csv_options: {separator: ',', delimiter: '"'},
        datatables_options: {"paging": false},
        ready: function() {
          $('#my-table_filter input').val('<?php echo str_replace("'", '', $user->name); ?>').trigger('input');
        }
      });
    </script>
  </body>
</html>
