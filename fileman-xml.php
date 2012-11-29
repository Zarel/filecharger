<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * PHP XML AJAX reply
 * 
 */

$time = time();

if (isset($_REQUEST['frame']) && !isset($_REQUEST['d']))
{
?>
<!DOCTYPE html>
<html style="margin:0;padding:0;">
  <head>
    <title>Throbber</title>
    <script type="text/javascript">
    <!--
      function load(l)
      { document.getElementById('loading').src = 'images/throbber'+(l?'-loading.gif':'.gif'); var limg = new Image(); limg.src = 'images/throbber-loading.gif';
      }
    //-->
    </script>
  </head>
  <body style="margin:0;padding:0;" onload="load(0);parent.fmf_okay(1);">
    <img id="loading" src="images/throbber-loading.gif" alt="" width="20" height="20" />
  </body>
</html>
<?php
	die();
}

include 'config.inc.php';
include 'fileman.lib.php';
if ($ftpmode)
	include_once 'ftpsession.lib.php';
else
	include_once 'session.lib.php';

  //====================
  // Frame
  //====================

//if it's being used as a frame, we'll need to do some more stuff.
if (isset($_REQUEST['frame']))
{
  if (isset($_POST['act'])) switch ($_POST['act'])
  {
  case 'textedit':
    if (fm_iswritable($d))
    {
      if (!is_string($_POST['val']))
        die('<!DOCTYPE html><script language="javascript">parent.fmf_error("Malformed request.");</script>');
      if (strpos(fm_contents($d),"\r\n")===false)
        $_POST['val'] = str_replace("\r\n","\n",$_POST['val']);
      if (!fm_editfile($d, $_POST['val']))
      {
        fm_close();
        die('<!DOCTYPE html><script language="javascript">parent.fmf_error("Cannot edit file \''.basename($d).'\'.");</script>');
      }
    }
    else
      die('<!DOCTYPE html><script language="javascript">parent.fmf_error("File \''.basename($d).'\' is not writable.");</script>');
  }
  // Written in HTML 5 because I'm too lazy to write XHTML
?>
<!DOCTYPE html>
<html style="margin:0;padding:0;">
  <head>
    <title>Throbber</title>
    <script type="text/javascript">
    <!--
      function load(l)
      { document.getElementById('loading').src = 'images/throbber'+(l?'-loading.gif':'.gif'); var limg = new Image(); limg.src = 'images/throbber-loading.gif';
      }
    //-->
    </script>
  </head>
  <body style="margin:0;padding:0;" onload="load(0);parent.fmf_okay(1);">
    <img id="loading" src="images/throbber-loading.gif" alt="" width="20" height="20" />
  </body>
</html>
<?php
  die();
}

  //====================
  // Otherwise... GO!
  //====================

// Deprecated. I'll probably never salvage this code, but
// it's here in case I ever need it again.

/*

header('Content-type: text/xml');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');  // disable IE caching
header('Last-Modified: '.gmdate("D, d M Y H:i:s").'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');

echo '<?xml version="1.0" ?>';
?>

<fileman>
  <s><?php echo $sess; ?></s>
  <p><?php echo $preurl.$presub; ?></p><d><?php echo $d; ?></d>
<?
  //====================
  // Are we doing something?
  //====================
if ($_POST['a'])
{
  $act = $_POST['a'];
  switch ($act)
  {
  case 'ren':
    if (strpos($_POST['f1'],'/')!==false || strpos($_POST['f1'],'\\')!==false || strpos($_POST['f2'],'/')!==false || strpos($_POST['f2'],'\\')!==false) echo '<ferr>s</ferr>';
    else fm_rename($fullpath.$_POST['f1'],$fullpath.$_POST['f2']);
    break;
  case 'del':
    if (strpos($_POST['f1'],'/')!==false || strpos($_POST['f1'],'\\')!==false) echo '<ferr>s</ferr>';
    else fm_fdelete($fullpath.$_POST['f1'],$fullpath.$_POST['f2']);
    break;
  case 'ndir':
    if (strpos($_POST['f1'],'/')!==false || strpos($_POST['f1'],'\\')!==false) echo '<ferr>s</ferr>';
    else fm_newdir($fullpath.$_POST['f1']);
    break;
  case 'nfile':
    if (strpos($_POST['f1'],'/')!==false || strpos($_POST['f1'],'\\')!==false) echo '<ferr>s</ferr>';
    else fm_newfile($fullpath.$_POST['f1']);
    break;
  default:
    echo '<ferr>?</ferr>';
  }
}
  //====================
  // All the stuff they need and some they don't for good measure
  //====================
$dfile = fm_fileinfo($fullpath);
?>
  <fl>
    <cf><n><?=$dfile['name']?></n><id><?=$dfile['id']?></id><d><?=($dfile['isdir']?'1':'')?></d><s><?=$dfile['size']?></s><ts><?=$dfile['tsize']?></ts><m><?=$dfile['modified']?></m><tm><?=$dfile['tmodified']?></tm><p><?=$dfile['perms']?></p><e><?=$dfile['ext']?></e><i><?=$dfile['img']?></i><t><?=$dfile['type']?></t><ft><?=$dfile['ft']?></ft><ea><?=$dfile['extac']?></ea><vi><?=$dfile['isvimg']?></vi><is><? if ($dfile['imgsize']) echo '<w>'.$dfile['imgsize'][0].'</w><h>'.$dfile['imgsize'][1].'</h><t>'.$dfile['imgsize'][2].'</t>'; ?></is></cf>
<?php
$dfiles = fm_getdfiles($fullpath);

foreach ($dfiles as $dfile)
{
?>
    <fi><n><?=$dfile['name']?></n><id><?=$dfile['id']?></id><d><?=($dfile['isdir']?'1':'')?></d><s><?=$dfile['size']?></s><ts><?=$dfile['tsize']?></ts><m><?=$dfile['modified']?></m><tm><?=$dfile['tmodified']?></tm><p><?=$dfile['perms']?></p><e><?=$dfile['ext']?></e><i><?=$dfile['img']?></i><t><?=$dfile['type']?></t><ft><?=$dfile['ft']?></ft><ea><?=$dfile['extac']?></ea><vi><?=$dfile['isvimg']?></vi><is><? if ($dfile['imgsize']) echo '<w>'.$dfile['imgsize'][0].'</w><h>'.$dfile['imgsize'][1].'</h><t>'.$dfile['imgsize'][2].'</t>'; ?></is></fi>
<?php
}
$dfiles = fm_getdfiles('cache/clip/');

fm_close();

foreach ($dfiles as $dfile)
{
?>
    <ci><n><?=$dfile['name']?></n><id><?=$dfile['id']?></id><d><?=($dfile['isdir']?'1':'')?></d><s><?=$dfile['size']?></s><ts><?=$dfile['tsize']?></ts><m><?=$dfile['modified']?></m><tm><?=$dfile['tmodified']?></tm><p><?=$dfile['perms']?></p><e><?=$dfile['ext']?></e><i><?=$dfile['img']?></i><t><?=$dfile['type']?></t><ft><?=$dfile['ft']?></ft><ea><?=$dfile['extac']?></ea><vi><?=$dfile['isvimg']?></vi><is><? if ($dfile['imgsize']) echo '<w>'.$dfile['imgsize'][0].'</w><h>'.$dfile['imgsize'][1].'</h><t>'.$dfile['imgsize'][2].'</t>'; ?></is></ci>
<?php
}
?>
  </fl>
</fileman>
<?php
*/
?>