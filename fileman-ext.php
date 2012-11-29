<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * PHP extended functions
 *
 * CORE LIBRARY - DO NOT REMOVE
 * 
 */

include 'config.inc.php';
include 'fileman.lib.php';
include 'session.lib.php';

$fm_dev = isset($_REQUEST['debug']);
if ($fm_dev)
{
  $qsid .= ($qsid?'&':'?').'debug';
  $hqsid .= ($hqsid?'&amp;':'?').'debug';
  $asid .= '&debug';
  $hasid .= '&amp;debug';
  $isid .= '<input type="hidden" name="debug" value="1" />';
}

  //====================
  // And... GO!
  //====================

if (isset($_GET['down']))
{
  header('Content-Disposition: attachment; filename="'.$file.'"');
  readfile($prepath.$presub.$postsub,$file);
  die();
}
else if (isset($_GET['upload']))
{
  //parent.ext_fclose();
  if ($_FILES)
  {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Upload</title>
    <style type="text/css"><!--
      @import "fileman.css";
    --></style>
  </head>
  <body style="overflow:hidden;background:#AEC8EC none;">
  <div style="padding:5px;">
<?php
    foreach($_FILES as $tfile)
    {
      if ($tfile['name'])
      {
        $tfilen = basename($tfile['name']);
        echo "Uploading '$tfilen'... ";
        move_uploaded_file($tfile['tmp_name'],'cache/up/'.$tfilen) or die(':(');
        if (fm_upload($tfilen,$d.$tfilen))
          echo '<strong style="color:#090;">Successful</strong> ';
        else
          echo '<strong style="color:#F00;">Failed</strong> ';
      }
    }
?>
      <input type="button" value="Done" onclick="window.location.href='index.php?p=a&d=<?php echo $hd,$hasid; ?>';" /> <input type="button" value="Upload more files" onclick="location.replace('fileman_ext.php?upload&amp;d=<?php echo $hd,$hasid; ?>');" />
    </div>
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
    <title>Upload</title>
    <style type="text/css"><!--
      @import "fileman.css";
    --></style>
    <script language="javascript" type="text/javascript">
    <!--
      function loading()
      {
        document.getElementById('l').style.display='block';
        document.getElementById('l').innerHTML='<'+'img src="images/throbber-loading.gif" /> <strong>Loading...</strong>';
      }
      function setmore(a)
      {
        // do nothing
      }
    //-->
    </script>
  </head>
  <body style="overflow:hidden;background:#AEC8EC none;">
    <div id="l" style="display:none;padding:0 0 100px 0;background:#5A94DE none;vertical-align:center;color:#FFFFFF;font-size:12pt;"></div>
    <form id="uploadform" action="fileman_ext.php?upload&amp;d=<?php echo $hd,$hasid; ?>" method="post" enctype="multipart/form-data">
      <div style="padding:5px;"><input type="file" name="f1" onblur="if (this.value) document.getElementById('uploadform').submit();loading();" onchange="if (this.value) document.getElementById('uploadform').submit();loading();" /><input type="submit" value="Upload" /> <input type="button" value="Cancel" onclick="window.location.href='index.php?p=a&d=<?php echo $hd,$hasid; ?>';" /></div>
    </form>
  </body>
</html>
<?
  }
}
else
{
  echo '<err>?</err>';
}
?>