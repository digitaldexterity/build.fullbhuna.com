<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>">
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- TemplateBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = ""; echo $pageTitle." | ".$site_name; ?>
</title>
<!--[if IE]><![endif]-->
<!-- TemplateEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- TemplateBeginEditable name="head" -->
<style>
<!--
-->
</style>
<!-- TemplateEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- TemplateBeginEditable name="Body" --><div class="container pageBody">Sorry, this page is currently unavailable.</div><!-- TemplateEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
</html>