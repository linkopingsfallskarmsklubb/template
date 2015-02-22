<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  <jdoc:include type="head" />
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
    <jdoc:include type="modules" name="menu" />
    <jdoc:include type="modules" name="top" />
    <div id="content">
      <jdoc:include type="message" />
<?php if ($this->countModules('article')) { ?>
      <div id="articleimg">
        <jdoc:include type="modules" name="article" />
      </div>
<?php } ?>
      <jdoc:include type="component" />
    </div>
    <jdoc:include type="modules" name="bottom" />
  </div>
  <div id="lfk-footer">
    <div class="fb-like" data-href="http://linkopingsfallskarmsklubb.se/" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
  </div>
</body>
</html>
