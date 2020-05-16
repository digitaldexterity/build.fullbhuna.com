<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/upload.inc.php'); ?><?php require_once('../includes/galleryfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,7,8,9,10";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

$dir = isset($_POST['directory']) ? $_POST['directory'] : "";
$files =  getUploads(UPLOAD_ROOT.$dir,$image_sizes,"","",0,"",array("gif","png","jpeg","jpg"),"longest");
if($files) { 

	//echo $files; die(); 
	$galleryID =  isset($_GET['galleryID']) ? intval($_GET['galleryID']) : 0;
	header("location: review.php?galleryID=".$galleryID);
	exit;


}

?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Pictures"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/dropzone/dropzone.js"></script>
<link rel="stylesheet" href="/core/scripts/dropzone/dropzone.css">
<script>
$(document).ready(function(e) {
  /* Dropzone requires no configuration whatsoever - just the js file - but added below to redirect when finished */  

Dropzone.options.dropzone1 = {
  init: function() {
    this.on("queuecomplete", function(file) { 
	document.location.href = "review.php?galleryID=<?php echo isset($_GET['galleryID']) ? intval($_GET['galleryID']) : 0; ?>";
	 });
  }
};
});
</script>

<link href="../css/defaultGallery.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="container pageBody addphotos">
    <h1>Add your pictures...</h1>
  <form action="add_photos.php" class="dropzone" name = "dropzone1" id="dropzone1" enctype="multipart/form-data" method="post">
<div class="fallback">
    <input name="file" type="file" multiple ><button type="submit" class="btn btn-primary" >Upload</button>
    <input type="hidden" name="nodropzone" value="true">
  </div><input name="directory" type="hidden" value="users/<?php echo $_SESSION['MM_Username']; ?>/<?php echo session_id(); ?>/"></form>
  
  </div><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>