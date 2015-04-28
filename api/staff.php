<?php
define('_LFK_API', '');
require_once('api.inc.php');

assert_user_has_view_level('Schedule Admin');

header('Content-Type: application/javascript');

$type = isset($_GET['type']) ? $_GET['type'] : null;

$query == null;
if ($type == 'hl') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "HL" GROUP BY m.InternalNo';
} else if ($type == 'hm') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "HM" GROUP BY m.InternalNo';
} else if ($type == 'manifest') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, m.Year FROM skywin.memberclubfunction AS mcf, skywin.member AS m WHERE m.InternalNo = mcf.InternalNo AND ClubfunctionType = "MANIFEST" GROUP BY m.InternalNo';
} else if ($type == 'pilot') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, IFNULL(Club, "LFK") as Club, MAX(YEAR(lr.regdate)) as Year FROM skywin.member AS m, skywin.loadrole AS lr WHERE pilot="Y" AND m.internalno = lr.internalno AND lr.roletype = "PILOT" GROUP BY m.internalno';
} else if ($type == 'tandem') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "T" GROUP BY m.InternalNo';
} else if ($type == 'foto') {
  $query = 'SELECT InternalNo, FirstName, LastName, Club, Year FROM skywin.member WHERE video="Y"';
} else {
  exit('[]');
}

$db = JFactory::getDBO();
$db->setQuery($query);
try {
  $db->execute();
} catch (Exception $e) {
  send_error_report('Staff DB exception', $e->getMessage());
  internal_error('Database exception');
}

$results = $db->loadAssocList();
echo json_encode($results, JSON_UNESCAPED_UNICODE);
?>
