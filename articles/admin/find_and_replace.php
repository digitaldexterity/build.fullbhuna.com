<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/functions.inc.php'); ?>
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varRegionID_rsSections = "0";
if (isset($regionID)) {
  $varRegionID_rsSections = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT articlesection.ID, articlesection.`description`, parentsection.`description`AS parent FROM articlesection LEFT JOIN articlesection AS parentsection ON (articlesection.subsectionofID = parentsection.ID) WHERE  %s = 0 OR articlesection.regionID = %s OR articlesection.regionID= 0", GetSQLValueString($varRegionID_rsSections, "int"),GetSQLValueString($varRegionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Housekeeping - Find and Replace</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page articles">
<h1><i class="glyphicon glyphicon-file"></i> Find &amp; Replace...</h1>
<nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav"><li><a href="index.php" class="link_back"><i class="glyphicon glyphicon-arrow-left"></i> Manage Pages</a></li></ul></div></nav>
<p>The following page will find and replace any set of characters
 or words in the page titles, meta descriptions or body text. Be careful for unexpected results with simple words, such as replacing "me" for "you" will change "became" to "becayou".</p>
<?php if(isset($_POST['replace'])) {
	$excluded = array();
	$included = array();
	
	$fields = array("body", "metadescription", "title", "seotitle");
	$find = utf8_decode($_POST['find']);
	$replace = utf8_decode($_POST['replace']);
	if (PHP_VERSION < 6) {
    	$find = get_magic_quotes_gpc() ? stripslashes($find) : $find;
		$replace = get_magic_quotes_gpc() ? stripslashes($replace) : $replace;
  	}
	$where = "";
	if(isset($_POST['sectionID']) && intval($_POST['sectionID'])>0) {
		$where .= " AND (articlesection.ID = ".intval($_POST['sectionID'])." OR articlesection.subsectionofID = ".intval($_POST['sectionID']).") ";
	}
	$select = "SELECT article.ID, article.".implode(", article.",$fields)." FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE versionofID IS NULL AND (article.regionID = ".$regionID." OR articlesection.regionID = ".$regionID.")".$where;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	while($row = mysql_fetch_assoc($result)) {
		$sql = "";
		foreach($fields as $key=>$field) {
			$new= isset($_POST['casesensitive']) ? str_replace($find, $replace, $row[$field]) : str_ireplace($find, $replace, $row[$field]);
			if(isset($_POST['fix'])) {
				// fix empty <p> tags (tinymce set not to check) and grammarly markup
			
				$new = str_ireplace("<p></p>","<p>&nbsp;</p>", $new);
				$new = preg_replace("/<g (.*)>(.*)<\/g>/iU", "$2", $new);
			}

			if($new !=$row[$field]) { // are changes
				$count ++;
				$sql .= $sql == "" ? "SET " : ", ";
				$sql .= $field." = ".GetSQLValueString($new, "text");					
			} 
		} // end foreach field
		if($sql!="") {
			
			$update = "UPDATE article ".$sql.", modifiedbyID = ".intval($row_rsLoggedIn['ID']).", modifieddatetime = '".date('Y-m-d H:i:s')."' WHERE ID = ".$row['ID'];
			if(!isset($_POST['dryrun'])) {
				saveArticleVersion($articleID);
				mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
				$actiontext = "have been";
			} else {
				$actiontext = "will be";
			}
			array_push($included,$row['ID']);
		} else {
			array_push($excluded,$row['ID']);
		}
		 				
	}	// end for each article
	$msg =  "Search complete. ".count($included)." of ".mysql_num_rows($result)." pages ".$actiontext." updated.";
}
	?>
    <?php require_once('../../core/includes/alert.inc.php'); ?>

<form action="" method="post" id="form1">
    <table class="form-table">
    <tr>
      <td><label for="find">Find:</label></td>
      <td><label for="replace">Replace:</label></td>
    </tr>
    <tr>
      <td class="top"><span id="sprytextarea1">
        <textarea name="find" id="find" cols="45" rows="5" class="form-control"><?php echo isset($find) ? htmlentities($find, ENT_COMPAT, "UTF-8") : ""; ?></textarea><br />
        <span class="textareaRequiredMsg">A value is required.</span></span></td>
      <td class="top">
        <textarea name="replace" id="replace" cols="45" rows="5" class="form-control"><?php echo isset($replace) ? htmlentities($replace, ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
      </tr>
    <tr>
      <td colspan="2" class="form-inline"><label for="submit"></label>
       
        <label>
          <input type="checkbox" name="dryrun" id="dryrun" <?php if(isset($_POST['dryrun'])) { echo "checked \"checked\""; } ?> />
          Dry run
          </label>
        
        &nbsp;&nbsp;&nbsp;
        <label>
          <input type="checkbox" name="casesensitive" id="casesensitive" <?php if(isset($_POST['casesensitive'])) { echo "checked \"checked\""; } ?> />
          Case sensitive
          </label>
          
           &nbsp;&nbsp;&nbsp;
        <label>
          <input type="checkbox" name="fix"  <?php if(isset($_POST['fix'])) { echo "checked \"checked\""; } ?> />
          Fix HTML
          </label>
          
          
           &nbsp;&nbsp;&nbsp;
        <label class="section">
          in section <select name="sectionID" id="sectionID" class=" form-control">
      <option value="0" <?php if (!(strcmp(0, @$_POST['sectionID']))) {echo "selected=\"selected\"";} ?> >All sections</option><option value="-1" <?php if (!(strcmp(-1, @$_POST['sectionID']))) {echo "selected=\"selected\"";} ?>>Templates</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], @$_POST['sectionID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($row_rsSections['parent']) ? $row_rsSections['parent']." &rsaquo; " : ""; echo  $row_rsSections['description'];  ?></option>
      <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
  </select> </label>  <button type="submit" class="btn btn-primary"  onclick="if(!document.getElementById('dryrun').checked) { return confirm('Are you sure you want to replace all occurances? This cannot be undone, although original versions of pages are available in the History tab'); }" >Replace...</button>
         
         
        
      </td>
      </tr>
    </table>
</form></div>
<script>
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
</script>
<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSections);
?>
