<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../includes/newsfunctions.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?><?php require_once('../../core/tags/includes/tags.inc.php'); ?>
<?php $_GET['sectionID'] = isset($_GET['sectionID']) ? $_GET['sectionID'] : 1; 
$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "8,9,10";
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











if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO news (longID, title, displayfrom, eventdatetime, status, postedbyID, posteddatetime, regionID, sectionID) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['displayfrom'], "date"),
                       GetSQLValueString($_POST['eventdatetime'], "date"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString($_POST['postedbyID'], "int"),
                       GetSQLValueString($_POST['posteddatetime'], "date"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$newsID = mysql_insert_id();
	
	$longID = createURLname($_POST['longID'], $_POST['title'], "-",  "news", $newsID);
	if($longID!=$_POST['longID']) {
		$update = "UPDATE news SET longID = ".GetSQLValueString($longID, "text")." WHERE ID = ".$newsID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	// insert template values for section if any
	 $select = "SELECT defaultsummary, defaultbody FROM newssection WHERE ID = ".GetSQLValueString($_POST['sectionID'], "int");
	 $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	 $newssection = mysql_fetch_assoc($result);
	 $update = "UPDATE news SET summary = ".GetSQLValueString($newssection['defaultsummary'], "text").", body = ".GetSQLValueString($newssection['defaultbody'], "text")." WHERE ID = ".$newsID;
	 mysql_query($update, $aquiescedb) or die(mysql_error());
	
	// add default tags
	$select = "SELECT tag.ID FROM tag WHERE (regionID = 0 OR regionID = ".intval($regionID).") AND taggeddefault = 1";
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
	 while($tag = mysql_fetch_assoc($result)) {
		 addTag($tag['ID'], 0, 0, $newsID, $_POST['postedbyID']);
	 }
	}
	
	
		$insertGoTo = "update_news.php?newsID=".$newsID."&newpost=true";

	
  header(sprintf("Location: %s", $insertGoTo));exit;
}



$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, email FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);



$varRegionID_rsSections = "1";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
$varUserGroup_rsSections = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsSections = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT * FROM newssection WHERE statusID = 1 AND (%s >=9 OR newssection.regionID = 0 OR newssection.regionID = %s) ORDER BY newssection.ordernum, newssection.ID", GetSQLValueString($varUserGroup_rsSections, "int"),GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);


$colname_rsThisSection = "1";
if (isset($_GET['sectionID'])) {
  $colname_rsThisSection = $_GET['sectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSection = sprintf("SELECT * FROM newssection WHERE ID = %s", GetSQLValueString($colname_rsThisSection, "int"));
$rsThisSection = mysql_query($query_rsThisSection, $aquiescedb) or die(mysql_error());
$row_rsThisSection = mysql_fetch_assoc($rsThisSection);
$totalRows_rsThisSection = mysql_num_rows($rsThisSection);




?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Add Post"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script>
var showEventDate = new Array();

  <?php if($totalRows_rsSections>0) { do { 
  echo "showEventDate[".$row_rsSections['ID']."] = ".$row_rsSections['showeventdatetime'].";\n";
   } while ($row_rsSections = mysql_fetch_assoc($rsSections)); 
   mysql_data_seek($rsSections,0); 
   $row_rsSections = mysql_fetch_assoc($rsSections);
  } ?>
   
   function toggleEventDate() {
	   var sectionID = document.getElementById('sectionID').value;
	   if(showEventDate[sectionID] ==1 ) {
		   $(".eventdatetime").show();
	   } else {
		   $(".eventdatetime").hide();
	   }
   }
   $(document).ready(function(e) {
	   toggleEventDate() ;
	   
	   
	   
    
});
   </script>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style><!--
.liveURLscrapeImgPreview img {
	max-width:300px;
}

<?php if (!isset($mod_rewrite)) { // no mod re-write so hide URL option ?>

.longID {
	display:none;
}
 <?php } ?>


<?php 
$regionID = isset($_POST['regionID']) ? $_POST['regionID'] : $regionID;

if($totalRows_rsSections < 1) {?>

.section {
	display:none;
}



<?php } 
	if($totalRows_rsSections < 1 || $row_rsThisSection['showeventdatetime']==1) {
		echo ".eventdatetime { display: none !important; }";
	}
	
?>
 -->
</style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page news">
     <h1><i class="glyphicon glyphicon-bullhorn"></i> Add Post </h1>
     <?php if(isset($submit_error)) { ?>
     <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
     <?php } ?>
     <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1" >
       
        <table class="form-table">
               <tr class="section">
                 <td class="text-nowrap text-right">Section:</td>
                 <td><select name="sectionID"  id="sectionID" onChange="toggleEventDate()" class="form-control">
                   <option value="1" <?php if (!(strcmp(1, $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], $_GET['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['sectioname']?></option>
                   <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
                 </select></td>
               </tr>
               <tr class="eventdatetime">
                 <td class="text-nowrap text-right top">Event date:</td>
                 <td><input name="eventdatetime" type="hidden" class='highlight-days-67 split-date format-y-m-d divider-dash' id="eventdatetime" value=""/>
                   <?php $inputname = "eventdatetime"; include("../../core/includes/datetimeinput.inc.php"); ?></td>
               </tr>
               <tr>
                 <td class="text-nowrap text-right">Title:</td>
                 <td><span id="sprytextfield1">
                   <input name="title" id="title" type="text" class="form-control liveURLscrapeTitle" value="<?php echo isset($_POST['title']) ?  htmlentities($_POST['title'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="100" onBlur="seoPopulate(this.value, document.getElementById('summary').value);"  />
                   <span class="textfieldRequiredMsg">A value is required.</span></span></td>
               </tr>
               <tr>
                 <td class="text-nowrap text-right">&nbsp;</td>
                 <td><button type="submit"  class="btn btn-primary">Add Post...</button></td>
               </tr>
             </table>
        <input type="hidden" name="postedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
       <input type="hidden" name="status" value="0" />
        <input type="hidden" name="longID" value="" />
       <input type="hidden" name="regionID" value="<?php echo $regionID; ?>" />
       
       <input name="displayfrom" type="hidden" id="displayfrom" value="<?php echo date('Y-m-d H:i:s'); ?>" />
       <input name="posteddatetime" type="hidden" id="posteddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
       <input type="hidden" name="MM_insert" value="form1" />
       
       
      
       </form>
  </div>
    <script>
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
    </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsPreferences);

mysql_free_result($rsSections);

mysql_free_result($rsThisSection);

?>
