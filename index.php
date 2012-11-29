<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 *
 * Licensed under the Creative Commons Attribution-NonCommercial-ShareAlike
 * http://creativecommons.org/licenses/by-nc-sa/3.0/
 * Unless another otherwise licensed in writing from Novawave Inc.
 *
 * CORE FILE - DO NOT DELETE
 * 
 */

$nokill = true; //Don't autokill if access denied.

@include_once 'persist.lib.php';

//if (!$_PERSIST['inst_id'] || !$_PERSIST['licensekey']) die('<body><a href="install.php">Install</a><script language="javascript" type="text/javascript">document.location.href="install.php"</script></body>');
if (!$_PERSIST['inst_id'] && !$manual_install) die('<body><a href="install.php">Install</a><script language="javascript" type="text/javascript">document.location.href="install.php"</script></body>');

require_once 'fileman.lib.php';
if ($ftpmode)
  include_once 'ftpsession.lib.php';
else
  include_once 'session.lib.php';

if ($_REQUEST['fmclip'] || $_POST['act']=='clip' || $_POST['act']=='declip' || $_POST['act']=='declipall')
{
  session_name('fmclip'); session_start();
  if (!$_SESSION['clip']) $_SESSION['clip'] = array();
  if (SID && $_POST['act']!='declipall' && $_POST['act']!='pastehere' && count($_SESSION))
  {
    $qsid .= ($qsid?'&':'?').SID; $hqsid .= ($hqsid?'&amp;':'?').SID;
    $asid .= '&'.SID; $hasid .= '&amp;'.SID;
    $isid .= '<input type="hidden" name="fmclip" value="'.substr(SID,7).'" />';
  }
}

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
  // Parse GET strings
  //====================

$pane = '';
if ($_GET['p']) $pane = $_GET['p']; // Get the pane
if (!$pane) $pane = ($uid||$_GET['d']||$status=='li'||$status=='gli'||(!$loginfirst&&$_PERSIST['users'][0]['priv']))?'f':'l'; // If no pane, use frameset or login
if (isset($_GET['login']) && !$uid) $pane = 'l';
if ($pane !== 'l' && !$user['priv'])
{ // Account disabled
  $pane = 'l';
  $status = 'gad';
}
if (!$isauth && $pane != 'l') // Access denied
{
  $pane = 'l';
  $status = 'pd';
}

$havmode = ($_GET['vmode']?'&amp;vmode='.$_GET['vmode']:'');
$avmode = ($_GET['vmode']?'&vmode='.$_GET['vmode']:'');

// Special page information
$spn = array('about'=>'About Filecharger','account'=>'Account Settings','admin'=>'Administration','help'=>'Help');
$spi = array('about'=>'icon','account'=>'icons/_opt','admin'=>'icons/_opt','help'=>'icons/_help');

if ($_FILES['dragupload']) $_POST['act'] = 'dragupload';
if ($_POST['act'])
{
  //====================
  // Carry out an action
  //====================
  $buffer = '';
  if ($fm_dev) $buffer .= "<tr><td colspan=\"2\">act: '{$_POST['act']}'</td><td><tr><td colspan=\"2\">val: '{$_POST['val']}'</td><td>";
  $redir = true;
  $acted = array(); // What files to select after action
  $retryact = ''; $retryval = ''; // What action to retry
  $conflict = 'rename';
  $val = $_POST['val']?$_POST['val']:'';
  if ($_POST['r_overwrite']) $conflict = 'overwrite';
  if ($_POST['r_rename']) $conflict = 'rename';
  switch($_POST['act'])
  {
   case 'delete':
    //--------------------
    // Delete
    //--------------------
    $tfiles = ids2files($val);
    foreach ($tfiles as $tfile)
    {
      $buffer .= "<tr><td>Deleting '$tfile'...</td><td>";
      if (!fm_canaccess($d.$tfile))
      {
        $buffer .= '<strong style="color:#F00;">Access denied</strong>';
        $redir = false;
      }
      else if (fm_delete($d.$tfile))
      {
        $buffer .= '<strong style="color:#090;">Successful</strong>';
      }
      else
      {
        $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfile)?', permission error (Filecharger installed incorrectly?)':', file doesn\'t exist').'</strong>';
        $redir = false;
      }
      $buffer .= "</td></tr>";
    }
    break;
   case 'rename':
   case 'replace':
    //--------------------
    // Rename
    //--------------------
    $tfiles = explode(',',$val);
    $tfile = $tfiles[0];
    $nfile = $tfiles[1];
    $buffer .= "<tr><td>Renaming '$tfile' to '$nfile'...</td><td>";
    if (!fm_canaccess($d.$tfile) || !fm_canaccess($d.$nfile))
    {
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
      $redir = false;
    }
    else if ($tfile == $nfile)
    {
      $buffer .= '<strong style="color:#D20;">Nothing to do</strong>';
      $acted[] = file2id($nfile.(fm_isdir($d.$nfile)?'/':''));
      $redir = false;
    }
    else if (!fm_exists($d.$tfile))
    {
      $buffer .= '<strong style="color:#D20;">"'.$tfile.'" doesn\'t exist.</strong>';
      $redir = false;
    }
    else if (!$conflict && fm_exists($d.$nfile))
    {
      $buffer .= '<strong style="color:#D20;">"'.$nfile.'" already exists.</strong>';
      $retryact = 'rename';
      $retryval = $val;
      $redir = false;
    }
    else if (fm_rename($d.$tfile,$d.$nfile,$conflict))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      $acted[] = file2id($nfile.(fm_isdir($d.$nfile)?'/':''));
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed, permission error (Filecharger installed incorrectly?)</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'extract':
    //--------------------
    // Extract
    //--------------------
    $tfiles = explode(',',$val);
    $tfile = $tfiles[0];
    $nfile = $tfiles[1];
    $buffer .= "<tr><td>Extracting '$tfile' to ".($nfile?"'$nfile'":'current folder')."...</td><td>";
    if (!fm_canaccess($d.$tfile) || !fm_canaccess($d.$nfile))
    {
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
      $redir = false;
    }
    else if (!fm_exists($d.$tfile))
    {
      $buffer .= '<strong style="color:#D20;">"'.$tfile.'" doesn\'t exist.</strong>';
      $redir = false;
    }
    else if (fm_extractzip($d.$tfile,$d.$nfile,$conflict))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      if ($nfile) $acted[] = file2id($nfile.'/');
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed, some files already exist, or permission error (Filecharger installed incorrectly?)</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'compress':
    //--------------------
    // Compress
    //--------------------
    $tfiles = explode(',',$val);
    $nfile = $tfiles[count($tfiles)-1];
  unset($tfiles[count($tfiles)-1]);
  $tfiles = ids2files(implode(',',$tfiles));
    $buffer .= "<tr><td>Compressing '".implode("','",$tfiles)."' to '$nfile'...</td><td>";
  foreach ($tfiles as $i => $tfile) if (fastcanaccess($tfile)) $tfiles[$i] = $d.$tfile;
  //if ($fm_dev) echo '<pre>'.persist_tophp($tfiles).'</pre>';
    if (!fm_canaccess($d.$nfile))
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
    else if (fm_compresszip($tfiles,$d.$nfile,$conflict))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      $acted[] = file2id($nfile);
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$nfile)?", '$nfile' already exists":', source files don\'t exist or permission error (Filecharger installed incorrectly?)').'</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'chmod':
    //--------------------
    // CHMOD
    //--------------------
    $tfile = $val;
    $_POST['perms'] = trim($_POST['perms']);
    $buffer .= "<tr><td>CHMODing '".basename($d.$tfile)."' to '".$_POST['perms']."'...</td><td>";
    if (!fm_canaccess($d.$tfile))
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
    else if (ctype_digit($_POST['perms']) && strlen($_POST['perms'])==3 && fm_chmod($d.$tfile,$_POST['perms']))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      $acted[] = file2id($tfile);
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfile)?', '.$_POST['perms'].' may not be a valid CHMOD value':', file doesn\'t exist').'</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'urlupload':
    //--------------------
    // Upload from URL
    //--------------------
    $tfile = $val;
    $buffer .= "<tr><td>Uploading file '".basename($tfile)."'...</td><td>";
    if (fm_urlupload($tfile,$d,$conflict))
      $buffer .= '<strong style="color:#090;">Successful</strong>';
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed (check your URL, or file may already exist)</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'upload':
    //--------------------
    // Upload
    //--------------------
    if ($fm_dev) echo '<pre>'.persist_tophp($_FILES).'</pre>';
  foreach($_FILES as $tfile)
    {
      if ($tfile['name'])
      {
        $tfilen = basename($tfile['name']);
        $buffer .= "<tr><td>Uploading file '{$tfile['name']}'...</td><td>";
        if (is_uploaded_file($tfile['tmp_name']))
        {
          $tfiletn = $tfilen; $i=1;
          while (file_exists('cache/up/'.$tfiletn)) $tfiletn = $tfilen.' ('.(++$i).')';
          if (move_uploaded_file($tfile['tmp_name'],'cache/up/'.$tfiletn))
          {
            $buffer .= '<strong style="color:#090;">Successful</strong>';
            $buffer .= "</td></tr><tr><td>...to current directory...</td><td>";
            $tfiletn2 = $tfilen; $i=1;
            //while (fm_exists($d.$tfiletn2)) $tfiletn2 = addbeforeext($tfilen,' ('.(++$i).')');
            if (fm_upload($tfiletn,$d.$tfiletn2,$conflict))
            {
              $buffer .= '<strong style="color:#090;">Successful</strong>';
              $acted[] = file2id($tfiletn2);
            }
            else
            {
              $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfiletn2)?", '$tfilen' already exists":', permission error (Filecharger installed incorrectly?)').'</strong>';
              $redir = false;
            }
          }
          else
          {
            $buffer .= '<strong style="color:#F00;">Failed, Filecharger may be installed incorrectly (check permissions on temporary directory "cache/up/")</strong>';
            $redir = false;
          }
        }
        else
        {
          if ($tfile['error']==UPLOAD_ERR_INI_SIZE)
        $buffer .= '<strong style="color:#F00;">Failed; file larger than PHP max upload file size</strong>';
          else if ($tfile['error']==UPLOAD_ERR_FORM_SIZE)
        $buffer .= '<strong style="color:#F00;">Failed; file larger than HTML form max upload file size</strong>';
          else if ($tfile['error']==UPLOAD_ERR_PARTIAL)
        $buffer .= '<strong style="color:#F00;">Failed; file didn\'t finish uploading, please try again</strong>';
          else if ($tfile['error']==UPLOAD_ERR_NO_TMP_DIR || $tfile['error']==UPLOAD_ERR_CANT_WRITE)
        $buffer .= '<strong style="color:#F00;">Failed; PHP misconfigured - nowhere to upload to</strong>';
      else
        $buffer .= '<strong style="color:#F00;">Failed; unknown error</strong>';
          $redir = false;
        }
        $buffer .= "</td></tr>";
      }
    }
    break;
   case 'dragupload':
    //--------------------
    // Drag upload
    //--------------------
    $num = count($_FILES['dragupload']['name']);
    for ($k=0; $k<$num; $k++)
    {
      $tfile = array(
        'name' => $_FILES['dragupload']['name'][$k],
        'tmp_name' => $_FILES['dragupload']['tmp_name'][$k],
        'size' => $_FILES['dragupload']['size'][$k],
        'type' => $_FILES['dragupload']['type'][$k],
        'error' => $_FILES['dragupload']['error'][$k]
      );
      
      if ($tfile['name'])
      {
        $tfilen = basename($tfile['name']);
        $buffer .= "<tr><td>Uploading file '{$tfile['name']}'...</td><td>";
        if (is_uploaded_file($tfile['tmp_name']))
        {
          $tfiletn = $tfilen; $i=1;
          while (file_exists('cache/up/'.$tfiletn)) $tfiletn = $tfilen.' ('.(++$i).')';
          if (move_uploaded_file($tfile['tmp_name'],'cache/up/'.$tfiletn))
          {
            $buffer .= '<strong style="color:#090;">Successful</strong>';
            $buffer .= "</td></tr><tr><td>...to current directory...</td><td>";
            $tfiletn2 = $tfilen; $i=1;
            //while (fm_exists($d.$tfiletn2)) $tfiletn2 = addbeforeext($tfilen,' ('.(++$i).')');
            if (fm_upload($tfiletn,$d.$tfiletn2,$conflict))
            {
              $buffer .= '<strong style="color:#090;">Successful</strong>';
              $acted[] = file2id($tfiletn2);
            }
            else
            {
              $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfiletn2)?", '$tfilen' already exists":', permission error (Filecharger installed incorrectly?)').'</strong>';
              $redir = false;
            }
          }
          else
          {
            $buffer .= '<strong style="color:#F00;">Failed, Filecharger may be installed incorrectly (check permissions on temporary directory "cache/up/")</strong>';
            $redir = false;
          }
        }
        else
        {
          if ($tfile['error']==UPLOAD_ERR_INI_SIZE)
        $buffer .= '<strong style="color:#F00;">Failed; file larger than PHP max upload file size</strong>';
          else if ($tfile['error']==UPLOAD_ERR_FORM_SIZE)
        $buffer .= '<strong style="color:#F00;">Failed; file larger than HTML form max upload file size</strong>';
          else if ($tfile['error']==UPLOAD_ERR_PARTIAL)
        $buffer .= '<strong style="color:#F00;">Failed; file didn\'t finish uploading, please try again</strong>';
          else if ($tfile['error']==UPLOAD_ERR_NO_TMP_DIR || $tfile['error']==UPLOAD_ERR_CANT_WRITE)
        $buffer .= '<strong style="color:#F00;">Failed; PHP misconfigured - nowhere to upload to</strong>';
      else
        $buffer .= '<strong style="color:#F00;">Failed; unknown error</strong>';
          $redir = false;
        }
        $buffer .= "</td></tr>";
      }
    }
    if ($_REQUEST['response'] === 'ajax')
    {
      if ($fm_dev) $redir = false;
      if ($redir) die('success;index.php?d='.$hd.'&act&p=v'.$asid.'&select='.implode(',',$acted));
      die($buffer);
    }
    break;
   case 'textedit':
    //--------------------
    // Text edit
    //--------------------
    /* $buffer .= "<tr><td>Editing file '$file'...</td><td>";
    if (!fm_canaccess($d.$tfile))
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
    else if (fm_editfile($d.$tfile,$_POST['text']))
      $buffer .= '<strong style="color:#090;">Successful</strong>';
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";*/
  $buffer .= "<tr><td colspan=\"2\">No longer supported: use textedit.php instead.</td></tr>";
    break;
   case 'newfold':
    //--------------------
    // New Folder
    //--------------------
    $tfile = $_POST['val'];
    $buffer .= "<tr><td>Creating folder '$tfile'...</td><td>";
    if (!fm_canaccess($d.$tfile))
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
    else if (fm_mkdir($d.$tfile))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      $acted[] = file2id($tfile.'/');
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfile)?", '$tfile' already exists":', permission error (Filecharger installed incorrectly?)').'</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'newfile':
    //--------------------
    // New File
    //--------------------
    $tfile = $_POST['val'];
    $buffer .= "<tr><td>Creating file '$tfile'...</td><td>";
    if (!fm_canaccess($d.$tfile))
      $buffer .= '<strong style="color:#F00;">Access denied</strong>';
    else if (fm_mkfile($d.$tfile))
    {
      $buffer .= '<strong style="color:#090;">Successful</strong>';
      $acted[] = file2id($tfile);
    }
    else
    {
      $buffer .= '<strong style="color:#F00;">Failed'.(fm_exists($d.$tfile)?", '$tfile' already exists":', permission error (Filecharger installed incorrectly?)').'</strong>';
      $redir = false;
    }
    $buffer .= "</td></tr>";
    break;
   case 'clip':
    //--------------------
    // Add to clipboard
    //--------------------
    $tfiles = ids2files(substr($_POST['val'],2));
    $approvedfiles = array();
    foreach ($tfiles as $i => $tfile)
    {
       if (fastcanaccess($tfile))
      {
        $approvedfiles[] = $d.$tfile;
        $buffer .= '<tr><td>Adding \''.$tfile.'\' to clipboard...</td><td><strong style="color:#090;">Successful</strong></td></tr>';
      }
      else
      {
        $buffer .= '<tr><td>Adding \''.$tfile.'\' to clipboard...</td><td><strong style="color:#F00;">Access denied</strong></td></tr>';
        $redir = false;
      }
    }
    if ($approvedfiles)
    {
      if (substr($_POST['val'],0,1)=='c' || substr($_POST['val'],0,1)=='m')
        $_SESSION['cliptype'] = substr($_POST['val'],0,1);
      $_SESSION['clip'] = array_unique(array_merge($_SESSION['clip'],$approvedfiles));
    }
    break;
   case 'declip':
    //--------------------
    // Remove from clipboard
    //--------------------
    if (!$_SESSION)
    {
      $buffer .= '<tr><td>Removing from clipboard...</td><td><strong style="color:#F00;">Failed; clipboard doesn\'t exist</strong></td></tr>';
      $redir = false;
      break;
    }
    $tfiles = ids2files(substr($_POST['val'],2));
    foreach ($tfiles as $i => $tfile)
    {
      $tfiles[$i] = $presub.$postsub.$tfile;
      $buffer .= '<tr><td>Removing \''.$tfile.'\' from clipboard...</td><td><strong style="color:#090;">Successful</strong></td></tr>';
    }
    $_SESSION['clip'] = array_unique(array_diff($_SESSION['clip'],$tfiles));
    if (!count($_SESSION['clip']))
    {
      $_SESSION = array();
      if (isset($_COOKIE['fmclip'])) setcookie('fmclip', '', time()-42000, '/');
      session_destroy();
    }
    break;
   case 'pastehere':
    //--------------------
    // Paste clipboard here
    //--------------------
    if (!$_SESSION)
    {
      $buffer .= '<tr><td>Paste clipboard here...</td><td><strong style="color:#F00;">Failed; clipboard doesn\'t exist</strong></td></tr>';
      $redir = false;
    }
    else if ($file)
    {
      $buffer .= '<tr><td>Paste clipboard here...</td><td><strong style="color:#F00;">Failed; not in a folder</strong></td></tr>';
      $redir = false;
    }
    else foreach ($_SESSION['clip'] as $i => $tfilen)
    {
      if ($_SESSION['cliptype']=='c')
      {
        $tfiletn = filefrompath($tfilen);
        $buffer .= "<tr><td>Copying '$tfiletn'...</td><td>";
        if ($tfilen == $d.$tfiletn) $tfiletn = addbeforeext($tfiletn,' (copy)');
        if (fm_copy($tfilen,$d.$tfiletn,$conflict))
        {
          $buffer .= '<strong style="color:#090;">Successful</strong>';
          $acted[] = file2id($tfiletn);
          unset($_SESSION['clip'][$i]);
        }
        else if (fm_exists($d.$tfiletn))
        {
          $buffer .= '<strong style="color:#F00;">Failed, file/folder already exists</strong>';
          $redir = false;
        }
        else
        {
          $buffer .= '<strong style="color:#F00;">Failed, permission error</strong>';
          $redir = false;
        }
      }
      if ($_SESSION['cliptype']=='m')
      {
        $buffer .= "<tr><td>Moving '".filefrompath($tfilen)."'...</td><td>";
        if (fm_rename($tfilen,$d.filefrompath($tfilen),$conflict))
        {
          $buffer .= '<strong style="color:#090;">Successful</strong>';
          $acted[] = file2id(filefrompath($tfilen));
          unset($_SESSION['clip'][$i]);
        }
        else if (fm_exists($d.filefrompath($tfilen)))
        {
          $buffer .= '<strong style="color:#F00;">Failed, file/folder already exists</strong>';
          $redir = false;
        }
        else
        {
          $buffer .= '<strong style="color:#F00;">Failed, permission error</strong>';
          $redir = false;
        }
      }
    }
    if (!count($_SESSION['clip']))
    {
      $_SESSION = array();
      if (isset($_COOKIE['fmclip'])) setcookie('fmclip', '', time()-42000, '/');
      session_destroy();
    }
  break;
   case 'declipall':
    //--------------------
    // Remove all from clipboard
    //--------------------
    if (!$_SESSION)
    {
      $buffer .= '<tr><td>Clearing clipboard...</td><td><strong style="color:#F00;">Failed; clipboard doesn\'t exist</strong></td></tr>';
      $redir = false;
      break;
    }
    $_SESSION = array();
    if (isset($_COOKIE['fmclip'])) setcookie('fmclip', '', time()-42000, '/');
    session_destroy();
    $buffer .= '<tr><td>Clearing clipboard...</td><td><strong style="color:#090;">Successful</strong></td></tr>';
    break;
   default:
    echo 'Error: Action not supported.';
  }
  if ($fm_dev) $redir = false;
  if ($redir) header('Location: http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '\\/').'/index.php?d='.$hd.($_GET['p']?'&act&p='.$_GET['p']:'').$avmode.$asid.'&select='.implode(',',$acted));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Directory</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      body
      {
        color: #000;
        background-color: #FFF;
        font-size: 12pt;
        padding: 8px 12px 8px 12px;
      }
      table
      {
        border: 1px solid #1e395b;
      }
      td,
      th.begin
      {
        border-bottom: 1px solid #1e395b;
      }
      th
      {
        text-align: left;
      }
    --></style>
<?php
  if ($redir)
  {
?>
    <meta http-equiv="refresh" content="1;url=index.php?d=<?php echo $hd,($_GET['p']?'&amp;act&amp;p='.$_GET['p']:''),$havmode,$hasid; ?>" />
    <script type="text/javascript">
    <!--
      window.location.href = 'index.php?d=<?php echo $hd,($_GET['p']?'&act&p='.$_GET['p']:''),$avmode,$asid; ?>';
    //-->
    </script>
<?php
  }
?>
  </head>
  <body>
    <table>
      <tr><th colspan="2" class="begin">
        Doing the following things...
      </th></tr>
      <?php echo $buffer; ?>
      <tr><th colspan="2">
<?php
  if ($redir)
  {
?>
        You should be redirected automatically.<br />
<?php
  }
?>
        <a href="index.php?d=<?php echo $hd,($_GET['p']?'&amp;act&amp;p='.$_GET['p']:''),$havmode,$hasid,'&amp;select=',implode(',',$acted); ?>">Continue</a>
      </th></tr>
    </table>
  </body>
</html>
<?php
  fm_close();die();
}

if ($pane == 'l')
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>Novawave Filecharger</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css">
    <!--
      html { height:100%;}
      body
      {
        color: #1e395b;
        background-color: #429cca;
        font-size: 8pt;
        height: 100%;
      }
	  a {
	    text-decoration:none;
		color:#1e395b;
	  }
    -->
    </style>
    <script language="javascript" type="text/javascript">
    <!--
      var height=0, loaded=false;
      function resize(fload)
      {
        if (!loaded && !(loaded=fload)) return;
        if (!height) height = (document.getElementById('wrapper').offsetHeight);
        document.getElementById('wrapper').style.paddingBottom = (document.getElementById('wraptab').offsetHeight>height+102)?'100px':'0';
        document.getElementById('logo').style.display = (document.getElementById('wraptab').offsetHeight>height+2)?'block':'none';
      }
    //-->
    </script>
  </head>
  <body onload="resize(1)" onresize="resize(0)">
    <table border="0" margin="0" padding="0" width="100%" height="100%">
      <tr><td id="wraptab" align="center" valign="middle"><div id="wrapper">
        <div style="text-align:center;" id="logo"><img src="images/loginlogosmall.gif" /></div>
<?php
  if ($status)
  {
?>
        <div id="status"><div class="box" style="width:330px;"><div class="boxd">
<?php
    switch ($status)
    {
    case 'li':
      echo 'You have logged in.';
      break;
    case 'gli':
      echo 'You are a guest.';
      break;
    case 'nli':
      echo 'Incorrect username/password.';
      break;
    case 'lo':
      echo 'You have logged out.';
      break;
    case 'ad':
      echo 'Your account is disabled.';
      break;
    case 'gad':
      echo 'The guest account is disabled.';
      break;
    case 'pd':
      echo 'You do not have permission to view this '.(substr('/'.$d,-1)=='/'?'folder':'file').'.';
      break;
    case 'nsc':
      echo 'Error connecting to FTP server. Did you misspell it?';
      break;
    default:
      echo 'Error: Code "'.$status.'" error.';
    }
?>
          <div style="text-align:right;"><input type="button" value="OK" onclick="document.getElementById('status').style.display = 'none'" /></div>
        </div></div><br /></div>
<?php
  }
  //echo 'persist = ';print_r($_PERSIST);
?>
        <div class="box" style="width:330px;">
<?php
  if (!$uid)
  {
?>
          <a class="boxh boxhd"<?php if ($_PERSIST['users'][0]['priv']) echo 'href="index.php?d=',$hd,'"';?> id="ucp_a">
            <?php if ($_PERSIST['users'][0]['priv']) { ?><span class="togbtn" id="ucp_col">X</span><?php } ?>
            Log in
          </a>
          <div class="boxd lb" id="ucp">
            <form action="index.php?d=<?php echo $hd; ?>&login" method="post" target="_parent">
<?php if ($ftpmode) { ?>
              <div><label for="server">Server/port:</label></div>
              <div><input type="text" class="textbox" id="server" name="server" value="<?php if ($_REQUEST['server']) echo $_REQUEST['server']; ?>" /> <input type="text" class="textbox" id="port" name="port" value="<?php echo $_REQUEST['port']?$_REQUEST['port']:'21'; ?>" size="3" /></div>
<?php } ?>
              <div><label for="uname">Username:</label></div>
              <div><input type="text" class="textbox" id="uname" name="uname" value="<?php if ($_REQUEST['uname']) echo $_REQUEST['uname']; ?>" /></div>
              <div><label for="pass">Password:</label></div>
              <div><input type="password" class="textbox" id="pass" name="pass" value="" /></div>
<?php if (!$ftpmode) { ?>
              <div><input type="checkbox" id="rem" name="rem" checked="checked" /><label for="rem"> Remember me</label></div>
<?php } else { ?>
              <div><input type="checkbox" id="ftps" name="ftps"<?php if ($_REQUEST['ftps']) echo ' checked="checked"'; ?> /><label for="ftps"> FTPS connection</label></div>
<?php } ?>
              <div>&nbsp;</div>
              <div><input type="hidden" name="sact" value="login" /><input type="submit" name="login" value="Log in" /><?php if ($_PERSIST['users'][0]['priv']) { ?> <input type="button" value="Cancel (log in as guest)" onclick="document.location.href='index.php?d=<?php echo $hd; ?>'" /><?php } ?></div>
            </form>
<?php
  }
  else
  {
?>
          <a class="boxh boxhd"<?php if ($_PERSIST['users'][0]['priv']) echo 'href="index.php?d=',$hd,'"';?> id="ucp_a">
            <span class="togbtn" id="ucp_col">X</span>
            Log out
          </a>
          <div class="boxd lb" id="ucp">
            <p></p>
            <form action="index.php<?php echo $hqsid; ?>" method="post" id="flo"><input type="submit" value="Log Out" /><input type="hidden" name="logout" value="Log out" />&nbsp;</form>
<?php
  }
  
?>
          </div>
        </div>
      </div>
	  <!-- Do not remove, change, or obscure this notice //-->
	  <br/><a href="http://filecharger.com">Filecharger</a> from <a href="http://novawave.ca">Novawave Inc.</a></td></tr>
    </table>
  </body>
</html>
<?php
}
else if ($pane == 'f')
{
  //====================
  // Frameset
  //====================
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo $_GET['sp']?$spn[$_GET['sp']]:basename($d).(!$d||endsWith($d,'/')?'/':''); ?> - Filecharger</title>
  </head>
  <frameset rows="40,*" border="0"<?php /* if (!$file) echo ' onresize="if (main.loaded) main.resize()"'; */ ?>>


    <frame name="dirname" src="index.php?p=i&amp;d=<?php echo $hd,($_GET['sp']?'&amp;sp='.$_GET['sp']:''),$hasid; ?>" frameborder="0" marginwidth="0" marginheight="0" noresize="noresize" scrolling="no" />
    <frameset cols="220,*" border="0">
      <frame name="sidebar" src="index.php?p=s&amp;d=<?php echo $hd,($_GET['sp']?'&amp;sp='.$_GET['sp']:''),$havmode,$hasid,($status?'&amp;status='.$status:''); ?>" frameborder="0" marginwidth="0" marginheight="0" noresize="noresize" scrolling="auto" />
<?php
if (($_GET['vmode'] == 'list' || !$_GET['vmode']) && !$_GET['sp'] && !$file)
{
?>
      <frameset rows="28,*" border="0">
        <frame name="actbar" src="index.php?p=a&amp;d=<?php echo $hd,$hasid ?>" frameborder="0" marginwidth="0" marginheight="0" noresize="noresize" scrolling="no" />
        <frame name="main" src="index.php?p=v&amp;d=<?php echo $hd,$havmode,$hasid;?>" frameborder="0" marginwidth="0" marginheight="0" noresize="noresize" scrolling="auto" />
      </frameset>
<?php
}
else
{
?>
      <frame name="main" src="<?php

if ($_GET['sp']=='admin')
  echo 'admin.php?d=',$hd,$havmode,'&amp;sp=',$_GET['sp'],$hasid;
else if ($_GET['sp'])
  echo 'index.php?p=v&amp;d=',$hd,$havmode,($_GET['sp']?'&amp;sp='.$_GET['sp']:''),$hasid;
else if ($_GET['vmode'] == 'e_txt')
  echo 'textedit.php?p&amp;d=',$hd,$hasid;
else if (($file && !$_GET['vmode'] && ($fileext=='html'||$fileext=='htm'||$fileext=='php'||$fileext=='phps'&&$support_phps||$fileext=='swf')) || $_GET['vmode']=='frame')
  echo fm_readdirect()?$fullurl:('index.php?p=v&amp;d='.$hd.$havmode.$hasid.'&amp;vmode=txt');
else if (substr($file,-4)=='.flv' && !$_GET['vmode'])
  echo 'flvplayer.php?d=',$hd,$hasid;
else
  echo 'index.php?p=v&amp;d=',$hd,$havmode,$hasid;

?>" frameborder="0" marginwidth="0" marginheight="0" noresize="noresize" scrolling="auto" />
<?php
}
?>
    </frameset>
  </frameset>
  <noframes>
    Error: Frames-less version not yet supported. Please check back later.
  </noframes>
</html>
<?php
}
else if ($pane == 'i')
{
  //====================
  // Location bar
  //====================
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Directory</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      body
      {
        color: #1e395b;
        background-color: #429cca;
        font-size: 8pt;
        padding: 8px 12px 8px 12px;
      }
      .dir
      {
        color: #000000;
        background: #FFFFFF none no-repeat scroll 1px 1px;
        border: 1px solid #1e395b;
        font-family: "Courier New", monospace;
        margin: 0px;
        padding: 2px 2px 2px 20px;
        height: 14px;
      }
      .dir a
      {
        color: #000000;
        text-decoration: none;
      }
      .dir a:hover
      {
        color: #072A66;
        text-decoration: underline;
      }
    --></style>
  </head>
  <body<?php if (!$file) echo ' onmousedown="parent.main.cmenucl()" onmouseup="parent.main.focus()"'; ?>>
<?php
  if ($_GET['sp'])
  {
?>
<div class="dir" style="background-image: url(images/<?php echo $spi[$_GET['sp']]; ?>.gif)">Special: <?php echo $spn[$_GET['sp']]; ?> [<a href="index.php?d=<?php echo $hd,$havmode,$hasid ?>" target="_parent">Back</a>]</div>
<?php
  }
  else
  {
?>
   <div class="dir" style="background-image: url(images/icons/<?php echo fticon($fileext); ?>.gif)"><?php

    $patharray = explode('/',$postsub);
    $cururl = $presub;
    $vpreurl = $preurl;
    if (!fm_readdirect())
      $vpreurl = 'ftp://'.$ftp_server.'/';
    echo '<a href="index.php?d=',($presub?fm_urlencode($presub):'./'),$hasid,'" target="_top">',substr($vpreurl.$presub,0,-1),'</a>/';
    foreach ($patharray as $cdir)
    {
      if ($cdir)
      {
        $cururl .= $cdir.'/';
        echo "<a href=\"index.php?d=$cururl$hasid\" target=\"_top\">$cdir</a>/";
      }
    }
    if ($file)
    {
      echo "<a href=\"index.php?d=$cururl$file$hasid\" target=\"_top\">$file</a>";
    }

   ?></div>
<?php
  }
?>
  </body>
</html>
<?php
}
else if ($pane == 's')
{
  //====================
  // Sidebar
  //====================
  $fileinfo = array();
  $files = array();
  if ($file)
    $fileinfo = fm_fileinfo($d);
  else if ($_GET['mi'])
  {
    $files = ids2files($_GET['mi']);
    if (count($files)==1)
      $fileinfo = fm_fileinfo($presub.$postsub.$files[0]);
  }
  else if (!fm_readdirect() && endsWith('/'.$d,'/'))
  {
    //$fileinfo = fm_fileinfo($d);
    $fileinfo = array(
'name' => basename($d)?basename($d):'/',
'bname' => basename($d).'/',
'id' => file2id(basename($d).'/'),
'isdir' => true,
'size' => 0,
'tsize' => '-',
'modified' => 0,
'tmodified' => '-',
'tdmodified' => '-',
'perms' => 0,
'owner' => '',
'group' => '',
'ext' => '/',
'img' => fticon('/'),
'type' => textfiletype('/'),
'ft' => 1,
'writable' => true,
'isvimg' => false,
'imgsize' => false
    );
  }
  else
    $fileinfo = fm_fileinfo($d);
  $vmode = $_GET['vmode'];
  if ($vmode == 'dl')
  {
    if (fm_contenttype(fext($file)))
    {
      header('Content-Type: '.fm_contenttype(fext($file)));
      header('Content-Disposition: attachment; filename="'.$file.'"');
    }
    echo fm_contents($file);
    fm_close();die();
  }
  if (!$vmode)
  {
    if ($fileinfo['isvimg'])
      $vmode = 'vimg';
    else if ($fileinfo['ext'] == 'css' || $fileinfo['ext'] == 'txt' || $fileinfo['ext'] == 'ini')
      $vmode = 'txt';
    else if ($fileinfo['ext'] == 'html' || $fileinfo['ext'] == 'htm' || $fileinfo['ext'] == 'php')
      $vmode = 'frame';
    else
      $vmode = 'list';
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>Sidebar</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      html
      {
        background: #429cca none;
      }
      body
      {
        color: #1e395b;
        background: #429cca none;
        font-size: 8pt;
      }
      #wrapper
      {
        padding: 4px 12px 12px 12px;
      }
      .file
      {
        width: 100%;
        display: block;
        font-size: 10px;
      }
      .fdiv
      {
        overflow: hidden;
        margin-left: -5px;
        margin-right: -9px;
      }
      /*.lb div a
      {
        display: block;
        vertical-align: middle;
        border: 1px solid #FFFFFF;
        padding: 1px;
      }
      .lb div a:hover
      {
        border: 1px solid #6699CC;
        background-color: #D7E7F7;
        text-decoration: none;
      }
      .lb div a img
      {
        vertical-align: middle;
      }*/
	  #foot {
	    width:90%;
		margin:auto;
		text-align:center;
	  }
	  #foot a {
	    text-decoration:none;
		color:#1e395b;
	  }
    --></style>
    <script type="text/javascript" language="javascript">
    <!--
      var cdetails='<div class="overflowable"><strong><?php echo jsesc(htmlspecialchars($fileinfo['name'])); ?><\/strong><\/div><?php if (!$fileinfo['writable']) echo '<strong style="color:#F00">Read-only</strong><br \/>'; ?><?php echo $fileinfo['type']; ?><br \/><br \/><?php if ($fileinfo['imgsize'][2]) { ?>Dimensions: <?php echo $fileinfo['imgsize'][0].'x'.$fileinfo['imgsize'][1]; ?><br \/><?php   } ?>Modified: <?php echo $fileinfo['tmodified']; ?><br \/>Size: <?php echo $fileinfo['tsize']; ?><br \/>';
      var fmsb = '101';
      function onLoad_sidebar()
      {
        if (document.getElementById('uploaddiv'))
          document.getElementById('uploaddiv').style.display = 'none';
        var ca = document.cookie.split(';');
        for (var i=0; i < ca.length; i++)
        {
          var c = ca[i];
          while (c.charAt(0)==' ') c = c.substr(1);
          if (c.indexOf('fmsb=') == 0) fmsb = c.substr(5);
        }
        if (fmsb.charAt(0)=='0') pretoggle('ucp');
<?php
  if (!$_GET['sp'])
  {
?>
        if (fmsb.charAt(1)=='0') pretoggle('fftask');
        if (fmsb.charAt(2)=='0') pretoggle('details');
<?php
  }
?>
      }
      function setfmsb()
      {
        value = (document.getElementById('ucp').style.display=='none'?'0':'1')
          + (document.getElementById('fftask').style.display=='none'?'0':'1')
          + (document.getElementById('details').style.display=='none'?'0':'1');
        if (value == '101')
        {
          var date = new Date();
          date.setTime(date.getTime()-(24*60*60*1000));
          document.cookie = "fmsb=; expires="+date.toGMTString()+'; path=/';
        }
        else
          document.cookie = 'fmsb='+value+'; path=/';
      }
      function toggle(elem)
      {
        if (document.getElementById(elem).style.display == 'none')
        {
          document.getElementById(elem).style.display = 'block';
          document.getElementById(elem+'_exp').style.display = 'none';
          document.getElementById(elem+'_col').style.display = 'block';
        }
        else
        {
          document.getElementById(elem).style.display = 'none';
          document.getElementById(elem+'_exp').style.display = 'block';
          document.getElementById(elem+'_col').style.display = 'none';
        }
        parent.main.focus();
        setfmsb();
      }
      function pretoggle(elem)
      {
        document.getElementById(elem).style.display = 'none';
        document.getElementById(elem+'_exp').style.display = 'block';
        document.getElementById(elem+'_col').style.display = 'none';
      }
      function getft(fn)
      //Translated from ft() in fileman.lib.php
      {
        var fext;
        fext = fn.substr(fn.lastIndexOf(".")+1).toLowerCase();
        switch (fext)
        {
        case 'htm':
        case 'html':
          return 2; // HTML [plaintext]
        case 'php':
        case 'php1':
        case 'php2':
        case 'php3':
        case 'php4':
        case 'php5':
        case 'php6':
        case 'php7':
        case 'asp':
        case 'cgi':
        case 'jsp':
        case 'phtml':
          return 3; // server-side scripted HTML [plaintext]
        case 'txt':
        case 'css':
        case 'js':
        case 'ini':
        case 'log':
        case 'phps':
        case 'h':
        case 'c':
        case 'cpp':
        case 'java':
        case 'htaccess':
          return 4; // Other plaintext
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
          return 5; // Browser-displayable images
        case 'bmp':
          return 6; // Other images
        case 'zip':
          return 7; // ZIP archives
        default:
          return 0; // Other
        }
      }
      function restore()
      { //flag
        document.getElementById('details').innerHTML = cdetails;
        document.getElementById('fftask').innerHTML = '<?php if ($postsub) { ?><div><a href="javascript:void(0);" onclick="parent.location.href = \'index.php?d=<?php echo fm_urlencode($presub.upOne($postsub,$presub)),$hasid; ?>\'" target="_top">Up one level</a></div><?php } ?><div><a href="javascript:void(0);" onclick="f_newfold();" target="_top">New Folder</a></div><div><a href="javascript:void(0);" onclick="f_newfile();" target="_top">New Text Document</a></div><div><a href="javascript:void(0);" onclick="parent.main.fdrag_openform();return false" target="_top">Upload</a></div><div><a href="javascript:void(0);" onclick="f_urlupload();" target="_top">Upload from URL</a></div>';
      }
      function f_rename(f)
      {
        if (f.charAt(f.length-1)=='/') f = f.substring(0,f.length-1);
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var tmp = prompt('What would you like the new name of this file to be?',f);
        if (tmp != null && tmp != f)
        {
          document.actions.act.value = 'rename';
          document.actions.val.value = (f + ',' + tmp);
          document.actions.submit();
          parent.main.fdrag_uploading('Renaming...');
        }
      }
      function f_extract(f,t)
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        if (f.charAt(f.length-1)=='/') f = f.substring(0,f.length-1);
        if (t==0) var tmp = '';
    else tmp = f.substr(0,(f.substr(f.length-4)=='.zip')?f.length-4:f.length);
    if (t==2) tmp = prompt('Where would you like to extract to?',tmp);
        if (tmp != null)
        {
          document.actions.act.value = 'extract';
          document.actions.val.value = (f + ',' + tmp);
          document.actions.submit();
          parent.main.fdrag_uploading('Extracting...');
        }
      }
      function f_compress(seld)
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var tmp = id2file(seld.split(',')[0]);
        tmp = tmp.substr(0,tmp.indexOf('.')>0?tmp.indexOf('.'):tmp.length)+'.zip';
        tmp = prompt('Where would you like to compress to?',tmp);
        if (tmp != null && tmp.indexOf(',')!=-1)
        {
          tmp = id2file(seld.split(',')[0]);
          tmp = tmp.substr(0,tmp.indexOf('.')>0?tmp.indexOf('.'):tmp.length)+'.zip';
        }
        
        if (tmp != null)
        {
          document.actions.act.value = 'compress';
          document.actions.val.value = (seld + ',' + tmp);
          document.actions.submit();
          parent.main.fdrag_uploading('Compressing...');
        }
      }
      function f_urlupload()
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var tmp = prompt('URL of file to upload?','');
        if (tmp != null)
        {
          document.actions.act.value = 'urlupload';
          document.actions.val.value = tmp;
          document.actions.submit();
          parent.main.fdrag_uploading('Uploading from URL...');
        }
      }
      function f_newfold()
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var tmp1 = 'New Folder';
        var i=1;
        while (parent.main.exists('fold_New_0_Folder'+(i==1?'':'_0_'+i))) tmp1='New Folder '+(++i);
        var tmp = prompt('What would you like the name of the new folder to be?',tmp1);
        if (parent.main.exists('fold_'+tmp)) alert('Error: Folder already exists.');
        else if (tmp != null)
        {
          document.actions.act.value = 'newfold';
          document.actions.val.value = tmp;
          document.actions.submit();
          parent.main.fdrag_uploading('Creating new folder...');
        }
      }
      function f_newfile()
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var tmp1 = 'New Text Document.txt';
        var i=1;
        while (parent.main.exists('file_New_0_Text_0_Document'+(i==1?'':'_0_'+i)+'_d_txt')) tmp1='New Text Document '+(++i)+'.txt';
        var tmp = prompt('What would you like the name of the new text document to be?',tmp1);
        if (parent.main.exists('file_'+tmp)) alert('Error: File already exists.');
        else if (tmp != null)
        {
          document.actions.act.value = 'newfile';
          document.actions.val.value = tmp;
          document.actions.submit();
          parent.main.fdrag_uploading('Creating new file...');
        }
      }
      function f_delete(seld, forcedel)
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        var sels = seld.split(',');
        var tmp = '';
        for (var i=0; i<5&&i<sels.length; i++)
        {
          if (i == 4) tmp = tmp.concat(', ...');
          else if (i == 0) tmp = tmp.concat(id2file(sels[i]));
          else tmp = tmp.concat(', ',id2file(sels[i]));
        }
        if (!forcedel && !confirm('Are you sure you want to delete the following?\r\n\r\n'+tmp)) return false;
        document.actions.act.value = 'delete';
        document.actions.val.value = seld;
        document.actions.submit();
        parent.main.fdrag_uploading('Deleting...');
      }
      function f_clip(type,seld)
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        sels = seld.split(',');
        for (var i=0;i<sels.length;i++)
          if (parent.main.isclipped(sels[i])) sels[i] = '';
        seld = sels.join(',');
        document.actions.act.value = 'clip';
        document.actions.val.value = type+' '+seld;
        document.actions.submit();
        parent.main.fdrag_uploading('Adding to clipboard...');
      }



      function f_declip(seld)
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        sels = seld.split(',');
        for (var i=0;i<sels.length;i++)
          if (!parent.main.isclipped(sels[i])) sels[i] = '';
        seld = sels.join(',');
        document.actions.act.value = 'declip';
        document.actions.val.value = 'n '+seld;
        document.actions.submit();
        parent.main.fdrag_uploading('Removing from clipboard...');
      }
      function f_declipall()
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        document.actions.act.value = 'declipall';
        document.actions.val.value = '';
        document.actions.submit();
        parent.main.fdrag_uploading('Clearing clipboard...');
      }
      function f_pastehere()
      {
        if (document.actions.act.value != '')
        {
          alert('Error: An action is already being carried out. If it was not successful because of a bad Internet connection, please reload this page.');
          return;
        }
        document.actions.act.value = 'pastehere';
        document.actions.val.value = '';
        document.actions.submit();
        parent.main.fdrag_uploading('Pasting...');
      }
      function id2file(id)
      {
        return parent.main.document.getElementById(id).title+(id.substring(0,4)=='fold'?'/':'');
      }
      function openupload()
      {
        document.getElementById('uploaddiv').style.display='block';
      }
      var mo_ucp = false;
    //-->
    </script>
  </head>
  <body onload="onLoad_sidebar()"<?php if (!$file) echo ' onmousedown="parent.main.cmenucl()" onmouseup="if (!mo_ucp) parent.main.focus()"'; ?>>
<?php
  if ($_GET['sp'])
  {
?>
    <div style="padding-bottom:8px;"><a class="ablink" href="index.php?d=<?php echo $hd,$hasid,$havmode; ?>" target="_parent" onclick="parent.main.focus()">&laquo; Back</a></div>
<?php
  }
  else if ($file)
  {
?>
    <div style="padding-bottom:8px;"><a class="ablink" href="index.php?d=<?php echo ($presub.$postsub==''?'./':fm_urlencode($presub.$postsub)),$hasid; ?>" target="_parent" onclick="parent.main.focus()">&laquo; Back to folder</a></div>
<?php
  }
  else if ($postsub)
  {
?>
    <div style="padding-bottom:8px;"><a class="ablink" href="index.php?d=<?php echo fm_urlencode($presub.upOne($postsub,$presub)),$hasid; ?>" target="_parent" onclick="parent.main.focus()">&laquo; <?php echo $vmode=='list'?'Up one level':'Back to folder'; ?></a></div>
<?php
  }
  ?><div id="wrapper"><div id="clipboard"<?php if (!$_SESSION) echo ' style="display:none"'; ?>><?php
  if ($_SESSION)
  {
?>
    <div class="box"><div class="boxd" style="border-bottom: 1px dotted #1e395b;padding:5px 10px;">
      <div style="float:right;color:#888;"><?php echo count($_SESSION['clip']) ?> file<?php if (count($_SESSION['clip'])!==1) echo 's' ?></div><strong><?php echo $_SESSION['cliptype']=='m'?'Move to...':'Copy to...' ?></strong>
    </div><div class="boxd">
<?php
    foreach ($_SESSION['clip'] as $clipfile)
    {
?>
      <div class="overflowable"><img src="images/icons/<?php echo fticon(fext(filefrompath($clipfile))); ?>.gif" /> <?php echo filefrompath($clipfile); ?></div>
<?php
    }
?>
      <div style="text-align:right;"><input type="button" value="<?php echo $_SESSION['cliptype']=='m'?'Move here':'Copy here' ?>" onclick="f_pastehere()" /></div>
      <div style="text-align:right;"><input type="button" value="Cancel" onclick="f_declipall()" /></div>
    </div></div>
    <br />
<?php
  }
?>
    </div>
<?php
  if ($_GET['status'])
  {
?>
    <div id="status"><div class="box"><div class="boxd">
<?php
    switch ($_GET['status'])
    {
    case 'li':
      echo 'You have logged in.';
      break;
    case 'gli':
      echo 'You are a guest.';
      break;
    case 'nli':
      echo 'Incorrect username/password.';
      break;
    case 'lo':
      echo 'You have logged out.';
      break;
    case 'ad':
      echo 'Your account is disabled.';
      break;
    case 'gad':
      echo 'The guest account is disabled.';
      break;
    default:
      echo 'Error: Unrecognized status code "'.$_GET['status'].'".';
    }
?>
      <div style="text-align:right;"><input type="button" value="OK" onclick="document.getElementById('status').style.display = 'none'" /></div>
    </div></div><br /></div>
<?php
  }
  if ($fm_dev)
  {
?>
    <div class="box"><div class="boxd">
      <strong style="color:red">Fileman is in DEBUG MODE</strong>
    </div></div><br />
<?php
  }
?>
    <div class="box">
      <a class="boxh boxhd" href="javascript:toggle('ucp');" id="ucp_a">
        <span class="togbtn" id="ucp_exp" style="display:none;">+</span>
        <span class="togbtn" id="ucp_col">&minus;</span>
        User control panel
      </a>
      <div class="boxd lb" id="ucp" onmouseover="mo_ucp=true" onmouseout="mo_ucp=false">
<?php
  if ($uid) // Logged in
  {
?>
        <div>Welcome, <strong><?php echo $user['dname']; ?></strong><?php if ($fm_dev) echo ' (#',$uid,')'; ?>.</div>
<?php
    if (isadmin())
    {
?>
        <div>You have root administrator access.</div>
        <div><a href="index.php?d=<?php echo $hd,'&amp;sp=admin',$asid; ?>"<?php if ($_GET['sp']=='admin') echo ' class="cur"'; ?> target="_parent"><img src="images/icons/_opt.gif" /> Administration</a></div>
<?php
    }
    if (!$ftpmode)
    {
?>
        <div><a href="index.php?d=<?php echo $hd,'&amp;sp=account',$asid; ?>"<?php if ($_GET['sp']=='account') echo ' class="cur"'; ?> target="_parent"><img src="images/icons/_opt.gif" /> Account settings</a></div>
<?php
    }
?>
        <div><form action="index.php<?php echo $hqsid; ?>" method="post" id="flo" target="_parent"><input type="hidden" name="logout" value="Log out" />&nbsp;</form></div>
        <div><a href="javascript:void('Log out');" onclick="document.getElementById('flo').submit();" target="_top"><img src="images/icons/_lo.gif" /> Log out</a></div>
<?php
  }
  else // Not logged in
  {
?>
        <div><a href="index.php?d=<?php echo $hd,'&amp;login',$asid; ?>" target="_top"><img src="images/icons/_lo.gif" /> Log in</a></div>
<?php
  }
  //====================================================================\\
  // Removal of the about page is a violation of the license agreement! \\
  //====================================================================\\
?>
        <div>&nbsp;</div>
        <div><a href="index.php?d=<?php echo $hd,$havmode,'&amp;sp=help',$hasid; ?>"<?php if ($_GET['sp']=='help') echo ' class="cur"'; ?> target="_top"><img src="images/icons/_help.gif" /> Help</a></div>
        <div><a href="index.php?d=<?php echo $hd,$havmode,'&amp;sp=about',$hasid; ?>"<?php if ($_GET['sp']=='about') echo ' class="cur"'; ?> target="_top"><img src="images/icon.gif" /> About Filecharger <?php echo $fm_version; ?></a></div>
      </div>
    </div>
<?php
  if (!$_GET['sp'])
  {
?>
    <br />
    <div style="display:none"><div class="box">
      <a class="boxh" href="javascript:toggle('fftask');" id="fftask_a">
        <span class="togbtn" id="fftask_exp" style="display:none;">+</span>
        <span class="togbtn" id="fftask_col">&minus;</span>
        File and Folder Tasks
      </a>
      <div class="boxd lb" id="fftask">
<?php
  if (!$file && !$files) // Directory list
  {
    if ($postsub)
    {
?>
        <div><a href="javascript:void(0);" onclick="parent.location.href = 'index.php?d=<?php echo fm_urlencode($presub.upOne($postsub,$presub)),$hasid; ?>'" target="_top">Up one level</a></div>
<?php
    }
?>
        <div><a href="javascript:void(0);" onclick="f_newfold();" target="_top">New Folder</a></div>
        <div><a href="javascript:void(0);" onclick="f_newfile();" target="_top">New Text Document</a></div>
        <div><a href="javascript:void(0);" onclick="parent.main.fdrag_openform();" target="_top">Upload</a></div>
        <div><a href="javascript:void(0);" onclick="f_urlupload();" target="_top">Upload from URL</a></div>
<?php
  }
  else if ($vmode != '')
  {
?>
        <div><a href="javascript:void(0);" onclick="parent.location.href='index.php?d=<?php echo ($presub.$postsub?$presub.$postsub:'./'),$hasid; ?>'" target="_top">Back to folder</a></div>
<?php
  }
?>
      </div>
    </div>
    <br /></div>
    <div class="box">
      <a class="boxh" href="javascript:toggle('details');" id="details_a">
        <span class="togbtn" id="details_exp" style="display:none;">+</span>
        <span class="togbtn" id="details_col">&minus;</span>
        Details
      </a>
      <div class="boxd" id="details">
<?php if ($fileinfo) { ?>
        <div class="overflowable"><strong><?php echo htmlspecialchars($fileinfo['name']); ?></strong></div>
        <?php if (!$fileinfo['writable']) echo '<strong style="color:#F00">Read-only</strong><br \/>';
?>
        <?php echo $fileinfo['type']; ?><br />
        <br />
<?php   if ($fileinfo['imgsize'][2]) { ?>
        Dimensions: <?php echo $fileinfo['imgsize'][0].'x'.$fileinfo['imgsize'][1]; ?><br />
<?php   } ?>
        Modified: <?php echo $fileinfo['tmodified']; ?><br />
        Size: <?php echo $fileinfo['tsize']; ?><br />
<?php } else if ($files) { ?>
        <?php echo count($files); ?> items selected.<br />
        <br />
        <div class="overflowable"><?php
        for ($i=0;$i<5&&$i<count($files);$i++)
        {
          if ($i == 4) echo ', ...';
          else if ($i == 0) echo $files[$i];
          else echo ', '.$files[$i];
        }
        ?></div>
<?php } else { ?>
        <strong>Error:</strong> File/folder does not exist.
<?php } ?>
      </div>
    </div>
    <form name="actions" id="actions" action="index.php?d=<?php echo $hd,'&amp;p=v',$havmode,$hasid; ?>" target="main" method="post"><input type="hidden" name="act" id="act" value="" />&nbsp;<input type="hidden" name="val" id="val" value="" /></form>
<?php
  if (!$file && !$files) // Directory list
  {
?>
    <div id="uploaddiv"><br />
    <div class="box">
      <a class="boxh" href="javascript:void(0);" onclick="document.getElementById('uploaddiv').style.display='none';" name="upload">
        <span class="togbtn">X</span>
        Upload
      </a>
      <div class="boxd"><form action="index.php?d=<?php echo $hd,$havmode,$hasid; ?>" target="_top" method="post" enctype="multipart/form-data">
        <input type="hidden" name="act" value="upload" />
        <!--input type="hidden" name="MAX_FILE_SIZE" value="30000000000" /-->
        <div class="fdiv" id="fdiv1"><input class="file" type="file" name="file1" id="file1" /></div>
        <div class="fdiv" id="fdiv2"><input class="file" type="file" name="file2" id="file2" /></div>
        <div class="fdiv" id="fdiv3"><input class="file" type="file" name="file3" id="file3" /></div>
        <br />
        <div><input type="submit" name="submit" value="Upload" /><input type="button" name="cancel" value="Cancel" onclick="document.getElementById('uploaddiv').style.display='none';" /></div>
      </form></div>
    </div></div>
<?php
  }
  }
?>
  </body>
</html>
<?php
}
else if ($pane == 'a')
{
  //====================
  // Action bar
  //====================
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>actbar</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      body
      {
        color: #1e395b;
        background: #dce7f5 url(images/actbar.gif) repeat-x;
      }
      a
      {
        color: #1e395b;
        display: block;
        float: left;
        height: 18px;
        padding: 4px 5px;
        font-size: 14px;
        text-decoration: none;
        vertical-align: top;
		border: 1px solid transparent;
      }
      a:hover,a.hover
      {
        border: 1px solid #6699CC;
      }
      a.open, a.open:hover
      {
        border: 1px solid #868588;
        border-bottom: 0;
        padding: 4px 5px 5px 5px;
      }
      .open a, .open a:hover
      {
        border: 0;
        background-color: transparent;
      }
<?php
if ($fm_dev)
{
?>
      #aupload:hover
      {
        background-color: #DD2255;
      }
<?php
}
?>
      form.quick
      {
        display: block;
        float: left;
        position: relative;
        height: 28px;
        border: 0;
        background-color: transparent;
      }
      form.quick #file1
      {
        display: block;
        position: absolute;
        right: -1px;
        bottom: -1px;
        font-size: 200px;
        cursor: pointer;
        filter: alpha(opacity=01);
        opacity: 0.01;
        -moz-opacity: 0.01;
      }
      form.quick #file2
      {
        display: block;
        position: absolute;
        right: -1px;
        bottom: 14px;
        font-size: 200px;
        cursor: pointer;
        filter: alpha(opacity=01);
        opacity: 0.01;
        -moz-opacity: 0.01;
      }
      form.quick #filesub
      {
        display: block;
        position: absolute;
        right: 0;
        top: -50px;
        font-size: 1px;
      }
      #more div
      {
        float:left;
      }
      #more div.nopen
      {
        border: 1px solid transparent;
        padding: 0;
        background-color: transparent;
        height: 26px;
      }
      #more div.nopen:hover
      {
        border: 1px solid #6699CC;
      }
      #more div.open
      {
        border: 1px solid #868588;
        border-bottom: 0;
        padding-bottom: 1px;
        background-color: #C4CCDA;
        height: 26px;
      }
      #more a
      {
        float: none;
      }
      #more div.nopen a
      {
        border: 0;
      }
      #more .open a.l,
      #more .nopen a.l
      { padding-right:5px; }
      #more .nopen a.l:hover
      { padding-right:4px; border-right:1px solid #6699CC; }
      a.drop
      { padding-left:0; }
      .drop
      {
        color: #429cca;
      }
      #more div.m
      { display:none; }
    --></style>
<!--[if lte IE 6]><style type="text/css">
#more div {width: 68px;}
</style><![endif]-->
    <script language="javascript" type="text/javascript">
    <!--
      var nmo = true;
      function hpos(obj)
      {
        var curleft = 0;
        if (obj.offsetParent)
        {
          curleft = obj.offsetLeft;
          while (obj = obj.offsetParent)
            curleft += obj.offsetLeft;
        }
        return curleft;
      }
      function setmore(data)
      {
        if (data != '')
        {
          document.getElementById('more').innerHTML = data;
          document.getElementById('more').style.display = 'block';
        }
        else if (parent.main.cliptype != '')
        {
          document.getElementById('more').innerHTML = '<div><a href="#">Paste here<\/a><\/div>';
          document.getElementById('more').style.display = 'block';
        }
        else
        {
          document.getElementById('more').innerHTML = '';
          document.getElementById('more').style.display = 'none';
        }
      }
      function drop(id)
      {
       parent.main.cmenu_c(hpos(document.getElementById('l_'+id)),0,document.getElementById('d_'+id).innerHTML);
      }
      function f_delete(f)
      { parent.sidebar.f_delete(f, false); }
      function f_rename(f)
      { parent.sidebar.f_rename(f); }
      function f_urlupload()
      { parent.sidebar.f_urlupload(); }
      function f_clip(t,f)
      { parent.sidebar.f_clip(t,f); }
      function f_declip(f)
      { parent.sidebar.f_declip(f); }
      function f_declipall()
      { parent.sidebar.f_declipall(); }
      function f_pastehere()
      { parent.sidebar.f_pastehere(); }
      var opened = false;
      function lopen(link)
      {
        link.className = 'open';
        opened = link;
        return false;
      }
      function lclose()
      {
        if (!opened) return;
        opened.className = 'nopen';
        opened = false;
      }
      //-->
    </script>
  </head>
<?php
$compatmode = strpos($_SERVER['HTTP_USER_AGENT'],'Konqueror') !== false || strpos($_SERVER['HTTP_USER_AGENT'],'iCab') !== false;
?>
  <body onmousedown="if (parent.main.cmo) parent.main.cmenucl()" onmouseup="if (nmo) parent.main.focus()">
    <a id="aupload" href="fileman-ext.php?upload&amp;d=<?php echo $hd,$hasid;?>" onclick="parent.main.fdrag_openform();return false">Upload</a>
    <form id="upactions" class="quick" action="index.php?d=<?php echo $hd,$hasid; ?>&amp;p=v" target="main" method="post" enctype="multipart/form-data">
      <input type="hidden" name="act" value="upload" /><!--input type="hidden" name="MAX_FILE_SIZE" value="30000000000" /-->
      <input class="file" type="file" name="file1" id="file1" onmouseover="nmo=false;document.getElementById('aupload').className = 'hover';" onmouseout="nmo=true;document.getElementById('aupload').className = 'n';" onchange="if (this.value && parent.sidebar.document.getElementById('act').value == '') { if (typeof(FileReader) != 'undefined' || typeof(FormData) != 'undefined') { parent.main.fdrag_upload(this.files); } else { parent.sidebar.document.getElementById('act').value = 'up'; document.getElementById('upactions').submit();parent.main.fdrag_uploading('Uploading...'); } }"<?php if ($fm_dev) echo ' style="opacity:0.5;filter:alpha(opacity=50);-moz-opacity:0.5;-khtml-opacity:0.5;"'; ?> />
      <input class="file" type="file" name="file2" id="file2" onmouseover="nmo=false;document.getElementById('aupload').className = 'hover'" onmouseout="nmo=true;document.getElementById('aupload').className = 'n'" onchange="if (this.value && parent.sidebar.document.getElementById('act').value == '') { if (typeof(FileReader) != 'undefined' || typeof(FormData) != 'undefined') { parent.main.fdrag_upload(this.files); } else { parent.sidebar.document.getElementById('act').value = 'up'; document.getElementById('upactions').submit();parent.main.fdrag_uploading('Uploading...'); }
 }"<?php if ($fm_dev) echo ' style="opacity:0.5;filter:alpha(opacity=50);-moz-opacity:0.5;-khtml-opacity:0.5;"'; ?> />
      <input type="submit" id="filesub" value="Upload" />
    </form>
    <a href="javascript:void(0)" onclick="parent.main.cmenu_c(document.getElementById('aupload').offsetWidth,0,'N');return lopen(this)">New <span class="drop"><img src="images/dropdown.gif" /></span></a>
    <div id="more" style="border-left:3px solid #429cca;height:30px;float:left;display:none;"></div>
    <?php if ($fm_dev) { if (!$compatmode) { ?><div style="border-left:3px solid #429cca;float:left;"><a href="javascript:alert('hpos: '+hpos(document.getElementById('file1'))+' dim: '+document.getElementById('file1').offsetWidth+','+document.getElementById('file1').offsetHeight);void(0);">Debugupload</a><?php } ?><a href="javascript:document.getElementById('actions').innerHTML='';void(0);">C-mode</a></div><a href="fileman-ext.php?upload&amp;d=<?php echo $hd,$hasid;?>">C-up</a><?php } ?>
  </body>
</html>
<?php
}
else if ($pane == 'v')
{
  $sp = @$_REQUEST['sp'];
  if ($user['temppass'] || (empty($user['pass']) && $uid && !$ftpmode))
  {
    $sp = 'temppass';
  }
  else if ($sp == 'temppass') $sp = 'invalid'; // temppass bypasses usual password checking
  if ($file || $sp || $_GET['vmode'])
  {
  //====================
  // View file
  //====================
    $vmode = $_GET['vmode'];
    ($fileinfo = fm_fileinfo($d)) or ($vmode = 'doesntexist');
    if ($vmode == 'dl')
    {
      if (fm_contenttype(fext($file)))
      {
        header('Content-Type: '.fm_contenttype(fext($file)));
        header('Content-Disposition: attachment; filename="'.$file.'"');
      }
      readfile($prepath.$presub.$postsub.$file);
      fm_close();die();
    }
    if (!$vmode)
    {
      if ($fileinfo['ft'] == 5)
        $vmode = 'vimg';
      else if ($fileinfo['ft'] == 2 || $fileinfo['ft'] == 3)
        $vmode = 'frame';
      else if ($fileinfo['ft'] == 4)
        $vmode = 'txt';
      else
        $vmode = 'list';
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<?php
    if ($sp == 'dl')
    {
?>
    <meta http-equiv="refresh" content="1;url=index.php?d=<?php echo $presub,$postsub,$file,'&amp;p=v&amp;vmode=dl',$hasid; ?>" />
<?php
    }
?>
    <title>File view</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      html{height:100%;}
      body
      {
        color: #000000;
        background-color: #e8f1fc;
        font-size: 8pt;
        height: 100%;
      }
      h1
      {
        margin: 0 0 18px 0;
        padding: 0 0 5px 0;
        border-bottom: 1px solid #999999;
        font-family: Tahoma, Verdana, Arial, sans-serif;
        font-size: 16pt;
        font-weight: bold;
      }
      table.vimg,
      .fullscrn
      {
        height: 100%;
        width: 100%;
      }
      table.vimg td
      {
        text-align: center;
      }
      table.vimg img
      {
        border: 1px solid #000000;
        background: #FFF url(images/transp.gif);
      }
      div.txt
      {
        font-size: 10pt;
        padding: 12px;
        /*width: 10px;*/
      }
      div.txt div.sub
      {
        border: 1px solid #777777;
        background-color: #FFFFFF;
        padding: 12px;
        border-radius: 6px;
        max-width: 40em;
      }
      div.txt div.fsub
      {
        border: 1px solid #777777;
        background-color: #FFFFFF;
        padding: 12px;
        border-radius: 6px;
      }
      div.txt pre,
      div.txt .pre
      {
        /*width: 10px;*/
        font-family: "Lucida Console", monospace;
      }
      form.t_ed
      {
        width: 100%;
        height: 100%;
        border: 0;
        margin: 0;
        padding: 0;
      }
      textarea
      {
        width: 100%;
        height: 100%;
        border: 0;
        margin: 0;
        padding: 0;
      }
      .fullscr_txt
      {
        position: absolute;
        top: 80px;
        bottom: 0;
        left: 0;
        right: 0;
      }
      .fullscr_btns
      {
        padding: 20px;
      }
    --></style>
    <!--[if IE]><style type="text/css">
      body { width: 95%; }
    </style><![endif]-->
    <script language="javascript" type="text/javascript">
    <!--
      function cmenucl() {}
      function resize() {}
      function keydown(e) {}
      function keypress(e) {}
      function keyup(e) {}
    //-->
    </script>
  </head>
  <body>
<?php
    if ($sp)
    {
?>
    <div class="txt"><div class="sub">
<?php
      switch ($sp)
      {
      case 'about':
      //===============================================================\\
      // Removal of this page is a violation of the license agreement! \\
      //===============================================================\\
?>
      <p>
        <img src="images/logo.gif" alt="Novawave Filecharger" /><br />
        <em><strong><?php echo ($fm_se?'':'Version '),$fm_version; ?></strong> <span style="color: #999999;">- Revision <?php echo $fm_build; ?></span></em>
      </p>
      <p>
        Copyright &copy; 2005-<?php echo $fm_thisyear; ?> Guangcong Luo. All rights reserved.<br/>
		Distributed by <a href="http://novawave.ca">Novawave Inc.</a>
      </p>
<?php
        break;
      case 'help':
?>
      <h1>Help</h1>
      <div id="rplcos"><table border="0" cellspacing="0" cellpadding="0" class="table">
        <tr>
          <td><span class="click">click</span> on icon</td>
          <td>Select one file/folder</td>
        </tr>
        <tr>
          <td><span class="key">Ctrl</span>+<span class="click">click</span> on icon</td>
          <td>Select multiple files/folders</td>
        </tr>
        <tr>
          <td><span class="dblclick"><span class="click">double-click</span></span> on icon</td>
          <td>Open file/folder</td>
        </tr>
        <tr>
          <td><span class="click">click</span> on blank space</td>
          <td>Deselect all</td>
        </tr>
        <tr>
          <td><span class="dblclick"><span class="click">double-click</span></span> on blank space</td>
          <td>Select all</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="lkey">Ctrl</span>+<span class="key">A</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">A</span> (Mac)</span></td>
          <td>Select All</td>
        </tr>
        <tr><td colspan="2"><div class="hr"><hr /></div></td></tr>
        <tr>
          <td><span class="nonmaconly"><span class="lkey">Ctrl</span>+<span class="key">X</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">X</span> (Mac)</span></td>
          <td>Cut (move)</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="lkey">Ctrl</span>+<span class="key">C</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">C</span> (Mac)</span></td>
          <td>Copy</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="lkey">Ctrl</span>+<span class="key">V</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">V</span> (Mac)</span></td>
          <td>Paste</td>
        </tr>
        <tr><td colspan="2"><div class="hr"><hr /></div></td></tr>
        <tr>
          <td><span class="nonmaconly"><span class="key">Enter</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">Down</span> (Mac)</span></td>
          <td>View/Open</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="key">F2</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="key">Enter</span> (Mac)</span></td>
          <td>Rename</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="key">Del</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="key">Delete</span> (Mac)</span></td>
          <td>Delete</td>
        </tr>
        <tr>
          <td><span class="nonmaconly"><span class="lkey">Ctrl</span>+<span class="key">Del</span></span>
          <span class="maconly"><span class="maconlyor">or </span><span class="lkey">Cmd</span>+<span class="key">Delete</span> (Mac)</span></td>
          <td>Delete without asking</td>
        </tr>
      </table></div>
      <script>
      <!--
      document.getElementById('rplcos').className = (navigator.appVersion.indexOf("Mac")!=-1)?'usingmac':'notusingmac';
      -->
      </script>
<?php
        break;
      case 'temppass':
          $success = false;
          if (isset($_POST['npass']))
          {
            if ($_POST['npass'] && $_POST['npass']===$_POST['npass2'])
            {
              $_PERSIST['users'][$uid]['pass'] = pwencode($user['name'],$_POST['npass']);
              unset($_PERSIST['users'][$uid]['temppass']);
              if (!persist_update())
              {
                fm_close();
                die('<strong>Error:</strong>Persist not writable, please see admin.');
              }
?>
      <p style="padding:2px;border:1px solid #CC9966;"><strong>Password successfully changed.</strong></p>
      <input type="button" style="font-size: 14pt;" name="na" value="Continue &raquo;" onclick="parent.location.href = 'index.php?d=<?php echo $hd,$havmode,$hasid; ?>';" />
<?php
              $success = true;
            }
            else if ($_POST['npass'])
            {
?>
      <p style="padding:2px 4px;border:2px solid #FF7755;"><strong><span style="color:#FF2211">Error:</span> New passwords do not match.</strong></p>
<?php
            }
            else
            {
?>
      <p style="padding:2px 4px;border:2px solid #FF7755;"><strong><span style="color:#FF2211">Error:</span> You forgot to enter a new password.</strong></p>
<?php
            }
          }
          if (!$success)
          {
?>
            <h1>Temporary password</h1>
            <p>
              You are using a temporary password. Please set a new one now:
            </p>
            <form action="index.php?p=v&amp;d=<?php echo $hd,$havmode,'&amp;sp=temppass',$hasid; ?>" id="chg" style="border:3px solid #FFD389;padding:3px;" method="post">
              <span class="small">New password:</span><br />
              <input type="password" class="textbox" name="npass" /><br />
              <span class="small">Confirm new password:</span><br />
              <input type="password" class="textbox" name="npass2" /><br />
              <input type="hidden" name="aact" value="chgpass" /><input type="submit" value="Set password" />
            </form>
<?php
          }
        break;
      case 'account':
?>
      <h1>Account settings</h1>
<?php
        if ($_POST['aact']=='chgdpsub')
        {
          $user['dpsub'] = $_PERSIST['users'][$uid]['dpsub'] = $d;
          if (!persist_update())
          {
            fm_close();
            die('<strong>Error:</strong>Persist not writable, please see admin.');
          }
?>
      <p style="padding:2px;border:1px solid #CC9966;"><strong>Default folder successfully changed.</strong></p>
<?php
        }
        if (@$_POST['opass'] || $_POST['nopass'])
        {
          if ($_POST['nopass']) $_POST['opass'] = '';
          if (empty($user['pass']) || $user['pass']===pwencode($user['name'],$_POST['opass']))
          {
            if ($_POST['npass'] && $_POST['npass']===$_POST['npass2'])
            {
              $_PERSIST['users'][$uid]['pass'] = pwencode($user['name'],$_POST['npass']);
              if (!persist_update())
              {
                fm_close();
                die('<strong>Error:</strong>Persist not writable, please see admin.');
              }
?>
      <p style="padding:2px;border:1px solid #CC9966;"><strong>Password successfully changed.</strong></p>
<?php
            }
            else if ($_POST['npass'])
            {
?>
      <p style="padding:2px 4px;border:2px solid #FF7755;"><strong><span style="color:#FF2211">Error:</span> New passwords do not match.</strong></p>
<?php
            }
            else
            {
?>
      <p style="padding:2px 4px;border:2px solid #FF7755;"><strong><span style="color:#FF2211">Error:</span> You forgot to enter a new password.</strong></p>
<?php
            }
          }
          else
          {
?>
      <p style="padding:2px 4px;border:2px solid #FF7755;"><strong><span style="color:#FF2211">Error:</span> Incorrect old password.</strong></p>
<?php
          }
        }
?>
      <table border="0" cellspacing="0" cellpadding="1">
        <tr>
          <th align="right" valign="top">
            Username:&nbsp;
          </th>
          <td align="left" valign="top">
            <?php echo $user['dname']; ?>
          </td>
        </tr>
        <tr>
          <th align="right" valign="top">
            Password:&nbsp;
          </th>
          <td align="left" valign="top">
            <div id="chgb">
              <input type="button" value="Change password" onclick="chg(1)" />
            </div>
            <form action="index.php?p=v&amp;d=<?php echo $hd,$havmode,'&amp;sp=account',$hasid; ?>" id="chg" style="border:3px solid #FFD389;padding:3px;" method="post">
              <span class="small">Old password:</span><br />
              <input type="password" class="textbox" name="opass" /><br />
              <span class="small">New password:</span><br />
              <input type="password" class="textbox" name="npass" /><br />
              <span class="small">Confirm new password:</span><br />
              <input type="password" class="textbox" name="npass2" /><br />
              <input type="hidden" name="aact" value="chgpass" /><input type="submit" value="Change" /> <input type="button" value="Cancel" onclick="chg(0)" />
            </form>
          </td>
        </tr>
        <tr>
          <th align="right" valign="top">
            Can&nbsp;access:&nbsp;
          </th>
          <td align="left" valign="top">
            <?php echo $preurl.$presub; ?><strong>*</strong>
          </td>
        </tr>
        <tr>
          <th align="right" valign="top">
            Home&nbsp;folder:&nbsp;
          </th>
          <td align="left" valign="top">
            <form action="index.php?p=v&amp;d=<?php echo $hd,$havmode,'&amp;sp=account',$hasid; ?>" id="hf" method="post">
              <?php echo $preurl.$user['dpsub']; ?><br />
              <input type="hidden" name="aact" value="chgdpsub" /><?php if ($d!==$user['dpsub']) { ?><button type="submit">Change to current folder:<br /><em><?php echo $preurl.$d; ?></em></button><?php } ?>
            </form>
          </td>
        </tr>
        <tr>
          <th align="right" valign="top">
            Rank:&nbsp;
          </th>
          <td align="left" valign="top">
            <?php echo utype($user['priv'])?utype($user['priv']):'User'; ?>
          </td>
        </tr>
      </table>
      <script language="javascript" type="text/javascript">
      <!--
        document.getElementById('chg').style.display='none';
        function chg(val)
        {
          if (val)
          {
            document.getElementById('chg').style.display='block';
            document.getElementById('chgb').style.display='none';
          }
          else
          {
            document.getElementById('chg').style.display='none';
            document.getElementById('chgb').style.display='block';
          }
        }
      //-->
      </script>
<?php
        break;
      case 'dl':
?>
      Now downloading.<br />
      <br />
      If you are not redirected automatically, please download at the following link:<br />
      <br />
      <div class="lb" style="max-width: 20em;"><a href="index.php?d=<?php echo $presub,$postsub,$file,'&amp;p=v&amp;vmode=dl',$hasid; ?>">&raquo; Download <?php echo $file; ?></a></div>
<?php
        break;
      default:
?>
      <h1>Error: Unknown special page</h1>
      <p>
        The special page you have tried to reach does not exist.
      </p>
<?php
        break;
      }
?>
    </div><form action="index.php?d=<?php echo $hd,$havmode,$hasid; ?>" style="margin:20px;" method="post" target="_parent">
<?php
      if ($sp != 'temppass')
      {
?>
      <input type="button" style="font-size: 14pt;" name="na" value="&laquo; Back" onclick="parent.location.href = 'index.php?d=<?php echo $hd,$havmode,$hasid; ?>';" />
<?php
      }
?>
    </form></div>
<?php
    }
    else if ($vmode == 'doesntexist')
    {
?>
    <div class="txt"><div class="fsub">
      <strong>Error:</strong> The file you're looking for doesn't exist.
    </div></div>
<?php
    }
    else if ($vmode == 'vimg')
    {
?>
    <table class="vimg" border="0" cellspacing="0" cellpadding="0"><tr><td valign="middle">
      <div><img src="<?php echo fm_geturl($d); ?>" alt="" /></div>
    </td></tr></table>
<?php
    }
    else if ($vmode == 'txt')
    {
?>
    <div class="txt"><div class="fsub">
      <div class="pre"><?php echo str_replace(array('  ',"\n","\t"),array('&nbsp;',"\n<br />",'&nbsp;&nbsp;&nbsp;&nbsp;'),str_replace("\n ","\n&nbsp;",htmlspecialchars(fm_contents($d,"\r\n")))); ?></div>
    </div></div>
<?php
    }
    else if ($vmode == 'e_txt' || $vmode == 'e_html')
    {
?>
    <form class="t_ed" name="textedit" action="index.php?d=<?php echo $hd,$havmode,$hasid; ?>" target="_top" method="post">
      <div class="fullscr_btns">
        <input type="submit" name="submit" value="Save" />
        <input type="reset" name="reset" value="Revert" onclick="return confirm('Are you sure you want to revert to the last saved state?');" />
        <input type="hidden" name="act" value="textedit" />
      </div>
      <div class="fullscr_txt">
        <textarea name="text" id="text"><?php echo htmlspecialchars(file_get_contents($prepath.$presub.$postsub.$file)); ?></textarea>
      </div>
      <script language="javascript" type="text/javascript">
      <!--
        document.getElementById('text').focus();
      //-->
      </script>
    </form>
<?php
    }
    else if ($vmode == 'props')
    {
?>
    <div class="txt"><div class="sub">
      <form action="index.php?d=<?php echo $hd,$havmode,$hasid; ?>&amp;p=v" method="post"><table border="0" cellspacing="0" cellpadding="2">
        <tr><td valign="middle">
          <img src="images/icons32/<?php echo $fileinfo['img']; ?>.gif" width="32" height="32" alt="[<?php echo $fileinfo['img']; ?>]" />
        </td><td valign="middle">
          <?php echo $fileinfo['name']; ?>
        </td></tr>
        <tr style="border-top: 1px solid #888;"><td valign="top">
          Type:
        </td><td valign="top">
          <?php echo $fileinfo['type']; ?>
        </td></tr>
        <tr><td valign="top">
          Size:
        </td><td valign="top">
          <?php echo $fileinfo['tsize']; ?>
        </td></tr>
        <tr><td valign="top">
          Modified:
        </td><td valign="top">
          <?php echo $fileinfo['tmodified']; ?>
        </td></tr>
        <tr style="border-top: 1px solid #888;"><td valign="top">
          Owner:
        </td><td valign="top">
          <?php $temp = is_int($fileinfo['owner'])?posix_getpwuid($fileinfo['owner']):array('name'=>$fileinfo['owner']); echo $temp['name']; if (is_int($fileinfo['owner'])) echo ' <span style="color:#808080">(',$fileinfo['owner'],')</span>'; ?>
        </td></tr>
        <tr><td valign="top">
          Group:
        </td><td valign="top">
          <?php $temp = is_int($fileinfo['group'])?posix_getgrgid($fileinfo['group']):array('name'=>$fileinfo['group']); echo $temp['name']; if (is_int($fileinfo['group'])) echo ' <span style="color:#808080">(',$fileinfo['group'],')</span>'; ?>
        </td></tr>
        <tr><td valign="top">
          Permissions:
        </td><td valign="top">
          <input type="text" class="textbox" name="perms" id="perms" value="<?php echo $fileinfo['perms']; ?>" onchange="chg(0)" onfocus="chg(1)" onblur="chg(0)" />
        </td></tr>
      </table><div id="btnbar" style="border-top: 1px solid #BBBBBB; padding:12px 12px 0 12px; margin: 12px -12px 0 -12px;">
        <input type="hidden" name="act" id="act" value="chmod" />
        <input type="submit" value="Save" />
      </div></form>
    </div></div>
    <div style="margin:20px"><input type="button" style="font-size: 14pt;" id="na" name="na" value="&laquo; Back to folder" onclick="parent.location.href = 'index.php?d=<?php echo $presub,upOne($postsub.$file,$presub),$hasid; ?>';" /></div>
    <script language="javascript" type="text/javascript">
    <!--
      var cperms = '<?php echo $fileinfo['perms']; ?>';
      function chg(f)
      {
        var laquo = document.getElementById('na').value.substr(0,1);
        if (document.getElementById('perms').value == cperms && !f)
        {
          document.getElementById('btnbar').style.display = 'none';
          document.getElementById('na').value = laquo+' Back to folder';
        }
        else
        {
          document.getElementById('btnbar').style.display = 'block';
          document.getElementById('na').value = laquo+' Cancel';
        }
      }
      chg(0);
    //-->
    </script>
<?php
    }
    else // list
    {
?>
    <div class="txt"><div class="sub">
      <table border="0" cellspacing="0" cellpadding="2">
        <tr><td valign="middle">
          <img src="images/icons32/<?php echo $fileinfo['img']; ?>.gif" width="32" height="32" alt="[<?php echo $fileinfo['img']; ?>]" />
        </td><td valign="middle">
          <?php echo $fileinfo['name']; ?>
        </td></tr>
      </table>
      <br />
      This file cannot be displayed natively.<br />
      <br />
      <div class="lb" style="max-width: 20em;">
        <div><a href="index.php?d=<?php echo $hd; ?>&amp;vmode=txt" target="_parent">&raquo; View as text</a></div>
        <div><a href="index.php?d=<?php echo $hd; ?>&amp;vmode=e_txt" target="_parent">&raquo; Edit as text</a></div>
        <div><a href="<?php echo fm_geturl($d); ?>">&raquo; Attempt to view in-browser</a></div>
        <div><a href="index.php?d=<?php echo $hd; ?>&amp;p=v&amp;sp=dl">&raquo; Download</a></div>
      </div>
    </div></div>
<?php
    }
?>
  </body>
</html>
<?php
  }
  else
  {
  //====================
  // Directory list
  //====================
    $files = fm_getdfiles($presub.$postsub);
    $fileinfo = fm_fileinfo($presub.$postsub.$file);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>File list</title>
    <link rel="stylesheet" type="text/css" href="fileman-core.css" />
    <style type="text/css"><!--
      html {height:100%; cursor:default;}
      body
      {
        color: #000000;
        background-color: #FFFFFF;
        font-size: 8pt;
        min-height: 100%;
      }
      div.pad
      {
        padding: 12px;
      }
      ul
      {
        display: block;
        width: 100%;
        margin: 0;
        padding: 0;
      }
      ul li
      {
        display: block;
        float: left;
        width: 66px;
        margin: 0;
        padding: 5px;
      }
      a.nsel,
      a.sel
      {
        display: block;
        padding: 1px;
        border: 0;
        color: #000;
        text-decoration: none;
        cursor: default;
        border-radius: 4px;
      }
      a.sel
      {
        padding: 0;
        border: 1px solid #F0C424;
        background-color: #FFF5D0;
      }
      a.nsel:hover,
      a.nsel_hover
      {
        display: block;
        padding: 0;
        border: 1px solid #6792C2;
        background-color: #E6ECF8;
      }
      a.sel:hover,
      a.sel_hover
      {
        display: block;
        padding: 0;
        border: 1px solid #E9BA0F;
        background-color: #FFEEAE;
      }
      a span.sdiv
      {
        display: block;
        text-align: center;
        width: 64px;
        overflow: hidden;
      }
      a span.sdivt
      {
        height: 14px;
      }
      #selbox
      {
        width: 1px;
        height: 1px;
        border: 1px solid #6792c2;
        position: absolute;
        top: 0px;
        left: 0px;
        display: none;
      }
    --></style>
    <script type="text/javascript" language="javascript">
    <!--
    var cdir='<?php echo jsesc($presub.$postsub); ?>';
    var asid='<?php echo $asid; ?>';
    var cliptype='<?php if ($_SESSION) echo $_SESSION['cliptype']; ?>';
    var ext_pclzip=<?php echo $ext_pclzip?'true':'false'; ?>;
    var loaded = false;
    var filedesc=new Array();
<?php
    foreach ($files as $tfile)
    {
?>
    filedesc['<?php echo $tfile['id']; ?>'] = 
      '<?php 
    if ($tfile['ft']==5 && $tfile['imgsize'][0])
    {
      $w = $tfile['imgsize'][0];
      $h = $tfile['imgsize'][1];
      if ($w > 168)
        list($w,$h) = array(168,intval($h*168/$w));
      if ($h > 168)
        list($w,$h) = array(intval($w*168/$h),168);
      echo '<br \/><img src="'.addslashes(htmlspecialchars(fm_geturl($d.$tfile['name']))).'" width="'.$w.'" height="'.$h.'" /><br \/><br \/>';
    }
    if (!$tfile['writable']) echo '<strong style="color:#F00">Read-only</strong><br \/>';
            echo $tfile['type']; ?><br \/><br \/><?php   if ($tfile['imgsize'][2]) { ?>Dimensions: <?php echo $tfile['imgsize'][0].'x'.$tfile['imgsize'][1]; ?><br \/><?php   } ?>Modified: <?php echo $tfile['tmodified']; ?><br \/>Size: <?php echo $tfile['tsize']; ?><br \/>';
<?php
    }
?>
    function load()
    {
      loaded=true;
      parent.main.focus();
<?php
  if (isset($_GET['act']))
  {
    if ($_SESSION)
    {
?>
    parent.sidebar.document.getElementById('clipboard').innerHTML = '<div class="box"><div class="boxd" style="border-bottom: 1px dotted #1e395b;padding:5px 10px;"><div style="float:right;color:#888;"><?php echo count($_SESSION['clip']) ?> file<?php if (count($_SESSION['clip'])!==1) echo 's' ?></div><strong><?php echo $_SESSION['cliptype']=='m'?'Move to...':'Copy to...' ?></strong></div><div class="boxd"><?php
      foreach ($_SESSION['clip'] as $clipfile)
      {
?><div class="overflowable"><img src="images/icons/<?php echo fticon(fext($clipfile)); ?>.gif" /> <?php echo filefrompath($clipfile) ?></div><?php
      }
?><div style="text-align:right;"><input type="button" value="<?php echo $_SESSION['cliptype']=='m'?'Move here':'Copy here' ?>" onclick="f_pastehere()" /></div><div style="text-align:right;"><input type="button" value="Cancel" onclick="f_declipall()" /></div></div></div><br />';
    parent.sidebar.document.getElementById('clipboard').style.display = 'block';
<?php
    }
    else
    {
?>
    parent.sidebar.document.getElementById('clipboard').innerHTML = '';
    parent.sidebar.document.getElementById('clipboard').style.display = 'none';
<?php
    }
?>
      parent.sidebar.cdetails='<div class="overflowable"><strong><?php echo addslashes($fileinfo['name']); ?><\/strong><\/div><?php if (!$fileinfo['writable']) echo '<strong style="color:#F00">Read-only</strong><br \/>'; ?><?php echo $fileinfo['type']; ?><br \/><br \/><?php if ($fileinfo['imgsize'][2]) { ?>Dimensions: <?php echo $fileinfo['imgsize'][0].'x'.$fileinfo['imgsize'][1]; ?><br \/><?php   } ?>Modified: <?php echo $fileinfo['tmodified']; ?><br \/>Size: <?php echo $fileinfo['tsize']; ?><br \/>';
      parent.sidebar.restore();
      parent.actbar.document.getElementById('file1').value = '';
      parent.actbar.document.getElementById('file2').value = '';
      parent.sidebar.document.actions.act.value = '';
<?php
    $toselect = explode(',',$_GET['select']);
    foreach ($toselect as $select) if ($select)
    {
?>
      if (document.getElementById('<?php echo $select; ?>')) sel('<?php echo $select; ?>');
<?php
    }
  }
?>
    }
    //document.onDblClick = delsel();
    //-->
    </script>
    <script language="javascript" type="text/javascript" src="fileman-main.js"></script>
  </head>
  <body onload="load()" onkeypress="return keypress(event)" onkeydown="return keydown(event)" onkeyup="return keyup(event)" oncontextmenu="return cmenu_nmo(event);" ondblclick="if (nmo) selall()" onmousedown="return mousedown(event);" onmouseup="return mouseup(event);" onselectstart="return false">
    <div class="pad" id="filelist">
<?php
    if (!fm_isdir($d))
    {
?>
      <div style="padding:20px;"><em style="color:#888888;font-size:12pt;"><strong>Error:</strong> This folder doesn't exist.</em></div>
<?php
    }
    else if ($files===FALSE)
    {
?>
      <div style="padding:20px;"><em style="color:#888888;font-size:12pt;">Access denied.</em></div>
<?php
    }
    else if (count($files)==0)
    {
?>
      <div style="padding:20px;"><em style="color:#888888;font-size:12pt;">This folder is empty.</em></div>
<?php
    }
    else
    {
?>
      <ul>
<?php
      foreach ($files as $file)
      {
?>
        <li id="li_<?php echo $file['id'] ?>"<?php if ($_SESSION&&in_array($d.$file['name'],$_SESSION['clip'])) { echo ' class="clipped"'; if ($_SESSION['cliptype']=='m') echo ' style="opacity:0.5;filter:alpha(opacity=0.5);"'; } ?>><a onclick="fclick(event,'<?php echo $file['id'] ?>')" ondblclick="go('<?php echo addslashes(htmlspecialchars($file['name'])); ?>')" oncontextmenu="return cmenu(event, '<?php echo $file['id'] ?>', '<?php echo addslashes(htmlspecialchars($file['name'])) ?>', <?php echo $file['ft'] ?>)" id="<?php echo $file['id'] ?>" onmouseover="nmo=false" onmouseout="nmo=true" class="nsel" title="<?php echo htmlspecialchars($file['ft']==1?substr($file['name'],0,-1):$file['name']); ?>">
          <span class="sdiv"><img src="images/icons32/<?php echo $file['img'] ?>.gif" width="32" height="32" alt="[<?php echo $file['img'] ?>]" title="<?php echo htmlspecialchars($file['ft']==1?substr($file['name'],0,-1):$file['name']); ?>" /></span>
          <span class="sdiv sdivt" style="white-space:pre"><?php $fn = $file['ft']==1?substr($file['name'],0,-1):$file['name']; echo htmlspecialchars(strlen($fn)>12?substr($fn,0,9).'...':$fn); ?></span>
        </a></li>
<?php
      }
?>
      </ul>
<?php
    }
?>
    </div>
    <div id="extra"><div id="cmenu" onmousedown="cmmo=true" onclick="cmenucl()">&nbsp;</div><div id="dialog"></div><div id="selbox" style="display:none;"></div></div>
    <script>
    <!--
      function fdrag_enable(event){event.stopPropagation();event.preventDefault();}
      document.body.addEventListener("dragenter", fdrag_enable, false);
      document.body.addEventListener("dragover", fdrag_enable, false);
      document.body.addEventListener("drop", fdrag_upload, false);
      var entered=0;
      var safarimode = false;
      document.write('<div id="draguploadhint" style="position:fixed;top:8px;bottom:8px;left:8px;right:8px;border:3px solid #429cca;border-radius:7px;text-align:center;padding-top:17px;display:none;z-index:15;"><strong style="color:#ffffff;background:#429cca;padding:4px 8px;font-size:12pt;border-radius:4px;text-shadow: #333333 0px 1px 0;">Drag here to upload</strong></div>');
      // safari 4 and chrome 1-6
      /* if (false && navigator.userAgent.toLowerCase().indexOf('safari') > -1 && navigator.userAgent.toLowerCase().indexOf('chrome') == -1 && navigator.userAgent.toLowerCase().indexOf('chromium') == -1)
      {
        safarimode = true;
        // detects Safari and uses a different upload method
        document.body.removeEventListener("dragenter",fdrag_enable,false);
        document.body.removeEventListener("dragover",fdrag_enable,false);
        document.body.removeEventListener("drop",fdrag_upload,false);
        document.write('<form action="index.php?d='+cdir+'&p=v'+asid+'" method="post" enctype="multipart/form-data" id="draguploadform"><input type="file" id="draguploadelement" name="dragupload[]" multiple="multiple" onchange="if (this.value) { document.getElementById(\'draguploadform\').submit();fdrag_uploading() }" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;opacity:0;z-index:20;" /></form>');
      } */
        document.body.addEventListener("dragenter", 
          function(event){entered++;fdrag_dragenter();},
        false);
        document.body.addEventListener("dragleave", 
          function(event){entered--;if (!entered) fdrag_dragleave();},
        false);
      function fdrag_dragenter()
      {
        if (safarimode) document.getElementById('draguploadelement').style.display='block';
        document.getElementById('draguploadhint').style.display='block';
      }
      function fdrag_dragleave()
      {
        if (safarimode) document.getElementById('draguploadelement').style.display='none';
        document.getElementById('draguploadhint').style.display='none';
      }
      document.write('<div id="uploadingbg" style="position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;background-color:#ffffff;opacity:0.5;filter:alpha(opacity=50);display:none;z-index:10;"></div><div id="uploading" style="position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;padding-top:25px;display:none;z-index:11;"><strong style="color:#ffffff;background:#999999;padding:4px 8px;font-size:12pt;border-radius:4px;text-shadow: #333333 0px 1px 0;" id="uploadingtext">Uploading...</strong></div>');
      function fdrag_uploading(text)
      {
        fdrag_dragleave();
        document.getElementById('uploadingbg').style.display='block';
        document.getElementById('uploading').style.display='block';
        document.getElementById('uploadingformbg').style.display='none';
        document.getElementById('uploadingform').style.display='none';
        if (text) document.getElementById('uploadingtext').innerHTML = text;
      }
      function fdrag_doneuploading()
      {
        document.getElementById('uploadingbg').style.display='none';
        document.getElementById('uploading').style.display='none';
      }
      var xhr;
      function fdrag_onprogress(e)
      {
        if (document.getElementById('uploadbar') && document.getElementById('uploading').style.display=='block')
        {
          document.getElementById('uploadbar').innerHTML = '<div style="border-radius:7px;padding:2px;border:2px solid #707070;background:#ffffff;margin:0 auto;width:206px;height:6px;"><div style="height:6px;width:'+(6+Math.round((e.loaded / e.total) * 200))+'px;border-radius:3px;background:#707070"></div></div><strong style="color:#707070;font-size:10pt;background:#ffffff;border-radius:3px;padding:0 2px">'+(Math.round((e.loaded / e.total) * 100))+'% of '+(e.total>1024*1024?''+(Math.round(e.total * 10 / 1024/1024)/10)+' MB':''+(Math.round(e.total * 10 / 1024)/10)+' KB')+'</strong>';
        }
        else
        {
          document.getElementById('uploading').innerHTML = '<strong style="color:#ffffff;background:#999999;padding:4px 8px;font-size:12pt;border-radius:4px;text-shadow: #333333 0px 1px 0;" id="uploadingtext">Uploading...</strong><br /><br /><div id="uploadbar"><div style="border-radius:7px;padding:2px;border:2px solid #707070;background:#ffffff;margin:0 auto;width:206px;height:6px;"><div style="height:6px;width:'+(6+Math.round((e.loaded / e.total) * 200))+'px;border-radius:3px;background:#707070"></div></div><strong style="color:#707070;font-size:10pt;text-shadow: #ffffff 0px -1px 0">'+(Math.round((e.loaded / e.total) * 100))+'%</strong></div><br /><br /><button onclick="xhr.abort();fdrag_doneuploading();return false"><small>Cancel</small></button>';
        }
      }
      document.write('<div id="uploadingformbg" style="position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;background-color:#ffffff;opacity:0.5;filter:alpha(opacity=50);display:none;z-index:10;"></div><div id="uploadingform" style="position:fixed;top:0;bottom:0;left:0;right:0;text-align:center;padding-top:25px;display:none;z-index:11;"><form action="index.php?d='+cdir+'&p=v'+asid+'" method="post" enctype="multipart/form-data" style="width:200px;margin:0 auto;padding:20px;border:1px solid #999999;background:#ffffff;border-radius:6px;" id="uploadingforminner">'+((typeof(FileReader) == "undefined" && typeof(FormData) == "undefined")?'':'<p><strong style="font-size:12pt">You can also upload by dragging and dropping.</strong></p>')+'<p><input type="file" name="dragupload[]" multiple="multiple" onchange="fdrag_submitform(this.files);return false" /></p><p><button type="submit"><strong>Submit</strong></button> <button onclick="fdrag_closeform();return false">Cancel</button></p></form></div>');
      function fdrag_openform()
      {
        document.getElementById('uploadingformbg').style.display='block';
        document.getElementById('uploadingform').style.display='block';
      }
      function fdrag_closeform()
      {
        document.getElementById('uploadingformbg').style.display='none';
        document.getElementById('uploadingform').style.display='none';
      }
      function fdrag_submitform(files)
      {
        if (typeof(FileReader) == "undefined" && typeof(FormData) == "undefined")
        {
          document.getElementById('uploadingforminner').submit();
          fdrag_closeform();
          fdrag_uploading();
        }
        else
        {
          fdrag_upload(files);
        }
      }
    -->
    </script><div id="log"></div>
  </body>
</html>
<?php
  }
}
else
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Error.</title></head><body><strong>Error:</strong> Invalid pane.</body></html>
<?php
}
fm_close();
?>