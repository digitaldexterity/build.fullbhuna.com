<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsSurveys = 50;
$pageNum_rsSurveys = 0;
if (isset($_GET['pageNum_rsSurveys'])) {
  $pageNum_rsSurveys = $_GET['pageNum_rsSurveys'];
}
$startRow_rsSurveys = $pageNum_rsSurveys * $maxRows_rsSurveys;

$varUserID_rsSurveys = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsSurveys = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveys = sprintf("SELECT survey_session.ID, survey.surveyname, survey_session.startdatetime, survey_session.enddatetime, COUNT(survey_answer.answerscore) AS total, SUM(survey_answer.answerscore) AS score FROM survey_session LEFT JOIN survey ON (survey_session.surveyID  = survey.ID) LEFT JOIN survey_response_choice ON (survey_response_choice.sessionID = survey_session.ID) LEFT JOIN survey_answer ON (survey_response_choice.answerID =  survey_answer.ID) WHERE survey_session.userID = %s GROUP BY survey_session.ID ORDER BY survey_session.startdatetime ASC", GetSQLValueString($varUserID_rsSurveys, "int"));
$query_limit_rsSurveys = sprintf("%s LIMIT %d, %d", $query_rsSurveys, $startRow_rsSurveys, $maxRows_rsSurveys);
$rsSurveys = mysql_query($query_limit_rsSurveys, $aquiescedb) or die(mysql_error());
$row_rsSurveys = mysql_fetch_assoc($rsSurveys);

if (isset($_GET['totalRows_rsSurveys'])) {
  $totalRows_rsSurveys = $_GET['totalRows_rsSurveys'];
} else {
  $all_rsSurveys = mysql_query($query_rsSurveys);
  $totalRows_rsSurveys = mysql_num_rows($all_rsSurveys);
}
$totalPages_rsSurveys = ceil($totalRows_rsSurveys/$maxRows_rsSurveys)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "All Surveys by User"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
    <div class="page class">
      <h1>All Surveys by User</h1>
      <p>&nbsp;</p>
      <table class="table table-hover">
      <thead>
        <tr>
         
          <th>Survey</th>
          <th>Started</th>
          <th>Finished</th>
          <th>Score</th>
          <th>Total</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
          
            <td><?php echo $row_rsSurveys['surveyname']; ?></td>
            <td><?php echo $row_rsSurveys['startdatetime']; ?></td>
            <td><?php echo isset($row_rsSurveys['enddatetime']) ? date('d M Y H:i', strtotime($row_rsSurveys['enddatetime'])) : ""; ?></td>
            <td><?php echo $row_rsSurveys['score']; ?></td>
            <td><?php echo $row_rsSurveys['total']>0 ? $row_rsSurveys['total'] : ""; ?></td>
          </tr>
          <?php } while ($row_rsSurveys = mysql_fetch_assoc($rsSurveys)); ?></tbody>
      </table>
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsSurveys);
?>
