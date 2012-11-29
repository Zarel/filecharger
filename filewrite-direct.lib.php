<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 *
 * CORE FILE - DO NOT DELETE
 * 
 */

if ($ext_pclzip) include 'addons/pclzip.lib.php';

function fm_sanitize($file)
{
	global $prepath;
	if (!fm_isdir($file))
	{
		$newfile = $file; aphp($newfile);
		if ($newfile !== $file) fm_rename($file, $newfile, $true) or fm_delete($file);
		return true;
	}
	if (substr('/'.$file,-1)!='/') $file .= '/';
	if (!($handle = @opendir($prepath.$file))) return false;
	while (false !== ($cfile = readdir($handle)))
	{
		if ($cfile == '.' || $cfile == '..') continue;
		fm_sanitize($file.$cfile);
	}
	return true;
}

// From PHP Manual: copy()
// User-Contributed Notes: bobbfwed at comcast dot net on 02-Feb-2006 11:06
// At http://php.net/manual/en/function.copy.php#61408
function fm_copy($source, $dest, $overwrite = false)
{
	global $prepath;
	$success = true;
	if (!fm_isdir($source) || !fm_isdir($dest))
	{
		aphp($dest);
		$dest = fm_prepareoverwrite($dest, $overwrite);
		if (!$dest) return false;
		return @copy($prepath.$source,$prepath.$dest);
	}
	if (!($handle = @opendir($prepath.$source))) return false;
	if (substr('/'.$source,-1)!='/') $source .= '/';
	if (substr('/'.$dest,-1)!='/') $dest .= '/';
	while (false !== ($file = readdir($handle)))
	{
		if ($file == '.' || $file == '..') continue;
		if (fm_isdir($path) && !fm_isdir($dest.$file))
				if (!fm_mkdir($dest.$file)) $success = false; // make subdirectory before subdirectory is copied
		if (!fm_copy($source.$file,$dest.$file)) $success = false;
	}
	closedir($handle);
	return $success;
}

function fm_editfile($fname, $data)
{
	global $prepath;
	aphp($fname);
	$file = @fopen($prepath.$fname,'w');
	if ($file === false) return false;
	$works = @fwrite($file,$data);
	@fclose($file);
	if ($works !== false) return true;
	return false;
}

function fm_upload($file,$dest,$overwrite=false)
{
	global $prepath;
	aphp($dest);
	$dest = fm_prepareoverwrite($dest, $overwrite);
	if (!$dest) return false;
	fm_cachesanitize('up/'.$file);
    return @rename('cache/up/'.$file,$prepath.$dest); 
}
function fm_urlupload($url,$dest='',$overwrite=false)
{
    global $prepath;
    if (substr($url,0,7)!=='http://') 'http://'.$url;
    if (substr('/'.$dest,-1)=='/') $dest .= basename($url);

	aphp($dest);
	$dest = fm_prepareoverwrite($dest, $overwrite);
	if (!$dest) return false;
	return @copy($url,$prepath.$dest);
} 
function fm_move($old,$new,$overwrite=false) { return fm_rename($old,$new,$overwrite=false); }
function fm_rename($old,$new,$overwrite=false)
{
    global $prepath;
    aphp($new);
	if (fm_isdir($old)) fm_sanitize($old);
	$new = fm_prepareoverwrite($new, $overwrite);
	if (!$new) return false;
    return @rename($prepath.$old,$prepath.$new);
} 
function fm_delete($file)
{
	global $prepath;
	if (fm_isdir($file))
	{
		if (substr('/'.$file,-1)!=='/') $file .= '/';
		if (!($handle = @opendir($prepath.$file))) return false;
		while (false !== ($item = readdir($handle)))
		{
			if ($item != '.' && $item != '..' && !fm_fdelete($prepath.$file.$item))
				return false;
		}
		closedir($handle);
		return @rmdir($prepath.$file);
	}
	return @unlink($prepath.$file);
}
function fm_mkdir($file,$overwrite=false)
{
	global $prepath;
	if (fm_exists($file)) return $overwrite?true:false;
	return @mkdir($prepath.$file);
}
function fm_mkfile($file,$overwrite=false,$content='')
{
	global $prepath;
	aphp($file);
	$file = fm_prepareoverwrite($file, $overwrite);
	if (!$file) return false;
	$handle = @fopen($prepath.$file,'x');
	if ($content && $handle) @fwrite($handle,$content);
	return @fclose($handle);
}
function fm_chmod($file,$perms)
{
	global $prepath;
	$ofile = $file; aphp($file);
	if ($ofile != $file) fm_copy($ofile,$file);
	
	return @chmod($prepath.$source,octdec($perms));
}
function fm_extractzip($file,$dest=FALSE,$overwrite=FALSE)
{
	global $prepath;
	if (!$GLOBALS['ext_pclzip']) return false;
	if (is_dir('cache/up/extracted/')) fm_clearcache('up/extracted/');
	@mkdir('cache/up/extracted/');
	$archive = new PclZip($prepath.$file);
	$res = $archive->extract(PCLZIP_OPT_PATH, 'cache/up/extracted/');
	if ($res == 0) return FALSE;
	$res = fm_upload('extracted',$dest,$overwrite);
	fm_clearcache('up/extracted/');
	return $res;
}
function fm_compresszip($files, $dest=FALSE, $overwrite=FALSE)
{
	if (file_exists($dest) && !$overwrite || !count($files)) return FALSE;
	else fm_delete($dest);
	@unlink('cache/up/temp.zip');
	$lfiles = fm_mkalllocal($files);
	$archive = new PclZip('cache/up/temp.zip');
	$archive->create($lfiles, PCLZIP_OPT_REMOVE_PATH, upOne($lfiles[0]));
	fm_recachedir('down/local/');
	$res = fm_upload('temp.zip',$dest,$overwrite);
	@unlink('cache/up/temp.zip');
	return $res;
}

function fm_iswritable($file)
{
	global $prepath;
	return is_writable($prepath.$file);
}
function fm_ftpconnect() {}
function fm_close() {}
?>