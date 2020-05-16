<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html><html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Search Help"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <div class="container">
<h1>Help</h1>
<p>Searches are normally performed with "may contain" words. A match requires
  any of the words entered to be present on the page.</p>
<p>You can search for pages which contain a specific word by
  prefixing it with a plus (+) sign. Only pages which contain that word will be shown.</p>
<p>You can ignore all pages which contain a specific word by prefixing it with a
  minus (-) sign. Any page that contains that word will not be displayed in
  the search results.</p>
<p>You can search for a specific phrase by enclosing it in double quotes
  (&quot;). Only pages that contain that exact phrase will be shown.</p>
<p>All search words are not case sensitive. Matches will be found in the page
  title, keywords, description and body text.</p>

<table class="table">
  <TR>
    <TH>Search String</TH>
    <TH>Results will contain pages that:</TH>
  </TR>
  <TR>
    <TD>lion tiger</TD>
    <TD>contain either the word <I>lion</I> or the word <I>tiger (or both)</I></TD>
  </TR>
  <TR>
    <TD>+ lion + tiger</TD>
    <TD>contain both the word <I>lion</I> and the word <I>tiger</I></TD>
  </TR>
  <TR>
    <TD>lion + tiger</TD>
    <TD>contain the word <I>tiger</I>. If the page also contains the word <I>lion</I> it may rank higher in the results list.</TD>
  </TR>
  <TR>
    <TD>+ lion - tiger</TD>
    <TD>contain the word lion and not the word <I>tiger</I></TD>
  </TR>
</TABLE>
</div>

<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>