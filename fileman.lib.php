<?php

/*
 *
 * Filecharger Core Library
 * by Zarel of Novawave
 *
 * CORE FILE - DO NOT DELETE
 * 
 */


$fm_version = '1.0';
$fm_build = 50;
$fm_thisyear = '2011';

@set_magic_quotes_runtime(0);
function stripslashes_deep(&$value)
{  if (is_array($value) || is_string($value)) $value = is_array($value)?array_map('stripslashes_deep', $value):stripslashes($value); return $value; }
if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off")) )
{
    stripslashes_deep($_REQUEST);
    stripslashes_deep($_GET);
    stripslashes_deep($_POST);
    stripslashes_deep($_COOKIE);
    stripslashes_deep($_SESSION);
}
if (isset($_REQUEST['FM_VERSION'])) die($fm_version.'//'.$fm_build);

@include_once 'config.inc.php';
@include_once 'persist.lib.php';
include_once 'admin.lib.php';

  //====================
  // Routines
  //====================

function jsesc($f) { return addcslashes($f,'\'\\'); }

function startsWith($string, $prefix)
{ // Similar to Java function startsWith()
  return substr($string, 0, strlen($prefix)) == $prefix;
}

function endsWith($string, $suffix)
{ // Similar to Java function startsWith()
  return substr($string, -strlen($suffix)) == $suffix;
}

function cleanPath($path)
// From PHP Manual, User-Contributed Notes, [ bart at mediawave dot nl / 21-Sep-2005 12:31 ]
{ // Changes foo/../bar/ to bar/ (resolves references to ./ and ../)
  $result = array();
  $pathA = explode('/', $path);
  if (!$pathA[0]&&$path)
    $result[] = '';
  foreach ($pathA as $key => $dir)
  {
    if ($dir == '' && $key != count($pathA)-1)
    {
      $result = array('');
    }
    else if ($dir == '..')
    {
      if (end($result) == '..')
        $result[] = '..';
      else if (!array_pop($result))
        $result[] = '..';
    }
    else if ($dir && $dir != '.')
    {
      $result[] = $dir;
    }
  }
  if (!end($pathA))
    $result[] = '';
  return implode('/', $result);
}

function upOne($path,$nohd=true)
{
  // Note: Path is presumed to be clean. If it isn't, call upOne(cleanPath($path));
  
  return ($pos=strrpos(substr($path,0,-1),'/'))===FALSE?($nohd?'':'./'):substr($path,0,$pos+1);
}
function reldate($time)
{
  if (!$time) return 'never';
  $suf = 'ago';
  $rtime = ($ctime=time()) - $time;
  if ($rtime < 0) { $suf = 'from now'; $rtime = -$rtime; }
  if ($rtime < 60) return $rtime.' second'.($rtime==1?'':'s').' '.$suf;
  $rtime = intval($rtime/60);
  if ($rtime < 60) return $rtime.' minute'.($rtime==1?'':'s').' '.$suf;
  if ($rtime < 120) return 'an hour and '.($rtime-60).' minute'.($rtime==61?'':'s').' '.$suf;
  //$rtime = intval($rtime/60);
  //echo '[rtime '.$rtime.']';
  if (intval($time/86400)==intval($ctime/86400)) // same day
    return intval($rtime/60).($rtime%60>=30?' and a half':'').' hour'.($rtime<90?'':'s').' '.$suf;
  if (($daysago=intval($ctime/86400)-intval($time/86400))==1)
    return 'yesterday';
  if ($daysago==-1)
    return 'tomorrow';
  if ($daysago<=3)
    return $daysago.' days ago';
  $ctimea=getdate($ctime);$timea=getdate($time);
  if ($ctimea['year']==$timea['year'])
    return date('M j',$time);
  return date('M j, Y',$time);
}
function onreldate($time)
{
  if (!$time) return 'never';
  $suf = 'ago';
  $rtime = ($ctime=time()) - $time;
  if ($rtime < 0) { $suf = 'from now'; $rtime = -$rtime; }
  if ($rtime < 60) return $rtime.' second'.($rtime==1?'':'s').' '.$suf;
  $rtime = intval($rtime/60);
  if ($rtime < 60) return $rtime.' minute'.($rtime==1?'':'s').' '.$suf;
  if ($rtime < 120) return 'an hour and '.($rtime-60).' minute'.($rtime==61?'':'s').' '.$suf;
  //$rtime = intval($rtime/60);
  //echo '[rtime '.$rtime.']';
  if (intval($time/86400)==intval($ctime/86400)) // same day
    return intval($rtime/60).($rtime%60>=30?' and a half':'').' hour'.($rtime<90?'':'s').' '.$suf;
  if (($daysago=intval($ctime/86400)-intval($time/86400))==1)
    return 'yesterday';
  if ($daysago==-1)
    return 'tomorrow';
  if ($daysago<=3)
    return $daysago.' days ago';
  $ctimea=getdate($ctime);$timea=getdate($time);
  if ($ctimea['year']==$timea['year'])
    return 'on '.date('M j',$time);
  return 'on '.date('M j, Y',$time);
}

  //====================
  // Preparse
  //====================

// First, let's prelim parse GET strings with config, in case someone forgot to do it.
$d = cleanPath($_GET['d']);
$postsub = substr($d,strlen($presub));
$file = '';
$fullurl = $preurl.$presub.$postsub.$file;

if ($presub && $allow_php==='auto') $allow_php = $false;
$allow_php = ($allow_php?TRUE:FALSE);

  //====================
  // Functions
  //====================

// Read functions
  
if ($write_method == 'ftp' || $write_method == 'fullftp')
{
  include_once 'filewrite-ftp.lib.php';
}
else if ($write_method == 'direct')
{
  include_once 'filewrite-direct.lib.php';
}

// Fileman routines

function fm_urlencode($str)
{
  return str_replace('%2F','/',urlencode($str));
}

// Cache-only functions

function fm_clearcache($source='') // Partial clearcache
{
  //global $ftp,$ftp_prepath,$fmurl,$preurl;
  $source = cleanPath($source);
  if (startsWith($source,'../')) return FALSE;
  if (is_dir('cache/'.$source))
  {
    if ($source && substr($source,-1)!='/') $source .= '/';
    $dh = @opendir('cache/'.$source);
    if ($dh) while (false !== ($file = @readdir($dh)))
    {
      if ($file == '.' || $file == '..') continue;
      fm_clearcache($source.$file);
      if (is_dir('cache/'.$source.$file)) // directory?
        @rmdir('cache/'.$source.$file);
    }
    @closedir($dh);
  }
  else @unlink('cache/'.$source);
  if ($source=='' && !is_dir('cache/up/'))
  {
    @mkdir('cache/up/');
    @chmod('cache/up/',0777);
  }
  if ($source=='' && !is_dir('cache/down/'))
  {
    @mkdir('cache/down/');
    @chmod('cache/down/',0777);
  }
  if (!is_file('cache/data-backup.inc.php'))
  {
    @copy('persist.inc.php','cache/data-backup.inc.php');
    @chmod('cache/data-backup.inc.php',0777);
  }
  if (!is_file('cache/config-backup.inc.php'))
  {
    @copy('config.inc.php','cache/config-backup.inc.php');
    @chmod('cache/config-backup.inc.php',0777);
  }
  return TRUE;
}
function fm_cachesanitize($file)
{
  global $prepath;
  if (!is_dir('cache/'.$file))
  {
    $newfile = $file; aphp($newfile);
    if ($newfile !== $file)
    {
      if (file_exists('cache/'.$newfile)) @unlink('cache/');
      @rename('cache/'.$file, 'cache/'.$newfile) or @unlink('cache/'.$file);
    }
    return true;
  }
  if (!endsWith($file,'/')) $file .= '/';
  if (!($handle = @opendir('cache/'.$file))) return false;
  while (false !== ($cfile = readdir($handle)))
  {
    if ($cfile == '.' || $cfile == '..') continue;
    fm_sanitize($file.$cfile);
  }
  return true;
}

  //====================
  // Direct-read Functions
  //====================

if ($write_method == 'fullftp')
  include_once 'fileread-ftp.lib.php';
else
  include_once 'fileread-direct.lib.php';

  //====================
  // Misc Functions
  //====================

function filefrompath($path)
{
  if (!endsWith($path,'/'))
  {
    if (strrpos($path,'/') === false) return $path;
    return substr($path,strrpos($path,'/')+1);
  }
  $path = substr($path,0,-1);
  if (strrpos($path,'/') === false) return $path.'/';
    return substr($path,strrpos($path,'/')+1).'/';
}

function file2id($file)
{
  $file = str_replace(array('_',' ','\'','"','.','\\','/',',','%'),array('_u','_0','_a','_q','_d','_b','_s','_c','_p'),$file);
  return ((substr($file,-2) == '_s')?'fold_'.substr($file,0,-2):'file_'.$file);
}

function id2file($id)
{
  $file = substr($id,5).'/';
  if (substr($id,0,5)=='file_') $file = substr($id,5);
  $file = str_replace(array('_0','_a','_q','_d','_b','_s','_c','_p','_u'),array(' ','\'','"','.','\\','/',',','%','_'),$file);
  return $file;
}

function ids2files($ids)
{
  $ids = explode(',',$ids);
  $files = array();
  foreach ($ids as $id)
    if ($id) $files[] = id2file($id);
  return $files;
}

function ft($fext)
{
  if (strpos($fext,'htm')!==false && $fext != 'xhtml' && $fext != 'html' && $fext != 'htm' && $fext != 'html4' && $fext != 'html5')
    return 3; // server-side scripted HTML [plaintext]
  if (substr($fext,0,3)=='php' && $fext != 'phps')
    return 3; // I would not have to do this if PHP were secure
  switch ($fext)
  {
  case '/':
    return 1; // Directory
  case 'htm':
  case 'html':
    return 2; // HTML [plaintext]
  case 'php': case 'php1': case 'php2': case 'php3': case 'php4':
  case 'php5': case 'php6': case 'php7': case 'php8': case 'phtml':
  case 'inc':
  case 'asp':
  case 'cgi':
  case 'jsp':
  case 'pl':
  case 'py':
  case 'shtml': case 'ssi':
    return 3; // server-side scripted HTML [plaintext]
  case 'txt':
  case 'css':
  case 'js':
  case 'ini':
  case 'log':
  case 'phps':
  case 'h':
  case 'c':
  case 'cpp':
  case 'java':
  case 'htaccess':
    return 4; // Other plaintext
  case 'jpg':
  case 'jpeg':
  case 'gif':
  case 'png':
    return 5; // Browser-displayable images
  case 'bmp':
    return 6; // Other images
  case 'zip':
    return 7; // ZIP archives
  default:
    return 0; // Other
  }
}

function isadmin()
{
	return @$GLOBALS['user']['priv']>=127;
}

function aphp(&$file) // Sanitizes filename for writes
{
  if (isadmin()) return;
  if (!$GLOBALS['allow_php'] && ft(fext($file))==3) $file .= (substr($file,-4)=='.php'?'s':'.txt');
  if (substr(basename($file),0,1)=='.') $file = substr($file,0,-strlen(basename($file))).'new'.basename($file);

if (!$GLOBALS['allow_php']) {
  // Turns out Apache has a rather insecure file handler system, so
  $splitfile = explode('.',preg_replace('/[^A-Za-z0-9.]+/','',strtr($file,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')));
  if (!endsWith($file, '.txt') && !endsWith($file, '.phps') && array_intersect($splitfile, array('php','inc','asp','cgi','jsp','pl','py','shtml','ssi','phtml','php1','php2','php3','php4','php5','php6','php7','php8')))
    $file .= '.txt'; }
}
function fm_canaccess($file)
{
  return startsWith(cleanPath($file), $GLOBALS['presub']);
}
function fastcanaccess($file) // Automatically reject anything not in current directory
{
  if (substr($file,-1)=='/') $file = substr($file,0,-1);
  return strpos($file,'/')===false;
}
function addbeforeext($file, $text)
{
  $pos = strrpos($file,'.');
  $spos = strrpos($file,'/');
  if ($pos && $pos <= $spos+1) $pos = 0;
  if (!$pos) $pos = strlen($file);
  return substr($file,0,$pos).$text.substr($file,$pos);
}
function fm_unusedfilename($file)
{
  $ofile = $file;
  $i = 1;
  while (fm_exists($file))
  {
    $file = addbeforeext($ofile, ' ('.($i).')');
    $i++;
  }
  return $file;
}
function fm_prepareoverwrite($file, $overwrite=false, $forcedelete=true)
{
  if (!fm_isfile($file))
    return $file;
  if (!$overwrite)
    return false;
  if ($overwrite === 'rename')
    return fm_unusedfilename($file);
  if ($forcedelete && !fm_delete($dest))
    return false;
  return $file;
}

function isvimg($fext)
{
  switch ($fext)
  {
  case 'gif':
  case 'jpg':
  case 'jpeg':
  case 'jpe':

  case 'png':
    return TRUE;
  }
  return FALSE;
}

function textfilesize($fsize)
{
  if ($fsize >= 1048576) // Size in megabytes
    return round($fsize / 1024 / 1024, 2).' MB';
  if ($fsize >= 1024) // Size in kilobytes
    return round($fsize / 1024, 2).' KB';
  if ($fsize != 1) // Size in bytes
    return $fsize.' bytes';
  return '1 byte'; // Self-explanatory
}

function fext($fname)
{
  if (!$fname || substr($fname, -1) == '/' || substr($fname, -1) == '\\')
    return '/';
  $fname = basename($fname);
  $fname = preg_replace('/[^A-Za-z0-9.]+/','',strtr($fname,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'));
  while (substr($fname, -1) == '.') $fname = substr($fname, 0, -1);
  $fext = substr(strrchr($fname, '.'), 1);
  return $fext;
}

function fticon($fext)
{
  switch ($fext)
  {
  case 'doc': case 'ps': case 'gif': case 'dir':
  case 'html': case 'wav': case 'php': case 'pdf':
  case 'ppt': case 'mov': case 'ram': case 'rtf':
  case 'xml': case 'sit': case 'txt': case 'wmv':
  case 'bmp': case 'psd':
    return $fext;
  case 'phtml': case 'php1': case 'php2': case 'php3':
  case 'php4': case 'php5': case 'php6': case 'phps':
    return 'php';
  case 'c': case 'h': case 'cpp': case 'java':
    return 'txt';
  case 'zip': case 'rar': case '7z': case 'gz': case 'tar':
    return 'zip';
  case 'ini': case 'css': case 'log': case 'htaccess': case 'diff': case 'patch':
    return 'ini';
  case 'js':
    return 'xml';
  case 'htm':
    return 'html';
  case 'pps': case 'pptx':
    return 'ppt';
  case 'docx':
    return 'doc';
  case 'mp3':
  case 'wav':
  case 'wma':
    return 'wav';
  case 'hqx':
    return 'sit';
  case 'ico':
    return 'gif';
  case 'jpg': case 'jpe': case 'jpeg':
    return 'jpg';
  case 'png':
    return 'gif';
  case 'tif': case 'tiff':
    return 'bmp';
  case 'viv':
  case 'avi':
  case 'flv':
    return 'wmv';
  case 'wmv':
    return 'wmv';
  case '/':
    return 'dir';
  default:
    return 'unknown';
  }
}

$filetypes = array(
  '' => array(
    'ext' => '',
    'ft' => 0,
    'tft' => 'Unknown file',
    'fti' => 'unknown'
  )
);
$tft = array('Other','Directory','HTML','Server script','Plaintext','Image','Image');

function textfiletype($fext)
{
  switch ($fext)
  {
  case '': return 'Unknown File';
  case 'doc': return 'Word Document';
  case 'bin': return 'Binary File';
  case 'ps': return 'PostScript';
  case 'gif': return 'GIF Image';
  case '/': return 'File Folder';
  case 'html': case 'htm': return 'HTML Document';
  case 'wav': return 'WAVE file';
  case 'php': return 'PHP Script';
  case 'phps': return 'PHP Script Source';
  case 'php3': return 'PHP 3 Script';
  case 'php4': return 'PHP 4 Script';
  case 'php5': return 'PHP 5 Script';
  case 'php6': return 'PHP 6 Script';
  case 'c': return 'C Source File';
  case 'h': return 'C Header File';
  case 'cpp': return 'C++ Source File';
  case 'pdf': return 'PDF Document';
  case 'ppt': return 'Powerpoint Presentation';
  case 'mov': return 'QuickTime Movie';
  case 'ram': return 'RealAudio Movie';
  case 'rtf': return 'Rich Text Document';
  case 'xml': return 'XML Document';
  case 'sit': return 'StuffIt Archive';
  case 'txt': return 'Text Document';
  case 'wma': return 'Windows Media Audio';
  case 'wmv': return 'Windows Media Video';
  case 'zip': return 'ZIP Archive';
  case 'css': return 'CSS Document';
  case 'js': return 'JavaScript File';
  case 'mp3': return 'MP3 File';
  case 'hqx': return 'Mac Binary';
  case 'ico': return 'Icon File';
  case 'jpg': case 'jpe': case 'jpeg': return 'JPEG Image';
  case 'png': return 'PNG Image';
  case 'mng': return 'MNG Animated Image';
  case 'bmp': return 'BMP Image';
  case 'tif': case 'tiff': return 'TIFF Image';
  case 'viv': return 'VIVO video';
  case 'dll': return 'Application Extension';
  case 'exe': return 'Application';
  case 'psd': return 'Photoshop Document';
  case 'ini': return 'Configuration File';
  case 'diff': case 'patch': return 'Patch File';
  case 'log': return 'Log File';
  case 'flv': return 'Flash Video';
  case 'htaccess': return 'Apache .htaccess Configuration';
  default: return 'Unknown .'.$fext.' File';
  }
}

function fextac($fext)
{
  if (!$fext) return 'No Extension';
  if ($fext == 'doc') return 'Word Document';
  if ($fext == 'bin') return 'Binary File';
  if ($fext == 'ps') return 'PostScript';
  if ($fext == 'gif') return 'Graphics Interchange Format';
  if ($fext == '/') return 'Directory';


  if ($fext == 'html' || $fext == 'htm') return 'Hypertext Markup Language';

  if ($fext == 'wav') return 'Wave sound';
  if ($fext == 'php') return 'PHP Hypertext Preprocessor';
  if ($fext == 'pdf') return 'Portable Documents Format';
  if ($fext == 'ppt') return 'Powerpoint Presentation';
  if ($fext == 'mov') return 'QuickTime Movie';
  if ($fext == 'ram') return 'RealAudio Movie';
  if ($fext == 'rtf') return 'Rich Text Format';
  if ($fext == 'xml') return 'Extensible Markup Language';
  if ($fext == 'sit') return 'StuffIt Archive';
  if ($fext == 'txt') return 'Text Document';
  if ($fext == 'wmv') return 'Windows Media Video';
  if ($fext == 'zip') return 'WinZip Archive';
  if ($fext == 'css') return 'Cascading Style Sheets';
  if ($fext == 'js') return 'JavaScript';
  if ($fext == 'mp3') return 'MP3 File';
  if ($fext == 'hqx') return 'Mac Binary';
  if ($fext == 'ico') return 'Icon';
  if ($fext == 'jpg' || $fext == 'jpe' || $fext == 'jpeg') return 'JPEG Image';
  if ($fext == 'png') return 'Portable Network Graphics';
  if ($fext == 'mng') return 'MNG Animated Image';
  if ($fext == 'bmp') return 'Bitmap';
  if ($fext == 'tif' || $fext == 'tiff') return 'TIFF Image';
  if ($fext == 'viv') return 'VIVO video';
  if ($fext == 'dll') return 'Dynamic Linked Library';
  if ($fext == 'exe') return 'Executable';
  if ($fext == 'psd') return 'Adobe Photoshop Document';
  if ($fext == 'ini') return 'Initializer/Configurer';
  if ($fext == 'tif' || $fext == 'tiff') return 'TIFF Image';
  if ($fext == 'flv') return 'Flash Video';
  if ($fext == 'htaccess') return 'Hypertext Access Control';
  return 'Unknown Extension';
}

function fm_contenttype($fext)
{
  switch ($fext)
  {
  // Text
  case 'txt': case 'php': case 'htm': case 'html': case 'xml': case 'asp': case 'css':
  case 'ini': case 'tex': case 'htaccess':
    return 'text/plain';
  // Images
  case 'gif':
    return 'image/gif';
  case 'jpeg': case 'jpg': case 'jpe':
    return 'image/jpeg';
  case 'png':
    return 'image/png';
  case 'tiff': case 'tif':
    return 'image/tiff';
  case 'ico':
    return 'image/vnd.microsoft.icon';
  case 'svg':
    return 'image/svg+xml';
  // Documents
  case 'pdf':
    return 'application/pdf';
  case 'doc': case 'dot':
    return 'application/msword';
  case 'ppt': case 'pps': case 'pot':
    return 'application/vnd.ms-powerpoint';
  case 'xls': case 'xlt': case 'xlw': case 'xla':case 'xlc':case 'xlm':
    return 'application/vnd.ms-excel';
  case 'wcm': case 'wdb': case 'wks': case 'wps':
    return 'application/vnd.ms-works';
  // Media
  case 'avi':
    return 'video/x-msvideo';
  case 'wma':
    return 'audio/x-ms-wma';
  case 'wmv':
    return 'video/x-ms-wmv';
  case 'm3u':
    return 'audio/x-mpegurl';
  case 'mov': case 'qt':
    return 'video/quicktime';
  case 'mpg': case 'mpe': case 'mpeg': case 'mp2': case 'mpa': case 'mp3':
    return 'video/mpeg';
  case 'mp4':
    return 'video/mp4';
  case 'rv':
    return 'video/vnd.rn-realvideo';
  case 'ra': case 'ram':
    return 'audio/vnd.rn-realaudio';
  case 'wav':
    return 'audio/x-wav';
  // Plugins
  case 'class':
    return 'application/x-java-applet';
  case 'swf':
    return 'application/x-shockwave-flash';
  // Archives
  case 'zip':
    return 'application/zip';
  case 'gz':
    return 'application/x-gzip';
  case 'rar':
    return 'application/x-rar-compressed';
  // Misc
  case 'torrent':
    return 'application/x-bittorrent';
  default:
    return 'application/octet-stream';
  }
}

?>