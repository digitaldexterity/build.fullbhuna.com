<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../includes/userfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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

if (isset($_POST["legalchange"])) { // set all clients to not agree with new terms
	resetUserTermsAgree();

}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET termsconditions=%s, privacypolicy=%s, termsarticleID=%s, privacyarticleID=%s, termsagreetext=%s WHERE ID=%s",
                       GetSQLValueString($_POST['termsconditions'], "text"),
                       GetSQLValueString($_POST['privacypolicy'], "text"),
                       GetSQLValueString($_POST['termsarticleID'], "int"),
                       GetSQLValueString($_POST['privacyarticleID'], "int"),
                       GetSQLValueString($_POST['termsagreetext'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	$update = "UPDATE mailprefs SET  enableGroupEmail =";
	$update .= isset($_POST['enableGroupEmail']) ? 1 : 0;
	$Result1 = mysql_query($update, $aquiescedb) or die(mysql_error());
	
  $updateGoTo = "/admin/";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s LIMIT 1 ", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT enableGroupEmail FROM mailprefs";
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

$varRegionID_rsLinks = 1;
if (isset($_SESSION['regionID'])) {
  $varRegionID_rsLinks = $_SESSION['regionID'];
}
$varRegionID_rsLinks = "-1";
if (isset($regionID)) {
  $varRegionID_rsLinks = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLinks = sprintf("SELECT article.ID, article.longID, article.title, article.sectionID, articlesection.longID AS sectionlongID, articlesection.description FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.statusID = 1 AND article.versionofID IS NULL AND article.regionID = %s ORDER BY articlesection.description, article.title", GetSQLValueString($varRegionID_rsLinks, "int"));
$rsLinks = mysql_query($query_rsLinks, $aquiescedb) or die(mysql_error());
$row_rsLinks = mysql_fetch_assoc($rsLinks);
$totalRows_rsLinks = mysql_num_rows($rsLinks);

?><!doctype html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Legal Text"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryTabbedPanels.js"></script>
<script>
addListener("load", init);
function init() {
	togglePrivacy();
	toggleTerms();
	addListener("change",updateLegal,document.getElementById('termsconditions'));
	addListener("change",updateLegal,document.getElementById('privacypolicy'));
}
function updateLegal() {
	if(!document.getElementById('legalchange').checked) {
	if(confirm("You have updated the legal text. Do you wish to get all users to agree to terms again when they next sign in?")) {
		document.getElementById('legalchange').checked = true;
	}
	}
}

function togglePrivacy() {
	if(document.form1.privacyarticleID.value == "") {
		document.form1.privacypolicy.style.display="inline";
	} else {
		document.form1.privacypolicy.style.display="none";
	}
}

function toggleTerms() {
	if(document.form1.termsarticleID.value == "") {
		document.form1.termsconditions.style.display="inline";
	} else {
		document.form1.termsconditions.style.display="none";
	}
}
</script>
<link href="../../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<link href="../../../css/membersDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page users">
   <?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-user"></i> Legal Text</h1>
      <p class="message alert alert-info" role="alert">These options are only available to Managers (Rank 9).</p>
      <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php }  ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
     <div id="TabbedPanels1" class="TabbedPanels">
       <ul class="TabbedPanelsTabGroup">
         <li class="TabbedPanelsTab termsconditions" tabindex="0" >Terms and Conditions</li>
         <li class="TabbedPanelsTab privacypolicy" tabindex="0" >Privacy Policy</li>
       </ul>
       <div class="TabbedPanelsContentGroup">
         <div class="TabbedPanelsContent"><label for="termsagreetext">Terms agree text:</label><br><textarea name="termsagreetext" id="termsagreetext" cols="100" rows="5" class="form-control"  ><?php echo htmlentities($row_rsPreferences['termsagreetext'], ENT_COMPAT, "UTF-8"); ?></textarea><br><select name="termsarticleID" onChange="toggleTerms();" class="form-control">
           <option value="">Enter terms below or use existing article...</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsLinks['ID'];?>" <?php if($row_rsLinks['ID']==$row_rsPreferences['termsarticleID']) echo "selected"; ?>><?php echo $row_rsLinks['description']." &rsaquo; ".$row_rsLinks['title']; ?></option>
           <?php
} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
  $rows = mysql_num_rows($rsLinks);
  if($rows > 0) {
      mysql_data_seek($rsLinks, 0);
	  $row_rsLinks = mysql_fetch_assoc($rsLinks);
  }
?>
         </select>
         <br>
          <textarea name="termsconditions" cols="100" rows="20" id="termsconditions" class="form-control" ><?php echo $row_rsPreferences['termsconditions']; ?></textarea><br></div>
         <div class="TabbedPanelsContent"><select name="privacyarticleID" onChange="togglePrivacy();" class="form-control">
           <option value="">Enter privacy policy below or use existing article...</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsLinks['ID']?>"  <?php if($row_rsLinks['ID']==$row_rsPreferences['privacyarticleID']) echo "selected"; ?>><?php echo $row_rsLinks['description']." &rsaquo; ".$row_rsLinks['title']; ?></option>
           <?php
} while ($row_rsLinks = mysql_fetch_assoc($rsLinks));
  $rows = mysql_num_rows($rsLinks);
  if($rows > 0) {
      mysql_data_seek($rsLinks, 0);
	  $row_rsLinks = mysql_fetch_assoc($rsLinks);
  }
?>
         </select>
         <br><textarea name="privacypolicy" cols="100" rows="20" id="privacypolicy" ><?php echo $row_rsPreferences['privacypolicy']; ?></textarea><br></div>
       </div>
     </div>
<p><label><input name="legalchange" type="checkbox" id="legalchange" value="1" />
            All users must re-agree to terms on their next log in</label></p>
            <p>HINT: You can use the abbreviation orgname to insert any organisation name you have entered in the main Control Panel.
              
            </p>
<button type="submit" class="btn btn-primary" >Save Changes</button>
            <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsPreferences['ID']; ?>" />
        
      
      <input type="hidden" name="MM_update" value="form1" />
      </form>
   <script>
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
   </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsMailPrefs);

mysql_free_result($rsLinks);

?>