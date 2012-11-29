<?php

/*
 *
 * Filecharger FTP Write Library
 * by Zarel of Novawave
 *
 * Module - do not delete unless you don't need FTP access functionality
 * and you know what you're doing.
 * 
 */

if ($ext_pclzip) include 'addons/pclzip.lib.php';

$ftp = false;

function fm_ftpconnect()
{
  global $ftp, $ftp_server, $ftp_port, $ftp_username, $ftp_password, $ftp_passive, $ftp_ftps;
  if ($ftp) return true;
  if ($ftp_ftps)
  {
    if (!($ftp = @ftp_ssl_connect($ftp_server, $ftp_port))) return false;
  }
  else
  {
    if (!($ftp = @ftp_connect($ftp_server, $ftp_port))) return false;
  }
  if (!@ftp_login($ftp, $ftp_username, $ftp_password))
  {
    @ftp_close($ftp);
    return ($ftp = false);
  }
  if ($ftp_passive) @ftp_pasv();
  return true;
}

function fm_sanitize($file)
{
  if (!fm_isdir($file))
  {
    $newfile = $file; aphp($newfile);
    if ($newfile !== $file) fm_rename($file, $newfile, $true) or fm_delete($file);
    return true;
  }
  if (substr('/'.$file,-1)!='/') $file .= '/';
  $cfiles = fm_fastgetfiles($file);
  foreach ($cfiles as $cfile)
  {
    if ($cfile == '.' || $cfile == '..') continue;
    fm_sanitize($file.$cfile);
  }
  return true;
}

function fm_copy($source, $dest, $overwrite = false)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  
  $success = true;
  if (!fm_isdir($source))
  {
    aphp($dest);
    if (fm_exists($dest) && !$overwrite) return false;
    if (($local = fm_mklocal($source))===false) return false;
    $dest = fm_prepareoverwrite($dest, $overwrite);
    if (!$dest) return false;
    return @ftp_put($ftp,$ftp_prepath.$dest,$local,FTP_BINARY);
  }
  
  // A hack, but this is the only way FTP will let us do it.
  
  if (substr('/'.$source,-1)!='/') $source .= '/'; // enforce trailing slash
  if (substr('/'.$dest,-1)!='/') $dest .= '/'; // enforce trailing slash
  if (!fm_exists($dest) && !fm_mkdir($dest)) return false;
  $csources = fm_fastgetfiles($source);
  foreach ($csources as $csource)
  {
    if ($csource == '.' || $csource == '..') continue;
    if (!fm_copy($source.$csource,$dest.$csource,$overwrite)) $success = false;
  }
  return $success;
}

function fm_editfile($source, $data)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  aphp($source);
  
  $fhandle = @fopen('cache/temp.bin','w');
  if ($fhandle === false) return false;
  @fwrite($fhandle,$data);
  @fclose($fhandle);
  return @ftp_put($ftp,$ftp_prepath.$source,'cache/temp.bin',FTP_BINARY);
}

function fm_upload($source,$dest,$overwrite=false)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  
  if (!is_dir('cache/up/'.$source))
  {
    aphp($dest);
    $dest = fm_prepareoverwrite($dest, $overwrite);
    if (!$dest) return false;
    $success = @ftp_put($ftp,$ftp_prepath.$dest,'cache/up/'.$source,FTP_BINARY);
    if ($success) @unlink('cache/up/'.$source);
    return $success;
  }
  
  // A hack, but this is the only way FTP will let us do it.
  $success = true;
  if (substr('/'.$source,-1)!='/') $source .= '/'; // enforce trailing slash
  if (substr('/'.$dest,-1)!='/') $dest .= '/'; // enforce trailing slash
  if (!fm_exists($dest) && !fm_mkdir($dest)) return false;
  if (!($handle = @opendir('cache/up/'.$source))) return false;
  while (false !== ($csource = readdir($handle)))
  {
    if ($csource == '.' || $csource == '..') continue;
    if (!fm_upload($source.$csource,$dest.$csource,$overwrite)) $success = false;
  }
  @closedir($handle);
  @rmdir('cache/up/'.$source);
  return $success;
}



function fm_urlupload($url,$dest='',$overwrite=false)
{
    global $prepath;
    if (substr($url,0,7)!=='http://') 'http://'.$url;
    if (substr('/'.$dest,-1)=='/') $dest .= basename($url);

  aphp($dest);
  if (!$dest) return false;
  if (!@copy($url,'cache/up/temp.bin')) return false;
    return fm_upload('temp.bin',$dest,$overwrite);
}
function fm_move($old,$new,$overwrite=false) { return fm_rename($old,$new,$overwrite=false); }
function fm_rename($old,$new,$overwrite=false) // or move
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  aphp($new);
  
  $new = fm_prepareoverwrite($new, $overwrite);
  if (!$new) return false;
  return @ftp_rename($ftp,$ftp_prepath.$old,$ftp_prepath.$new);
}
function fm_delete($source)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  if (!fm_isdir($source))
    return @ftp_delete($ftp, $ftp_prepath.$source);
  if (substr('/'.$source,-1)!='/') $source .= '/'; // enforce trailing slash
  $csources = fm_fastgetfiles($source);
  foreach ($csources as $csource)
  {
    if ($csource == '.' || $csource == '..') continue 1;
    fm_delete($source.$csource);
  }
  return @ftp_rmdir($ftp, substr($ftp_prepath.$source,0,-1));
}
function fm_mkdir($source,$overwrite=false)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  aphp($source);
  
  if (fm_exists($source)) return $overwrite?true:false;
  return @ftp_mkdir($ftp,$ftp_prepath.$source);
}
function fm_mkfile($dest,$overwrite=false,$content='')
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  aphp($dest);
  
  $dest = fm_prepareoverwrite($dest, $overwrite);
  if (!$dest) return false;
  $fhandle = @fopen('cache/temp.bin','w');
  if ($fhandle === false) return false;
  if ($content) @fwrite($fhandle,$content);
  @fclose($fhandle);
  return @ftp_put($ftp,$ftp_prepath.$dest,'cache/temp.bin',FTP_BINARY);
}

function fm_chmod($file,$perms)
{
  global $ftp,$ftp_prepath; if (!fm_ftpconnect()) return false;
  $ofile = $file; aphp($file);
  if ($ofile !== $file) fm_copy($ofile,$file);
  
  return @ftp_chmod($ftp,octdec($perms),$ftp_prepath.$file);
}
function fm_iswritable($source)
{
  if (startsWith(cleanPath($source),str_repeat('../',1+substr_count($GLOBALS['ftp_prepath'],'/')))) return FALSE;
  //$fileinfo = fm_fileinfo($source);
  //return (intval(substr(@($fileinfo['perms']),0,1))&2)?TRUE:FALSE;
  return true;
}

function fm_extractzip($file,$dest=FALSE,$overwrite=FALSE)
{
  if (!$GLOBALS['ext_pclzip']) return false;
  $archive = new PclZip(fm_mklocal($file));
  if (is_dir('cache/up/extracted/')) fm_clearcache('up/extracted/');
  @mkdir('cache/up/extracted/');
  $res = $archive->extract(PCLZIP_OPT_PATH, 'cache/up/extracted/');
  if ($res == 0) return FALSE;
  $res = fm_upload('extracted/',$dest,$overwrite);
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

function fm_close()
{
  global $ftp;
  if ($ftp) ftp_close($ftp);
}

?>