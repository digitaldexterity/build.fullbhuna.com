<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "View Image" ?> | View <?php echo $pageTitle; $pageTitle .= htmlentities($_GET['imageURL'], ENT_COMPAT, "UTF-8"); ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../seo/includes/seo.inc.php'); ?>
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
      <p><a href="javascript:history.go(-1);" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p>
      <?php if (isset($keep_original) && $keep_original === false) { ?>
      <p><img src="<?php echo getImageURL(htmlentities($_GET['imageURL']),"large"); ?>" alt="Large image version" class="large" /></p>
      <?php } else if(is_readable(SITE_ROOT.getImageURL($_GET['imageURL']))) { ?>
      <p>Click to <a href="view_original.php?imageURL=<?php echo htmlentities($_GET['imageURL'], ENT_COMPAT, "UTF-8"); ?>" title="Click to see full size version" target="_blank" rel="noopener" >view</a> or <a href="<?php echo getImageURL($_GET['imageURL']); ?>" target="_blank" title="Click to download original version" rel="noopener" >download</a> original full size image</a></p>
      <a href="view_original.php?imageURL=<?php echo htmlentities($_GET['imageURL']); ?>" title="Click to see full size version" target="_blank" class="img" rel="noopener"><img src="<?php echo getImageURL(htmlentities($_GET['imageURL']),"large"); ?>" alt="Large image version" class="large" /></a>
      <?php } ?>
      <p><a href="javascript:history.go(-1);" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Back</a></p>
      </div>
      <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
