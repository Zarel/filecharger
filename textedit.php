<?php

/*
 *
 * Text Edit for Filecharger
 * by Zarel of Novawave
 *
 * Used to edit text files.
 *
 * Default module - Performs important functionality,
 * but can be deleted if absolutely necessary
 * 
 */

  //========================================
  // Before the libraries can throw errors
  //========================================

if (isset($_REQUEST['frame']) && !isset($_REQUEST['d']))
{
?>
<!DOCTYPE html>
<html style="margin:0;padding:0;">
  <head>
    <title>Throbber</title>
    <script type="text/javascript">
    <!--
      function load(l)
      { document.getElementById('loading').src = 'images/throbber'+(l?'-loading.gif':'.gif'); var limg = new Image(); limg.src = 'images/throbber-loading.gif';
      }
    //-->
    </script>
  </head>
  <body style="margin:0;padding:0;" onload="load(0);parent.fmf_okay(1);">
    <img id="loading" src="images/throbber-loading.gif" alt="" width="20" height="20" />
  </body>
</html>
<?php
	die();
}

  //========================================
  // Libraries
  //========================================

include_once 'config.inc.php';
include_once 'fileman.lib.php';
if ($ftpmode)
	include_once 'ftpsession.lib.php';
else
	include_once 'session.lib.php';

  //========================================
  // Pseudo-Ajax frame
  //========================================

//if it's being used as a frame, we'll need to do some more stuff.
if (isset($_REQUEST['frame']))
{
  if (isset($_POST['act'])) switch ($_POST['act'])
  {
  case 'textedit':
    if (fm_iswritable($d))
    {
      if (!is_string($_POST['val']))
        die('<!DOCTYPE html><script language="javascript">parent.fmf_error("Malformed request.");</script>');
      if (strpos(fm_contents($d),"\r\n")===false)
        $_POST['val'] = str_replace("\r\n","\n",$_POST['val']);
      if (!fm_editfile($d, $_POST['val']))
      {
        fm_close();
        die('<!DOCTYPE html><script language="javascript">parent.fmf_error("Cannot edit file \''.basename($d).'\'.");</script>');
      }
    }
    else
      die('<!DOCTYPE html><script language="javascript">parent.fmf_error("File \''.basename($d).'\' is not writable.");</script>');
  }
  // Written in HTML 5 because I'm too lazy to write XHTML
?>
<!DOCTYPE html>
<html style="margin:0;padding:0;">
  <head>
    <title>Throbber</title>
    <script type="text/javascript">
    <!--
      function load(l)
      { document.getElementById('loading').src = 'images/throbber'+(l?'-loading.gif':'.gif'); var limg = new Image(); limg.src = 'images/throbber-loading.gif';
      }
    //-->
    </script>
  </head>
  <body style="margin:0;padding:0;" onload="load(0);parent.fmf_okay(1);">
    <img id="loading" src="images/throbber-loading.gif" alt="" width="20" height="20" />
  </body>
</html>
<?php
  die();
}

  //========================================
  // Fallback for browsers with no frames/JS support
  //========================================

// 99% of the time, this is some idiot with Firefox and NoScript
if (isset($_POST['val'])) // Update
{
	if (fm_iswritable($fullpath))
	{
		fm_editfile($d,$_POST['val']);
		fm_close();
	}
	else
	{
		die("Error: $file is read-only. Please CHMOD to something like 644 before saving.");
	}
}

  //========================================
  // Close button
  //========================================

// Honestly, we should probably not have a close button. Half of browsers block this action anyway.
if (isset($_GET['c']))
{
  die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><title>Close this window.</title></head><body onload="window.close()">Close this window.</body></html>');
}

  //========================================
  // Start
  //========================================

if (isset($_REQUEST['rec']))
{
$lbs = '';
//for ($j=128;;$j*=4)
//{
//$res = fopen($fullpath,'r');
$filecontents = fm_contents($d);
for ($i=0;$i<1000;$i++)
{
if (ord($filecontents[$i])==13 || ord($filecontents[$i])==10)
{
$lbs .= (ord($filecontents[$i])-10?'\r':'\n');
}
else if ($lbs)
break; // break 2
}
//}
echo '<p><em>Warning: Do not modify linebreaks unless you know what you\'re doing.</em></p>';
echo '<p>First newline(s): '.$lbs.'</p>';
echo '<form action="textedit.php?d='.$d.'" method="post"><input name="recf" type="text" value="'.$lbs.'" /> to <input name="rect" type="text" value="\r\n" /><input type="submit" value="Replace" /><input type="button" value="Cancel" onclick="document.location.href=\'textedit.php?d='.$d.'\'" /></form>';
die();
}
if ($_POST['recf'])
{
$filecontents = fm_contents($d);
$from = ''; $to = '';
$rf = explode('\\',$_POST['recf']); $rt = explode('\\',$_POST['rect']);
foreach ($rf as $r) if ($r) $from .= ($r=='r'?chr(13):chr(10));
foreach ($rt as $r) if ($r) $to .= ($r=='r'?chr(13):chr(10));
fm_editfile($d,$filecontents=str_replace($from,$to,$filecontents)) or die('Error');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html style="overflow:hidden">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title><?php echo htmlentities($file); ?> - Text Editor</title>
    <link rel="stylesheet" href="fileman.css" type="text/css" media="screen" />
    <script language="javascript" type="text/javascript">
    <!--
      var changed = false, csaved = -1, timer = null, codemirror = false, codemirroreditor = null;
      var loaded = false, savequeue = false, original = '';
      function load()
      {
        window.onresize = resize;
        resize(); original = document.getElementById('val').value.replace(/\r\n/g,'\n');
        //document.getElementById('save').type = 'button';
        document.getElementById('save').onclick = save;
        loaded = true;
      }
      function resize()
      {
        pw = document.getElementById('contentwrapper').offsetWidth;
        ph = document.getElementById('contentwrapper').offsetHeight;
        if (codemirror && document.getElementsByTagName('iframe')[1])
        {
          document.getElementsByTagName('iframe')[1].style.height = ''+(ph-37)+'px';
          document.getElementsByTagName('iframe')[1].style.width = ''+(pw)+'px';
        }
        else
        {
          document.getElementById('val').style.height = ''+(ph-37)+'px';
          document.getElementById('val').style.width = ''+(pw-3)+'px';
        }
      }
      function change(e)
      {
        // arrow keys, nav keys, F1-12, Esc, Ins
        if (changed || (e.keyCode >= 112 && e.keyCode <= 123)
          || (e.keyCode >= 16 && e.keyCode <= 20)
          || (e.keyCode >= 33 && e.keyCode <= 45) || e.keyCode == 27) return;
        if (!changed) chkfast();
      }
      function chk()
      {
        if (codemirror && original == codemirroreditor.getCode() ||
            !codemirror && original == document.getElementById('val').value)
        {
          //document.getElementById('save').disabled = true;
          document.getElementById('save').className = 'bbbtnd';
          document.getElementById('sq').className = 'disabled';
          document.getElementById('rv').className = 'disabled';
          window.onbeforeunload = null;
          timer = setTimeout('chk()', 5000);
          return changed = false;
        }
        else
        {
          //document.getElementById('save').disabled = false;
          document.getElementById('save').className = 'bbbtn';
          document.getElementById('sq').className = 'nd';
          document.getElementById('rv').className = 'nd';
          window.onbeforeunload = bul;
          clearTimeout(timer);
          return changed = true;
        }
      }
      function chkfast()
      {
        if (codemirror && original != codemirroreditor.getCode() ||
            !codemirror && original != document.getElementById('val').value)
        {
          //document.getElementById('save').disabled = false;
          document.getElementById('save').className = 'bbbtn';
          document.getElementById('sq').className = 'nd';
          document.getElementById('rv').className = 'nd';
          window.onbeforeunload = bul;
          clearTimeout(timer);
        }
      }
      function chkchange()
      {
        if (changed) return true;
        //document.getElementById('save').disabled = false;
        document.getElementById('save').className = 'bbbtn';
        document.getElementById('sq').className = 'nd';
        document.getElementById('rv').className = 'nd';
        window.onbeforeunload = bul;
        clearTimeout(timer);
        return changed = true;
      }
      function keydown(e)
      {
        if (e.keyCode == 83 && (e.ctrlKey || e.altKey)) // Ctrl+S
        {
          if (chk())
            save();
          return false;
        }
        if (e.keyCode == 9 && !e.ctrlKey && !e.altKey) // Tab
        {
          insert_text("\t");
          return false;
        }
        return true;
      }
      function cmenuon(e)
      {
        if (!e) var e = window.event;
	    e.cancelBubble = true;
	    if (e.stopPropagation) e.stopPropagation();
	    
        document.getElementById('fmenu').blur();
        document.getElementById('cmenu').style.display = 'block';
        document.getElementById('fmenu').className = 'bbbtnp';
        setTimeout('document.onmouseup = cmenuoff',500);
      }
      function cmenuoff()
      {
        document.getElementById('cmenu').style.display = 'none';
        document.getElementById('fmenu').className = 'bbbtn';
        document.onmouseup = null;
      }
      function openfile()
      {
        window.open('<?php echo fm_geturl($d); ?>','_blank');
      }
      function save()
      {
        if (!chk()) return false;
        savequeue=false;
        if (document.getElementById('save').className == 'bbbtnd') savequeue=true;
        else
        {
          if (codemirror) document.getElementById('val').value = codemirroreditor.getCode();
          fmframe.load(1);original = document.getElementById('val').value.replace(/\r\n/g,'\n');
          //document.getElementById('save').disabled = true;
          document.getElementById('save').className = 'bbbtnd';
          document.getElementById('save').innerHTML = 'Saving';
          document.getElementById('save').style.fontWeight = 'normal';
          document.getElementById('ted').submit();
        }
        return false;
      }
      function revertform()
      {
        if (!csaved && !chk()) return;
        if (confirm('Are you sure you want to change this file back to how it was when you opened it, before any saves?'))
        {
          document.getElementById('ted').reset(); chk();
        }
      }
      function bul()
      { if (!codemirror && original != document.getElementById('val').value) return "You haven't saved your changes.";
        if (codemirror && original != codemirroreditor.getCode()) return "You haven't saved your changes.";
        return null; }
      function fmf_okay(o)
      {
        if (!o) { alert('Error: File not saved.'); return; }
        csaved++;
        if (loaded)
        {
          document.getElementById('save').innerHTML = 'Save';
          document.getElementById('save').style.fontWeight = 'bold';
          chk();
          //document.getElementById('save').disabled = false;
        }
        else
        {
          if (savequeue) save();
        }
      }
      function fmf_error(o)
      {
        alert('Error: '+o);
      }
function insert_text(text, spaces, popup)
{
	var textarea = document.getElementById('val');
	
	if (!isNaN(textarea.selectionStart))
	{
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '')
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	}
	else if (textarea.createTextRange && textarea.caretPos)
	{
		if (baseHeight != textarea.caretPos.boundingHeight) 
		{
			textarea.focus();
			storeCaret(textarea);
		}

		var caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
	}
	else
	{
		textarea.value = textarea.value + text;
	}
}
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	if (selEnd == 1 || selEnd == 2) 
	{
		selEnd = selLength;
	}

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selEnd + open.length + close.length;
	txtarea.selectionEnd = txtarea.selectionStart;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;

	return;
}
    //-->
    </script>
  </head>
  <body onload="load()" style="overflow:hidden" onkeydown="return keydown(event);">
    <div id="contentwrapper" class="cw_te">
      <form id="ted" action="textedit.php?frame&d=<?php echo $hd,$hasid;?>" target="fmframe" method="post" class="textedit">
        <div id="tetopbar"><div id="btnbar">
          <!--input type="button" value="File" id="fmenu" class="bbbtn" onmouseup="cmenuon(event)" />
          <input type="submit" value="Save" id="fsave" onclick="return save();" class="bbbtnd" /-->
          <input type="submit" value="Save" id="fsave" class="bbbtnd" />
          <script type="text/javascript">
          <!--
            document.getElementById('fsave').style.display = 'none';
            document.write('<span id="fmenu" class="bbbtn" onmouseup="cmenuon(event)">File</span> <span id="save" onclick="return save();" class="bbbtnd">Save</span>');
          -->
          </script>
          <input type="hidden" name="act" value="textedit" />
        </div>
<?php
$writable = fm_iswritable($d);
?>
<?php if (fm_isfile($d)) { ?>
        <div style="float:right;"><iframe src="textedit.php?frame<?php echo $hasid; ?>" name="fmframe" height="20" width="20" frameborder="0" scrolling="no" marginwidth="0" marginheight="0"></iframe></div>
<?php } ?>
        <div id="flocbar"><?php if (isset($_GET['p'])) echo '&nbsp;'; else { ?><div id="dir" class="dir" style="background-image: url(images/icons/<?php echo fticon($fileext); ?>.gif)"><?php echo $fullurl ?></div><?php } ?></div></div>
        <div id="valbar"><textarea name="val" onkeyup="change(event)" id="val" rows="30" cols="80"<?php if (fm_isfile($d)) echo ($writable?'>':' disabled="disabled">
Warning: This file is read-only. You will not be able to edit it unless you CHMOD it to something like 644.

================================
').str_replace('\n','\r\n',str_replace('\r\n','\n',htmlentities(isset($filecontents)?$filecontents:$filecontents=fm_contents($d)))); else echo ' disabled="disabled">


    Error: File doesn\'t exist'; ?></textarea></div>
      </form>
<?php if ($ext_codemirror && $writable) { if (ft($fileext) == 2 || ft($fileext) == 3 || $fileext == 'phps') { ?>
      <script type="text/javascript" src="addons/codemirror/codemirror.js"></script>
      <script type="text/javascript">
      <!--
      if (CodeMirror.isProbablySupported())
      {
        codemirror = true;
        codemirroreditor = CodeMirror.fromTextArea('val', {
          parserfile: ['parsexml.js', 'parsecss.js', 'parsetokenizejavascript.js',
                       'parsetokenizephp.js', 'parsephphtmlmixed.js'],
          stylesheet: 'addons/codemirror/colors.css',
          path: 'addons/codemirror/',
          basefiles: ['codemirror_base.js'],
          saveFunction: save,
          onChange: chkchange,
          tabMode: 'shift',
          continuousScanning: 1000
        });
      }
      //-->
      </script>
<?php } else if ($fileext == 'js') { ?>
      <script type="text/javascript" src="addons/codemirror/codemirror.js"></script>
      <script type="text/javascript">
      <!--
      if (CodeMirror.isProbablySupported())
      {
        codemirror = true;
        codemirroreditor = CodeMirror.fromTextArea('val', {
          parserfile: 'parsetokenizejavascript.js',
          stylesheet: 'addons/codemirror/colors.css',
          path: 'addons/codemirror/',
          basefiles: ['codemirror_base.js'],
          saveFunction: save,
          onChange: chkchange,
          tabMode: 'shift',
          continuousScanning: 1000
        });
      }
      //-->
      </script>
<?php } else if ($fileext == 'css') { ?>
      <script type="text/javascript" src="addons/codemirror/codemirror.js"></script>
      <script type="text/javascript">
      <!--
      if (CodeMirror.isProbablySupported())
      {
        codemirror = true;
        codemirroreditor = CodeMirror.fromTextArea('val', {
          parserfile: 'parsecss.js',
          stylesheet: 'addons/codemirror/colors.css',
          path: 'addons/codemirror/',
          basefiles: ['codemirror_base.js'],
          saveFunction: save,
          onChange: chkchange,
          tabMode: 'shift',
          continuousScanning: 1000
        });
      }
      //-->
      </script>
<?php } } ?>
    </div>
    <div id="cmenu" style="display:none;top:25px;left:5px;">
      <div><a href="<?php echo $fullurl; ?>" target="_blank" title="Open this file in a new window.">View</a></div>
      <div><a id="sq" href="javascript:void(0)" onmousedown="save();" class="disabled">Save</a></div>
      <div><a id="rv" href="javascript:void(0)" onmousedown="revertform();cmenuoff();" title="Change this file back to how it was the last time you saved it." class="disabled">Revert</a></div>
      <div class="hr"><hr /></div>
      <div><a id="crlf" href="javascript:void(0)" onmousedown="document.location.href='textedit.php?d=<?php echo $d; ?>&rec';cmenuoff();" title="Analyze the linebreaks in this document.">Analyze linebreaks</a></div>
    </div>
  </body>
</html>