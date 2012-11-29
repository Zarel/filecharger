<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 *
 */

/*

Add:

DirectoryIndex index.html index.php /fileman/dirindex.php

To .htaccess, to use Fileman's directory indexer.

*/

include 'dirlist.php';
$rq = urldecode($_SERVER['REQUEST_URI']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
    <title><?php echo $rq; ?></title>
    <style type="text/css"><!--
      body
      {
        font-family: Verdana, sans-serif;
        font-size: 12pt;
        padding: 1em;
      }
      h1
      {
        font-family: Verdana, sans-serif;
        font-size: 18pt;
        margin: 0 0 1em 0;
        padding: 0;
      }
      div.txt
      {
        font-size: 10pt;
        padding: 12px;
        max-width: 8in;
        margin-bottom: 20px;
      }
      div.txt div.sub
      {
        border: 1px solid #888888;
        background-color: #FFFFFF;
        padding: 12px;
      }
      div.txt div.fsub
      {
        border: 1px solid #888888;
        background-color: #FFFFFF;
        padding: 12px;
      }
      div.txt pre,
      div.txt .pre
      {
        /*width: 10px;*/
        font-family: "Lucida Console", monospace;
      }
    --></style>
  </head>
  <body>
    <h1>Directory: <?php echo $rq ?></h1>
<?php
if (file_exists('..'.$rq.'readme.txt'))
{
?>
    <div class="txt"><div class="fsub">
      <div class="pre"><?php echo str_replace(array('  ',"\n","\t"),array('&nbsp;',"<br />\n",'&nbsp;&nbsp;&nbsp;&nbsp;'),htmlspecialchars(file_get_contents('..'.$rq.'readme.txt'))); ?></div>
    </div></div>
<?php
}
?>
    <?php dirlist('..'.$rq,TRUE,'','',substr($rq,1)); ?>
  </body>
</html>