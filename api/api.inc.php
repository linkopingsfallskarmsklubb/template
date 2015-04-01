<?php
defined( '_LFK_API' ) or die( 'Restricted access' );

// Initialize Joomla framework for database access.
define('_JEXEC', '');
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../' ) );
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

require_once('secret.php');

/**
 * Validate that the current logged in user has a certain
 * view level access.
 */
function user_has_view_level($view_level) {

  // Map access level to internal ID
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $query->select(array('id'))
    ->from($db->quoteName('#__viewlevels'))
    ->where(array($db->quoteName('title') . ' = ' . $db->quote($view_level)));
  $db->setQuery($query);

  try {
    $db->execute();
  } catch (Exception $e) {
    send_error_report('View level lookup exception', $e->getMessage());
    return false;
  }

  $results = $db->loadAssocList();
  if (count($results) != 1) {
    send_error_report('View level not found', $view_level);
    return false;
  }
  $level_id = $results[0]['id'];

  $app = JFactory::getApplication('site');
  $user = JFactory::getUser();
  return in_array($level_id, $user->getAuthorisedViewLevels());
}

/**
 * Assert user has a certain view level, send 401 otherwise.
 */
function assert_user_has_view_level($view_level) {
  if (user_has_view_level($view_level)) {
    return;
  }

  header('HTTP/1.1 401 Unauthorized');
  header('Content-Type: text/plain');
  exit('Unauthorized');
}

/**
 * Send 500 with a specified error message
 */
function internal_error($error) {
  header('HTTP/1.1 500 Internal Server Error');
  header('Content-Type: text/plain');
  exit($error);
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


