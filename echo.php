<?php

/*
 *
 * Filecharger
 * by Zarel of Novawave
 *
 */

include_once 'config.inc.php';
require_once 'fileman.lib.php';

if ($ftpmode)
	include_once 'ftpsession.lib.php';
else
	include_once 'session.lib.php';

if (fm_contenttype(fext($d)))
{
	header('Content-Type: '.fm_contenttype(fext($d)));
}

echo fm_contents($d);

?>