<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Installation
 *
 * Should be deleted or chmoded to 000 after installation
 * 
 */

@include_once 'config.inc.php';

$owm = $write_method;
$write_method = 'ftp';

@include_once 'persist.inc.php';
include_once 'admin.lib.php';

$write_method = $owm;
$user['priv'] = 127;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>&AElig;soft File Manager</title>
    <style type="text/css">
    <!--
      html { margin:0; padding:0; height:100%;}
      form { margin:0; padding:0; border:0; }
      body
      {
        color: #2A4DAB;
        background-color: #5D91DA;
        font-family: Tahoma, Geneva, Verdana, Arial, Trebuchet MS, Sans-Serif;
        font-size: 8pt;
        margin: 0;
        padding: 0;
        height: 100%;
      }
      .box
      {
        color: #000000;
        text-align: left;
        background-color: #FFFFFF;
        border: 1px solid #2A4DAB;
        margin: 0px;
        padding: 0px;
        width: 600px;
      }
      .boxh
      {
        /*color: #D9E1F6;*/
        color: #FFFFFF;
        background: #3980D2 url(images/bhbg.gif);
        font-size: 8pt;
        font-weight: bold;
        margin: 0px;
        padding: 4px 10px 4px 10px;
        display: block;
        text-decoration: none;
      }
      .boxhd
      {
        background-image: url(images/bhdbg.gif);
      }
      .boxd
      {
        color: #000000;
        background-color: #FFFFFF;
        font-size: 8pt;
        margin: 0px;
        padding: 10px;
      }
      .boxd a
      {
        color: #002280;
        text-decoration: none;
        vertical-align: middle;
      }
      .boxd img
      { border: 0; }
      .boxd a:hover
      {
        text-decoration: underline;
      }
      .togbtn
      {
        float: right;
        text-decoration: none;
        display: block;
      }
      .togbtn img
      {
        border: 0;
      }
      .overflowable
      {
        overflow: hidden;
      }
      .textbox
      { font-family: Tahoma, Geneva, Verdana, Arial, Trebuchet MS, Sans-Serif; padding:2px; border: 1px solid #ABADB3; }
      .textbox:hover
      { padding:2px; border: 1px solid #2A4DAB; }
      .textbox:focus, .textbox:active
      { padding:1px; border: 2px solid #2A4DAB; }
    -->
    </style>
    <script language="javascript" type="text/javascript">
    <!--
      var height=0;
      function resize()
      {
        if (!height) height = (document.getElementById('wrapper').offsetHeight);
        document.getElementById('wrapper').style.paddingBottom = (document.getElementById('wraptab').offsetHeight>height+102)?'100px':'0';
        document.getElementById('logo').style.display = (document.getElementById('wraptab').offsetHeight>height+2)?'block':'none';
      }
    //-->
    </script>
  </head>
  <body onload="resize()" onresize="resize()">
    <table border="0" margin="0" padding="0" width="100%" height="100%">
      <tr><td id="wraptab" align="center" valign="middle"><div id="wrapper">
        <div style="text-align:center;" id="logo"><img src="images/loginlogosmall.gif" /></div>
        <div class="box">
          <a class="boxh boxhd" id="ucp_a">
            Install
          </a>
          <div class="boxd lb" id="ucp" style="height: 250px;position:relative;">
            <form action="install.php" method="post">
<?php
/*
$_SERVER['PHP_SELF'] = '/fileman/install.php'
$_SERVER['DOCUMENT_ROOT'] = '/home/aesoft/public_html'
__FILE__ = '/home/aesoft/public_html/fileman/install.php'
$_SERVER['SCRIPT_FILENAME'] = '/home/aesoft/public_html/fileman/install.php'
$_SERVER['PATH_TRANSLATED'] = '/home/aesoft/public_html/fileman/install.php'
$_SERVER['SCRIPT_NAME'] = '/fileman/install.php'
$_SERVER['REQUEST_URI'] = '/fileman/install.php'
$_SERVER['HTTP_HOST'] = 'aesoft.org'
*/

if ($_PERSIST['inst_id'] && $_REQUEST['inst_id']===$_PERSIST['inst_id'])
{
	$inst_auth = true;
	$inst_id = $_REQUEST['inst_id'];
}
else
{
	$inst_auth = $_PERSIST['users'][1]['pass']?($_PERSIST['users'][1]['pass'] === admin_salthash($_POST['adpass'])?true:false):false;
	if ($inst_auth)
	{
		$inst_id = ($_PERSIST['inst_id'] = md5(mt_rand()+1));
		persist_update();
	}
}

// initialize userdata (config data is initialized in admin.lib.php)
if (!@$_PERSIST['users'])
{
	$_PERSIST['users'] = array(
		array(
			'dname' => 'Guest',
			'name' => 'guest',
			'pass' => false,
			'psub' => 'public/',
			'priv' => 1,
			'sid' => FALSE,
			'dpsub' => 'public/'
		),
		array(
			'dname' => 'Admin',
			'name' => 'admin',
			'pass' => false,
			'psub' => '',
			'priv' => 127
		),
	);
}

//echo '$inst_id='.persist_tophp($inst_id);

$step = isset($_REQUEST['step'])?intval($_REQUEST['step']):($_PERSIST['users'][1]['pass']?-1:1);

if ($step == 0 && $inst_auth) $step = 1;

echo '<!-- Step '.$step.' -->';

function install_ftpconnect()
{
  global $ftp, $ftp_server, $ftp_port, $ftp_username, $ftp_password, $ftp_passive;
  if (!($ftp = @ftp_connect($ftp_server, $ftp_port)))
  {
    $GLOBALS['insterror'] = 'Cannot connect to server.';
    return false;
  }
  if (!@ftp_login($ftp, $ftp_username, $ftp_password))
  {
    @ftp_close($ftp);
    $GLOBALS['insterror'] = 'Incorrect FTP username or password.';
    var_export(array($ftp_server.$ftp_port.$ftp_username.$ftp_password));
    return ($ftp = false);
  }
  if ($ftp_passive) @ftp_pasv();
  return true;
}

$insterror = '';
$ftp = false;
if ($step == 1 && isset($_REQUEST['ftpuser']) && ($inst_auth || !@$_PERSIST['users'][1]['pass'] || !$_PERSIST['inst_id']))
{
	echo '<!-- Step 1 success 1 -->';
		
	$ftp_username = $_REQUEST['ftpuser'];
	$ftp_password = $_REQUEST['ftppass'];
	$ftp_server = 'localhost';
	$ftp_port = 21;
	$ftp_passive = false;
	if (install_ftpconnect())
	{
		echo '<!-- Step 1 success 2 -->';
		$cpath = __FILE__;
		if (substr($cpath,0,1)==='/') $cpath = substr($cpath,1);
		while (strpos($cpath,'/')!==FALSE && $temp = (ftp_mdtm($ftp, $cpath)<=0))
			$cpath = substr($cpath,strpos($cpath,'/')+1);
		if (!$temp)
		{
			echo '<!-- Step 1 success 3 -->';
			$ftp_prepath = substr($cpath,0,-strlen($_SERVER['REQUEST_URI'])+1);
			$prepath = substr(__FILE__,0,-strlen($_SERVER['REQUEST_URI'])+1);
			$preurl = 'http://'.$_SERVER['HTTP_HOST'].'/';
			$fmurl = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'],0,strrpos($_SERVER['REQUEST_URI'],'install.php'));
			$write_method = 'ftp';
			$fileman_path = substr($fmurl,strlen($preurl));
			echo '<!-- Step 1 success 3.1 -->';
			
			if (!file_exists('config.inc.php')) @ftp_put($ftp,$ftp_prepath.$fileman_path.'config.inc.php','blank.bin',FTP_BINARY);
			if (!file_exists('persist.inc.php')) @ftp_put($ftp,$ftp_prepath.$fileman_path.'persist.inc.php','blank.bin',FTP_BINARY);
			
			if ((is_writable('config.inc.php') || @ftp_chmod($ftp,octdec('777'),$ftp_prepath.$fileman_path.'config.inc.php'))
			&& (is_writable('persist.inc.php') || @ftp_chmod($ftp,octdec('777'),$ftp_prepath.$fileman_path.'persist.inc.php'))
			&& @ftp_chmod($ftp,octdec('777'),$ftp_prepath.$fileman_path.'cache/'))
			{
				echo '<!-- Step 1 success 4 -->';
				$step = 2;
				if (!$inst_id)
				{
					$inst_id = ($_PERSIST['inst_id'] = md5(mt_rand()+1));
					persist_update();
				}
				$inst_auth = true;
				echo '<!-- Step 1 success 5 -->';
			}
			
			update_config();
		}
	}
}
echo '<!-- Step 1 end -->';
if (!$_POST['adpass'] && !$_PERSIST['users'][1]['pass'])
{
	$insterror = 'You must enter an admin password.';
}
else if ($step == 2 && $inst_auth && isset($_POST['postsub']) && $_POST['adpass'] === $_POST['adpassc'])
{
	$loginfirst = ($_POST['nloginfirst']?false:true);
	if (!$_PERSIST['users'][1]['pass'] || $_POST['adpass'])
	{
		$_PERSIST['users'][1]['pass'] = admin_salthash($_POST['adpass']);
	}
	$_PERSIST['users'][0]['priv'] = ($_POST['enableguest']?1:0);
	$_PERSIST['users'][0]['psub'] = $_POST['postsub'];
	
	include_once 'admin.lib.php';
	
	update_config();
	persist_update();
	@copy('persist.inc.php','cache/data-backup.inc.php');
	fm_recache();
	$step = 3;
}

if ($step == 0)
{
?>
              <div>Please enter your administrator password to reinstall.</div>
              <div>&nbsp;</div>
<?php
	if (isset($_POST['adpass'])) echo '<div style="color:#EE1111;">Incorrect admin password.</div>';
?>
              <div><label>Admin username:</label></div>
              <div><input type="text" class="textbox" disabled="disabled" value="Admin"></div>
              <div><label for="ftpuser">Admin password:</label></div>
              <div><input type="password" class="textbox" name="adpass" id="adpass"></div>
              <div>&nbsp;</div>
              <div style="position:absolute;bottom:0;left:0;padding:10px;"><input type="hidden" name="sact" value="inst" /><input type="button" disabled="disabled" name="prev" value="&laquo; Back" /> <input type="submit" name="next" value="Next &raquo;" /></div>
<?php
}
else if ($step == 1 && ($inst_auth || !@$_PERSIST['users'][1]['pass'] || !$_PERSIST['inst_id']))
{
	if ($_REQUEST['ftpuser']) $ftpuser = $_REQUEST['ftpuser'];
	else if (!$ftpuser && $ftp_username !== 'example') $ftpuser = $ftp_username;
	else if (!$ftpuser)
	{
		if (substr($_SERVER['DOCUMENT_ROOT'],0,6) == '/home/' && strpos($_SERVER['DOCUMENT_ROOT'],'/public_html') && ctype_alnum(substr($_SERVER['DOCUMENT_ROOT'],6,strpos($_SERVER['DOCUMENT_ROOT'],'/public_html')-6))) $ftpuser = substr($_SERVER['DOCUMENT_ROOT'],6,strpos($_SERVER['DOCUMENT_ROOT'],'/public_html')-6);
	}
?>
              <h3>FTP details to <?php echo $_SERVER['HTTP_HOST']; ?> (Step 1/2)</h3>
              <div>Welcome to AEsoft File Manager!</div>
              <div>&nbsp;</div>
<?php
		if ($_POST['ftpuser'] && !$insterror) echo '<div style="color:#EE1111;">An unknown error occurred.</div>';
		else if ($_POST['ftpuser']) echo '<div style="color:#EE1111;">'.$insterror.'</div>';
?>
              <div><label for="ftpuser">FTP Username:</label></div>
              <div><input type="text" class="textbox" name="ftpuser" id="ftpuser" value="<?php if ($inst_auth) echo $ftpuser; ?>"></div>
              <div><label for="ftppass">FTP Password:</label></div>
              <div><input type="text" class="textbox" name="ftppass" id="ftppass" value="<?php if ($inst_auth) echo $ftp_password; ?>"></div>
              <div style="position:absolute;bottom:0;left:0;padding:10px;"><input type="hidden" name="sact" value="inst" /><input type="button" disabled="disabled" name="prev" value="&laquo; Back" /> <input type="submit" name="next" value="Next &raquo;" /></div>
<?php
}
else if ($step == 2 && $inst_auth)
{
?>
              <h3>Options (Step 2/2)</h3>
              <input type="hidden" name="iact" value="options" />
<?php
	if ($_PERSIST['users'][1]['pass'] || @$_POST['adpass'])
	{
		echo '<button onclick="this.style.display=\'none\';document.getElementById(\'editadpass\').style.display=\'block\';return false">Edit admin password</button><div id="editadpass" style="display:none">';
	}
		if (isset($_POST['adpass']) && $_POST['adpass'] !== $_POST['adpassc']) echo '<div style="color:#EE1111;">Passwords don\'t match.</div>';
?>
              <div><label>Admin Username</label></div>
              <div><input type="text" class="textbox" value="Admin" disabled="disabled"></div>
              <div><label for="adpass">Admin Password</label></div>
              <div><input type="password" class="textbox" name="adpass" id="adpass" value=""></div>
              <div><label for="adpassc">Confirm Admin Password</label></div>
              <div><input type="password" class="textbox" name="adpassc" id="adpassc" value=""></div>
<?php
	if ($_PERSIST['users'][1]['pass'] || @$_POST['adpass'])
	{
		echo '</div>';
	}
?>
              <div>&nbsp;</div>
              <div><input type="checkbox" name="enableguest" id="enableguest" value="true"<?php if ($_PERSIST['users'][0]['priv']) echo ' checked="checked"'; ?> /><label for="enableguest"> Enable Guest account</label></div>
              <div>Guest account can access: <?php echo $preurl; ?><input type="text" class="textbox" name="postsub" id="postsub" value="<?php echo $_PERSIST['users'][0]['psub']; ?>"></div>
              <div><input type="checkbox" name="nloginfirst" id="nloginfirst" value="true"<?php if (!$loginfirst) echo ' checked="checked"'; ?> /><label for="nloginfirst"> By default, open File Manager in Guest mode instead of showing login screen (has no effect if Guest is disabled)</label></div>
              <div style="position:absolute;bottom:0;left:0;padding:10px;"><input type="hidden" name="sact" value="inst" /><input type="button" onclick="document.location.href='install.php?step=1&inst_id=<?php echo $inst_id;?>'" name="prev" value="&laquo; Back" /> <input type="submit" name="next" value="Next &raquo;" /></div>
<?php
}
else if ($step == 3)
{
?>
              <h3>Installation successful!</h3>
              <div style="position:absolute;bottom:0;left:0;padding:10px;"><input type="hidden" name="sact" value="inst" /><input type="button" onclick="document.location.href='install.php?step=2&inst_id=<?php echo $inst_id;?>'" name="prev" value="&laquo; Back" /> <input type="button" onclick="document.location.href='index.php'" value="Finish &raquo;" /></div>
<?php
}
else
{
?>
              <h3>Already installed</h3>
              <p>File Manager is already installed. Would you like to reinstall?</p>
              <p>Reinstalling can fix problems that can occur if File Manager has been moved to a new server or folder, or if you wish to change your server's FTP account.</p>
              <div>&nbsp;</div>
              <div><input type="button" onclick="document.location.href='install.php?step=0'" value="Reinstall &raquo;" /></div>
<?php
}
?>
            <input type="hidden" name="inst_id" value="<?php echo $inst_id;?>" />
            <input type="hidden" name="step" value="<?php echo $step;?>" />
            <?php if ($inst_auth) echo '<!-- You are authorized. -->'; ?>
            </form>
          </div>
        </div>
      </div></td></tr>
    </table>
  </body>
</html>
