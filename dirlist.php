<?php

/*
 **************************************************
 ** Directory listing library                    **
 ** Version 1.0 RC1                              **
 ** By Zarel - released under public domain      **
 **************************************************
 *
 * Meant for PHP 4.0.4 and up. I've tried to use functions present in
 * PHP 3, but it might not work there.
 *
 * Based off libraries from AEsoft File Manager 1.0 RC2
 *
 **[ Description ]*********************************
 *
 * Displays a directory list
 *
 **[ Function reference ]**************************
 *
 * dirlist(dir, up, sortby)
 *
 * dir: Directory to list, defaults to current
 * up: Show "Up One Level"?
 * sortby: key to sort by
 *
 **************************************************/

  //=====================
  // Config
  //=====================

$dirlist_icondir = '/fileman/images/icons32/';

  //====================
  // Routines
  //====================

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
  if (!$pathA[0])
    $result[] = '';
  foreach ($pathA as $key => $dir)
  {
    if ($dir == '..')
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

  //====================
  // Reading Functions
  //====================

function getfiles($path, $exclude=array())
{
  $dh = @opendir($path);
  
  $dirs = array();
  $files = array();
  
  while (false !== ($file = @readdir($dh)))
  {
    if (substr($file,0,1) != "." && !in_array($file,$exclude))  #skip anything that starts with a '.'
    {                            #i.e.:('.', '..', or any hidden file)
      if (is_dir($path.$file))
        $dirs[] = $file.'/';  #put directories into dirs[] and append a '/' to differentiate
      else
        $files[] = $file;  #everything else goes into files[]
    }
  }
  @closedir($dh);
  
  if ($cnfs == '1')
  {
    $files = array_merge($dirs,$files); #merge dirs[] and files[] into files
    $dirs = array();
  }
  
  if ($carr = 'name')
  {
    if ($files) natcasesort($files); #natural case insensitive sort
    if ($dirs) natcasesort($dirs);
  }
  
  $files = array_merge($dirs,$files);  #merge dirs[] and files[] into files with dirs first
  return $files;
}

function getdfiles($path,$exclude=array())
{
  $files = getfiles($path,$exclude);
  $dfiles = array();
  foreach ($files as $file)
    $dfiles[] = fileinfo($path.$file);
  return $dfiles;
}

function fileinfo($path)
{
  $file = filefrompath($path);
  if (!file_exists($path)) return FALSE;
  $size = filesize($path);
  $modified = filemtime($path);
  $ext = getfext($file);
  return array(
    // The name of the file - 'example.gif'
    'name' => $file,
    // The ID of the file - 'file_example.gif'
    'id' => ((filetype($path) == 'dir')?'fold_'.substr($file,0,-1):'file_'.$file),
    // Is it a directory? - FALSE
    'isdir' => (filetype($path) == 'dir'),
    // File size - 1024
    'size' => $size,
    // Text file size - '1 KB'
    'tsize' => textfilesize($size),
    // Modified (Unix timestamp) - 0
    'modified' => $modified,
    // Modified - 'Jan 1, 1970 12:00:00 AM'
    'tmodified' => date("F j, Y g:i:s A",$modified),
    // Perms - 0777
    'perms' => substr(sprintf('%o', fileperms($path)), -4),
    // Extension - 'gif'
    'ext' => $ext,
    // File icon - 'gif'
    'img' => textfileimg($ext),
    // File type - 'GIF Image'
    'type' => textfiletype($ext),
    // File type ID - '0'
    'ft' => ft($ext),
    // Extension acronym - 'Graphics Interchange Format'
    'extac' => getfextac($ext),
    // Is it a viewable image (an image that can be displayed in a browser)? - TRUE
    'isvimg' => isvimg($ext),
    // Image size data - {$width, $height, 1, 'height="$height" width="$width"'}
    // ['imgsize'][2]:  1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
    'imgsize' => ((isvimg($ext)||$ext=='psd'||$ext=='bmp') && $size<=5242880)?$imgsize = @getimagesize($path.$file):FALSE);
}

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

function id2file($id)
{
  if (substr($id,0,5)=='file_') return substr($id,5);
  return substr($id,5).'/';
}

function ids2files($ids)
{
  $files = explode(',',$ids);
  for ($i=0;$i<count($files);$i++)
    $files[$i] = id2file($files[$i]);
  return $files;
}

function ft($fext)
{
  switch ($fext)
  {
  case '/':
    return 1; // Directory
  case 'htm':
  case 'html':
    return 2; // HTML [plaintext]
  case 'php':
  case 'php3':
  case 'phps':
  case 'asp':
  case 'cgi':
  case 'jsp':
  case 'phtml':
    return 3; // server-side scripted HTML [plaintext]
  case 'txt':
  case 'css':
  case 'js':
  case 'ini':
    return 4; // Other plaintext
  case 'jpg':
  case 'jpeg':
  case 'gif':
  case 'png':
    return 5; // Browser-displayable images
  case 'bmp':
    return 6; // Other images
  default:
    return 0; // Other
  }
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
  if ( $fsize >= 1048576 )
    // Size in megabytes
    return round( $fsize / 1024 / 1024, 2 ) . " MB";
  if( $fsize >= 1024 )
    // Size in kilobytes
    return round( $fsize / 1024, 2 ) . " KB";
  if( $fsize != 1 )
    // Size in bytes
    return $fsize . " bytes";
    // Self-explanatory
  return "1 byte";
}

function getfext($fname)
{
  if (substr($fname, -1) == '/' || substr($fname, -1) == '\\')
    return "/";
  $fext = substr(strrchr($fname, "."), 1);
  return strtr($fext,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz');
}

function textfileimg($fext)
{
  switch ($fext)
  {
  case "doc": case "ps": case "gif": case "dir":
  case "html": case "wav": case "php": case "pdf":
  case "ppt": case "mov": case "ram": case "rtf":
  case "xml": case "sit": case "txt": case "wmv":
  case "zip": case "bmp": case "psd": case "ini":
    return $fext;
  case "css":
    return "ini";
  case "js":
    return "xml";
  case "htm":
    return "html";
  case "mp3":
    return "wav";
  case "hqx":
    return "sit";
  case "ico":
    return "gif";
  case "jpg": case "jpe": case "jpeg":
    return "jpg";
  case "png":
    return "gif";
  case "tif": case "tiff":
    return "bmp";
  case "viv":
    return "wmv";
  case "/":
    return "dir";
  default:
    return "unknown";
  }
}

function textfiletype($fext)
{
  switch ($fext)
  {
  case "": return "Unknown File";
  case "doc": return "Word Document";
  case "bin": return "Binary File";
  case "ps": return "PostScript";
  case "gif": return "GIF Image";
  case "/": return "File Folder";
  case "html": case "htm": return "HTML Document";
  case "wav": return "WAV file";
  case "php": return "PHP Script";
  case "pdf": return "PDF Document";
  case "ppt": return "Powerpoint Presentation";
  case "mov": return "QuickTime Movie";
  case "ram": return "RealAudio Movie";
  case "rtf": return "Rich Text Document";
  case "xml": return "XML Document";
  case "sit": return "StuffIt Archive";
  case "txt": return "Text Document";
  case "wmv": return "Windows Media Video";
  case "zip": return "ZIP Archive";
  case "css": return "CSS Document";
  case "js": return "JavaScript File";
  case "mp3": return "MP3 File";
  case "hqx": return "Mac Binary";
  case "ico": return "Icon File";
  case "jpg": case "jpe": case "jpeg": return "JPEG Image";
  case "png": return "PNG Image";
  case "mng": return "MNG Animated Image";
  case "bmp": return "BMP Image";
  case "tif": case "tiff": return "TIFF Image";
  case "viv": return "VIVO video";
  case "dll": return "Application Extension";
  case "exe": return "Application";
  case "psd": return "Photoshop Document";
  case "ini": return "Configuration File";
  default: return "Unknown .$fext File";
  }
}

function getfextac($fext)
{
  if (!$fext) return "No Extension";
  if ($fext == "doc") return "Word Document";
  if ($fext == "bin") return "Binary File";
  if ($fext == "ps") return "PostScript";
  if ($fext == "gif") return "Graphics Interchange Format";
  if ($fext == "/") return "Directory";
  if ($fext == "html" || $fext == "htm") return "Hypertext Markup Language";
  if ($fext == "wav") return "Wave sound";
  if ($fext == "php") return "PHP Hypertext Preprocessor";
  if ($fext == "pdf") return "Portable Documents Format";
  if ($fext == "ppt") return "Powerpoint Presentation";
  if ($fext == "mov") return "QuickTime Movie";
  if ($fext == "ram") return "RealAudio Movie";
  if ($fext == "rtf") return "Rich Text Format";
  if ($fext == "xml") return "Extensible Markup Language";
  if ($fext == "sit") return "StuffIt Archive";
  if ($fext == "txt") return "Text Document";
  if ($fext == "wmv") return "Windows Media Video";
  if ($fext == "zip") return "WinZip Archive";
  if ($fext == "css") return "Cascading Style Sheets";
  if ($fext == "js") return "JavaScript";
  if ($fext == "mp3") return "MP3 File";
  if ($fext == "hqx") return "Mac Binary";
  if ($fext == "ico") return "Icon";
  if ($fext == "jpg" || $fext == "jpe" || $fext == "jpeg") return "JPEG Image";
  if ($fext == "png") return "Portable Network Graphics";
  if ($fext == "mng") return "MNG Animated Image";
  if ($fext == "bmp") return "Bitmap";
  if ($fext == "tif" || $fext == "tiff") return "TIFF Image";
  if ($fext == "viv") return "VIVO video";
  if ($fext == "dll") return "Dynamic Linked Library";
  if ($fext == "exe") return "Executable";
  if ($fext == "psd") return "Adobe Photoshop Document";
  if ($fext == "ini") return "Initializer/Configurer";
  return "Unknown Extension";
}

  //=====================
  // DIRLIST!
  //=====================

function sortbycmp($a,$b)
{
    if ($a[$GLOBALS['sortby']] == $b[$GLOBALS['sortby']]) {
        return 0;
    }
    return ($a[$GLOBALS['sortby']] < $b[$GLOBALS['sortby']]) ? -1 : 1;
}
function sortbydcmp($a,$b)
{
    if ($a[$GLOBALS['sortby']] == $b[$GLOBALS['sortby']]) {
        return 0;
    }
    return ($a[$GLOBALS['sortby']] < $b[$GLOBALS['sortby']]) ? 1 : -1;
}
function sortby($arr,$sub,$sortdes)
{
	$GLOBALS['sortby']=$sub;
	if ($sortdes) usort($arr, 'sortbydcmp');
	else usort($arr, 'sortbycmp');
	return $arr;
}

function dirlist($dir='./',$up=true,$sortby='',$sortdes='',$rq=false)
{
  global $dirlist_icondir;
  if (!$dir) $dir = './';
  $dfiles = getdfiles($dir,array('cgi-bin'));
  if ($sortby) $dfiles = sortby($dfiles,$sortby,$sortdes);

  if (count($dfiles)==0)
  {
?>
<div id="dirlist">
  <div class="nofiles">This directory contains no files.</div>
</div>
<?php
  }
  else
  {
?>
<div id="dirlist">
  <table border="0" cellspacing="0" cellpadding="0">
    <tr style="border-bottom:1px solid #777777;">
      <th style="width:32px;text-align:left;" width="32">
        <div style="padding:1px;border-bottom:1px solid #777777;">&nbsp;</div>
      </td>
      <th style="text-align:left;">
        <div style="padding:1px 5px;font-style:italic;border-bottom:1px solid #777777;">Name</div>
      </td>
      <th style="text-align:left;">
        <div style="padding:1px 5px;font-style:italic;border-bottom:1px solid #777777;">Type</div>
      </td>
      <th style="text-align:left;">
        <div style="padding:1px 5px;font-style:italic;border-bottom:1px solid #777777;">Size</div>
      </td>
      <th style="text-align:left;">
        <div style="padding:1px 5px;font-style:italic;border-bottom:1px solid #777777;">Modified</div>
      </td>
    </tr>
<?php
    if ($up)
    {
?>
    <tr>
      <td>
        <a href="../"><img src="<?php echo $dirlist_icondir.'_udir.gif'; ?>" width="32" height="32" alt="dir" style="border:0" /></a>
      </td>
      <td colspan="4">
        <a href="../" style="display:block;overflow:hidden;padding:1px 10px 1px 5px;text-decoration:none;color:#000000;">../ <span style="color:#777777">(Up one level)</span></a>
      </td>
    </tr>
<?php
    }
    foreach ($dfiles as $dfile)
    {
      $dfp = '';
      if ($dfile['ext']=='flv') $dfp='/fileman/flvplayer.php?d='.($rq?$rq:(substr($_SERVER['PHP_SELF'],1,strrpos($_SERVER['PHP_SELF'],'/'))));
?>
    <tr>
      <td>
        <a href="<?php echo $dfp,urlencode($dfile['name']); ?>"><img src="<?php echo $dirlist_icondir.$dfile['img'].'.gif'; ?>" width="32" height="32" alt="<?php echo $dfile['ext']; ?>" style="border:0" /></a>
      </td>
      <td>
        <a href="<?php echo $dfp,urlencode($dfile['name']); ?>" style="display:block;overflow:hidden;padding:1px 10px 1px 5px;text-decoration:none;color:#000000;<?php if ((!$dir || $dir=='./') && basename($_SERVER['PHP_SELF'])==$dfile['name']) echo 'text-decoration:underline;'; ?>"><?php echo $dfile['name']; ?></a>
      </td>
      <td>
        <div style="overflow:hidden;padding:1px 10px 1px 5px;color:#777777;"><?php echo $dfile['type']; ?></div>
      </td>
      <td>
        <div style="overflow:hidden;padding:1px 10px 1px 5px;color:#777777;"><?php echo ($dfile['ft']==1?'&nbsp;':$dfile['tsize']); ?></div>
      </td>
      <td>
        <div style="overflow:hidden;padding:1px 10px 1px 5px;color:#777777;"><?php echo date('Y M j',$dfile['modified']); ?></div>
      </td>
    </tr>
<?php
    }
?>
  </table>
</div>
<?php
  }
}
?>