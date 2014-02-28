<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Session library
 *
 * CORE LIBRARY - DO NOT REMOVE
 * 
 */

require_once 'config.inc.php';
require_once 'fileman.lib.php';

include_once 'persist.inc.php';
include_once 'persist.lib.php';

$file = '';
$sid = '';
$uid = 0;
$user = $_PERSIST['users'][0];

$asid = '';
$hasid = '';
$qsid = '';
$hqsid = '';
$isid = '';
$ssid = '';

  //====================
  // Function reference
  //====================

function dname2name($dname)
{
  $dname = strtr($dname, "ABCDEFGHIJKLMNOPQRSTUVWXYZŒŠÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞŸœšàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ", "abcdefghijklmnopqrstuvwxyzosaaaaaaaceeeeiiiidnoooooouuuuypyosaaaaaaaceeeeiiiidnoooooouuuuypy");
  //echo '<pre>Debug: '.preg_replace('/[^A-Za-z0-9]+/','',$dname).'</pre>';
  return preg_replace('/[^A-Za-z0-9]+/','',$dname);
}
function pwencode($uname,$pass)
{
  return md5('dbu068as_'.$uname.'_qw891a_'.$pass.'_a910wv8tk6');
}
function utype($priv)
{
  if ($priv==127) return 'Admin';
  if ($priv==1) return 'Guest';
  if ($priv==0) return 'Disabled';
  return '';
}
function upriv($type)
{
  switch (strtolower($type))
  {
    case 'admin': return 127;
    case 'guest': return 1;
    case 'disabled': return 0;
    case 'none':case 'normal':default: return 2;
  }
}

  //====================
  // Log in
  //====================

$d = (isset($_GET['d'])?cleanPath($_GET['d']):false); // $d is replaced after login.

$status = '';

if ($_POST['logout'])
{
  setcookie('fmsid','',time()-86400);
  $status = 'lo';
}
else if ($_POST['login'] && $_POST['uname'] && dname2name($_POST['uname'])!=='guest')
{
  $uname = dname2name($_POST['uname']);
  $status = 'nli';
  foreach ($_PERSIST['users'] as $i => $cuser)
  {
    if ($cuser['name']===$uname)
    {
      if ($cuser['pass']===FALSE
      || $cuser['pass']===pwencode($uname,$_POST['pass']))
      {
        if ($cuser['priv'])
        {
          $uid = $i;
          if (!$uid) die('Trying to log in as guest.');
          $sid = md5($_POST['uname'].microtime().rand().'_s9k84ry');
          $_PERSIST['users'][$i]['sid']=$sid;
          $_PERSIST['users'][$i]['pli']=time();
          $_PERSIST['users'][$i]['rem']=($_POST['rem']?true:false);
          persist_update() or die('<err>Users not writable; CHMOD persist to 777.</err>');
          $user = $cuser;
          setcookie('fmsid',$sid,($_POST['rem']?time()+60*60*24*30:0));
          $status = 'li';
          $d = $user['dpsub']?$user['dpsub']:$user['psub'];
        }
        else
          $status = 'ad';
      }
      break;
    }
  }
}
else
{
  $sid = false;
  if ($_GET['fmsid']) $sid = $_GET['fmsid'];
  else if ($_COOKIE['fmsid']) $sid = $_COOKIE['fmsid'];
  if ($sid) foreach ($_PERSIST['users'] as $i => $cuser)
  {
    if ($cuser['sid']===$sid)
    {
      if ($cuser['pli']+60*60*24>time() || $cuser['rem'])
      {
        $uid = $i;
        $user = $cuser;
      }
      break;
    }
  }
}
if (!$uid) $sid = false;
else
{ // Logged in
  if ($_COOKIE['fmsid'] !== $sid)
  {
    $asid = '&fmsid='.$sid;
    $hasid = '&amp;fmsid='.$sid;
    $qsid=$hqsid = '?fmsid='.$sid;
    $isid = '<input type="hidden" name="fmsid" value="'.$sid.'" />';
    $ssid = $sid;
  }
}
if (!$sid)
  $sid = FALSE;
unset($i);
$presub = ($user['priv']==127?'':$user['psub']);

if ($presub) $allow_php = $false;

if ($_POST['login'] && dname2name($_POST['uname'])==='guest' && !$_PERSIST['users'][0]['priv'])
  $status = $_PERSIST['users'][0]['priv']?'gli':'gad';

if ($user['priv'] == 127) $aphp = TRUE;

  //====================
  // Parse GET strings
  //====================

if ($d===false) $d = (is_string($user['dpsub'])?$user['dpsub']:$user['psub']);

//$tmp = substr($d,0,strlen($presub));
//echo "<pre>";var_dump($presub);echo "\n";var_dump($d);echo "\n";var_dump($tmp);echo "</pre>";

if ($presub && substr($d,0,strlen($presub))!==$presub && !($nokill && !($isauth=false))) die('<err>Access Denied (Trying to access forbidden directory).</err>');
$postsub = substr($d,strlen($presub));

while (startsWith($postsub,'/'))
{
  if ($presub == '')
    $postsub = substr($postsub,1);
  else if ($nokill)
  {
    $isauth = false;
    break;
  }
  else
    die('<err>Access Denied.</err>');
}
if (!isadmin() && startsWith($postsub,'..') && !($nokill && !($isauth=false)))
  die('<err>Access Denied.</err>');

if (!endsWith($postsub,'/'))
{
  if (strrpos($postsub,'/') === false)
  {
    $file = $postsub;
    $postsub = '';
  }
  else
  {
    $file = substr($postsub,strrpos($postsub,'/')+1);
    $postsub = substr($postsub,0,strrpos($postsub,'/')+1);
  }
}

$fullpath = $prepath.$presub.$postsub.$file;
$fullurl = $preurl.$presub.$postsub.$file;
$fileext = fext($file);
$d = $presub.$postsub.$file;
$hd = urlencode($d?$d:'./');

  //====================
  // Account?
  //====================


?>