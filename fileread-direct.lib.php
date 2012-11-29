<?php

/*
 *
 * Filecharger Direct Read Library
 * by Zarel of Novawave
 *
 * CORE FILE - DO NOT DELETE
 * 
 */


  //====================
  // Direct-read Functions
  //====================

function fm_mklocal($file)
{
	global $prepath;
	return $prepath.$file;
}
function fm_mkalllocal($files)
{
	global $prepath;
	if (!count($files)) return $files;
	$dir = upOne($files[0]);
	$nfiles = array();
	foreach ($files as $i => $file)
	{
		if (upOne($file) != $dir)
		{
			if (!is_dir('cache/down/local')) mkdir('cache/down/local');
			foreach ($files as $i => $file)
			{
				@copy($prepath.$file,'cache/down/local/'.basename($file));
				$files[$i] = 'cache/down/local/'.basename($file);
			}
			return $files;
		}
		else $nfiles[$i] = $prepath.$file;
	}
	return $nfiles;
}

  //====================
  // File list Functions
  //====================

function fm_fastgetfiles($path)
{
	$dh = @opendir($GLOBALS['prepath'].$path);
	if ($dh === FALSE) return FALSE;
	$files = array();
	while (false !== ($file = @readdir($dh)))
	{
		if ($file == '.' || $file == '..') continue; // Skip files not actually in the directory
		if (fm_isdir($path.$file) && substr('/'.$path.$file,-1)!='/') $file .= '/'; // directory?
		$files[] = $file;
	}
	@closedir($dh);
	return $files;
}

function fm_getfiles($path)
{
	$dh = @opendir($GLOBALS['prepath'].$path);
	if ($dh === FALSE) return FALSE;
	$dirs = array();
	$files = array();
	while (false !== ($file = @readdir($dh)))
	{
		if ($file == '.' || $file == '..') continue; // Skip files not actually in the directory
		if (fm_isdir($path.$file)) // directory?
			$dirs[] = $file.'/';
		else if (fm_isfile($path.$file))
			$files[] = $file;
	}
	@closedir($dh);

	if ($cnfs == '1')
	{
		$files = array_merge($dirs,$files); //merge
		$dirs = array();
	}

	if ($carr !== false)
	{
		if ($files) natcasesort($files);
		if ($dirs) natcasesort($dirs);
	}

	$files = array_merge($dirs,$files);  //merge
	return $files;
}

function fm_getdfiles($path)
{
	$files = fm_getfiles($path);
	if ($files === FALSE) return FALSE;
	$dfiles = array();
	foreach ($files as $file)
		$dfiles[] = fm_fileinfo($path.$file);
	return $dfiles;
}

function fm_fileinfo($d)
{
	$path = $GLOBALS['prepath'].$d;
	$file = filefrompath($path);
	if (!fm_exists($d)) return FALSE;
	$size = filesize($path);
	$modified = filemtime($path);
	$ext = fext($file);
	return array(
		// The name of the file (with slashes) - 'example.gif'
		'name' => $file,
		// The base name of the file (without slashes) - 'example.gif'
		'bname' => (endsWith($file,'/')?substr($file,0,-1):$file),
		// The ID of the file - 'file_example.gif'
		'id' => file2id($file),
		// Is it a directory? - FALSE
		'isdir' => (filetype($path) == 'dir'),
		// File size - 1024
		'size' => $size,
		// Text file size - '1 KB'
		'tsize' => textfilesize($size),
		// Modified (Unix timestamp) - 0
		'modified' => $modified,
		// Modified - 'Jan 1, 1970 12:00:00 AM'
		'tmodified' => date("M j, Y g:i:s A",$modified),
		// Date Modified - 'Jan 1, 1970'
		'tdmodified' => date("M j, Y",$modified),
		// Perms - 777
		'perms' => substr(sprintf('%o', fileperms($path)), -3),
		// Owner - user
		'owner' => fileowner($path),
		// Group - group
		'group' => filegroup($path),
		// Extension - 'gif'
		'ext' => $ext,
		// File icon - 'gif'
		'img' => fticon($ext),
		// File type - 'GIF Image'
		'type' => textfiletype($ext),
		// File type ID - '0'
		'ft' => ft($ext),
		// Is it writable - TRUE
		'writable' => fm_iswritable($d),
		// Is it a viewable image (an image that can be displayed in a browser)? - TRUE
		'isvimg' => isvimg($ext),
		// Image size data - {$width, $height, 1, 'height="$height" width="$width"'}
		// ['imgsize'][2]:  1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
		'imgsize' => ((isvimg($ext)||$ext=='psd'||$ext=='bmp') && $size<=5242880)?@getimagesize($path):FALSE
	);
}

function fm_readdirect()
{
	return true;
}
function fm_geturl($d)
{
	global $preurl;
	return $preurl.$d;
}
function fm_contents($file, $newline = false)
{
	global $prepath;
	$res = file_get_contents($prepath.$file);
	if ($newline) $res = str_replace("\n",$newline,str_replace("\r","",$res));
	return $res;
}

function fm_exists($path)
{
	return @file_exists($GLOBALS['prepath'].$path);
}
function fm_isfile($path)
{
	return @is_file($GLOBALS['prepath'].$path);
}
function fm_isdir($path)
{
	return @is_dir($GLOBALS['prepath'].$path);
}
