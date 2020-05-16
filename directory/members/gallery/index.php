<?php require_once('../../../Connections/aquiescedb.php'); 
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$colname_rsThisDirectory = "-1";
if (isset($_GET['directoryID'])) {
  $colname_rsThisDirectory = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisDirectory = sprintf("SELECT ID, name FROM directory WHERE ID = %s", GetSQLValueString($colname_rsThisDirectory, "int"));
$rsThisDirectory = mysql_query($query_rsThisDirectory, $aquiescedb) or die(mysql_error());
$row_rsThisDirectory = mysql_fetch_assoc($rsThisDirectory);
$totalRows_rsThisDirectory = mysql_num_rows($rsThisDirectory);

$varDirectoryID_rsGalleries = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsGalleries = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGalleries = sprintf("SELECT photocategories.ID, photocategories.categoryname FROM photocategories LEFT JOIN directorygallery ON (photocategories.ID = directorygallery.galleryID) WHERE directorygallery.directoryID = %s", GetSQLValueString($varDirectoryID_rsGalleries, "int"));
$rsGalleries = mysql_query($query_rsGalleries, $aquiescedb) or die(mysql_error());
$row_rsGalleries = mysql_fetch_assoc($rsGalleries);
$totalRows_rsGalleries = mysql_num_rows($rsGalleries);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Galleries for ". $row_rsThisDirectory['name'];  echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <h1 class="directoryheader">Galleries for <?php echo $row_rsThisDirectory['name']; ?></h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="../../../photos/members/galleries/add_gallery.php?directoryID=<?php echo $row_rsThisDirectory['ID']; ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add a gallery</a></li>
    </ul></div></nav>
<?php if ($totalRows_rsGalleries == 0) { // Show if recordset empty ?>
  <p>You don't have any photo galleries linked to <?php echo $row_rsThisDirectory['name']; ?>.</p>
  <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsGalleries > 0) { // Show if recordset not empty ?>
      <table border="0" cellpadding="2" cellspacing="0" class="form-table">
       
        <?php do { ?>
          <tr>
            
            <td><?php echo $row_rsGalleries['categoryname']; ?></td><td><a href="../../../photos/gallery/index.php?galleryID=<?php echo $row_rsGalleries['ID']; ?>" class="link_view">View</a></td>
          </tr>
          <?php } while ($row_rsGalleries = mysql_fetch_assoc($rsGalleries)); ?>
      </table>
      <?php } // Show if recordset not empty ?>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisDirectory);

mysql_free_result($rsGalleries);
?>
