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

include_once 'ftpusers.inc.php';
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
$time = time();

if ($_POST['logout'])
{
  setcookie('fmsid','',time()-86400);
  $_PERSIST['users'][$uid]['sid'] = '';
  persist_update('_PERSIST','ftpusers.inc.php');
  $status = 'lo';
}
else if ($_POST['login'] && $_POST['server'])
{
  $status = 'nli';
  if (!intval($_POST['port'])) $_POST['port'] = 21;
  $ftp = @ftp_connect($_POST['server'],intval($_POST['port']));
  if ($ftp)
  {
    if (!$_POST['uname']) $_POST['uname'] = 'anonymous';
    if (@ftp_login($ftp,$_POST['uname'],$_POST['pass']))
    {
      foreach ($_PERSIST['users'] as $i => $cuser)
      {
        if ($i==0)
        {}
        else if ($_PERSIST['users'][$i]['pli']<$time-60*60*24*7)
        {
          unset($_PERSIST['users'][$i]);
          $i--;
        }
        else if ($_PERSIST['users'][$i]['name'] == $_POST['uname']
                 && $_PERSIST['users'][$i]['server'] == $_POST['server'])
        {
          unset($_PERSIST['users'][$i]);
          $i--;
        }
      }
      $sid = md5($_POST['uname'].microtime().rand().'_s9k84ry');
      $user = array(
        'dname' => $_POST['uname'],
        'name' => $_POST['uname'],
        'pass' => $_POST['pass'],
        'server' => $_POST['server'],
        'port' => $_POST['port'],
        'ftps' => $_POST['ftps']?true:false,
        'pli' => $time,
        'sid' => $sid,
        'priv' => 63,
        'psub' => '',
        'dpsub' => ''
      );
      $_PERSIST['users'][] = $user;
      $_PERSIST['users'] = array_values($_PERSIST['users']);
      $uid = count($_PERSIST['users'])-1;
      persist_update('_PERSIST','ftpusers.inc.php') or die('<err>Users not writable; CHMOD persist to 777.</err>');
      setcookie('fmsid',$sid,($_POST['rem']?time()+60*60*24*30:0));
      $status = 'li';
      $d = '';
    }
  }
  else
    $status = 'nsc';
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
$presub = '';

$allow_php = TRUE;
$ftp_server = $user['server'];
$ftp_port = $user['port'];
$ftp_username = $user['name'];
$ftp_password = $user['pass'];
$ftp_prepath = '/';
$ftp_ftps = $user['ftps'];

  //====================
  // Parse GET strings
  //====================

if ($d===false) $d = '';

$postsub = substr($d,strlen($presub));

while (startsWith($postsub,'/'))
  $postsub = substr($postsub,1);

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


