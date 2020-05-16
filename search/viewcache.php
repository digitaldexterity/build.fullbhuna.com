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

$isearch_pageTitle = $isearch_lang['viewcache_title'];

/* Display the frame set */

echo <<<EOF

EOF;

$frame = isset($_REQUEST['frame']) ? $_REQUEST['frame'] : '';
$url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';

if ($frame == '')
{
    /* Display the frame set */

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>' . $isearch_pageTitle . '</title>
<meta http-equiv="Content-Type" content="text/html; charset=' . $isearch_config['char_set'] . '">
<meta http-equiv="Content-Language" content="' . $isearch_languageCode . '">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<meta name="robots" content="noindex,nofollow">
</head>
<FRAMESET cols="100%" rows="100,*" FRAMEBORDER="YES" FRAMESPACING="2" BORDER="2">
<FRAME frameborder="0" src="viewcache.php?frame=top&url=' . $url . '">
<FRAME frameborder="0" src="viewcache.php?frame=bottom&url=' . $url . '">
</FRAMESET>

<NOFRAMES>
  <body>
';

include("$isearch_path/inc/header.inc.php");

echo <<<EOF

    <H3>Sorry, you are unable to view cache contents - your browser does not support frames.</H3>

EOF;

}
else if ($frame == 'top')
{
    /* Display the top frame (message saying that this page is cached) */
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>'.$isearch_pageTitle.'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$isearch_config['char_set'].'">
<meta http-equiv="Content-Language" content="'.$isearch_languageCode.'">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<meta name="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="'.$isearch_path.'/style/'.$isearch_config['style_name'].'.css" >
</head>

<body>

';

    include("$isearch_path/inc/header.inc.php");

    echo <<<EOF

<h1 class="isearch">$isearch_pageTitle</h1>\n

EOF;

    $isearch_cachedpage = preg_replace("/(%u)/", "<a href=\"".$url."\">".$url."</a>", $isearch_lang['cachedpage']);

    echo "<h2 class=\"isearch_cachedpage\">".$isearch_cachedpage."</h2>\n";
}
else if ($frame == 'bottom')
{
    /* Display the cached page */

    $result = mysql_query ("SELECT cache, base FROM $isearch_table_urls WHERE  regionID = ".intval($regionID) ." AND url='$url'", $isearch_db);
    if (($result) && ($resultItem = mysql_fetch_object($result)))
    {
        $cache = $resultItem->cache;
        if (!preg_match("/<head>.*<base .*></head>/i", $cache))
        {
            /* Insert a base tag into the head of this cached page */
            $base = $resultItem->base;
            $cache = preg_replace("/</head>", "<base href=\"$base\">\n</head>/i", $cache);
        }
        echo $cache;
    }
}

/* Close the search component */
isearch_close();

include("$isearch_path/inc/footer.inc.php");

echo <<<EOF
</body>
</html>
EOF;

?>
