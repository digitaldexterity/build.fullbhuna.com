<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

$isearch_path = '..';
define('IN_ISEARCH', true);

require_once("$isearch_path/inc/core.inc.php");
require_once('config.inc.php');

isearch_open(True);

if (isset($isearch_help_lang_config[$isearch_config['lang_name']]))
{
    include $isearch_config['lang_name'] . '.inc.php';
}
else
{
    include './english.inc.php';
}

isearch_close();

?>
