<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Administation Functions
 *
 */

@include_once 'config.inc.php';
@include_once 'fileman.lib.php';

function admin_salthash($password)
{
	return md5('dbu068as_admin_qw891a_'.$password.'_a910wv8tk6');
}
function fm_recache()
{
	global $ftp,$ftp_prepath,$fmurl,$preurl;
	$dh = @opendir('cache/');
	while (false !== ($file = @readdir($dh)))
	{
	if ($file == '.' || $file == '..' || $file == 'config-backup.inc.php' || $file == 'data-backup.inc.php' || $file == 'index.html') continue;
	if (is_dir('cache/'.$file)) // directory?
		fm_recachedir($file);
	else
		@unlink('cache/'.$file) or fm_recachedel(substr($fmurl,strlen($preurl)).'cache/'.$file);
	}
	@closedir($dh);
	fm_close();
	
	if (!is_file('cache/index.html'))
	{
		$res = @fopen('cache/index.html','x');
		@fwrite($res,'<err>Access denied</err>');
		@fclose($res);
	}
	@mkdir('cache/up/');
	@chmod('cache/up/',0777);
	@mkdir('cache/down/');
	@chmod('cache/down/',0777);
	
	return true;
}
function fm_recachedir($source)
{
	global $ftp,$ftp_prepath,$fmurl,$preurl;
	$dh = @opendir('cache/'.$source);
	while (false !== ($file = @readdir($dh)))
	{
	if ($file == '.' || $file == '..') continue;
	if (is_dir('cache/'.$source.$file)) // directory?
		fm_recachedir($source.$file);
	else
		@unlink('cache/'.$source.$file) or fm_recachedel(substr($fmurl,strlen($preurl)).'cache/'.$source.$file);
	}
	@closedir($dh);
	@rmdir('cache/'.$source) or fm_recachedel(substr($fmurl,strlen($preurl)).$source);
	return true;
}
function fm_recachedel($source)
{
	global $ftp, $ftp_prepath;
	fm_ftpconnect();
	return @ftp_delete($ftp, $ftp_prepath.$source);
}
function update_config()
{
	global $manual_install, $fmurl, $prepath, $preurl, $presub,
		$loginfirst, $isauth, $allow_php, $ext_jw_flv, $ext_pclzip, $ext_codemirror, $write_method,
		$ftp_server, $ftp_username, $ftp_password, $ftp_prepath;
	if (!isset($manual_install)) $manual_install = false;
	if (!isset($ftp_server)) $ftp_server = 'localhost';
	if (!isset($isauth)) $isauth = true;
	if (!isset($presub)) $presub = '';
	if (!isset($write_method)) $write_method = 'ftp';

	if (!isset($ftp_port)) $ftp_port = 21;

	if (!isset($ext_jw_flv)) $ext_jw_flv = true;
	if (!isset($ext_codemirror)) $ext_codemirror = true;
	if (!isset($ext_pclzip)) $ext_pclzip = true;

	$configdata = '<?php
/* IMPORTANT - SET THIS TO TRUE IF YOU ARE EDITING THIS FILE
 * INSTEAD OF USING THE INSTALLER
 */
$manual_install = '.persist_tophp($manual_install).';

/*** MANUAL INSTALLATION INSTRUCTIONS: ********************************
 *
 * 1. Edit this config file to suit your needs
 * 2. CHMOD cache/ to 777
 * 3. CHMOD persist.inc.php and config.inc.php to 777
 * 4. Log in using the username \'Admin\' and no password
 * 5. Go to Account Settings and set a password
 * 
 *** ABOUT DIRECT MODE: ***********************************************
 *
 * Direct mode is recommended only for users running PHP under the
 * same user as their FTP server (which is NOT the case under many
 * hosting services), or users with no FTP server.
 * 
 * To install using direct mode, you\'ll need to install manually
 * (remember to set $manual_install above to TRUE, and $write_method
 * to \'direct\'). In direct mode, you\'ll need to either CHMOD any
 * folders you want File Manager to manage to 777, or you\'ll need to
 * run PHP as a user with write permissions to all folders you want
 * File Manager to access.
 * 
 **********************************************************************/

/* The full URL of the directory File Manager is in.
 * Example: $fmurl = "http://www.example.com/fileman/";
 */
$fmurl = '.persist_tophp($fmurl).';

/* The path from PHP root to www.yoursite.com. Should end with "/"
 * Should start with "/" in everything but Windows. In Windows, should start
 * with "C:/" or "D:/" or something like that.
 * Example: $prepath = "/home/example/public_html/";
 */
$prepath = '.persist_tophp($prepath).';

/* The domain of your site. Should start with "http://" or something like that
 * and end with "/"
 * Example: $preurl = "http://www.example.com/";
 */
$preurl = '.persist_tophp($preurl).';

/* The subdirectory the filemanager is allowed to access.
 * Leave blank to allow the whole site to be accessible
 * Example: $presub = "subdir/othersubdir/";
 */
$presub = '.persist_tophp($presub).';

/* Whether people should see the login screen or enter Fileman as a
 * guest when not logged in.
 * Note: Only has effect when guest account is enabled
 * Example: $loginfirst = TRUE;
 */
$loginfirst = '.persist_tophp($loginfirst).';

/* Whether or not the user has permission to use Fileman
 * Example: $isauth = is_admin();
 * Or:      $isauth = TRUE;
 *  Unless you do not want to use File Manager\'s built-in user
 *  management, this should be set to TRUE.
 */
$isauth = '.persist_tophp($isauth).';

/* Whether or not you allow PHP.
 * Notice: PHP cannot be controlled yet, so this is VERY VERY insecure
 * if $presub isn\'t blank. Leave FALSE unless you\'re absolutely
 * sure you know what you\'re doing.
 */
$allow_php = '.persist_tophp($allow_php).';

/* Is the jw_flv_player addon installed?
 * Controls viewing FLV videos in-browser.
 * (It should be installed by default)
 */
$ext_jw_flv = '.persist_tophp($ext_jw_flv).';

/* Is the CodeMirror addon installed?
 * Allows syntax highlighting.
 * (It should be installed by default)
 */
$ext_codemirror = '.persist_tophp($codemirror).';

/* Is the PclZip addon installed?
 * Controls zipping/unzipping functionality.
 * (It should be installed by default)
 */
$ext_pclzip = '.persist_tophp($ext_pclzip).';

/* How files are edited. Can be "direct" or "ftp" or "fullftp".
 * direct -  Read and write locally on the server.
 * ftp -     Read locally from the server, write using FTP.
 *           Default, because many hosts don\'t allow direct
 *           PHP writing.
 * fullftp - Read and write using FTP. This diminishes many
 *           features and slows down File Manager significantly.
 *           Should be used if editing files on a remote server.
 */
$write_method = '.persist_tophp($write_method).';

/* FTP options
 */
$ftp_server = '.persist_tophp($ftp_server).';
$ftp_port = '.persist_tophp($ftp_port).';
$ftp_username = '.persist_tophp($ftp_username).';
$ftp_password = '.persist_tophp($ftp_password).';
$ftp_prepath = '.persist_tophp($ftp_prepath).';
$ftp_ftps = '.persist_tophp($ftp_ftps).';
?>';
	$file = @fopen('config.inc.php','w');
	if ($file === false) return false;
	$works = @fwrite($file,$configdata);
	@fclose($file);
	@copy('config.inc.php','cache/config-backup.inc.php');
	if ($works !== false) return true;
	return false;
}
function update_data()
{
	@copy('persist.inc.php','cache/data-backup.inc.php');
	return true;
}

?>