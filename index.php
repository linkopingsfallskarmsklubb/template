<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

// Hide the Joomla generator.
// Security by obscurity, sure - but it's nice to not be automatically
// scraped.
JFactory::getDocument()->setGenerator('');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  <jdoc:include type="head" />
  <link rel="shortcut icon" href="/templates/<?php echo $this->template ?>/favicon.ico" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

  <div id="lfk-languages">
    <jdoc:include type="modules" name="languages" />
  </div>
  <div id="lfk-logo"></div>
  <div id="lfk-page">
    <div id="menu-top">
      <jdoc:include type="modules" name="menu-top" />
    </div>
    <div id="menu-sub">
      <jdoc:include type="modules" name="menu-sub" />
    </div>
    <div id="menu-bottom">
      <jdoc:include type="modules" name="menu-bottom" />
    </div>
    <?php
$alias = JFactory::getApplication()->getMenu()->getActive()->alias;
$prefix = '/images/pages/' . $alias . '/';
$localdir = JPATH_ROOT . $prefix;

echo '<!-- alias: ' . $alias . ' -->';
if (is_file($localdir . 'top.jpg')) {
  echo '<div id="topimg" ';
  echo 'style="background-image: url(' . $prefix . 'top.jpg);">';
  echo '</div>';
}
    ?>
    <div id="content">
      <jdoc:include type="message" />
        <?php
// Show all images matching images/pages/$alias/*.jpg (but not top.jpg)
if (is_dir($localdir)) {
  $scan = scandir($localdir);
  $first = true;
  foreach ($scan as $file) {
    $localpath = $localdir . $file;
    if (is_dir($localpath) || pathinfo($file, PATHINFO_EXTENSION) != 'jpg')
      continue;
    if ($file == 'top.jpg')
      continue;
    if ($first) {
      echo '<div id="articleimg">';
      $first = false;
    }
    echo '<img src="'. $prefix . $file . '" />';
  }
  if (!$first)
    echo '</div>';
}
	  ?>
      <jdoc:include type="component" />
    </div>
    <jdoc:include type="modules" name="bottom" />
  </div>
  <div id="lfk-footer">
    <div class="fb-like" data-href="http://linkopingsfallskarmsklubb.se/" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
  </div>
</body>
</html>
