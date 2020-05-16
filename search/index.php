<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../core/includes/generate_tokens.inc.php'); ?>
<?php

if(isset($CSRFtoken)) { // if using basic token security
	if(isset($_REQUEST['s']) || isset($_REQUEST['advanced'])) {
		if(!isset($_REQUEST['CSRFtoken']) || $_REQUEST['CSRFtoken'] != getToken()) {
			
			//die("Security token mismatch. If this problem persists please contact the web adminsitrator"); 
			
		}
	}
}


// PHPLOCKITOPT NOENCODE Search engine code

$isearch_path= SITE_ROOT.'search';
define('IN_ISEARCH', true);

require_once "inc/core.inc.php";
require_once "inc/search.inc.php";

/* Open the search component (read only) */
isearch_open(True);

$partial = isset($_REQUEST['partial']) ? $_REQUEST['partial'] : ($isearch_config['search_partial'] == 1);
$advanced = isset($_REQUEST['advanced']) ? $_REQUEST['advanced'] : 0;

if (isset($_REQUEST['s_all']))
{
    /* Using advanced search form. Build up search string. */
    $advanced = 1;
    $s = '';
    $s_all = trim(htmlentities($_REQUEST['s_all'], ENT_COMPAT, "UTF-8"));
    $s_any = trim(htmlentities($_REQUEST['s_any'], ENT_COMPAT, "UTF-8"));
    if ($s_all != '')
    {
        $s .= '+' . preg_replace('# +#', '+', $s_all);
    }
    if ($s_any != '')
    {
        $s .= ' ' . $s_any;
    }

    $s_exact = trim(htmlentities($_REQUEST['s_exact'], ENT_COMPAT, "UTF-8"));
    if ($s_exact != '')
    {
        $s .= ' ' . '"'.$s_exact.'"';
    }

    if (!$isearch_config['allow_dashes'])
    {
        $s_without = trim(htmlentities($_REQUEST['s_without'], ENT_COMPAT, "UTF-8"));
        if ($s_without != '')
        {
            $s .= '-' . preg_replace('# +#', '-', $s_without);
        }
    }
}
else if (isset($_REQUEST['s']))
{
    $s = htmlentities($_REQUEST['s'] , ENT_COMPAT, "UTF-8");
}
else
{
    $s = '';
}
$s = trim($s);


/* Check the action and set the page title accordingly */
if (isset($_REQUEST['action']))
{
    $isearch_pageTitle = $isearch_lang['results_title'];
}
else
{
    $isearch_pageTitle = $isearch_lang['search_title'];

}

$s = str_replace("_"," ",$s); // replace ubderscores from URL based SEO
?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $s." ".$isearch_pageTitle; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->  <meta name="robots" content="noindex,nofollow" />
<link href="css/defaultSearch.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="container pageBody"><h1><?php echo $isearch_pageTitle; ?></h1><?php 


if ($s == '')
{
    /* We do not have a search string to process */
}
else if (preg_match('#[\\x0d\\x0a]#', $s))
{
    echo '
<p>Probable SPAM hacking attempt detected.
';
}
else
{
    /* Process the search form data */
    if ($isearch_config['char_set'] == 'utf-8')
    {
        $s = utf8_decode($s);
    }

    /* Clean the search strings */
    $s = isearch_cleanSearchString($s);

    if (isset($_REQUEST['groups']))
    {
        $group = '';
        foreach ($_REQUEST['groups'] as $g)
        {
            if ($g == 'isearch_all')
            {
                $group = '';
                break;
            }

            if ($group != '')
            {
                $group .= ',';
            }
            $group .= $g;
        }
    }
    else if (isset($_REQUEST['group']))
    {
        $group = ($_REQUEST['group'] == 'isearch_all') ? '' : $_REQUEST['group'];
    }
    else
    {
        /* Search all groups */
        $group = '';
    }

    if ($isearch_config['soundex'] == 1)
    {
        /* Always use soundex */
        $numResults = isearch_find($s, $group, $partial, True);

        if (($numResults == 0) && (!$partial) && ($isearch_config['search_partial'] == 2))
        {
            $numResults = isearch_find($s, $group, true, true);
        }
    }
    else
    {
        /* Try normal match */
        $numResults = isearch_find($s, $group, $partial, false);
		

        if (($numResults == 0) && (!$partial) && ($isearch_config['search_partial'] == 2))
        {
            /* Try partial match */
            $numResults = isearch_find($s, $group, True, False);
        }

        if (($numResults == 0) && ($isearch_config['soundex'] == 2))
        {
            /* Try soundex match */
            $numResults = isearch_find($s, $group, $partial, True);
        }
    }

    isearch_showResults(isset($_REQUEST['page']) ? $_REQUEST['page'] : 1);
}

/* Display the search form */
require_once "$isearch_path/inc/form_internal.inc.php";




?>
<p>Try other <a href="searchterms.php">search terms</a> used on this site</p></div>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html><?php /* Close the search component */
isearch_close();
?>