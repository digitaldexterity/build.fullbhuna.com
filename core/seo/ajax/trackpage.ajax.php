<?php require_once('../../../Connections/aquiescedb.php');  ?>
<?php require("../includes/seo.inc.php"); ?>
<?php if(isset($_GET['pageTitle'])) {
	trackPage(htmlentities($_GET['pageTitle'], ENT_COMPAT, "UTF-8"));
} ?>