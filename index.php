<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
  <jdoc:include type="head" />
  <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
</head>
<body>
  <div id="lfk-languages">
    <jdoc:include type="modules" name="languages" />
  </div>
  <div id="lfk-logo"></div>
  <div id="lfk-page">
    <jdoc:include type="modules" name="breadcrumbs" />
    <jdoc:include type="modules" name="menu" />
    <div id="content">
      <jdoc:include type="message" />
      <jdoc:include type="component" />
    </div>
    <jdoc:include type="modules" name="bottom" />
  </div>
</body>
</html>
