<?php require_once('../../../core/includes/sslcheck.inc.php'); ?>
<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,3,2";
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// upload filed handling code
if ($_FILES["filename"]['size'] > 0) { // upload file check
$uploaddir = SITE_ROOT.DIRECTORY_SEPARATOR.'Uploads'.$aqslash;
$_FILES['filename']['name'] = str_replace(" ", "_" , $_FILES['filename']['name']);
$_FILES['filename']['name'] = $_SESSION['MM_Username'].time()."_".$_FILES['filename']['name']; //prefix timestanp
$uploadfile = $uploaddir .'cv_'.$_FILES['filename']['name'];

//check for errors
if ($_FILES['filename']['type'] != "image/gif" && $_FILES['filename']['type'] != "image/jpeg" && $_FILES['filename']['type'] != "application/vnd.ms-powerpoint" && $_FILES['filename']['type'] != "application/vnd.ms-excel" && $_FILES['filename']['type'] != "application/pdf" && $_FILES['filename']['type'] != "application/msword") {
print "Only MS Word, MS Powerpoint, MS Excel, PDF, JPEG and GIF files are allowed. Please use browser's back button and try again.";
exit;
} else if ($_FILES['filename']['size'] > 2000000) {
print $_FILES['filename']['name'] . " is " . $_FILES['imageURL']['size'] . " bytes in size.\n";
print "Only files up to 2000000 bytes are allowed. Please use browser's back button and try again.";
exit;
} else if (!move_uploaded_file($_FILES['filename']['tmp_name'], $uploadfile)) {
   print "Possible file upload attack!  Here's some debugging info:\n";
   print_r($_FILES);
   exit;
}

// insert image URL into database
$_POST['filename'] = $_FILES['filename']['name'];

}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO cv (cvname, userID, filename, uploaddatetime) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['cvname'], "text"),
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['filename'], "text"),
                       GetSQLValueString($_POST['uploaddatetime'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "../../index.php?uploadcv=true&jobApplyID=" . $_POST['jobApplyID']. "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT * FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsCV = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsCV = (get_magic_quotes_gpc()) ? $_SESSION['MM_Username'] : addslashes($_SESSION['MM_Username']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCV = sprintf("SELECT cv.ID, cv.cvname, cv.filename FROM cv INNER JOIN users ON cv.userID = users.ID WHERE users.username = '%s' AND cv.active = 1", $colname_rsCV);
$rsCV = mysql_query($query_rsCV, $aquiescedb) or die(mysql_error());
$row_rsCV = mysql_fetch_assoc($rsCV);
$totalRows_rsCV = mysql_num_rows($rsCV);
?>
<?php
if (isset($editFormAction)) {
  if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "&GP_upload=true";
  } else {
    $editFormAction .= "?GP_upload=true";
  }
}
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - Upload CV</title>
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
      <h1>Upload My CV</h1>
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" onsubmit="checkFileUpload(this,'doc,rtf,txt',true,100,'','','','','','');showProgressWindow('upload_progress.htm',300,100);return document.MM_returnValue">
        <table width="100%"  border="0" cellpadding="2" cellspacing="0" class="form-table">
          <tr>
            <td><strong>CV Name: </strong></td>
          </tr>
          <tr>
            <td><input name="cvname" type="text"  id="cvname" value="My CV" size="25" maxlength="50" /> 
              <span class="small">(You can change this to a name of your choice)</span> </td>
          </tr>
          <tr>
            <td><strong>Choose CV document file: </strong></td>
          </tr>
          <tr>
            <td><input name="filename" type="file" class="fileinput" id="filename"  size="30" /></td>
          </tr>
          <tr>
            <td><input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <input name="uploaddatetime" type="hidden" id="uploaddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
              <button type="submit" class="button"  onclick="updateFile(this.form);">Upload CV</button>
              </td>
          </tr>
        </table>
          <input type="hidden" name="MM_insert" value="form1" />
          <input name="jobApplyID" type="hidden" id="jobApplyID" value="<?php echo intval($_GET['jobApplyID']); ?>" />
    </form>
      <h2 align="left">How do I upload my CV? </h2>
      <ol>
        <li>
          <div align="left">First you must have a copy of your CV in Microsoft Word format, Rich Text Format or Standard Text Format. Files of these types usually end in .doc, .rtf and .txt respectively.</div>
        </li>
        <li>
          <div align="left">Choose and enter  a name for your CV, then click on the &quot;Browse...&quot; button (above) and navigate to your CV file on your computer and click &quot;Open&quot;.</div>
        </li>
        <li>
          <div align="left">Click on the &quot;Upload&quot; button and WAIT. Once your CV has uploaded to our site you will be informed. <em>This may take a couple of minutes so only press once!</em></div>
        </li>
      </ol>
     
      <p><strong>TIP:</strong> You can upload as many CVs as you like! You can use each with different types of job application - just give each a different name. </p>
      <p>
        <?php if ($totalRows_rsCV > 0) { // Show if recordset not empty ?>
</p>
          <h2 align="left">CVs currently uploaded: </h2>
          <table width="100%" border="0" cellpadding="2" cellspacing="0" class="form-table">
            <?php do { ?>
            <tr>
              <td align="center"><img src="../../../jobs/images/cv_icon.gif" width="11" height="14" style="vertical-align:
middle;" /> <a href="/Uploads/cv_<?php echo $row_rsCV['filename']; ?>" target="_parent"><?php echo $row_rsCV['cvname']; ?></a></td>
            </tr>
            <?php } while ($row_rsCV = mysql_fetch_assoc($rsCV)); ?>
          </table>
          <?php } // Show if recordset not empty ?>
          <h2 align="left">What's This?</h2>
          <p align="left">You can keep a copy of one or more of your CVs on the My  Academy web site. This means that they are available for potential employers to view and help our staff to find jobs that may be suitable for you.</p>
          <p align="left">They will not be accessable to any other users of this site. </p>
          <p align="left">To put a copy of your CV on the site, you need to &quot;Upload&quot; it. </p>
          <h2 align="left">Where can I get help with my CV?</h2>
          <p align="left">We have a section on this web site which offers hints and tips on completeing a CV. <a href="../../../jobs/advice/lookingwork_applytips.php">Click here for more details. </a></p>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsCV);
?>
