<?php
define('_LFK_API', '');
require_once('api.inc.php');
require_once('secret.php');

if ($_GET['secret'] !== READONLY_SECRET) {
  assert_user_has_view_level('Registered');
}

header('Content-Type: application/javascript');

$type = isset($_GET['type']) ? $_GET['type'] : null;

$query == null;
if ($type == 'hl') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "HL" GROUP BY m.InternalNo ORDER BY LastName';
} else if ($type == 'hm') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "HM" GROUP BY m.InternalNo';
} else if ($type == 'manifest') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, m.Year FROM skywin.memberclubfunction AS mcf, skywin.member AS m WHERE m.InternalNo = mcf.InternalNo AND ClubfunctionType = "MANIFEST" GROUP BY m.InternalNo ORDER BY LastName';
} else if ($type == 'pilot') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, IFNULL(Club, "LFK") as Club, MAX(YEAR(lr.regdate)) as Year FROM skywin.member AS m, skywin.loadrole AS lr WHERE pilot="Y" AND m.internalno = lr.internalno AND lr.roletype = "PILOT" GROUP BY m.internalno ORDER BY LastName';
} else if ($type == 'tandem') {
  $query = 'SELECT m.InternalNo, FirstName, LastName, m.Club, MAX(mi.Year) as Year FROM skywin.memberinstruct AS mi, skywin.member AS m WHERE m.InternalNo = mi.InternalNo AND InstructType = "T" GROUP BY m.InternalNo ORDER BY LastName';
} else if ($type == 'foto') {
  $query = 'SELECT InternalNo, FirstName, LastName, Club, Year FROM skywin.member WHERE video="Y" ORDER BY LastName';
} else if ($type == 'member') {
  $query = 'SELECT NULLIF(MemberNo, 0) as MemberNo, FirstName, LastName, Address1, Address2, phone1.PhoneNo as PhoneWork, phone2.PhoneNo as PhoneHome, phone3.PhoneNo as PhoneMobile, Emailaddress FROM skywin.member '.
    'LEFT JOIN skywin.memberphone phone1 ON phone1.InternalNo = member.InternalNo AND phone1.PhoneType = "A" LEFT JOIN skywin.memberphone phone2 ON phone2.InternalNo = member.InternalNo AND phone2.PhoneType = "B" '.
    'LEFT JOIN skywin.memberphone phone3 ON phone3.InternalNo = member.InternalNo AND phone3.PhoneType = "M" WHERE (pilot="Y" or (club = "LFK" and year > YEAR(NOW()) - 5 and membertype != "PAX")) and LastName != "Övrig" ORDER BY LastName';
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

if (isset($_GET['json'])) {
  echo json_encode($results, JSON_UNESCAPED_UNICODE);
} else {
  print 'InternalNo,Name' . "\n";
  foreach ($results as $result) {
    $name = $result['FirstName'] . ' '. $result['LastName'];
    print $result['InternalNo'] . ',' . $name . "\n";
  }
}
?>
