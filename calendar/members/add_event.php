<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
<?php require_once('../../location/includes/locationfunctions.inc.php'); ?><?php require_once('../../location/includes/mapit.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
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
  if (isset($_SESSION['QUERY_STRING']) && strlen($_SESSION['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SESSION['QUERY_STRING'];
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



$varRegionID_rsEventPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsEventPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsEventPrefs = sprintf("SELECT * FROM eventprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsEventPrefs, "int"));
$rsEventPrefs = mysql_query($query_rsEventPrefs, $aquiescedb) or die(mysql_error());
$row_rsEventPrefs = mysql_fetch_assoc($rsEventPrefs);
$totalRows_rsEventPrefs = mysql_num_rows($rsEventPrefs);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$uploaded = getUploads();
	if (isset($uploaded) && is_array($uploaded)) {
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
		}
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$insert = "INSERT INTO eventgroup (eventtitle, eventdetails, directoryID, categoryID, customvalue1, customvalue2,  createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['eventtitle'], "text").",".GetSQLValueString($_POST['eventdetails'], "text").",".GetSQLValueString($_POST['directoryID'], "int").",".GetSQLValueString($_POST['categoryID'], "int").",".GetSQLValueString($_POST['customvalue1'], "text").",".GetSQLValueString($_POST['customvalue2'], "text").",".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$_POST['eventgroupID'] = mysql_insert_id();
	$locationID = ""; $latitude = ""; $longitude = "";
	if(isset($_POST["locationname"]) && trim($_POST["locationname"]) !="") { 
	if(isset($_POST["postcode"]) && trim($_POST["postcode"]) !="") {
		$postcode = getDataFromPostCode($_POST["postcode"]);
		$latitude = $postcode['latitude']; $longitude = $postcode['longitude'];
	}
	 $locationID = createLocation(1,0,$_POST["locationname"],"",$_POST["address1"],"","","","",$_POST["postcode"],"", "", "", "", "", "", "",$latitude, $longitude,$_POST['createdbyID']);
	}
}
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO event (eventgroupID, imageURL, startdatetime, enddatetime, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['eventgroupID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertGoTo = isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo)); exit;
}

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");


?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php  $pageTitle = "Add Event"; echo $site_name." | ".$pageTitle;?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../css/calendarDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --><div class="calendar members container">
      
      <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="/calendar/index.php" rel="index"><span itemprop="name">Events</span></a>
       <meta itemprop="position" content="2" />
      </li> 
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  Add Event</span></a> <meta itemprop="position" content="3" /></li></ol>
      
      
      </div></div>
      
      
      
      <h1 class="calendarHeader">Add Event</h1><?php require_once('includes/add_event_form.inc.php'); ?>
      </div>
	    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsEventPrefs);
?>
