<?php
/* IMPORTANT - SET THIS TO TRUE IF YOU ARE EDITING THIS FILE
 * INSTEAD OF USING THE INSTALLER
 */
$manual_install = FALSE;

/*** MANUAL INSTALLATION INSTRUCTIONS: ********************************
 *
 * 1. Edit this config file to suit your needs
 * 2. If not writable by PHP, CHMOD cache/ to 777
 * 3. If not writable by PHP, CHMOD persist.inc.php, config.inc.php to 666
 * 4. Log in using the username 'Admin' and no password
 * 5. Go to Account Settings and set a password
 * 
 *** ABOUT DIRECT MODE: ***********************************************
 *
 * Direct mode is recommended only for users running PHP under the
 * same user as their FTP server (which is NOT the case under many
 * hosting services), or users with no FTP server.
 * 
 * To install using direct mode, you'll need to install manually
 * (remember to set $manual_install above to TRUE, and $write_method
 * to 'direct'). In direct mode, you'll need to either CHMOD any
 * folders you want Filecharger to manage to 777, or you'll need to
 * run PHP as a user with write permissions to all folders you want
 * Filecharger to access.
 * 
 **********************************************************************/

/* The full URL of the directory File Manager is in.
 * Example: $fmurl = "http://www.example.com/fileman/";
 */
$fmurl = 'http://example.com/fileman/';

/* The path from PHP root to www.yoursite.com. Should end with "/"
 * Should start with "/" in everything but Windows. In Windows, should start
 * with "C:/" or "D:/" or something like that.
 * Example: $prepath = "/home/example/public_html/";
 */
$prepath = '/home/example/public_html/';

/* The domain of your site. Should start with "http://" or something like that
 * and end with "/"
 * Example: $preurl = "http://www.example.com/";
 */
$preurl = 'http://example.com/';

/* The subdirectory the filemanager is allowed to access.
 * Leave blank to allow the whole site to be accessible
 * Example: $presub = "subdir/othersubdir/";
 */
$presub = NULL;

/* Whether people should see the login screen or enter Fileman as a
 * guest when not logged in.
 * Note: Only has effect when guest account is enabled
 * Example: $loginfirst = TRUE;
 */
$loginfirst = TRUE;

/* Whether or not the user has permission to use Fileman
 * Example: $isauth = is_admin();
 * Or:      $isauth = TRUE;
 *  Unless you do not want to use File Manager's built-in user
 *  management, this should be set to TRUE.
 */
$isauth = NULL;

/* Whether or not you allow PHP.
 * Notice: PHP allows you to do anything the account running PHP can do,
 * so this gives users full control over your website.
 * Leave FALSE unless you're absolutely sure you know what you're doing.
 */
$allow_php = FALSE;

/* Is the jw_flv_player addon installed?
 * Controls viewing FLV videos in-browser.
 * (It should be installed by default)
 */
$ext_jw_flv = NULL;

/* Is the CodeMirror addon installed?
 * Allows syntax highlighting.
 */
$ext_codemirror = NULL;

/* Is the PclZip addon installed?
 * Controls zipping/unzipping functionality.
 * (It should be installed by default)
 */
$ext_pclzip = NULL;

/* How files are edited. Can be "direct" or "ftp" or "fullftp".
 * direct -  Read and write locally on the server.
 * ftp -     Read locally from the server, write using FTP.
 *           Default. Most hosts don't allow direct PHP
 *           writing.
 * fullftp - Read and write using FTP. This diminishes many
 *           features and slows down File Manager significantly.
 *           Should be used if editing files on a remote server.
 */
$write_method = 'ftp';

/* FTP options
 */
$ftp_server = 'localhost';
$ftp_port = NULL;
$ftp_username = 'username';
$ftp_password = 'password';
$ftp_prepath = 'public_html/';
$ftp_ftps = false;
