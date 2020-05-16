<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/surveyfunctions.inc.php'); ?>
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

if(isset($_GET['resetsessionID']) && $_GET['resetsessionID']>0) { // reset session
	mysql_select_db($database_aquiescedb, $aquiescedb);
 	$update = "UPDATE survey_session SET enddatetime = NULL WHERE ID = ".intval($_GET['resetsessionID']);
 	$result = mysql_query($update, $aquiescedb) or die(mysql_error());
}

if (isset($_GET['deletesessionID']) && $_GET['deletesessionID']>0 ) {
	// delete user's answers
	deleteSurvey($_GET['deletesessionID']);
}

$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : "survey_session.enddatetime DESC";

$currentPage = $_SERVER["PHP_SELF"];

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$maxRows_rsResponders = 20;
$pageNum_rsResponders = 0;
if (isset($_GET['pageNum_rsResponders'])) {
  $pageNum_rsResponders = $_GET['pageNum_rsResponders'];
}
$startRow_rsResponders = $pageNum_rsResponders * $maxRows_rsResponders;

$varSurveyID_rsResponders = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsResponders = $_GET['surveyID'];
}
$varSurname_rsResponders = "-1";
if (isset($_GET['surname'])) {
  $varSurname_rsResponders = $_GET['surname'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponders = sprintf("SELECT users.firstname, users.surname, users.ID, users.username, users.email, survey_session.startdatetime, survey_session.enddatetime, users.jobtitle, survey_session.ID AS sessionID, directory.name FROM survey_session LEFT JOIN users ON (users.ID = survey_session.userID)  LEFT JOIN directory ON (survey_session.directoryID = directory.ID) WHERE survey_session.surveyID = %s AND (%s = '-1' OR users.surname LIKE %s)  ORDER BY ".$orderby."", GetSQLValueString($varSurveyID_rsResponders, "int"),GetSQLValueString($varSurname_rsResponders, "text"),GetSQLValueString($varSurname_rsResponders . "%", "text"));
$query_limit_rsResponders = sprintf("%s LIMIT %d, %d", $query_rsResponders, $startRow_rsResponders, $maxRows_rsResponders);
$rsResponders = mysql_query($query_limit_rsResponders, $aquiescedb) or die(mysql_error());
$row_rsResponders = mysql_fetch_assoc($rsResponders);

if (isset($_GET['totalRows_rsResponders'])) {
  $totalRows_rsResponders = $_GET['totalRows_rsResponders'];
} else {
  $all_rsResponders = mysql_query($query_rsResponders);
  $totalRows_rsResponders = mysql_num_rows($all_rsResponders);
}
$totalPages_rsResponders = ceil($totalRows_rsResponders/$maxRows_rsResponders)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

$queryString_rsResponders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsResponders") == false && 
        stristr($param, "totalRows_rsResponders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsResponders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsResponders = sprintf("&totalRows_rsResponders=%d%s", $totalRows_rsResponders, $queryString_rsResponders);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Survey Results - User Answers"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
   <div class="page surveys"><h1><i class="glyphicon glyphicon-education"></i> Survey Results</h1>
   <h2><?php echo $row_rsThisSurvey['surveyname']; ?></h2>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Surveys</a></li> 
    <li><a href="results.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> View by question</a></li>
   </ul></div></nav>
   <form action="index.php" method="get" name="form1" id="form1">
     <fieldset class="form-inline">
       <legend>Filter</legend>
       <span id="sprytextfield1">
       <input name="surname" type="text" id="surname" value="<?php echo isset($_GET['surname']) ? htmlentities($_GET['surname']) : ""; ?>" size="20" maxlength="20" class="form-control" />
</span>
        order by 
        <select name="orderby" id="orderby" class="form-control" >
         <option value="survey_session.enddatetime DESC" <?php if (!(strcmp("survey_session.enddatetime DESC", @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>Order by...</option>
         <option value="survey_session.enddatetime DESC" <?php if (!(strcmp("survey_session.enddatetime DESC", @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>Most recent completed</option>
         <option value="survey_session.enddatetime ASC" <?php if (!(strcmp("survey_session.enddatetime ASC", @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>Not Completed</option>
         <option value="users.surname" <?php if (!(strcmp("users.surname", @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>Surname</option>
       </select>
       <input name="surveyID" type="hidden" id="surveyID" value="<?php echo intval($_GET['surveyID']); ?>" />
       <button type="submit" name="search" id="search" class="btn btn-default btn-secondary" >Search...</button>
     </fieldset>
   </form>
   <?php if ($totalRows_rsResponders > 0) { // Show if recordset not empty ?>
     <p class="text-muted">Responders <?php echo ($startRow_rsResponders + 1) ?> to <?php echo min($startRow_rsResponders + $maxRows_rsResponders, $totalRows_rsResponders) ?> of <?php echo $totalRows_rsResponders ?> </p>
     <table class="table table-hover">
       <tbody>
       <?php do { ?>
         <tr>
           <td class="status<?php $status = isset($row_rsResponders['enddatetime']) ? 1 : 0 ; echo $status; ?>"></td>
           <td><?php echo isset($row_rsResponders['enddatetime']) ? date('d M Y H:i', strtotime($row_rsResponders['enddatetime'])) : date('d M Y H:i', strtotime($row_rsResponders['startdatetime'])); ?></td>
           <td><?php echo isset($row_rsResponders['ID']) ? "<a href=\"../../../members/admin/modify_user.php?userID=".$row_rsResponders['ID']."\">".$row_rsResponders['firstname']." ".$row_rsResponders['surname']."</a>" : "<em>Anonymous</em>"; ?> <?php echo (isset($row_rsResponders['jobtitle']) && $row_rsResponders['jobtitle']!="") ? "(".$row_rsResponders['jobtitle'].")" : ""; ?></td>
           <td><?php echo isset($row_rsResponders['name']) ? $row_rsResponders['name'] : "&nbsp;"; ?></td>
            <td><?php echo isset($row_rsResponders['email']) ? "<a href=\"mailto:".$row_rsResponders['email']."\">".$row_rsResponders['email']."</a>" : "&nbsp;"; ?></td>
           <td><a href="user_summary.php?sessionID=<?php echo $row_rsResponders['sessionID']; ?>" class="link_view">View</a></td>
           <td><a href="user.php?userID=<?php echo $row_rsResponders['ID']; ?>" class="link_view">All</a></td> 
           <td><a href="index.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&amp;resetsessionID=<?php echo $row_rsResponders['sessionID']; ?>" class="link_undo display<?php echo $status; ?>" onclick="return confirm('Are you sure you wish to reset this survey for this respondent?\n\nAll answers will remain, but can be updated as required.')">Reset</a></td>
		  <td><a href="index.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&amp;deletesessionID=<?php echo $row_rsResponders['sessionID']; ?>" onclick="document.returnValue = confirm('Are you sure you want to delete this user’s answers?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
		 
         </tr>
         <?php } while ($row_rsResponders = mysql_fetch_assoc($rsResponders)); ?></tbody>
     </table>
     <?php } // Show if recordset not empty ?>
     <?php if ($totalRows_rsResponders == 0) { // Show if recordset empty ?>
  <p>There have been no responsers so far.</p>
  <?php } // Show if recordset empty ?>
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsResponders > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsResponders=%d%s", $currentPage, 0, $queryString_rsResponders); ?>">First</a>
      <?php } // Show if not first page ?>
      </td>
    <td><?php if ($pageNum_rsResponders > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsResponders=%d%s", $currentPage, max(0, $pageNum_rsResponders - 1), $queryString_rsResponders); ?>" rel="prev">Previous</a>
      <?php } // Show if not first page ?>
      </td>
    <td><?php if ($pageNum_rsResponders < $totalPages_rsResponders) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsResponders=%d%s", $currentPage, min($totalPages_rsResponders, $pageNum_rsResponders + 1), $queryString_rsResponders); ?>" rel="next">Next</a>
      <?php } // Show if not last page ?>
      </td>
    <td><?php if ($pageNum_rsResponders < $totalPages_rsResponders) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsResponders=%d%s", $currentPage, $totalPages_rsResponders, $queryString_rsResponders); ?>">Last</a>
      <?php } // Show if not last page ?>
      </td>
    </tr>
</table>
  
  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"All surnames", isRequired:false});
//-->
  </script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisSurvey);

mysql_free_result($rsResponders);

mysql_free_result($rsSurveyPrefs);
?>