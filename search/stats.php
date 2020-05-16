
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

/* Open the search component (read only) */
isearch_open(True);

/* Check the action and set the page title accordingly */
if (isset($isearch_lang['stats_title']))
{
    $isearch_pageTitle = $isearch_lang['stats_title'];
}
else
{
    $isearch_pageTitle = $isearch_lang['results_title'];
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>' . $isearch_pageTitle . '</title>
<meta http-equiv="Content-Type" content="text/html; charset=' . $isearch_config['char_set'] . '">
<meta http-equiv="Content-Language" content="' . $isearch_languageCode . '">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<LINK REL=StyleSheet href="' . $isearch_path . '/style/' . $isearch_config['style_name'] . '.css" >
</head>

<body onLoad="self.focus();">

';

include("$isearch_path/inc/header.inc.php");

echo <<<EOF

<H1 class="isearch">$isearch_pageTitle</H1>\n

EOF;

if ($isearch_config['total_searches'])
{
    $result = mysql_query("SELECT count(search_term) AS num FROM $isearch_table_search_log WHERE regionID = ".intval($regionID) ."");
    if (!$result)
    {
        echo "<p>ERROR: MySQL error : " . mysql_error();
    }
    else if ($item = mysql_fetch_object($result))
    {
        echo '<p><TABLE WIDTH="100%" BORDER=0 CELLPADDING=0 CLASS="isearch_stats">
<CAPTION>Total Searches</CAPTION>
<TR><TH>Total Searches</TH></TR>
<TR><TD ALIGN="center">' . $item->num . '</TD></TR>
</TABLE></P>
';
    }
}

if ($isearch_config['top_searches'] > 0)
{
    $result = mysql_query("SELECT DISTINCT search_term, matches, count(search_term) AS num FROM $isearch_table_search_log WHERE  regionID = ".intval($regionID) ." AND matches>0 GROUP BY search_term ORDER BY num desc LIMIT " . $isearch_config['top_searches']);
    if (!$result)
    {
        echo "<p>ERROR: MySQL error : " . mysql_error();
    }
    else
    {
        echo '<p><TABLE WIDTH="100%" BORDER=0 CELLPADDING=0 CLASS="isearch_stats">' . "\n";
        echo '<CAPTION>' . str_replace("%d", $isearch_config['top_searches'], $isearch_lang['topsearches']) . '</CAPTION>' . "\n";
        echo '<TR><TH>Searches</TH><TH WIDTH="90%">' . $isearch_lang['searchterm'] . '</TH><TH WIDTH="10%">' . $isearch_lang['matches'] . '</TH>' . "\n";
        while ($item = mysql_fetch_object($result))
        {
            echo "<TR>";
            echo "<TD>$item->num</TD><TD><A HREF=\"index.php?s=$item->search_term\">$item->search_term</TD><TD>$item->matches</TD>";
            echo "</TR>\n";
        }
        echo "</TABLE></P>\n";
    }
}

if ($isearch_config['last_searches'] > 0)
{
    $result = mysql_query("SELECT DISTINCT search_term, matches FROM $isearch_table_search_log WHERE regionID = ".intval($regionID) ." ORDER BY time desc LIMIT " . $isearch_config['last_searches']);
    if (!$result)
    {
        echo "<p>ERROR: MySQL error : " . mysql_error();
    }
    else
    {
        echo '<p><TABLE WIDTH="100%" BORDER=0 CELLPADDING=0 CLASS="isearch_stats">' . "\n";
        echo '<CAPTION>' . str_replace("%d", $isearch_config['last_searches'], $isearch_lang['lastsearches']) . '</CAPTION>' . "\n";
        echo '<TR><TH WIDTH="90%">' . $isearch_lang['searchterm'] . '</TH><TH WIDTH="10%">' . $isearch_lang['matches'] . '</TH>' . "\n";
        while ($item = mysql_fetch_object($result))
        {
            echo "<TR>";
            echo "<TD><A HREF=\"index.php?s=$item->search_term\">$item->search_term</TD><TD>$item->matches</TD>";
            echo "</TR>\n";
        }
        echo "</TABLE></P>\n";
    }
}

/* Display the search form */
$s = '';
$partial=False;
$advanced=False;
require_once "$isearch_path/inc/form_internal.inc.php";

/* Close the search component */
isearch_close();

include("$isearch_path/inc/footer.inc.php");

echo <<<EOF
</body>
</html>
EOF;

?>
