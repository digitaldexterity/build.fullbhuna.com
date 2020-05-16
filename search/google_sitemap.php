<?php

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

// PHPLOCKITOPT NOENCODE

$isearch_path = '.';
define('IN_ISEARCH', true);

require_once("$isearch_path/inc/core.inc.php");
require_once("$isearch_path/inc/search.inc.php");

/* Open the search component (read only) */
isearch_open(True);

/* Show flat site map (ordered by url) */
$result = mysql_query("SELECT url, lastmod, changefreq, priority FROM $isearch_table_urls ORDER BY url", $isearch_ro_db);
if (!$result)
{
    echo "<p>MySQL error : " . mysql_error() . ' File: ' . __FILE__ . ', Line:' . __LINE__ . "</P>\n";
}
else if (mysql_num_rows($result) > 0)
{
    echo '<?xml version="1.0" encoding="UTF-8"?>

<!-- Powered by iSearch2                     -->
<!-- http://www.iSearchTheNet.com/isearch    -->

<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
';
    while ($item = mysql_fetch_object($result))
    {
        echo '
  <url>
    <loc>'.$item->url.'</loc>
';
        if ($item->lastmod > 0)
        {
            echo '    <lastmod>'.date('Y-m-d', $item->lastmod).'T'.date('H:i:s', $item->lastmod).'+00:00</lastmod>
';
        }
        if ($item->changefreq != '')
        {
            echo '    <changefreq>'.$item->changefreq.'</changefreq>
';
        }
        if ($item->priority >= 0)
        {
            echo '    <priority>'.$item->priority.'</priority>
';
        }
        echo '  </url>
';
    }
    echo '
</urlset>
';
}

?>
