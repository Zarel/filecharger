<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Previewer
 *
 * Used to view text and image files; other files may be supported
 * in later versions.
 *
 * Default module - Performs important functionality,
 * but can be deleted if absolutely necessary
 * 
 */

include 'config.inc.php';
include 'fileman.lib.php';
if ($ftpmode)
	include_once 'ftpsession.lib.php';
else
	include_once 'session.lib.php';

$finfo = fm_fileinfo($d);

if ($finfo['ft'] == 5)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html style="width:100%;height:100%;margin:0;padding:0;">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title><?php echo htmlentities($file); ?> - Text Editor</title>
    <link rel="stylesheet" href="fileman.css" type="text/css" media="screen" />
  </head>
  <body style="width:100%;height:100%;margin:0;padding:0;background:#CCCCCC none;">
    <table id="preview_img" border="0" cellspacing="0" cellpadding="0" style="width:100%;height:100%;margin:0;padding:0;"><tr><td align="center" valign="middle">
      <img src="<?php echo fm_geturl($d); ?>" class="image" style="border:1px solid #000000;background:#FFFFFF url(fileman_img/transp.gif);" />
    </td></tr></table>
  </body>
</html>
<?php
}
else
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title><?php echo htmlentities($file); ?> - Text Editor</title>
    <link rel="stylesheet" href="fileman.css" type="text/css" media="screen" />
  </head>
  <body>
    <pre><?php echo htmlspecialchars(fm_contents($d)); ?></pre>
  </body>
</html>
<?php
}
?>