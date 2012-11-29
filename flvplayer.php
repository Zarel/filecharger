<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 * Text Edit
 *
 * Used to edit text files.
 *
 * Default module - Performs important functionality,
 * but can be deleted if absolutely necessary
 * 
 */

include 'config.inc.php';
include 'fileman.lib.php';
@include 'session.lib.php';

if (isset($_GET['c']))
{
  die('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><title>Close this window.</title></head><body onload="window.close()">Close this window.</body></html>');
}

if (!fm_exists($d)) die('File doesn\'t exist.');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title><?php echo htmlentities($file); ?> - FLV Player</title>
    <script type="text/javascript" src="addons/swfobject.js"></script>
    <style type="text/css">
    <!--
    #sizes { font-family: Verdana, Arial, sans-serif; font-size: 9pt; }
    #sizes a { text-decoration: none; color: #3D61AA; }
    #sizes a:active { color: #3D61AA; }
    #sizes a.sel { padding: 2px 3px 3px 3px; border: 1px solid #3D61AA; border-bottom: 0; background-color: #D9E1F6; }
    #sizes a.nsel { padding: 3px 4px 3px 4px; border: 0; background-color: #FFFFFF; }
    #sizes a.nsel:hover { padding: 2px 3px 3px 3px; border: 1px solid #C9D1E6; border-bottom: 0; }
    -->
    </style>
  </head>
  <body>
    <div style="padding: 10px 20px 2px 20px;" id="sizes"><strong>Size:</strong> <a href="#" onclick="return resize(0)" id="size0" class="nsel">Standard</a> <a href="#" onclick="return resize(1)" id="size1" class="nsel">Bigger</a> <a href="#" onclick="return resize(2)" id="size2" class="nsel">Even bigger</a> <a href="#" onclick="return resize(3)" id="size3" class="nsel">Huge</a> <a href="#" onclick="return resize(4)" id="size4" class="nsel">Even huger</a></div>
    <div id="player" style="padding: 0 20px 10px 20px;">Please turn on JavaScript</div>
    <script type="text/javascript">
    <!--
      var so = false;
      var size = -1;
      function resize(nsize)
      {
        if (size == nsize) return false;
        var w=481, h=362;
        switch (nsize)
        {
        case 1:
			w=640; h=480;
			break;
        case 2:
			w=800; h=600;
			break;
        case 3:
			w=1024; h=768;
			break;
        case 4:
			w=1280; h=960;
        }
        so = new SWFObject('addons/flvplayer.swf','single',''+w,''+h,'7');
        so.addParam("allowfullscreen","true");
        so.addVariable("file","<?php echo $fullurl; ?>");
        so.addVariable("backcolor","0x5D91DA");
        so.addVariable("frontcolor","0xD9E1F6");
        so.addVariable("lightcolor","0xFFFFFF");
        so.write('player');
        if (size >= 0) document.getElementById('size'+size).className = 'nsel';
        size = nsize;
        document.getElementById('size'+size).className = 'sel';
        document.getElementById('size'+size).blur();
      }
      resize(0);
    //-->
    </script>
  </body>
</html>