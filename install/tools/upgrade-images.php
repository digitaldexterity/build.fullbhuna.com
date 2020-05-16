<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
$MM_donotCheckaccess = "false";

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
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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
?><?php
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocs = "SELECT ID, filename FROM documents WHERE filename IS NOT NULL";
$rsDocs = mysql_query($query_rsDocs, $aquiescedb) or die(mysql_error());
$row_rsDocs = mysql_fetch_assoc($rsDocs);
$totalRows_rsDocs = mysql_num_rows($rsDocs);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotos = "SELECT ID, imageURL FROM photos WHERE imageURL IS NOT NULL";
$rsPhotos = mysql_query($query_rsPhotos, $aquiescedb) or die(mysql_error());
$row_rsPhotos = mysql_fetch_assoc($rsPhotos);
$totalRows_rsPhotos = mysql_num_rows($rsPhotos);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsImages = "SELECT ID, imageURL FROM news WHERE imageURL IS NOT NULL";
$rsNewsImages = mysql_query($query_rsNewsImages, $aquiescedb) or die(mysql_error());
$row_rsNewsImages = mysql_fetch_assoc($rsNewsImages);
$totalRows_rsNewsImages = mysql_num_rows($rsNewsImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticleImages = "SELECT ID, imageURL FROM article WHERE imageURL IS NOT NULL";
$rsArticleImages = mysql_query($query_rsArticleImages, $aquiescedb) or die(mysql_error());
$row_rsArticleImages = mysql_fetch_assoc($rsArticleImages);
$totalRows_rsArticleImages = mysql_num_rows($rsArticleImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserImages = "SELECT ID, imageURL FROM users WHERE imageURL IS NOT NULL";
$rsUserImages = mysql_query($query_rsUserImages, $aquiescedb) or die(mysql_error());
$row_rsUserImages = mysql_fetch_assoc($rsUserImages);
$totalRows_rsUserImages = mysql_num_rows($rsUserImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAckImages = "SELECT ID, imageURL FROM acknowledgments WHERE imageURL IS NOT NULL";
$rsAckImages = mysql_query($query_rsAckImages, $aquiescedb) or die(mysql_error());
$row_rsAckImages = mysql_fetch_assoc($rsAckImages);
$totalRows_rsAckImages = mysql_num_rows($rsAckImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsServiceImages = "SELECT ID, imageURL FROM service WHERE imageURL IS NOT NULL";
$rsServiceImages = mysql_query($query_rsServiceImages, $aquiescedb) or die(mysql_error());
$row_rsServiceImages = mysql_fetch_assoc($rsServiceImages);
$totalRows_rsServiceImages = mysql_num_rows($rsServiceImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBlogImages = "SELECT ID, imageURL FROM blogentry WHERE imageURL IS NOT NULL";
$rsBlogImages = mysql_query($query_rsBlogImages, $aquiescedb) or die(mysql_error());
$row_rsBlogImages = mysql_fetch_assoc($rsBlogImages);
$totalRows_rsBlogImages = mysql_num_rows($rsBlogImages);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCVdoc = "SELECT ID, filename FROM cv WHERE filename IS NOT NULL";
$rsCVdoc = mysql_query($query_rsCVdoc, $aquiescedb) or die(mysql_error());
$row_rsCVdoc = mysql_fetch_assoc($rsCVdoc);
$totalRows_rsCVdoc = mysql_num_rows($rsCVdoc);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumComImage = "SELECT ID, imageURL FROM forumcomment WHERE imageURL IS NOT NULL";
$rsForumComImage = mysql_query($query_rsForumComImage, $aquiescedb) or die(mysql_error());
$row_rsForumComImage = mysql_fetch_assoc($rsForumComImage);
$totalRows_rsForumComImage = mysql_num_rows($rsForumComImage);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForumTopImage = "SELECT ID, imageURL FROM forumtopic WHERE imageURL IS NOT NULL";
$rsForumTopImage = mysql_query($query_rsForumTopImage, $aquiescedb) or die(mysql_error());
$row_rsForumTopImage = mysql_fetch_assoc($rsForumTopImage);
$totalRows_rsForumTopImage = mysql_num_rows($rsForumTopImage);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDirectoryImage = "SELECT ID, imageURL FROM directory WHERE imageURL IS NOT NULL";
$rsDirectoryImage = mysql_query($query_rsDirectoryImage, $aquiescedb) or die(mysql_error());
$row_rsDirectoryImage = mysql_fetch_assoc($rsDirectoryImage);
$totalRows_rsDirectoryImage = mysql_num_rows($rsDirectoryImage);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductImage = "SELECT ID, imageURL FROM product WHERE imageURL IS NOT NULL";
$rsProductImage = mysql_query($query_rsProductImage, $aquiescedb) or die(mysql_error());
$row_rsProductImage = mysql_fetch_assoc($rsProductImage);
$totalRows_rsProductImage = mysql_num_rows($rsProductImage);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductCatImage = "SELECT ID, imageURL FROM productcategory WHERE imageURL IS NOT NULL";
$rsProductCatImage = mysql_query($query_rsProductCatImage, $aquiescedb) or die(mysql_error());
$row_rsProductCatImage = mysql_fetch_assoc($rsProductCatImage);
$totalRows_rsProductCatImage = mysql_num_rows($rsProductCatImage);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsHomePageImage = "SELECT ID, homepageimageURL FROM preferences WHERE homepageimageURL IS NOT NULL";
$rsHomePageImage = mysql_query($query_rsHomePageImage, $aquiescedb) or die(mysql_error());
$row_rsHomePageImage = mysql_fetch_assoc($rsHomePageImage);
$totalRows_rsHomePageImage = mysql_num_rows($rsHomePageImage);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupEmail = "SELECT ID, imageURL FROM groupemail WHERE imageURL IS NOT NULL";
$rsGroupEmail = mysql_query($query_rsGroupEmail, $aquiescedb) or die(mysql_error());
$row_rsGroupEmail = mysql_fetch_assoc($rsGroupEmail);
$totalRows_rsGroupEmail = mysql_num_rows($rsGroupEmail);
?><!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Move uploaded files to version 2.</h1>
<p>Home Page</p>


<?php if ($totalRows_rsHomePageImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td>ID</td>
      <td>homepageimageURL</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsHomePageImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsHomePageImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsHomePageImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsHomePageImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsHomePageImage['imageURL']); ?></td>
        <td><?php echo $row_rsHomePageImage['homepageimageURL']; ?></td>
      </tr>
      <?php } while ($row_rsHomePageImage = mysql_fetch_assoc($rsHomePageImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Group Email</p>

<?php if ($totalRows_rsGroupEmail > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td>ID</td>
      <td>imageURL</td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsGroupEmail['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsGroupEmail['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsGroupEmail['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsGroupEmail['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsGroupEmail['imageURL']); ?></td>
        <td><?php echo $row_rsGroupEmail['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsGroupEmail = mysql_fetch_assoc($rsGroupEmail)); ?>
</table>
  <?php } // Show if recordset not empty ?>
<p>Photos</p>


<?php if ($totalRows_rsPhotos > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsPhotos['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsPhotos['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsPhotos['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsPhotos['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsPhotos['imageURL']); ?></td>
        <td><?php echo $row_rsPhotos['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsPhotos = mysql_fetch_assoc($rsPhotos)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>News</p>


<?php if ($totalRows_rsNewsImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsNewsImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsNewsImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsNewsImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsNewsImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsNewsImages['imageURL']); ?></td>
        <td><?php echo $row_rsNewsImages['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsNewsImages = mysql_fetch_assoc($rsNewsImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Articles</p>


<?php if ($totalRows_rsArticleImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsArticleImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsArticleImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsArticleImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsArticleImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsArticleImages['imageURL']); ?></td>
        <td><?php echo $row_rsArticleImages['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsArticleImages = mysql_fetch_assoc($rsArticleImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Users</p>


<?php if ($totalRows_rsUserImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsUserImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsUserImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsUserImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsUserImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsUserImages['imageURL']); ?></td>
        <td><?php echo $row_rsUserImages['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsUserImages = mysql_fetch_assoc($rsUserImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Acknowledgements</p>


<?php if ($totalRows_rsAckImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsAckImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsAckImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsAckImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsAckImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsAckImages['imageURL']); ?></td>
        <td><?php echo $row_rsAckImages['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsAckImages = mysql_fetch_assoc($rsAckImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Services</p>


<?php if ($totalRows_rsServiceImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsServiceImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsServiceImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsServiceImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsServiceImages['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsServiceImages['imageURL']); ?></td>
        <td><?php echo $row_rsServiceImages['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsServiceImages = mysql_fetch_assoc($rsServiceImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Blogs</p>


<?php if ($totalRows_rsBlogImages > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL']); ?></td>
        <td><?php echo $row_rsForumTopImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsBlogImages = mysql_fetch_assoc($rsBlogImages)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Forum Topic</p>


<?php if ($totalRows_rsForumTopImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsForumTopImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsForumTopImage['imageURL']); ?></td>
        <td><?php echo $row_rsForumTopImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsForumTopImage = mysql_fetch_assoc($rsForumTopImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Forum Comments</p>


<?php if ($totalRows_rsForumComImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsForumComImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsForumComImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsForumComImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsForumComImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsForumComImage['imageURL']); ?></td>
        <td><?php echo $row_rsForumComImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsForumComImage = mysql_fetch_assoc($rsForumComImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Directory</p>


<?php if ($totalRows_rsDirectoryImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsDirectoryImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsDirectoryImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsDirectoryImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsDirectoryImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsDirectoryImage['imageURL']); ?></td>
        <td><?php echo $row_rsDirectoryImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsDirectoryImage = mysql_fetch_assoc($rsDirectoryImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Departments</p>


<?php if ($totalRows_rsDepImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsDepImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsDepImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsDepImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsDepImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsDepImage['imageURL']); ?></td>
        <td><?php echo $row_rsDepImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsDepImage = mysql_fetch_assoc($rsDepImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Product Category</p>


<?php if ($totalRows_rsProductCatImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsProductCatImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsProductCatImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsProductCatImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsProductCatImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsProductCatImage['imageURL']); ?></td>
        <td><?php echo $row_rsProductCatImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsProductCatImage = mysql_fetch_assoc($rsProductCatImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>Products</p>


<?php if ($totalRows_rsProductImage > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'t_'.$row_rsProductImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'display'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'m_'.$row_rsProductImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'large'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'l_'.$row_rsProductImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'square'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'s_'.$row_rsProductImage['imageURL']);
	  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'originals'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsProductImage['imageURL']); ?></td>
        <td><?php echo $row_rsProductImage['imageURL']; ?></td>
      </tr>
      <?php } while ($row_rsProductImage = mysql_fetch_assoc($rsProductImage)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<p>CV</p>
<?php if ($totalRows_rsCVdoc > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
      <tr>
        <td><?php echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'cv'.DIRECTORY_SEPARATOR.$row_rsCVdoc['filename'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsCVdoc['filename']); ?></td>
        <td><?php echo $row_rsCVdoc['filename']; ?></td>
      </tr>
      <?php } while ($row_rsCVdoc = mysql_fetch_assoc($rsCVdoc)); ?>
</table> <?php } // Show if recordset not empty ?>
  <p>Docs </p>

  <?php if ($totalRows_rsDocs > 0) { // Show if recordset not empty ?>
  <table border="1">
    <tr>
      <td><strong>Moved</strong></td>
      <td><strong>File name</strong></td>
    </tr>
    <?php do { ?>
    <tr>
      <td><strong></strong>
          <?php  echo copy(SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.'Documents'.DIRECTORY_SEPARATOR.$row_rsDocs['filename'],SITE_ROOT.'Uploads'.DIRECTORY_SEPARATOR.$row_rsDocs['filename']); ?></td>
      <td><?php echo $row_rsDocs['filename']; ?></td>
    </tr>
    <?php } while ($row_rsDocs = mysql_fetch_assoc($rsDocs)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDocs);

mysql_free_result($rsPhotos);

mysql_free_result($rsNewsImages);

mysql_free_result($rsArticleImages);

mysql_free_result($rsUserImages);

mysql_free_result($rsAckImages);

mysql_free_result($rsServiceImages);

mysql_free_result($rsBlogImages);

mysql_free_result($rsCVdoc);

mysql_free_result($rsForumComImage);

mysql_free_result($rsForumTopImage);

mysql_free_result($rsDirectoryImage);

mysql_free_result($rsDepImage);

mysql_free_result($rsProductImage);

mysql_free_result($rsProductCatImage);

mysql_free_result($rsHomePageImage);

mysql_free_result($rsGroupEmail);
?>
