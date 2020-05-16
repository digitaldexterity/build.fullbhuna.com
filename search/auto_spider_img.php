<?php

require_once("../Connections/aquiescedb.php");
/* Set the following variable to True to debug the automatic spidering */
$isearch_verbose = false;

if (!$isearch_verbose)
{
    // Send a 1x1 pixel transparent GIF
    header("Content-type: image/gif");

    echo "\x47\x49\x46\x38\x39\x61\x04\x00\x04\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x04\x00\x04\x00\x00\x02\x04\x84\x8f\x09\x05\x00\x3b";
}

// Start output buffering
ob_start();

define('IN_ISEARCH', true);
$isearch_path= SITE_ROOT.'search';
require ($isearch_path."/inc/auto_spider.inc.php");

$contents = ob_get_contents();

if (strlen($contents) > 0)
{
    // Write output to a file
     if ($fh = @fopen(SITE_ROOT.'Uploads/_log/auto_spider_img.log', 'w'))
    {
        fwrite($fh, $contents);
        fclose($fh);
    }
}

if ($isearch_verbose)
{
    // Flush the output
    ob_flush();
}

// Discard any output
ob_clean();

?>