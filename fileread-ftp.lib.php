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
  // Filecache
  //====================

$filecache = array();

  //====================
  // FTP-read Functions
  //====================

function fm_mklocal($file)
{
	global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
	@unlink('cache/down/temp.bin');
	if (@ftp_get($ftp,'cache/down/temp.bin',$ftp_prepath.$file,FTP_BINARY))
		return 'cache/down/temp.bin';
	return false;
}
function fm_mkalllocal($files)
{
	global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
	if (!count($files)) return $files;
	if (!is_dir('cache/down/local')) mkdir('cache/down/local');
	foreach ($files as $i => $file)
	{
		if (fm_isdir($file))
		{
			fm_recursivemklocal('cache/down/local/'.(endsWith($file,'/')?'':'/'), (endsWith($file,'/')?'':'/'), '');
			$files[$i] = 'cache/down/local/'.basename($file);
		}
		else
		{
			@ftp_get($ftp,'cache/down/temp.bin',$ftp_prepath.$file,FTP_BINARY);
			@chmod('cache/down/temp.bin',0600); // Don't want those silly hackers
			@rename('cache/down/temp.bin','cache/down/local/'.basename($file));
			$files[$i] = 'cache/down/local/'.basename($file);
		}
	}
	return $files;
}
function fm_recursivemklocal($localdir, $prefix, $file)
{
	global $ftp,$ftp_prepath;
	
	$cfiles = fm_fastgetfiles($prefix.$file);
	foreach ($cfiles as $i => $cfile)
	{
		if (endsWith($file,'/'))
		{
			fm_recursivemklocal($localdir.$cfile, $prefix, $file.$cfile);
		}
		else
		{
			@ftp_get($ftp,'cache/down/temp.bin',$ftp_prepath.$prefix.$file.$cfile,FTP_BINARY);
			@chmod('cache/down/temp.bin',0600); // Don't want those silly hackers
			@rename('cache/down/temp.bin','cache/down/local/'.$file.$cfile);
		}
	}
}

function fm_fastgetfiles($path)
{
	global $ftp,$ftp_prepath,$filecache;
	if (!endsWith($path,'/')) $path .= '/';
	if (is_array($filecache[$path]['list'])) return $filecache[$path]['list'];
	
	if (is_array($filecache[$path]['raw']))
	{
		$cfiles = $filecache[$path]['raw'];
	}
	else
	{
		if (!fm_ftpconnect()) return false;
		ftp_chdir($ftp, $ftp_prepath.$path);
		$cfiles = ftp_rawlist($ftp, '.');
		ftp_chdir($ftp, '/');
		$filecache[$path]['raw'] = $cfiles;
	}
	$files = array();
	foreach ($cfiles as $cfile)
	{
		$info = array();
		$cinfo = preg_split('/[\s]+/', $cfile, 9);
		if ($cinfo[8] != '.' && $cinfo[8] != '..' && $cinfo[0] !== 'total')
		{
			$files[] = $cinfo[8].($cinfo[0]{0}=='d'?'/':'');
		}
	}
	$filecache[$path]['list'] = $files;
	return $files;
}

function permconvert($str)
{
	return ''.(($str{1}!='-'?4:0)+($str{2}!='-'?2:0)+($str{3}!='-'?1:0)).(($str{4}!='-'?4:0)+($str{5}!='-'?2:0)+($str{6}!='-'?1:0)).(($str{7}!='-'?4:0)+($str{8}!='-'?2:0)+($str{9}!='-'?1:0));
}

function fm_getfiles($path)
{
	global $ftp,$ftp_prepath,$filecache;
	if (!endsWith($path,'/')) $path .= '/';
	if (is_array($filecache[$path]['raw']))
	{
		$cfiles = $filecache[$path]['raw'];
	}
	else
	{
		if (!fm_ftpconnect()) return false;
		ftp_chdir($ftp, $ftp_prepath.$path);
		$cfiles = ftp_rawlist($ftp, '.');
		ftp_chdir($ftp, '/');
		$filecache[$path]['raw'] = $cfiles;
	}
	$dirs = array();
	$files = array();
	foreach ($cfiles as $cfile)
	{
		$info = array();
		$cinfo = preg_split('/[\s]+/', $cinfo, 9);
		if ($cinfo[8] != '.' && $cinfo[8] != '..' && $cinfo[0] !== 'total')
		{
			if ($cinfo[0]{0}=='d'?'/':'')
				$dirs[] = $cinfo[8].'/';
			else
				$files[] = $cinfo[8];
		}
	}

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
	$filecache[$path]['list'] = $files;
	return $files;
}

function fm_getdfiles($path)
{
	global $ftp,$ftp_prepath;
	if (!endsWith($path,'/')) $path .= '/';
	
	$dolist = !is_array($filecache[$path]['list']);
	if (is_array($filecache[$path]['raw']))
	{
		$cfiles = $filecache[$path]['raw'];
	}
	else
	{
		if (!fm_ftpconnect()) return false;
		ftp_chdir($ftp, $ftp_prepath.$path);
		$cfiles = ftp_rawlist($ftp, '.');
		ftp_chdir($ftp, '/');
		$filecache[$path]['raw'] = $cfiles;
	}
	$dirs = array();
	$files = array();
	$flist = array();
	foreach ($cfiles as $cfile)
	{
		$info = array();
		$cinfo = preg_split('/[\s]+/', $cfile, 9);
		if ($cinfo[8] != '.' && $cinfo[8] != '..' && $cinfo[0] !== 'total')
		{
			$cfilen = $cinfo[8].($cinfo[0]{0}=='d'?'/':'');
			$ext = fext($cfilen);
			$info = array(
				'name' => $cfilen,
				'bname' => $cinfo[8],
				'id' => file2id($cfilen),
				'isdir' => ($cinfo[0]{0}=='d'?'/':''),
				'size' => intval($cinfo[4]),
				'tsize' => textfilesize(intval($cinfo[4])),
				'modified' => 0,
				'tmodified' => $cinfo[5].' '.$cinfo[6].' '.$cinfo[7],
				'tdmodified' => $cinfo[5].' '.$cinfo[6],
				'perms' => permconvert($cinfo[0]),
				'owner' => $cinfo[2],
				'group' => $cinfo[3],
				'ext' => $ext,
				'img' => fticon($ext),
				'type' => textfiletype($ext),
				'ft' => ft($ext),
				'writable' => (intval(substr(@permconvert($cinfo[0]),0,1))&2)?true:false,
				'isvimg' => false,
				'imgsize' => false
			);
			if ($info['isdir'])
				$dirs[] = $info;
			else
				$files[] = $info;
			if ($dolist)
				$flist[] = $cfilen;
		}
	}
	if (!is_array($filecache[$path]['list']))
		$filecache[$path]['list'] = $flist;

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

function fm_fileinfo($d)
{
	global $ftp,$ftp_prepath;
	if (!$d)
	{
		return array(
			'name' => '/',
			'bname' => '',
			'id' => file2id('/'),
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
	
	$up = upOne($ftp_prepath.$d);
	if (is_array($filecache[$up]['raw']))
	{
		$cfiles = $filecache[$up]['raw'];
	}
	else
	{
		if (!fm_ftpconnect()) return false;
		ftp_chdir($ftp, $up);
		$cfiles = ftp_rawlist($ftp, '.');
		ftp_chdir($ftp, '/');
		$filecache[$up]['raw'] = $cfiles;
	}
	$file = basename($d);
	foreach ($cfiles as $cfile)
	{
		$info = array();
		$cinfo = preg_split('/[\s]+/', $cfile, 9);
		if ($cinfo[0] !== 'total' && $cinfo[8] != '.' && $cinfo[8] != '..'
		    && $cinfo[8]==$file)
		{
			$cfilen = $cinfo[8].($cinfo[0]{0}=='d'?'/':'');
			$ext = fext($cfilen);
			$info = array(
				'name' => $cfilen,
				'bname' => $cinfo[8],
				'id' => file2id($cfilen),
				'isdir' => ($cinfo[0]{0}=='d'?'/':''),
				'size' => intval($cinfo[4]),
				'tsize' => textfilesize(intval($cinfo[4])),
				'modified' => 0,
				'tmodified' => $cinfo[5].' '.$cinfo[6].' '.$cinfo[7],
				'tdmodified' => $cinfo[5].' '.$cinfo[6],
				'perms' => permconvert($cinfo[0]),
				'owner' => $cinfo[2],
				'group' => $cinfo[3],
				'ext' => $ext,
				'img' => fticon($ext),
				'type' => textfiletype($ext),
				'ft' => ft($ext),
				'writable' => true,
				'isvimg' => false,
				'imgsize' => false
			);
			return $info;
		}
	}
	return false;
}

function fm_readdirect()
{
	return false;
}
function fm_geturl($file)
{
	if ($GLOBALS['ext_rewrite']) return 'echo/'.urlencode($file);
	return 'echo.php?d='.urlencode($file);
}
function fm_contents($file, $newline = false)
{
	$lfile = fm_mklocal($file);
	$res = file_get_contents($lfile);
	@unlink($lfile);
	if ($newline) $res = str_replace("\n",$newline,str_replace("\r","",$res));
	return $res;
}

  //====================
  // Existence Functions
  //====================

function fm_exists($path)
{
	if (!$path) return true;
	
	$cfiles = fm_fastgetfiles(upOne($ftp_prepath.$path));
	return in_array(basename($path),$cfiles) || in_array(basename($path).'/',$cfiles);
}
function fm_isfile($path)
{
	if (!$path) return false;
	
	$cfiles = fm_fastgetfiles(upOne($ftp_prepath.$path));
	return in_array(basename($path),$cfiles);
}
function fm_isdir($path)
{
	if (!$path) return true;
	
	$cfiles = fm_fastgetfiles(upOne($ftp_prepath.$path));
	return in_array(basename($path).'/',$cfiles);
}
