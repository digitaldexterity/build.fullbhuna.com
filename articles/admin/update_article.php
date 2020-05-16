<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../../core/includes/upload.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "7,8,9,10";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticlePrefs = "SELECT * FROM articleprefs WHERE ID =".$regionID;
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, users.regionID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


$colname_rsArticle = "1";
if (isset($_GET['articleID'])) {
  $colname_rsArticle = $_GET['articleID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticle = sprintf("SELECT article.*, users.firstname, users.surname, CONCAT(modifier.firstname, ' ',modifier.surname) AS modifiedbyname, articlesection.regionID AS sectionregionID,articlesection.showlink AS sectionshowlink, articlesection.accesslevel AS sectionaccesslevelID, usertype.name AS sectionaccesslevelname, editor.firstname AS editorfirstname, editor.surname AS editorsurname, articlesection.longID AS sectionlongID, parent.ID AS parentID, parent.longID AS parentlongID, articlesection.writerankID, articlesection.groupwriteID,  articlesection.approverankID AS sectionapproverankID FROM article LEFT JOIN users ON (article.createdbyID = users.ID)  LEFT JOIN users AS modifier ON (article.modifiedbyID = modifier.ID)  LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN articlesection AS parent ON (articlesection.subsectionofID = parent.ID) LEFT JOIN usertype ON (articlesection.accesslevel = usertype.ID) LEFT JOIN users AS editor ON (article.editedbyID = editor.ID) WHERE article.ID = %s", GetSQLValueString($colname_rsArticle, "int"));
$rsArticle = mysql_query($query_rsArticle, $aquiescedb) or die(mysql_error());
$row_rsArticle = mysql_fetch_assoc($rsArticle);
$totalRows_rsArticle = mysql_num_rows($rsArticle);


$error = "";	$msg = "";




$livearticleID = isset($row_rsArticle['versionofID']) ? $row_rsArticle['versionofID']: $row_rsArticle['ID'];

// REVERT TO

// 7 - has access but cannot edit live documents
if(isset($_GET['reverttoID']) && isset($_GET['token']) && isset($_GET['articleID']) && $_GET['token'] == md5(@PRIVATE_KEY.$_GET['articleID'].$_GET['reverttoID'])) {
	revertToVersion($livearticleID, $_GET['reverttoID'], $row_rsLoggedIn['ID']);	
	// now go to this verion in editor
	header("location: update_article.php?articleID=".$livearticleID."&warning=".urlencode("This article has now been updated back to the version you specified.")); exit;
}




/******

DELETE VERSION 

********/

if(isset($_GET['deleteversionID'])) {
	$delete = "DELETE FROM article WHERE ID = ".intval($_GET['deleteversionID'])." AND versionofID IS NOT NULL";
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	if(mysql_affected_rows($aquiescedb)>0) {
		// if delete page is same as current ediing then redirect to live page edit
		$articleID = ($_GET['deleteversionID']==$_GET['articleID']) ?$livearticleID  : $_GET['articleID'];
		$msg = "The specified page has been deleted.";
		header("location: update_article.php?articleID=".$articleID."&msg=".urlencode($msg)); exit;
	} else {
		$error .= "Sorry the specified page could not be deleted.\n\n";
	}
	
}


/******

SAVE AS DRAFT 

********/

if(isset($_POST['draft'])) {
	if(intval($_POST['versionofID'])>0) { 
		// already draft so no need to duplicate
	} else {
		$draftID = duplicateMySQLRecord ("article", $livearticleID);
		$update = "UPDATE article SET versionofID = ".GetSQLValueString($_POST['ID'], "int")." WHERE ID = ".$draftID;
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		$_POST['ID'] = $draftID; // change the version to save to the duplicated draft version and save below!	
	}
}



/******

SAVE

********/

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) { // post
	mysql_select_db($database_aquiescedb, $aquiescedb);
	
	// oldlong id posted is the current longID before update
	// if the current longID has changed then the oldlongId becomes the previous
	$_POST['oldlongID'] = ($_POST['currentlongID'] != $_POST['longID']) ? $_POST['currentlongID'] : $_POST['oldlongID'];
	if(!isset($_POST['draft'])) {
		$_POST['longID'] = createURLname($_POST['longID'], $_POST['title'], "-",  "article", $_POST['ID']);
	}
	
	$_POST['regionID'] = (isset($_POST['regionID']) && $_POST['regionID']!="")  ? $_POST['regionID'] : 1;
	$_POST['redirecttype'] = isset($_POST['redirecttype']) ? $_POST['redirecttype'] : 302;


	if(isset($_POST['statusID']) && $_POST['statusID'] == 1 && $_SESSION['MM_UserGroup'] <8) {
	$error .="You do not have the privileges to edit live documents or make a document live.\n\n";

	}
	
	if(isset($_POST['sectionID']) && $_POST['sectionID']==0 && !isset($_POST['draft']) && $_POST['versionofID']=="") { 
	// is home page so check another one already doesn't exist
		$select = "SELECT ID FROM article 
		WHERE sectionID = 0 AND versionofID IS NULL AND ID != ".GetSQLValueString($_POST['ID'], "int")." 
		AND regionID = ".GetSQLValueString($_POST['regionID'], "int");
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
		if( mysql_num_rows($result)>0) { // already exists
			$error .= "A home page already exists for this site. Please choose another section.\n\n";
		} // end already exists
	} // end is home page

	
  if (isset($_POST["legalchange"])) { // set all clients to not agree with new terms
	  resetUserTermsAgree();  
  } 

	if($row_rsArticlePrefs['safeemail']==1 && function_exists("encodeSafeEmails")) {
		$_POST['body'] = encodeSafeEmails($_POST['body'], $_POST['ID']);
	}	

	if(isset($row_rsArticlePrefs['containerclass']) && $row_rsArticlePrefs['containerclass']!="") {
		if(strpos(stripslashes($_POST['body']),'<div class="'.$row_rsArticlePrefs['containerclass']) === false) {
			$_POST['body'] = "<div class=\"".$row_rsArticlePrefs['containerclass']."\">".$_POST['body']."</div>";
		}
	}
	
	
// fix empty <p> tags (tinymce set not to check) and grammarly markup
	$_POST['body'] = str_replace("<p></p>","<p>&nbsp;</p>", $_POST['body']);
	// get rid of &amp; in URLs
	//$_POST['body'] = str_replace("&amp;","&",$_POST['body']);
		// get rid of grammarly stuff
	$_POST['body'] = preg_replace("/<g (.*)>(.*)<\/g>/iU", "$2", $_POST['body']);
	// fix tinymce CDATA inserts
	$_POST['body'] = str_replace(array("// <![CDATA[", "// ]]>"), array("", ""), $_POST['body']);

	if($error!="") {
		unset($_POST["MM_update"]);
	} else if(isset($_POST['saveversion'])) { // make back up before update
		saveArticleVersion($_POST['ID']);
		$uploaded = getUploads();	
	if(isset($_POST['noImage'])) {
		$_POST['ogimageURL'] = "";
	}	
	if (isset($uploaded) && is_array($uploaded)) { 
		if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
			$_POST['ogimageURL'] = $uploaded["filename"][0]["newname"]; 
		}
		
	}
	} // no error
} // end post

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE article SET longID=%s, oldlongID=%s, ordernum=%s, regionID=%s, robots=%s, history=%s, title=%s, seotitle=%s, body=%s, notes=%s, metakeywords=%s, metadescription=%s, googleconversions=%s, showlink=%s, newWindow=%s, statusID=%s, draft=%s, redirectURL=%s, redirecttype=%s, sectionID=%s, photogalleryID=%s, allowcomments=%s, headHTML=%s, headHTMLineditor=%s, `class`=%s, accesslevel=%s, modifiedbyID=%s, modifieddatetime=%s, linktitle=%s, ogimageURL=%s WHERE ID=%s",
                       GetSQLValueString($_POST['longID'], "text"),
                       GetSQLValueString($_POST['oldlongID'], "text"),
                       GetSQLValueString($_POST['ordernum'], "int"),
                       GetSQLValueString($_POST['regionID'], "int"),
                       GetSQLValueString($_POST['robots'], "int"),
                       GetSQLValueString($_POST['history'], "int"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['seotitle'], "text"),
                       GetSQLValueString($_POST['body'], "text"),
                       GetSQLValueString($_POST['notes'], "text"),
                       GetSQLValueString($_POST['metakeywords'], "text"),
                       GetSQLValueString($_POST['metadescription'], "text"),
                       GetSQLValueString($_POST['googleconversions'], "text"),
                       GetSQLValueString($_POST['showlink'], "int"),
                       GetSQLValueString(isset($_POST['newWindow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['status'], "int"),
                       GetSQLValueString(isset($_POST['draft']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['redirectURL'], "text"),
                       GetSQLValueString($_POST['redirecttype'], "int"),
                       GetSQLValueString($_POST['sectionID'], "int"),
                       GetSQLValueString($_POST['photoGalleryID'], "int"),
                       GetSQLValueString(isset($_POST['allowcomments']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['headHTML'], "text"),
                       GetSQLValueString(isset($_POST['headHTMLineditor']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['class'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['linktitle'], "text"),
                       GetSQLValueString($_POST['ogimageURL'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}




if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	// DRAFT NOW SAVED AS LIVE
	if(isset($_POST['versionofID']) && intval($_POST['versionofID'])>0 && !isset($_POST['draft'])) {
		revertToVersion($livearticleID, $_POST['ID'], $row_rsLoggedIn['ID']);			
	}
	

	// clean up history
	cleanArticleHistory($_POST['ID'], $_POST['history']);
		
	// free editing
	$update = "UPDATE article SET editedbyID = NULL, editeddatetime = NULL WHERE ID = ".GetSQLValueString($_POST['ID'], "int");
	mysql_query($update, $aquiescedb) or die(mysql_error().$update);
	
	if(isset($_REQUEST['templateID']) && intval($_REQUEST['templateID'])>0) { // apply template
		$select = "SELECT headHTML, body FROM article WHERE ID = ".intval($_REQUEST['templateID'])." LIMIT 1";
	 	$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
	 	$row = mysql_fetch_assoc($result);
		$update = "UPDATE article SET headHTML = ".GetSQLValueString($row['headHTML'],"text").", body = ".GetSQLValueString($row['body'],"text").", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime= ".GetSQLValueString($_POST['modifieddatetime'], "date")." WHERE ID = ".GetSQLValueString($_GET['articleID'], "int");
		mysql_query($update, $aquiescedb) or die(mysql_error().$update);
		$msg .= "Template applied to page.<br>";
		header("Location: ".$_SERVER['REQUEST_URI']);exit;
	 
	} else if(isset($_POST['sectiontemplateID']) && $_POST['sectiontemplateID']!="") { // apply template to other pages
		
		$select = "SELECT article.ID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE (articlesection.regionID = ".$regionID." OR article.regionID = ".$regionID.") AND article.statusID = 1 AND article.versionofID IS NULL AND article.sectionID >= 0 AND (".GetSQLValueString($_POST['sectiontemplateID'], "int")." = 0 OR article.sectionID = ".GetSQLValueString($_POST['sectiontemplateID'], "int")." OR parentsection.ID = ".GetSQLValueString($_POST['sectiontemplateID'], "int").")";
		
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
		if(mysql_num_rows($result)>0) { 
			while($article = mysql_fetch_assoc($result)) {
		
				saveArticleVersion($article['ID']);
				$update = "UPDATE article SET headHTML = ".GetSQLValueString($_POST['headHTML'],"text").", body = ".GetSQLValueString($_POST['body'],"text").", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime= ".GetSQLValueString($_POST['modifieddatetime'], "date")." WHERE ID = ".$article['ID'];
		
				mysql_query($update, $aquiescedb) or die(mysql_error().$update);
			}
		}
	
	}
	
	if(isset($_POST['closeaftersave']) && $_POST['closeaftersave']==1) {

  $updateGoTo =isset($_GET['returnURL']) ? $_GET['returnURL'] : "index.php?sectionID=".urlencode(intval($_POST['sectionID']));
 // removed addition of Query String here as I don't think it is needed
  header(sprintf("Location: %s", $updateGoTo));exit;
	}
	
}





mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT * FROM preferences WHERE ID = %s", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = "SELECT articlesection.ID, articlesection.`description`, parentsection.`description`AS parent FROM articlesection LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE articlesection.regionID = 0 OR articlesection.regionID IS NULL OR articlesection.regionID = ".$regionID;
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPhotoGalleries = "SELECT ID, categoryname FROM photocategories WHERE active !=2 ORDER BY categoryname ASC";
$rsPhotoGalleries = mysql_query($query_rsPhotoGalleries, $aquiescedb) or die(mysql_error());
$row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries);
$totalRows_rsPhotoGalleries = mysql_num_rows($rsPhotoGalleries);



$sectionaccesslevelID = isset($row_rsArticle['sectionaccesslevelID']) ? $row_rsArticle['sectionaccesslevelID'] : 0 ;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = "SELECT * FROM usertype WHERE ID > ".$sectionaccesslevelID." ORDER BY ID ASC";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

$varRegionID_rsTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplates = sprintf("SELECT ID, title FROM article WHERE regionID = %s AND article.sectionID = -1 AND versionofID IS NULL ORDER BY title ASC", GetSQLValueString($varRegionID_rsTemplates, "int"));
$rsTemplates = mysql_query($query_rsTemplates, $aquiescedb) or die(mysql_error());
$row_rsTemplates = mysql_fetch_assoc($rsTemplates);
$totalRows_rsTemplates = mysql_num_rows($rsTemplates);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$varArticleID_rsVersions = "-1";
if (isset($livearticleID )) {
  $varArticleID_rsVersions = $livearticleID ;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsVersions = sprintf("SELECT article.ID,article.draft, title, users.firstname, users.surname, article.modifieddatetime, article.createddatetime FROM article LEFT JOIN users ON article.modifiedbyID = users.ID WHERE article.statusID = 1 AND versionofID = %s OR article.ID = %s ORDER BY modifieddatetime DESC", GetSQLValueString($varArticleID_rsVersions, "int"),GetSQLValueString($varArticleID_rsVersions, "int"));
$rsVersions = mysql_query($query_rsVersions, $aquiescedb) or die(mysql_error());
$row_rsVersions = mysql_fetch_assoc($rsVersions);
$totalRows_rsVersions = mysql_num_rows($rsVersions);

// check if being editied already or set

if(isset($row_rsArticle['editedbyID']) && $row_rsArticle['editeddatetime'] > date('Y-m-d H:i:s', strtotime("1 HOUR AGO")) && $row_rsArticle['editedbyID'] != $row_rsLoggedIn['ID']) { 
// being edited by someone else
	$error .= "This page is currently being edited by ".$row_rsArticle['editorfirstname']." ".$row_rsArticle['editorsurname'].".\nIMPORTANT: Before you edit this page:\n1. First, ensure that they save their changes;\n2. Then, reload this page before you edit.";
} else {
	$update = "UPDATE article SET editedbyID = ".intval($row_rsLoggedIn['ID']).", editeddatetime = NOW() WHERE ID = ".intval($row_rsArticle['ID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

if(function_exists("decodeSafeEmails")) {
	$row_rsArticle['body'] = decodeSafeEmails($row_rsArticle['body']);
}

if(isset($row_rsArticlePrefs['containerclass']) && $row_rsArticlePrefs['containerclass']!="") {
	if(strpos($row_rsArticle['body'],"<div class=\"".$row_rsArticlePrefs['containerclass']) === false) {
		$row_rsArticle['body'] = "<div class=\"".$row_rsArticlePrefs['containerclass']."\">".$row_rsArticle['body']."</div>";
	}
}


// if redirect set - see if we can find an article to edit...

if(isset($row_rsArticle['redirectURL']) && $row_rsArticle['redirectURL']!="") {
	if(preg_match("/http/i",$row_rsArticle['redirectURL']) && !preg_match("/".$_SERVER['HTTP_HOST']."/i",$row_rsArticle['redirectURL'])) { 
	// external
	} else {
		$url = parse_url($row_rsArticle['redirectURL']);
		if(isset($articleID)) {
			$redirectID = $articleID;
		} else {
			$redirectlongID = end(explode("/",$row_rsArticle['redirectURL']));
			if($redirectlongID  !="") { // potential mod rewrite article
				$select = "SELECT ID from article WHERE versionofID IS NULL and regionID = ".$regionID." AND longID LIKE ".GetSQLValueString($redirectlongID, "text")." LIMIT 1";
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$row = mysql_fetch_assoc($result);
				$redirectID = $row['ID'];				
			}
		}
	}
}

$host =  getProtocol()."://";
$host .= isset($thisRegion['hostdomain']) ? $thisRegion['hostdomain'] : $_SERVER['HTTP_HOST']; 
$link = $host .articleLink($row_rsArticle['ID'], $row_rsArticle['longID'], $row_rsArticle['sectionID'], $row_rsArticle['sectionlongID'], $row_rsArticle['parentID'], $row_rsArticle['parentlongID'], $page=0); ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Update Page: ".htmlentities($row_rsArticle['title'], ENT_COMPAT,"UTF-8"); echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/saveChanges.js"></script>
<?php require_once('../../core/tinymce/tinymce.inc.php'); ?>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script>
<script>
$(document).ready(function(e) {
	updateStatus();
    $("#status").change(function() {
		updateStatus();
	});
});
$(window).load(function() {
	toggleRedirectMessage();

	addHeadTags($("#headHTML").val());
	$("#headHTML").blur(function() {
		addHeadTags($("#headHTML").val());
	});
	
});

function toggleRedirectMessage() {
	if(document.getElementById('redirectURL').value=="") { 
		
		document.getElementById('noeditor').style.display = "none";
	} else {
		
		document.getElementById('noeditor').style.display = "block";
	}
	
}

function addHeadTags(headHTML) {	
	if(document.getElementById('headHTMLineditor').checked) {
		var oldHeadHTML = $(tinyMCE.activeEditor.getDoc()).children().find('head').html(); 
		oldHeadHTML = oldHeadHTML.replace(/<!--editorHead-->.+?<!--editorHead-->/gi, ""); 
		$(tinyMCE.activeEditor.getDoc()).children().find('head').html(oldHeadHTML+"<!--editorHead-->"+headHTML+"<!--editorHead-->");
	}
}

function updateStatus() {
	
	$("#status").parents("fieldset").removeClass();
	$("#status").parents("fieldset").addClass("form-inline pagestatus"+$("#status").val());
}
</script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<style>
<!--
 <?php if($row_rsPreferences['termsarticleID']!=$row_rsArticle['ID'] && $row_rsPreferences['privacyarticleID']!=$row_rsArticle['ID']) echo ".legalchange { display: none; }";
?>  tr.current .link_edit {
 display:none !important;
}
tr.live .link_delete {
	display: none !important;
}
fieldset.pagestatus0 {
	background-color: #FC9;
}
fieldset.pagestatus1 {
	background-color: #CF9;
}
fieldset.pagestatus2, fieldset.pagestatus3 {
	background-color: #F99;
}
h1 small {
	font-size:50%;
}
 <?php if($row_rsArticle['sectionapproverankID'] > $_SESSION['MM_UserGroup'] ) {
 echo ".status-chooser, .draft-chooser {
 visibility:hidden;
position:absolute;
}
";
}
?>
-->
</style>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../css/defaultArticles.css" rel="stylesheet"  />
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
    <div class="page articles">
      <?php if(!isset($row_rsArticle['sectionregionID']))  { // for home page
  $row_rsArticle['sectionregionID']  = $row_rsArticle['regionID']; 
   } 
   
   if(thisUserHasAccess($row_rsArticle['writerankID'] ,$row_rsArticle['groupwriteID']) && ($row_rsPreferences['useregions'] != 1 || $row_rsLoggedIn['regionID'] == 0 || $row_rsArticle['sectionregionID'] == $row_rsLoggedIn['regionID'] || $row_rsArticle['createdbyID'] == $row_rsLoggedIn['ID'] || $row_rsLoggedIn['usertypeID'] >= 9))  { //authorised ?>
      <h1><?php if($row_rsArticle['sectionID']==0) { ?>
        <i class="glyphicon glyphicon-home"></i> Edit Home Page
        <?php } else { ?>
        <i class="glyphicon glyphicon-file"></i> Edit Page
        <?php } ?>
        <?php 
	 if($row_rsArticle['draft']==1) echo " [DRAFT]"; 
	 
	 $editdatetime =  isset($row_rsArticle['modifieddatetime']) ? strtotime($row_rsArticle['modifieddatetime']) : strtotime($row_rsArticle['createddatetime']);
	 
	 if(isset($row_rsArticle['versionofID'])) echo " (".date('d M Y H:i', $editdatetime)." version)"; ?>
     
    <small><a href="<?php $url = $link; $url .= strpos("?",$url)>0 ? "&" : "?";$url .= "preview=true";echo $url; ?>" target="_blank" rel="noopener"   onClick="openMainWindow(this.href); return false;" ><?php  if($row_rsArticle['sectionID']!=0) {  echo htmlentities($link,ENT_COMPAT, "UTF-8");  } else { echo $host; } ?></a> <a data-toggle="tooltip" title="Copy link to clipboard" onClick="copyToClipboard(document.getElementById('articleURL'))" href="javascript:;"><i class="glyphicon glyphicon-copy"></i></a></small><input type="text" style="display:none;" id="articleURL" value="<?php echo $url; ?>"></h1>
      <?php if((isset($row_rsVersions['createddatetime']) && $row_rsVersions['createddatetime'] >$editdatetime) || (isset($row_rsVersions['modifieddatetime']) && $row_rsVersions['modifieddatetime'] >$editdatetime)) { ?>
      <p class="alert warning alert-warning" role="alert">Note - this is not the most recent edit of this page. Click on History tab to view other edits and drafts.</p>
      <?php } ?>
      <?php if(isset($row_rsArticle['notes']) && strlen(trim($row_rsArticle['notes']))>0) {
		 $msg = $row_rsArticle['notes'];
	} ?>
      <?php require_once('../../core/includes/alert.inc.php'); ?>
      <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form1" id="form1"  onsubmit="if(Spry.Widget.Form.validate(form1)) { seoPopulate(document.getElementById('title').value,top.frames[0].document.getElementById('tinymce').innerHTML); } else { alert('There are highlighted errors on the page. Please correct before submitting.'); return false; } ">
        <fieldset class="form-inline pagestatus<?php echo $row_rsArticle['statusID']; ?>" >
          <span id="sprytextfield1">
          <input name="title" id= "title" type="text" value="<?php echo isset($_POST['title']) ? htmlentities($_POST['title'], ENT_COMPAT,"UTF-8") : htmlentities($row_rsArticle['title'], ENT_COMPAT,"UTF-8"); ?>" size="50" maxlength="100" onblur="seoPopulate(this.value, this.value);" class="form-control" />
          <span class="textfieldRequiredMsg">A title is required.</span></span>
          <?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
            <select name="sectionID" id="sectionID" class="form-control">
              <option value="1" <?php if (!(strcmp(1, $row_rsArticle['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
              <option value="-1" <?php if (!(strcmp(-1, $row_rsArticle['sectionID']))) {echo "selected=\"selected\"";} ?>>Templates</option>
              <option value="0" <?php if (!(strcmp(0, $row_rsArticle['sectionID']))) {echo "selected=\"selected\"";} ?>>Home Page</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], $row_rsArticle['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsSections['parent']) ? $row_rsSections['parent']." &rsaquo; " : ""; echo  $row_rsSections['description'];  ?></option>
              <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
            </select>
            <?php } // Show if recordset not empty ?>
          <?php if ($totalRows_rsSections == 0) { // Show if recordset empty ?>
            <input name="sectionID" type="hidden" id="sectionID" value="<?php echo $row_rsArticle['sectionID']; ?>" />
            No sections created
            <?php } // Show if recordset empty ?>
          <span class="status-chooser">
          <select name="status" id="status" class="form-control">
            <?php
do {  
?>
            <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsArticle['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
            <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
          </select>
          </span>
        </fieldset>
        <div id="TabbedPanels1" class="TabbedPanels">
          <ul class="TabbedPanelsTabGroup">
            <li class="TabbedPanelsTab" tabindex="0" id="tabEditor">Editor</li>
            <li class="TabbedPanelsTab" tabindex="0" id="tabOptions">Options</li>
            <li class="TabbedPanelsTab" tabindex="0">SEO</li>
            <li class="TabbedPanelsTab" tabindex="0"  id="tabAdvanced">Advanced</li>
            <li class="TabbedPanelsTab" tabindex="0">History</li>
          </ul>
          <div class="TabbedPanelsContentGroup">
            <div class="TabbedPanelsContent editor">
              <div id="noeditor">
                <h2><i class="glyphicon glyphicon-share-alt"></i></h2>
                <h2>This page redirects... </h2>
                <p>This page automatically redirects to <a href="<?php echo $row_rsArticle['redirectURL']; ?>"><?php echo $row_rsArticle['redirectURL']; ?></a>. The editor text below may not have effect on final page.</p>
                <p>Click on Options tab to update redirect settings
                  <?php if(isset($redirectID)) { ?>
                  or <a href="update_article.php?articleID=<?php echo $redirectID; ?>">click here to edit target page</a>
                  <?php } ?>
                  .</p>
              </div>
              <div id="editor">
                <textarea name="body" id="body" class="articleBody tinymce" rows="20"><?php echo isset($_POST['body']) ? htmlentities($_POST['body'], ENT_COMPAT,"UTF-8") : htmlentities($row_rsArticle['body'], ENT_COMPAT,"UTF-8"); ?></textarea>
              </div>
            </div>
            <div class="TabbedPanelsContent">
              <table class="form-table">
                <tr>
                  <td class="text-nowrap  top text-right"><label for="notes">Notes:</label></td>
                  <td><textarea cols="60" rows="4"  name="notes" class="form-control" id="notes" ><?php echo isset($_POST['notes']) ? htmlentities($_POST['notes'], ENT_COMPAT,"UTF-8") :  htmlentities($row_rsArticle['notes'], ENT_COMPAT,"UTF-8"); ?></textarea></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><label for="accesslevel">Access:</label></td>
                  <td><select name="accesslevel" id="accesslevel" class="form-control">
                      <option value="0" <?php if (!(strcmp(0, $row_rsArticle['accesslevel']))) {echo "selected=\"selected\"";} ?>>Same as Section (<?php echo ($sectionaccesslevelID == 0) ? "Everyone" : $row_rsArticle['sectionaccesslevelname']; // set to zero by default so article will assume any section value if lowered ?>)</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsArticle['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['name']?></option>
                      <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
                    </select></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><label for="newWindow" data-toggle="tooltip" title="When a use clicks on any automatically generated navigation links to this page, they will open in a new window or tab (depending on browser settings)">Open in new window:</label></td>
                  <td><input <?php if (!(strcmp($row_rsArticle['newWindow'],1))) {echo "checked=\"checked\"";} ?> name="newWindow" type="checkbox" id="newWindow" value="1" /></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right">Show in menus:</td>
                  <td class="text-nowrap"><?php if($row_rsArticle['sectionshowlink']!=1) { ?>
                    <span class="glyphicon glyphicon-exclamation-sign"></span> NOTE: This page is within a section that is set not to show in menus which overrides this setting.
                    <?php } ?>
                    <label data-toggle="tooltip" title="Show links to this page in any navigation menus. You can also add manual links to this page within any page content">
                      <input <?php if (!(strcmp($row_rsArticle['showlink'],"1"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="1"  />
                      Yes</label>
                    &nbsp;&nbsp;&nbsp;
                    <label data-toggle="tooltip" title="Do not show links to this page in any navigation menus, however you can add manual links within page content">
                      <input <?php if (!(strcmp($row_rsArticle['showlink'],"-1"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="-1"  />
                      No</label>
                    &nbsp;&nbsp;&nbsp;
                    <label data-toggle="tooltip" title="Do not show links to this page in any navigation menus, however do show link to this  page in site map/index page">
                      <input <?php if (!(strcmp($row_rsArticle['showlink'],"0"))) {echo "checked=\"checked\"";} ?> name="showlink" type="radio" id="showlink" value="0"  />
                      Site map only</label></td>
                </tr>
                <?php if($totalRows_rsTemplates>0) { ?>
                <tr class="templates">
                  <td class="text-nowrap text-right"><label for="templateID">Template:</label></td>
                  <td class="form-inline">Apply template to this page:
                    <select name="templateID" id="templateID" onChange="if(this.value !='' && confirm('Do you want to revert this page to the template?\n(WARNING: All current content will be lost)')) { formSubmitted = true; document.form1.submit(); }" class="form-control">
                      <option value="">Choose…</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsTemplates['ID']?>"><?php echo $row_rsTemplates['title']?></option>
                      <?php
} while ($row_rsTemplates = mysql_fetch_assoc($rsTemplates));
  $rows = mysql_num_rows($rsTemplates);
  if($rows > 0) {
      mysql_data_seek($rsTemplates, 0);
	  $row_rsTemplates = mysql_fetch_assoc($rsTemplates);
  }
?>
                    </select>
                    <strong>OR</strong> apply this page as template to all pages in:
                    <select name="sectiontemplateID" onChange="if(this.value !='') { if(!confirm('Are you sure you want to apply this page template to the selected pages in the site?\n\nWARNING: All current content on these pages will be overwritten.\n\nClick Save Changes to apply.')) { this.value = ''} } " class="form-control">
                      <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                      <option value="0">Whole site</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsSections['ID']?>"><?php echo isset($row_rsSections['parent']) ? $row_rsSections['parent']." &rsaquo; " : ""; echo  $row_rsSections['description'];  ?></option>
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
                <?php } ?>
                <tr  class="longID">
                  <td height="51" class="text-nowrap  top text-right"><input name="currentlongID" type="hidden" id="currentlongID" value="<?php echo $row_rsArticle['longID']; ?>" class="form-control"/>
                    <input name="oldlongID" type="hidden" id="oldlongID" value="<?php echo $row_rsArticle['oldlongID']; ?>" />
                    URL name:</td>
                  <td><input name="longID" type="text" class="form-control" id="longID" value="<?php echo isset($_POST['longID']) ? htmlentities($_POST['longID']) : $row_rsArticle['longID']; ?>" size="70" maxlength="100" /></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">Auto-redirect to:</td>
                  <td class="form-inline"><input name="redirectURL" type="text" id="redirectURL" value="<?php echo isset($_POST['redirectURL']) ? htmlentities($_POST['redirectURL']) : $row_rsArticle['redirectURL']; ?>" size="70" maxlength="255" onChange="toggleRedirectMessage();" class="form-control"/>
                    <label>
                      <input name="redirecttype" type="checkbox" id="redirecttype" value="301" <?php if($row_rsArticle['redirecttype']==301) echo "checked=\"checked\""; ?>>
                      301</label></td>
                </tr>
                <tr>
                  <td class="text-right"><label for="allowcomments">Allow comments:</label></td>
                  <td><input <?php if (!(strcmp($row_rsArticle['allowcomments'],1))) {echo "checked=\"checked\"";} ?> name="allowcomments" type="checkbox" id="allowcomments" value="1"></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right"><label for="photoGalleryID">Gallery:</label></td>
                  <td><?php if ($totalRows_rsPhotoGalleries == 0) { // Show if recordset empty ?>
                      No photo galleries created
                      <input name="photoGalleryID" type="hidden" id="photoGalleryID" value="<?php echo $row_rsArticle['photogalleryID']; ?>" />
                      <?php } // Show if recordset empty ?>
                    <?php if ($totalRows_rsPhotoGalleries > 0) { // Show if recordset not empty ?>
                      <select name="photoGalleryID" id="photoGalleryID" class="form-control">
                        <option value="" <?php if (!(strcmp("", $row_rsArticle['photogalleryID']))) {echo "selected=\"selected\"";} ?>>Do not link to a photo gallery</option>
                        <?php
do {  
?>
                        <option value="<?php echo $row_rsPhotoGalleries['ID']?>"<?php if (!(strcmp($row_rsPhotoGalleries['ID'], $row_rsArticle['photogalleryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPhotoGalleries['categoryname']?></option>
                        <?php
} while ($row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries));
  $rows = mysql_num_rows($rsPhotoGalleries);
  if($rows > 0) {
      mysql_data_seek($rsPhotoGalleries, 0);
	  $row_rsPhotoGalleries = mysql_fetch_assoc($rsPhotoGalleries);
  }
?>
                      </select>
                      <?php } // Show if recordset not empty ?></td>
                </tr>
                <tr>
                  <td class="text-nowrap text-right"><label for="ogimageURL">Sharing image:</label></td>
                  <td><?php if (isset($row_rsArticle['ogimageURL'])) { ?>
            <img src="<?php echo getImageURL($row_rsArticle['ogimageURL'], "medium"); ?>" alt="Current image" class="medium" />
            <label>
              <input name="noImage" type="checkbox" value="1" />
              Remove image</label>
            <br />
            <?php } ?>
          
              <input name="filename" id="ogimageURL" type="file" class="fileinput " accept=".jpg,.jpeg,.gif,.png"  />
              <input type="hidden" name="ogimageURL" value="<?php echo $row_rsArticle['ogimageURL']; ?>"  />
            </td>
                </tr>
              </table>
            </div>
            <div class="TabbedPanelsContent">
              <table class="form-table">
                <tr>
                  <td class="text-nowrap  top text-right"><label for="seotitle" data-toggle="tooltip" title="The page title is the title text that appears at the top of the window. This is important for SEO as it is the title that appears in search results">Page title:</label></td>
                  <td><span id="sprytextfield2">
                    <input name="seotitle" type="text" id="seotitle" value="<?php echo $row_rsArticle['seotitle']; ?>" size="70" maxlength="100" class="seo-length form-control">
                    </span></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><label for="linktitle" data-toggle="tooltip" title="The link title is the hidden descriptive text that appear in menu lins to the page">Link title:</label></td>
                  <td><input name="linktitle" type="text" id="linktitle" value="<?php echo $row_rsArticle['linktitle']; ?>" size="70" maxlength="50" class="form-control"/></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">Meta Keywords:</td>
                  <td><textarea cols="60" rows="4"  name="metakeywords" class="form-control" id="metakeywords"  ><?php echo isset($_POST['metakeywords']) ? htmlentities($_POST['metakeywords'], ENT_COMPAT,"UTF-8") :  htmlentities($row_rsArticle['metakeywords'], ENT_COMPAT,"UTF-8"); ?></textarea></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">Meta Description:</td>
                  <td><textarea name="metadescription" cols="60" rows="4" id="metadescription"  class="seo-length form-control"><?php echo isset($_POST['metadescription']) ? htmlentities($_POST['metadescription'], ENT_COMPAT,"UTF-8") : htmlentities($row_rsArticle['metadescription'], ENT_COMPAT,"UTF-8"); ?></textarea></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><p>Google Conversions:</p>
                    <p>(Overrides <a href="/core/seo/admin/index.php">site-wide code</a>)</p></td>
                  <td><textarea name="googleconversions" cols="60" rows="4" id="googleconversions"  class="form-control"><?php echo isset($_POST['googleconversions']) ? $_POST['googleconversions'] : $row_rsArticle['googleconversions']; ?></textarea></td>
                </tr>
              </table>
            </div>
            <div class="TabbedPanelsContent">
              <table class="form-table">
                <tr class="region">
                  <td class="text-nowrap text-right">Site:</td>
                  <td><select name="regionID" id="regionID" class="form-control" >
                      <option value="1" <?php if (!(strcmp(1, $row_rsArticle['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                      <?php if($row_rsArticle['sectionID']!=0) { ?>
                      <option value="0" <?php if (!(strcmp(0, $row_rsArticle['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
                      <?php } ?>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], $row_rsArticle['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
                      <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
                    </select></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><label for="robots">Robots:</label></td>
                  <td><select name="robots" id="robots" class="form-control">
                      <option value="1" <?php if (!(strcmp(1, $row_rsArticle['robots']))) {echo "selected=\"selected\"";} ?>>Index, follow</option>
                      <option value="2" <?php if (!(strcmp(2, $row_rsArticle['robots']))) {echo "selected=\"selected\"";} ?>>Index, no follow</option>
                      <option value="3" <?php if (!(strcmp(3, $row_rsArticle['robots']))) {echo "selected=\"selected\"";} ?>>No index, follow</option>
                      <option value="4" <?php if (!(strcmp(4, $row_rsArticle['robots']))) {echo "selected=\"selected\"";} ?>>No index, no follow</option>
                    </select></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">&lt;HEAD&gt; HTML:</td>
                  <td class="top"><span id="sprytextarea1">
                    <textarea name="headHTML" id="headHTML" cols="60" rows="10" class="form-control monospace"><?php echo isset($_POST['headHTML']) ? htmlentities($_POST['headHTML'], ENT_COMPAT, "UTF-8") : $row_rsArticle['headHTML']; ?></textarea>
                    </span></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">&nbsp;</td>
                  <td class="top"><label>
                      <input <?php if (!(strcmp($row_rsArticle['headHTMLineditor'],1))) {echo "checked=\"checked\"";} ?> name="headHTMLineditor" type="checkbox" id="headHTMLineditor" value="1">
                      Use in editor</label></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right"><label for="class">Class:</label></td>
                  <td class="top"><input name="class" type="text" id="class" value="<?php echo $row_rsArticle['class']; ?>" size="50" maxlength="50" class="form-control"></td>
                </tr>
                <tr>
                  <td class="text-nowrap  top text-right">Order number:</td>
                  <td class="form-inline"><input name="ordernum" type="text" value="<?php echo $row_rsArticle['ordernum']; ?>" size="10" maxlength="10" class="form-control"></td>
                </tr>
              </table>
              <p>&nbsp;</p>
              <p>A section submenu can be inserted on the page using the {submenu} merge text.</p>
            </div>
            <div class="TabbedPanelsContent">
              <p>
                <label>
                  <input name="saveversion" type="checkbox" id="saveversion" value="1" checked>
                  Keep a backup of the previous version of this document on next save </label>
              </p>
              <?php if ($totalRows_rsVersions == 0) { // Show if recordset empty ?>
                <p>There are no previous saved versions of this document</p>
                <?php } // Show if recordset empty ?>
              <?php if ($totalRows_rsVersions > 0) { // Show if recordset not empty ?>
                <p><a href="javascript:formSubmitted = true; document.location='update_article.php?articleID=<?php echo intval($_GET['articleID']); ?>'" onClick="return confirm('Are you sure you want to revert to the start of this editing session?\n\nDocument will revert to the previous saved version.')">Revert to start of this editing session</a></p>
                <table  class="table table-hover">
                  <thead>
                    <tr>
                      <th>Live</th>
                      <th colspan="2">Last updated</th>
                      <th>Title</th>
                      <th>Created by</th>
                      <th colspan="4">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php   do { ?>
                      <tr class="<?php if($row_rsVersions['ID']==$livearticleID ) echo " live ";  if($row_rsVersions['ID']==$row_rsArticle['ID']) echo " current "; ?>">
                        <td><?php if($row_rsVersions['ID']==$livearticleID ) { ?>
                          <span class="glyphicon glyphicon-ok" data-toggle="tooltip" data-placement="right" title="This version is the current live version"></span>
                          <?php } else if($row_rsVersions['draft'] ==1 ) { ?>
                          <span class="glyphicon glyphicon-file" data-toggle="tooltip" data-placement="right" title="This version is in draft form"></span>
                          <?php } 
          if($row_rsVersions['ID']==$row_rsArticle['ID'])  { ?>
                          <span class="glyphicon glyphicon-pencil" data-toggle="tooltip" data-placement="right" title="This version is the version you are currently editing"></span>
                          <?php } ?></td>
                        <td><?php $datetime =  isset($row_rsVersions['modifieddatetime']) ? strtotime($row_rsVersions['modifieddatetime']) : strtotime($row_rsVersions['createddatetime']); 
		echo date('d M Y', $datetime); 
		 ?></td>
                        <td><?php echo date('H:ia', $datetime); ?></td>
                        <td><?php echo $row_rsVersions['title']; ?></td>
                        <td><em><?php echo isset($row_rsVersions['surname']) ? $row_rsVersions['firstname']." ".$row_rsVersions['surname'] : $row_rsArticle['firstname']." ".$row_rsArticle['surname']; ?></em></td>
                        <td><a href="../article.php?articleID=<?php echo $row_rsVersions['ID']; ?>&preview=1&regionID=<?php echo $regionID; ?>" target="_blank" class="link_view" rel="noopener">View</a></td>
                        <td><a href="update_article.php?articleID=<?php echo $row_rsVersions['ID']; ?>"  onClick="return confirm('Are you sure you want to edit this version of the page? Any changes to the current document will not be saved.')" class="link_edit icon_only">Edit</a></td>
                        <td><?php if($row_rsVersions['ID']!=$livearticleID ) { ?>
                          <a href="update_article.php?articleID=<?php echo intval($_GET['articleID']); ?>&reverttoID=<?php echo $row_rsVersions['ID']; ?>&token=<?php echo md5(@PRIVATE_KEY.$row_rsArticle['ID'].$row_rsVersions['ID']); ?>" onClick="formSubmitted = true; return confirm('Are you sure you want to revert live page to this version of the page?');" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Revert</a>
                          <?php } ?></td>
                        <td><a href="update_article.php?articleID=<?php echo $row_rsArticle['ID']; ?>&deleteversionID=<?php echo $row_rsVersions['ID']; ?>&defaultTab=4" onClick="return confirm('Are you sure you want to delete this version of the page?');" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
                      </tr>
                      <?php } while ($row_rsVersions = mysql_fetch_assoc($rsVersions)); ?>
                  </tbody>
                </table>
                <p>
                  <label class="form-inline">Keep:
                    <select name="history" id="history" class="form-control">
                      <option value="0" <?php if (!(strcmp(0, $row_rsArticle['history']))) {echo "selected=\"selected\"";} ?>>None</option>
                      <option value="1" <?php if (!(strcmp(1, $row_rsArticle['history']))) {echo "selected=\"selected\"";} ?>>Recent and monthly (recommended)</option>
                      <option value="2" <?php if (!(strcmp(2, $row_rsArticle['history']))) {echo "selected=\"selected\"";} ?>>All</option>
                    </select>
                  </label>
                </p>
                <p><em>This page was originally created by <?php echo $row_rsArticle['firstname']; ?> <?php echo $row_rsArticle['surname']; ?> at <?php echo date('g:ia',strtotime( $row_rsArticle['createddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsArticle['createddatetime'])); ?>
                  <?php if(isset($row_rsArticle['modifieddatetime'])) { ?>
                  <br>
                  This version was updated by <?php echo $row_rsArticle['modifiedbyname']; ?> at <?php echo date('g:ia',strtotime($row_rsArticle['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsArticle['modifieddatetime']));   } ?></em></p>
                <?php } // Show if recordset not empty ?>
            </div>
          </div>
        </div>
        <p>
          <label class="draft-chooser">
            <input type="checkbox" name="draft" id="draft" onClick="if(this.checked) { alert('The current live page will remain unchanged and your edits will be saved as a draft copy.\n\nYou will be able to access drafts from the History tab.') } else { alert('This is now unchecked so this edit of the page will now go live once saved.') };" <?php if(isset($row_rsArticle['versionofID']) || $row_rsArticle['sectionapproverankID'] > $_SESSION['MM_UserGroup']) echo "checked"; ?>>
            Save as draft</label>
          &nbsp;&nbsp;&nbsp;
          <label class="legalchange">
            <input type="checkbox" name="legalchange" id="legalchange" onClick="if(this.checked) alert('As this page is legal text, when checked all users will need to reagree to terms &amp; conditions and privacy policy next time they log in.');">
            Users must re-agree to terms</label>
        </p>
        <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
        <input type="hidden" name="MM_update" value="form1" />
        <input type="hidden" name="ID" value="<?php echo $row_rsArticle['ID']; ?>" />
        <input type="hidden" name="closeaftersave" id="closeaftersave" value="0" />
        <input type="hidden" name="versionofID" value="<?php echo $row_rsArticle['versionofID']; ?>" />
        <input name="modifieddatetime" type="hidden" id="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input name="returnURL" type="hidden" id="returnURL" value="<?php echo isset($_GET['returnURL']) ? $_GET['returnURL'] : ""; ?>" />
        
        <button type="submit" onClick="document.getElementById('closeaftersave').value=1";   class="btn btn-primary">Save and close</button> <button type="submit"   class="btn btn-default btn-secondary">Save changes</button>
        <a href="javascript:history.go(-1);" class="btn btn-default btn-secondary btn-secondary">Cancel</a>
      </form>
      <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
</script>
      <?php } //end authorsised to edit 
else { ?>
      <p class="alert warning alert-warning" role="alert">Sorry, you are not authorised to edit this page. Please consult your system administrator. <?php echo "[". $row_rsArticle['writerankID'].",".$row_rsArticle['groupwriteID']."]"; ?> </p>
      <?php } //not authorsised to edit ?>
      <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var client = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
      <script>
<!--
var client = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:0});
//-->
    </script>
      <?php } ?>
      <script>
if(!commonjsversion || commonjsversion<2.0) alert("This page requires a later version of the Javascript library. Please reinstall.");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {isRequired:false, hint:"(Optional - replaces menu title above on page header)"});
</script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsArticle);

mysql_free_result($rsStatus);

mysql_free_result($rsPreferences);

mysql_free_result($rsSections);

mysql_free_result($rsPhotoGalleries);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsTemplates);

mysql_free_result($rsRegions);

mysql_free_result($rsVersions);

mysql_free_result($rsArticlePrefs);
?>
