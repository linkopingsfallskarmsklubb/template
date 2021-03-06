<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

$activeMenu = JFactory::getApplication()->getMenu()->getActive();

// Hack to remove category views from naked urls
if ($activeMenu->link == 'index.php?Itemid=') {
  header('Location: /');
}

// Hide the Joomla generator.
// Security by obscurity, sure - but it's nice to not be automatically
// scraped.
JFactory::getDocument()->setGenerator('');
JHtml::_('jquery.ui');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  <link rel="shortcut icon" href="/templates/<?php echo $this->template ?>/favicon.ico" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/calendar.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/pure-min.css" type="text/css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.css" type="text/css" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <jdoc:include type="head" />
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0&appId=1545884919007445";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-60538721-1', 'auto');
ga('require', 'linkid', 'linkid.js');
ga('send', 'pageview');

var menuState = false;
jQuery(document).ready(function() {
  // Hack: Install click handlers on the images
  // to support clicking on the images as well as the
  // headlines
  var href = jQuery('#topimg h2 a').attr('href');
  if (href != undefined) {
    jQuery('#topimg').click(function() {
      window.location = href;
    });
    jQuery('#topimg').css('cursor', 'pointer');
  }
  jQuery('.blog-featured .item').each(function() {
    var link = jQuery(this).find('a').attr('href');
    jQuery(this).find('.item-image').click(function() {
      window.location = link;
    });
  });

  checkSize();
  jQuery(window).resize(checkSize);
});

function checkSize(){
  if (jQuery('#menu-top-expander').css('display') == 'none') {
    jQuery('#menu-top .menu > li:not(.active)').show();
    jQuery('#menu-sub').show();
    jQuery('#menu-bottom').show();
    jQuery('.menu > li').css('display', '');
  } else {
    console.log('Visible');
  }
}
function toggle() {  
  if (menuState) {
    jQuery('#menu-top .menu > li:not(.active)').hide(100);
    jQuery('#menu-sub').show(100);
    jQuery('#menu-bottom').show(100);
    jQuery('#menu-top-state').removeClass('icon-chevron-up').addClass('icon-chevron-down');
  } else {
    jQuery('#menu-top .menu > li:not(.active)').show(100);
    jQuery('#menu-sub').hide(100);
    jQuery('#menu-bottom').hide(100);
    jQuery('#menu-top-state').removeClass('icon-chevron-down').addClass('icon-chevron-up');
  }
  menuState = !menuState;
}  
</script>

  <div id="lfk-languages">
    <jdoc:include type="modules" name="languages" />
  </div>
  <a id="lfk-logo" href="/"></a>
  <div id="lfk-page">
    <div id="menu-top-expander" onclick="toggle(); return false;">
      <div id="menu-top-state" class="btn icon-chevron-down"></div>
    </div>
    <div id="menu-top">
      <jdoc:include type="modules" name="menu-top" />
    </div>
    <div id="menu-sub">
      <jdoc:include type="modules" name="menu-sub" />
    </div>
    <div id="menu-bottom">
      <jdoc:include type="modules" name="menu-bottom" />
    </div>
    <div id="menu-spacer"></div>
    <?php
/* Use article alias if available */
if (isset($activeMenu->query) &&
    $activeMenu->query['option'] == 'com_content' &&
    $activeMenu->query['view'] == 'article') {
  $articleId = $activeMenu->query['id'];
  $article = JTable::getInstance('content');
  $article->load($articleId);
  $alias = $article->alias;
} else {
  $alias = $activeMenu->alias;
}

$prefix = '/images/pages/' . $alias . '/';
$localdir = JPATH_ROOT . $prefix;
echo '<!-- alias: ' . $alias . ' -->' . "\n";

if (isset($localdir) && is_file($localdir . 'top.jpg')) {   
  echo '<div id="topimg" ';
  echo 'style="background-image: url(' . $prefix . 'top.jpg);">';
  // Hack to display 'Get here' link on the top page for the start page
  if ($alias == 'start') {
    echo '<h2>';
    echo '<a href="' . JText::_('PAGE_START_GET_HERE_LINK') . '">';
    echo JText::_('PAGE_START_GET_HERE') . '</a></h2>';
  } 
  echo '</div>';
}
    ?>
    <div id="content">
      <jdoc:include type="message" />
        <?php
// Show all images matching images/pages/$alias/*.jpg (but not top.jpg)
if (isset($localdir) && is_dir($localdir)) {
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
    <jdoc:include type="modules" name="sub-bottom" />
  </div>
  <div id="lfk-footer">
    <div style="float: left">
<style>.ig-b- { display: inline-block; }
.ig-b- img { visibility: hidden; }
.ig-b-:hover { background-position: 0 -60px; } .ig-b-:active { background-position: 0 -120px; }
.ig-b-32 { width: 32px; height: 32px; background: url(//badges.instagram.com/static/images/ig-badge-sprite-32.png) no-repeat 0 0; }
@media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2 / 1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
.ig-b-32 { background-image: url(//badges.instagram.com/static/images/ig-badge-sprite-32@2x.png); background-size: 60px 178px; } }</style>
<a href="http://instagram.com/linkopingsfallskarmsklubb?ref=badge" class="ig-b- ig-b-32"><img src="//badges.instagram.com/static/images/ig-badge-32.png" alt="Instagram" /></a>
      <br />
      <a href="https://plus.google.com/+LinkopingsfallskarmsklubbSe"
   rel="publisher" target="_top" style="text-decoration:none;">
<img src="//ssl.gstatic.com/images/icons/gplus-32.png" alt="Google+" style="border:0;width:32px;height:32px;"/>
</a>
    </div>
    <!-- Needs to be 225 in data-width for iPhone 4 -->
    <div class="fb-like" data-href="http://www.facebook.com/linkopingsfallskarmsklubb" data-width="225" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
  </div>
</body>
</html>
