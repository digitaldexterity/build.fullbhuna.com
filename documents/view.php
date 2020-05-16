<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../members/includes/userfunctions.inc.php'); ?>
<?php



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

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID,  users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsDocument = "-1";
if (isset($_GET['documentID'])) {
  $colname_rsDocument = $_GET['documentID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocument = sprintf("SELECT documents.ID, documents.documentname, documents.documentcategoryID, documents.active, documents.`lock`, documents.userID, documents.directoryID, documents.filename, documents.type, documentcategory.accessID, documentcategory.groupreadID,documentcategory.active AS categoryactive, uploads.filesize FROM documents LEFT JOIN documentcategory ON (documents.documentcategoryID = documentcategory.ID)LEFT JOIN uploads ON (documents.uploadID = uploads.ID) WHERE documents.ID = %s", GetSQLValueString($colname_rsDocument, "int"));
$rsDocument = mysql_query($query_rsDocument, $aquiescedb) or die(mysql_error());
$row_rsDocument = mysql_fetch_assoc($rsDocument);
$totalRows_rsDocument = mysql_num_rows($rsDocument);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocumentPrefs = "SELECT * FROM documentprefs";
$rsDocumentPrefs = mysql_query($query_rsDocumentPrefs, $aquiescedb) or die(mysql_error());
$row_rsDocumentPrefs = mysql_fetch_assoc($rsDocumentPrefs);
$totalRows_rsDocumentPrefs = mysql_num_rows($rsDocumentPrefs);

 // check access rights
if(thisUserHasAccess($row_rsDocument['accessID'], $row_rsDocument['groupreadID'],$row_rsLoggedIn['ID'])) {
if($row_rsDocument['active'] ==1) { // is available
		
			
			  $pageTitle = $row_rsDocument['documentname'];
			  require_once('../core/seo/includes/seo.inc.php'); trackPage(@$pageTitle); // add to visitor stats
			  $filePath = SITE_ROOT."Uploads/".$row_rsDocument['filename'];
			  if(is_readable($filePath)) { // can access
			  if(isset($_GET['download'])) { // force download
				header("location:download.php?filename=".urlencode("/Uploads/".$row_rsDocument['filename'])); exit;				      
			  } else { // open in browser
			  // the sever cannot get the # fragment of pdf page so use a get variable, which we'll convert here...
			$row_rsDocument['filename'] .= isset($_GET['page']) ? "#page=".intval($_GET['page']) : "";
			 if($row_rsDocumentPrefs['opennewwindow']==1) { // open in new window
				 $size = isset($row_rsDocument['filesize']) ? ($row_rsDocument['filesize']/1000) : "";
				 if ($size > 999) {
					 $size = " (Large file: ".number_format(($size/1000),1)."MB)";
				 } else if($size > 0) {
					 $size = " (".number_format(($size),1)."KB)";
				 }
				 $html = "<"."!"."doctype";
				 $html .= " html>";
				 $html .= "<html><head><meta http-equiv='refresh' content='0;URL=/Uploads/".$row_rsDocument['filename']."' /><title>".$row_rsDocument['documentname']."</title><link href='/documents/css/documentsDefault.css' rel='stylesheet' type='text/css'></head><body class=\"loading_document\">";
				// $html .="<h1>Currently Downloading:</h1><h2><a href='/Uploads/".$row_rsDocument['filename']."' >".$row_rsDocument['documentname'].$size."</a></h2><progress></progress><p>Depending on your browser's settings it will open shortly in this window or appear in your downloads folder.</p>";
				  echo $html;
				/*  if($row_rsDocumentPrefs['opennewwindow']!=1) { echo"<p>&laquo;&nbsp;<a href=\"/documents/index.php?categoryID=".intval($row_rsDocument['documentcategoryID'])."\">Back to documents</a></p>"; } 
				  echo "<div id=\"downloadadvice\"><h4>If document does not download:</h4>";
				  echo "<ul><li>If a yellow bar appears at the top of your screen, click on it to allow the document to be downloaded to your computer</li>";
				  echo "<li>Some larger documents over 5Mb may take a few minutes to download</li>";
				   echo "<li>If you don't get a toolbar allowing you to print or save an Office document, right-click the top of the document and check to show 'Standard' toolbar</li></ul></div>";
				  echo "<script> if(window.opener) document.write('<p><a href=\"javascript:void(0);\" onclick=\"window.close();\">Close this window</a></p>'); </script>";*/
			  	echo "</body></html>";
			 } else {
				 header("location: /Uploads/".$row_rsDocument['filename']);
			 }
			  } // end open in browser
			 exit;
			  } // end is readable
			  else { // can't read document
			  $error = "<h2>Sorry, the document cannot be found. It may have been deleted.</h2>";
			  
			if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10 ) { $error .= "\n".SITE_ROOT."Uploads/".$row_rsDocument['filename']; }
			  }
} else {
	$error = "<h2>Sorry, this document is no longer available.</h2>";
}
			  } //end OK to access 
			  else { // no access
			  
			  if(isset($_SESSION['MM_Username'])) { // already logged in
			  $error = "<h2>Sorry, you do not have access to this document.</h2><p>You need to log in with the correct access privileges.</p>";
			  }else {
			   $error = "<h2>This document has restricted access.</h2><p>You can <a href=\"/login/index.php?accesscheck=".urlencode($_SERVER['REQUEST_URI'])."\">log in or sign up here</a> with the correct privilges to view.</p>";
			  }
			  }
			
			
   
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "No access"; echo " - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="container">
    <h1>Document Access</h1>
    
  
    <?php echo $error; ?></div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>

<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDocument);

mysql_free_result($rsDocumentPrefs);
?>
