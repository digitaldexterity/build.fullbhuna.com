<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
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
?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE articleprefs SET titleheading=%s, membersubmit=%s, safeemail=%s, notfoundURL=%s, addtitle=%s, containerclass=%s, text_siteindex=%s, pageclass=%s, indextitle=%s, indexmetadescription=%s, articlesectionorder=%s, articleshare=%s, indexshowsearch=%s, productlinks=%s, documentlinks=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['titleheading']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['membersubmit']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['safeemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['notfoundURL'], "text"),
                       GetSQLValueString(isset($_POST['addtitle']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['containerclass'], "text"),
                       GetSQLValueString($_POST['text_siteindex'], "text"),
                       GetSQLValueString($_POST['pageclass'], "text"),
                       GetSQLValueString($_POST['indextitle'], "text"),
                       GetSQLValueString($_POST['indexmetadescription'], "text"),
                       GetSQLValueString($_POST['articlesectionorder'], "int"),
                       GetSQLValueString(isset($_POST['articleshare']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['indexshowsearch']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['productlinks']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['documentlinks']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$varRegionID_rsArticlePrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsArticlePrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = sprintf("SELECT * FROM articleprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsArticlePrefs, "int"));
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);

if($totalRows_rsArticlePrefs ==0) {
	duplicateMySQLRecord ("articleprefs", 1,"ID", $regionID);
	
	
	header("location: index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Page Options"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultArticles.css" rel="stylesheet" >
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
         <div class="page articles"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
  <h1><i class="glyphicon glyphicon-file"></i> Page Options</h1><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Manage Pages</a></li>
  </ul></div></nav>
  <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
        <p><label>
          <input name="membersubmit" type="checkbox" id="membersubmit" value="1" <?php if (!(strcmp($row_rsArticlePrefs['membersubmit'],1))) {echo "checked=\"checked\"";} ?> />
          Allow members to submit articles</label></p>
          
          <p><label>
          <input name="articleshare" type="checkbox" id="articleshare" value="1" <?php if (!(strcmp($row_rsArticlePrefs['articleshare'],1))) {echo "checked=\"checked\"";} ?> />
          Show sharing links</label></p>
          
        <p>
          <label>
            <input <?php if (!(strcmp($row_rsArticlePrefs['titleheading'],1))) {echo "checked=\"checked\"";} ?> name="titleheading" type="checkbox" id="titleheading" value="1" />
            Automatically insert title on pages</label>
        </p>
        
        
        <p>
          <label>
            <input <?php if (!(strcmp($row_rsArticlePrefs['addtitle'],1))) {echo "checked=\"checked\"";} ?> name="addtitle" type="checkbox" id="addtitle" value="1" />
            Automatically add heading in editor</label>
        </p>
        
        
        
        
        <p>
          <label>
            <input <?php if (!(strcmp($row_rsArticlePrefs['productlinks'],1))) {echo "checked=\"checked\"";} ?> name="productlinks" type="checkbox" id="productlinks" value="1" />
            Show product links in editor</label>
        </p>
        
        
        <p>
          <label>
            <input <?php if (!(strcmp($row_rsArticlePrefs['documentlinks'],1))) {echo "checked=\"checked\"";} ?> name="documentlinks" type="checkbox" id="documentlinks" value="1" />
            Show document links in editor</label>
        </p>
        
        
        <p>
        <label>
      <input <?php if (!(strcmp($row_rsArticlePrefs['safeemail'],1))) {echo "checked=\"checked\"";} ?> name="safeemail" type="checkbox" id="safeemail" onClick="if(this.checked) { alert('In javascript-enabled browsers, email addresses will be converted so that they can be clicked to email the recipient as well as making them invisible to spam bots.\n\nIn non-javascript browsers, the @ will be replaced with the word \'at\'.'); }" value="1" />
      Make any email addresses clickable and spam safe</label></p>
      
     
      <p>In menu, articles appear <label><input <?php if (!(strcmp($row_rsArticlePrefs['articlesectionorder'],"-1"))) {echo "checked=\"checked\"";} ?> type="radio" value="-1" name="articlesectionorder"> before</label>&nbsp;&nbsp;&nbsp; <label><input <?php if (!(strcmp($row_rsArticlePrefs['articlesectionorder'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" value="1" name="articlesectionorder"> after</label> sections &nbsp;&nbsp;&nbsp; <label><input <?php if (!(strcmp($row_rsArticlePrefs['articlesectionorder'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" value="0" name="articlesectionorder"> Neither</label></p>
      
       <p class="form-inline">
       <label>Add page wrapper with class:
            <input name="pageclass" type="text" class="form-control" id="pageclass" value="<?php echo $row_rsArticlePrefs['pageclass']; ?>" size="50" maxlength="255">
          </label></p>
     
       <p class="form-inline">
       <label>Add content wrapper with class:
            <input name="containerclass" type="text" class="form-control" id="containerclass" value="<?php echo $row_rsArticlePrefs['containerclass']; ?>" size="50" maxlength="255">
          </label></p>
     
   
        <p class="form-inline"><span id="sprytextfield1">
          <label>Not found URL:
            <input name="notfoundURL" type="text" class="form-control" id="notfoundURL" value="<?php echo $row_rsArticlePrefs['notfoundURL']; ?>" size="50" maxlength="255">
          </label>
</span>
        <p class="form-inline"> <label>Site index name: 
            <input name="text_siteindex" type="text" id="text_siteindex" value="<?php echo $row_rsArticlePrefs['text_siteindex']; ?>" size="50" maxlength="255" class="form-control">
          </label>       
        <p>
        
        
        
         <p class="form-inline"> <label>Site index title: 
            <input name="indextitle" type="text" id="indextitle" value="<?php echo $row_rsArticlePrefs['indextitle']; ?>" size="50" maxlength="255" class="form-control">
          </label>       
        <p>
        
        
         <p> <label>Index meta description:<br> 
            <textarea name="indexmetadescription" class="form-control"><?php echo $row_rsArticlePrefs['indexmetadescription']; ?></textarea>
          </label>       
        <p>
          
          
          <p> <label>Site index show search: 
            <input <?php if (!(strcmp($row_rsArticlePrefs['indexshowsearch'],1))) {echo "checked=\"checked\"";} ?> name="indexshowsearch" type="checkbox" id="indexshowsearch" value="1" >
          </label>       
        <p>
        
        
            <button name="save" type="submit" class="btn btn-primary" id="save" >Save changes</button>
        
          <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsArticlePrefs['ID']; ?>" />
        </p>
        <input type="hidden" name="MM_update" value="form1" />
        <a href="export.php">Export Text</a>
  </form>
  <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"(by default returns to section index)", isRequired:false});
  </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticlePrefs);
?>
