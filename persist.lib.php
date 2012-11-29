<?php

/********************************************************
 ** Persistence library                                **
 ** Version 1.0 RC9 - Updated 2008 Dec 7               **
 ** By Zarel - http://en.wikipedia.org/wiki/User:Zarel **
 ** released under public domain                       **
 ********************************************************
 *
 * Meant for PHP 4.0.4 and up. I've tried to make it work in
 * PHP 3, but I've never tested it, so use it in PHP 3 at
 * your own risk.
 *
 **[ Description ]***************************************
 *
 * Creates a new global variable called $_PERSIST, stored in
 * persist.inc.php . If you call persist_update(),
 * persist.inc.php will be updated to store the new
 * value of $_PERSIST.
 *
 * Since you'll probably want to store more than one variable, use
 * $_PERSIST as an array.
 *
 * Make sure PHP has permissions to edit persist.inc.php (If all else
 * fails, CHMOD to 777).
 *
 * The location and name of persist.inc.php, as well as the name of
 * $_PERSIST, can be set either before persist.lib.php is called or
 * below.
 *
 * Example code:
 * <?php
 *  $persist_path = 'includes/storage.inc.php';
 *  $persist_name = 'STORAGE';
 *  include 'persist.lib.php';
 *
 *  echo 'You are visitor #'.(++$STORAGE['hitcounter']).'.';
 *  persist_update();
 * ?>
 *
 * Alternate example code - does the same thing:
 * <?php
 *  include 'persist.lib.php';
 *  include 'includes/storage.inc.php';
 *
 *  echo 'You are visitor #'.(++$STORAGE['hitcounter']).'.';
 *  persist_update('STORAGE', 'includes/storage.inc.php');
 * ?>
 *
 * If a script does not need to update the value of $_PERSIST, it can
 * simply include persist.inc.php instead of persist.lib.php .
 *
 * The first example is useful if you are using one persist variable
 * in one file, while the second is useful if you are using multiple
 * persist variables in multiple files.
 *
 **[ Function reference ]********************************
 *
 *  persist_update(name, path)
 *   Updates stored $_PERSIST.
 *   Both parameters are optional.
 *   name - the name of the variable.
 *          Default: $persist_name if it is set,
 *                   '_PERSIST' otherwise
 *   path - the path to the file storing the variable.
 *          Default: $persist_path if it is set,
 *                   'persist.inc.php' in current directory
 *                   otherwise
 *
 ********************************************************/

if (!isset($persist_name) || !$persist_name)
// $persist_name is the name of the variable that is persisted when persist_update()
// is called without a "name" parameter.
// Do not add $ before it; it will be added automatically.
// Defaults to _PERSIST (Which would be the variable $_PERSIST)
	$persist_name = '_PERSIST';

if (!isset($persist_path) || !$persist_path)
// $persist_path is the URL of whatever file contains the data you want to
// persist, when persist_update() is called without a "path" parameter.
// Defaults to persist.inc.php in the directory containing
// persist.lib.php (as opposed to the directory the script is running
// in).
// Remember to CHMOD this file to 666 if necessary.
	$persist_path = substr(__FILE__,0,strrpos(__FILE__,'/')+1).strtolower(substr($persist_name,0,1)==='_'?substr($persist_name,1):$persist_name).'.inc.php';

@include_once $persist_path;

function persist_update($name='', $path='')
// Updates $_PERSIST
{
	global $persist_name,$persist_path;
	if (!$name) $name = $persist_name;
	if (!$path && $name == $persist_name) $path = $persist_path;
	if (!$path || !file_exists($path)) $path = substr(__FILE__,0,strrpos(__FILE__,'/')+1).strtolower(substr($name,0,1)==='_'?substr($name,1):$name).'.inc.php';
	// Open persist.inc.php and start editing it
	if (!is_writable($path)) return false;
	$res = fopen($path,"w");
	if (!$res) return false;
	fwrite($res,"<?php\n\$".$name." = ".persist_tophp($GLOBALS[$name]).";\n");
	fclose($res);
	return true;
}

function persist_tophp($var, $pre='')
// Recursive function to create array
// It really needs a better name
{
	return var_export($var, true);
}

if (isset($_REQUEST['forceupdate'])) if (persist_update()) echo 'SUCCESS'; else echo 'ERROR';

?>