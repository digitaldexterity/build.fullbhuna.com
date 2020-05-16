<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!DOCTYPE html>
<html class="" lang="en"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Shop temporarily closed"; echo $pageTitle." | ".$site_name;?></title>
<!-- InstanceEndEditable -->
<!--[if IE]><![endif]-->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body id="OffTemplate" class="bootstrap <?php echo $body_class;  ?>">
<?php require_once('../local/includes/header.inc.php'); ?>
<main id="content"><!-- InstanceBeginEditable name="Body" --><section>
      <div class="container">
      <div align="center"><img src="images/back_soon.jpg" width="446" height="452" alt="Back soon - the shop is temorarily closed. Please check back later." /></div></div></section>
    <!-- InstanceEndEditable --></main>
<?php require_once('../local/includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>