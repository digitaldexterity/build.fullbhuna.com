<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php
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
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	// check for duplication 
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT linkkeywords FROM keywordlinks WHERE ID !=".GetSQLValueString($_POST['ID'], "int");
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		while($row = mysql_fetch_assoc($result)) {
			if(stristr($row['linkkeywords'], $_POST['linkkeywords']) || stristr($_POST['linkkeywords'], $row['linkkeywords'])){
				$error = $_POST['linkkeywords']." conflicts with existing ".$row['linkkeywords'].". Keyphrases than contain other keyphrases can cause unpredicatable results.";
				unset($_POST["MM_update"]);
			}
		}
	}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE keywordlinks SET linkkeywords=%s, linkURL=%s, linktitle=%s, modifiedbyID=%s, modifieddatetime=%s, statusID=%s WHERE ID=%s",
                       GetSQLValueString($_POST['linkkeywords'], "text"),
                       GetSQLValueString($_POST['linkURL'], "text"),
                       GetSQLValueString($_POST['linktitle'], "text"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifiedddatetime'], "date"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());

  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = '%s'", $colname_rsLoggedIn);
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsStatus = "SELECT * FROM status";
$rsStatus = mysql_query($query_rsStatus, $aquiescedb) or die(mysql_error());
$row_rsStatus = mysql_fetch_assoc($rsStatus);
$totalRows_rsStatus = mysql_num_rows($rsStatus);

$colname_rsKeywordLink = "-1";
if (isset($_GET['keywordlinkID'])) {
  $colname_rsKeywordLink = $_GET['keywordlinkID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsKeywordLink = sprintf("SELECT * FROM keywordlinks WHERE ID = %s", GetSQLValueString($colname_rsKeywordLink, "int"));
$rsKeywordLink = mysql_query($query_rsKeywordLink, $aquiescedb) or die(mysql_error());
$row_rsKeywordLink = mysql_fetch_assoc($rsKeywordLink);
$totalRows_rsKeywordLink = mysql_num_rows($rsKeywordLink);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Update Auto Link"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
   <div class="page seo"><h1><i class="glyphicon glyphicon-globe"></i> Update Auto Link   </h1><?php require_once('../../../includes/alert.inc.php'); ?>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Key word/phrase:</td>
         <td><span id="sprytextfield1">
           <input name="linkkeywords" type="text" class="form-control"  value="<?php echo htmlentities($row_rsKeywordLink['linkkeywords'], ENT_COMPAT, "UTF-8"); ?>" size="60" maxlength="255" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Link URL:</td>
         <td><span id="sprytextfield2">
           <input name="linkURL" type="text" class="form-control"  value="<?php echo htmlentities($row_rsKeywordLink['linkURL'], ENT_COMPAT, "UTF-8"); ?>" size="60" maxlength="255" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Title:</td>
         <td><input name="linktitle" type="text" class="form-control"  value="<?php echo htmlentities($row_rsKeywordLink['linktitle'], ENT_COMPAT, "UTF-8"); ?>" size="60" maxlength="255" /></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Status:</td>
         <td><select name="statusID"  id="statusID">
           <?php
do {  
?>
           <option value="<?php echo $row_rsStatus['ID']?>"<?php if (!(strcmp($row_rsStatus['ID'], $row_rsKeywordLink['statusID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsStatus['description']?></option>
           <?php
} while ($row_rsStatus = mysql_fetch_assoc($rsStatus));
  $rows = mysql_num_rows($rsStatus);
  if($rows > 0) {
      mysql_data_seek($rsStatus, 0);
	  $row_rsStatus = mysql_fetch_assoc($rsStatus);
  }
?>
         </select>
</td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Save changes</button></td>
       </tr>
     </table>
     <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="modifiedddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsKeywordLink['ID']; ?>" />
     <input type="hidden" name="MM_update" value="form1" />
   </form>
   <p>&nbsp;</p>
   <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
//-->
</script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsStatus);

mysql_free_result($rsKeywordLink);
?>


