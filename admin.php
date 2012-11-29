<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Administration
 *
 * Administration module.
 *
 * Default module - Performs important functionality,
 * but can be deleted if absolutely necessary
 * 
 */

@include_once 'session.lib.php';
require_once 'config.inc.php';
require_once 'admin.lib.php';

if ($user['priv']!==127) die('Access denied (you are not logged in as an administrator).');

$tab = $_GET['t']?$_GET['t']:'main';

if (isset($_GET['c']))
{
  die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><title>Close this window.</title></head><body onload="window.close()">Close this window.</body></html>');
}

$id = (isset($_GET['id'])?intval($_GET['id']):FALSE);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title><?php echo htmlentities($file); ?> - File Manager Administration</title>
    <style type="text/css">
    <!--
      html,body,div,p,h1,h2,h3
      {
        margin:0;padding:0;
      }
      body
      {
        padding: 12px;
        font-family: Verdana, sans-serif;
        font-size: 10pt;
        background: #D6E3F5 none;
      }
      .wrapper,#warn
      {
        border: 1px solid #999;
        background: #FFFFFF none;
        padding: 12px;
        margin-bottom: 12px;
      }
#tabs { padding: 3px 6px; }
      #tabs a
      {
        border: 1px solid #999;
        border-bottom: 0;
        padding: 3px 6px; margin-left: 7px;
        background: #FFFFFF none;
color: #000000;
text-decoration: none;
      }
#tabs a.cur
{
padding-bottom: 4px;
}
p {margin-bottom: 1em;}
      #warn
      {
        background: #EFD3BD none;
      }
      .small
      {
        font-size: 8pt;
      }
      .ne .yedit, .ye .nedit
      { display: none; }
      .ne .yedit, .ye .nedit
      { display: block; }
      .btnbar
      {
        margin-top: 10px;
      }
      .textbox
      { font-family: Tahoma, Geneva, Verdana, Arial, Trebuchet MS, Sans-Serif; padding:2px; border: 1px solid #ABADB3; }
      .textbox:hover
      { padding:2px; border: 1px solid #2A4DAB; }
      .textbox:focus, .textbox:active
      { padding:1px; border: 2px solid #2A4DAB; }
      table.table
      { border:0; border-top: 1px solid #999999;border-left: 1px solid #999999; padding: 0; }
      table.table td, table th
      { border:0; border-bottom: 1px solid #999999;border-right: 1px solid #999999; padding: 3px; }
      table.table th
      { background: #EAF0FF none; }
    -->
    </style>
  </head>
  <body><div id="warn">
    <?php echo 'You are '.$user['dname'].' (#'.$uid.')'; ?>
  </div>
  <div id="tabs"><a href="admin.php?d=<?php echo $hd,$hasid; ?>"<?php if ($tab=='main') echo ' class="cur"'; ?>>Main</a><a href="admin.php?t=users&amp;d=<?php echo $hd,$hasid; ?>"<?php if ($tab=='users') echo ' class="cur"'; ?>>Users</a></div>
  <div class="wrapper">
<?php
if ($_POST['aact']=='newuser')
{
  if ($_POST['pass']===$_POST['pass2'] && dname2name($_POST['uname']))
  {
    if ($_POST['temppass'])
    {
      $_PERSIST['users'][] = array(
        'dname' => $_POST['uname'],
        'name' =>  dname2name($_POST['uname']),
        'pass' =>  pwencode(dname2name($_POST['uname']),$_POST['pass']),
        'psub' => $_POST['psub'],
        'dpsub' => ($_POST['dpsub']=='*'?$_POST['psub']:$_POST['dpsub']),
        'priv' => upriv($_POST['rank']),
        'sid' =>   '',
        'temppass' => true
      );
    }
    else
    {
      $_PERSIST['users'][] = array(
        'dname' => $_POST['uname'],
        'name' =>  dname2name($_POST['uname']),
        'pass' =>  pwencode(dname2name($_POST['uname']),$_POST['pass']),
        'psub' => $_POST['psub'],
        'dpsub' => ($_POST['dpsub']=='*'?$_POST['psub']:$_POST['dpsub']),
        'priv' => upriv($_POST['rank']),
        'sid' =>   ''
      );
    }
    persist_update() or die('<strong>Error:</strong>Persist not writable, please CHMOD persist.inc.php to 777.');
	update_data();
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>User <?php echo $_POST['uname']; ?> successfully added.</strong></p>
<?php
  }
  else
  {
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>Information missing (passwords do not match or username empty).</strong></p>
<?php
  }
}
else if ($_POST['aact']=='chguser' && $id!==FALSE)
{
  if ($_POST['pass']==$_POST['pass2'] && dname2name($_POST['uname']))
  {
    $_PERSIST['users'][$id]['dname'] = $_POST['uname'];
    $_PERSIST['users'][$id]['name'] = dname2name($_POST['uname']);
    if ($_POST['pass'] && ($id>1 || $id==1&&$uid==1))
    {
      $_PERSIST['users'][$id]['pass'] = pwencode(dname2name($_POST['uname']),$_POST['pass']);
    }
    if ($_POST['temppass'] && ($id>1 || $id==1&&$uid==1)) $_PERSIST['users'][$id]['temppass'] = true;
    else if ($_PERSIST['users'][$id]['temppass']) unset($_PERSIST['users'][$id]['temppass']);
    $_PERSIST['users'][$id]['psub'] = $_POST['psub'];
    $_PERSIST['users'][$id]['dpsub'] = ($_POST['dpsub']==='*'?$_POST['psub']:$_POST['dpsub']);
    $_PERSIST['users'][$id]['priv'] = upriv($_POST['rank']);

    if ($_PERSIST['users'][$id]['priv']==2 && $id==0) $_PERSIST['users'][$id]['priv'] = 1;
    persist_update() or die('<strong>Error:</strong>Persist not writable, please CHMOD persist.inc.php to 777.');
	update_data();
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>User <?php echo $_POST['uname'],' (#',$id; ?>) successfully edited.</strong></p>
<?php
    $id = FALSE;
  }
  else
  {
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>Information missing (passwords do not match or username empty).</strong></p>
<?php
  }
}
else if ($_POST['aact']=='deluser')
{
  if ($_PERSIST['users'][$_POST['id']]['name']===$_POST['uname'])
  {
    unset($_PERSIST['users'][$_POST['id']]);
    persist_update() or die('<strong>Error:</strong>Persist not writable, please CHMOD persist.inc.php to 777.');
	update_data();
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>User <?php echo $_POST['uname']; ?> successfully deleted.</strong></p>
<?php
  }
  else
  {
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>User ID/name mismatch.</strong> (User <?php echo $_POST['id'],' is ',$_PERSIST['users'][$_POST['id']]['name'],', not ',$_POST['uname'];?>)</p>
<?php
  }
}
else if ($_POST['aact']=='togguest')
{
  $_PERSIST['users'][0]['priv'] = ($_PERSIST['users'][0]['priv']?0:1);
  persist_update() or die('<strong>Error:</strong>Persist not writable, please CHMOD persist.inc.php to 777.');
	update_data();
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>Guest account is now <?php echo $_PERSIST['users'][0]['priv']?'enabled':'disabled' ?>.</strong></p>
<?php
}
else if ($_POST['aact']=='settings')
{
  $loginfirst = $_POST['nloginfirst']?FALSE:TRUE;
  update_config();
?>
      <p style="padding:2px;border:1px solid #CC9966;margin-bottom:10px;"><strong>Your settings have been saved.</strong></p>
<?php
}
if ($id !== FALSE)
{
  $cuser = $_PERSIST['users'][$id];
?>
    <form action="admin.php?t=users&amp;d=<?php echo $hd,'&amp;id=',$id,$hasid; ?>" id="chgu" style="border:3px solid #FFD389;padding:3px;" method="post">
      <span class="small">Username:</span><br />
<?php if ($id) { ?>
      <input class="textbox" type="text" name="uname" value="<?php echo $cuser['dname']; ?>" /><br />
      <div id="cpwb"><span class="small">Password:</span><br />
      <input type="button" value="Change password" onclick="tog('cpw',1);document.getElementById('temppass').checked=true" /></div>
      <div id="cpw"><span class="small">New Password:</span><br />
      <input class="textbox" type="password" name="pass" /><br />
      <span class="small">Confirm password:</span><br />
      <input class="textbox" type="password" name="pass2" /><br />
      <?php if (!$cuser['temppass']) { ?><input type="checkbox" name="temppass" id="temppass" /><label for="temppass"> Force password change upon login</label><?php } ?></div><?php if ($cuser['temppass']) { ?><input type="checkbox" name="temppass" id="temppass" checked="checked" /><label for="temppass"> Force password change upon login</label><?php } ?>
<?php } else { ?>
<?php } ?>
      <span class="small">Can access:</span><br />
      <?php echo $preurl; ?><input class="textbox" type="text" name="psub" value="<?php echo $cuser['psub']; ?>" /><br />
      <span class="small">Default folder: (* for same as above)</span><br />
      <?php echo $preurl; ?><input class="textbox" type="text" name="dpsub" value="<?php echo $cuser['dpsub']==$cuser['psub']?'*':$cuser['dpsub']; ?>" /><br />
      <span class="small">Rank:</span><br />
      <select name="rank">
        <option value="none"<?php if ($cuser['priv']==2||$cuser['priv']==1) echo ' selected="selected"' ?>>Normal</option>
        <option value="disabled"<?php if ($cuser['priv']==0) echo ' selected="selected"' ?>>Disabled</option>
<?php if ($id) { ?>
        <option value="admin"<?php if ($cuser['priv']==127) echo ' selected="selected"' ?>>Admin</option>
<?php } ?>
      </select>
      <div class="btnbar"><input type="hidden" name="aact" value="chguser" /><input type="submit" value="Edit" /> <input type="button" value="Cancel" onclick="document.location.href='admin.php?t=users&amp;d=<?php echo $hd,$hasid; ?>'" /></div>
    </form>
<?php
}
else if ($tab == 'main')
{
?>
    <p>This script is owned by <strong><?php $temp = posix_getpwuid(getmyuid()); echo $temp['name']; ?></strong><br />
    This script is running as <strong><?php $temp = posix_getpwuid(posix_getuid()); echo $temp['name']; ?></strong></p>
          <form action="admin.php?d=<?php echo $hd,$hasid; ?>" method="post">
              <div><input type="checkbox" name="nloginfirst" id="nloginfirst" value="true"<?php if (!$loginfirst) echo ' checked="checked"'; ?> /><label for="nloginfirst"> By default, open File Manager in Guest mode instead of showing login screen (has no effect if Guest is disabled)</label></div>
              <p><input type="hidden" name="aact" value="settings" /><input type="submit" value="Apply" /></p>
</form>
<p><a href="install.php?step=0" target="_blank">Reinstall File Manager</a></p>
<?php
}
else if ($tab == 'users')
{
?>
    <table class="table" border="1" cellspacing="0" cellpadding="3" style="min-width:40%;">
      <tr>
        <th>
          ID
        </th>
        <th>
          Name
        </th>
        <th>
          Can&nbsp;access
        </th>
        <th>
          Special
        </th>
        <th>
          Last&nbsp;logged&nbsp;in
        </th>
        <th ></th>
      </tr>
<?php
  foreach ($_PERSIST['users'] as $i => $cuser)
  {
?>
      <tr class="ne"<?php if (!$cuser['priv']) echo ' style="color: #999999;"'; ?>>
        <td>
          <?php echo $i; ?>
        </td>
        <td>
          <?php echo '<span'.(utype($cuser['priv'])==='Admin'?' style="color:#BB2222"':'').'>'.$cuser['dname'].'</span>'; ?>
        </td>
        <td>
          <?php echo $cuser['psub']?$cuser['psub']:'<em>All</em>'; ?>
        </td>
        <td>
          <?php echo utype($cuser['priv']); ?>&nbsp;
        </td>
        <td>
          <?php echo $i?reldate($cuser['pli']):'&mdash;'; ?>
        </td>
        <td>
<?php
    if ($i>1)
    {
?>
          <form action="admin.php?t=users&amp;d=<?php echo $hd,$hasid; ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $i; ?>" /><input type="hidden" name="uname" value="<?php echo $cuser['name']; ?>" />
            <input type="hidden" name="aact" value="deluser" /><input type="button" value="Edit" onclick="document.location.href='admin.php?t=users&d=<?php echo $hd,'&amp;id=',$i,$hasid; ?>'" /> <input type="submit" value="Delete" onclick="return confirm('Are you sure you want to delete the user <?php echo $cuser['name']; ?>?');"/>
          </form>
<?php
    }
    if ($i==0)
    {
?>
          <form action="admin.php?t=users&amp;d=<?php echo $hd,$hasid; ?>" method="post">
            <input type="hidden" name="aact" value="togguest" /><input type="button" value="Edit" onclick="document.location.href='admin.php?t=users&d=<?php echo $hd,'&amp;id=0',$hasid; ?>'" /> <input type="submit" value="<?php echo $cuser['priv']?'Disable':'Enable'; ?>" />
          </form>
<?php
    }
?>
        </td>
      </tr>
<?php
  }
?>
    </table>
    <br />
    <div id="addub">
      <input type="button" value="Create new user" onclick="tog('addu',1)" />
    </div>
    <form action="admin.php?t=users&amp;d=<?php echo $hd,$hasid; ?>" id="addu" style="border:3px solid #FFD389;padding:3px;" method="post">
      <span class="small">Username:</span><br />
      <input class="textbox" type="text" name="uname" /><br />
      <span class="small">Password:</span><br />
      <input class="textbox" type="password" name="pass" /><br />
      <span class="small">Confirm password:</span><br />
      <input class="textbox" type="password" name="pass2" /><br />
      <input type="checkbox" name="temppass" id="temppass" checked="checked" /><label for="temppass"> Force password change upon login</label><br />
      <span class="small">Can access:</span><br />
      <?php echo $preurl; ?><input class="textbox" type="text" name="psub" /><br />
      <span class="small">Default folder: (* for same as above)</span><br />
      <?php echo $preurl; ?><input class="textbox" type="text" name="dpsub" value="*" /><br />
      <span class="small">Rank:</span><br />
      <select name="rank">
        <option value="none">Normal</option>
        <option value="admin">Admin</option>
      </select>
      <div class="btnbar"><input type="hidden" name="aact" value="newuser" /><input type="submit" value="Create" /> <input type="button" value="Cancel" onclick="tog('addu',0)" /></div>
    </form>
<?php
}
?>
    <script language="javascript" type="text/javascript">
    <!--
<?php if ($id!==0) { ?>
      document.getElementById('<?php echo $id===FALSE?'addu':'cpw' ?>').style.display='none';
<?php } ?>
      function tog(id,val)
      {
        if (val)
        {
          document.getElementById(id).style.display='block';
          document.getElementById(id+'b').style.display='none';
        }
        else
        {
          document.getElementById(id).style.display='none';
          document.getElementById(id+'b').style.display='block';
        }
      }
    //-->
    </script>
    </div><div class="btnbar"><input type="button" style="font-size: 14pt;" name="na" value="&laquo; Back" onclick="parent.location.href = 'index.php?d=<?php echo $hd,$havmode,$hasid; ?>';" /></div>
  </body>
</html>