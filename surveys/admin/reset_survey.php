<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
 require_once('../../Connections/aquiescedb.php');

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

// delete sessions
mysql_select_db($database_aquiescedb, $aquiescedb);
$query = "DELETE FROM survey_session WHERE surveyID = ".intval($_GET['surveyID']);
//$result = mysql_query($query, $aquiescedb) or die(mysql_error());

//clean up elsewhere
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


$varSurveyID_rsTextAnswers = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsTextAnswers = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTextAnswers = sprintf("DELETE FROM survey_response_text USING survey_response_text, survey_question WHERE survey_response_text.questionID = survey_question.ID AND survey_question.surveyID = %s", GetSQLValueString($varSurveyID_rsTextAnswers, "int"));
$rsTextAnswers = mysql_query($query_rsTextAnswers, $aquiescedb) or die(mysql_error());


$varSurveyID_rsMultiAnswers = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsMultiAnswers = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMultiAnswers = sprintf("DELETE FROM survey_response_choice USING survey_response_choice, survey_answer, survey_question WHERE survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = survey_question.ID AND survey_question.surveyID = %s", GetSQLValueString($varSurveyID_rsMultiAnswers, "int"));
$rsMultiAnswers = mysql_query($query_rsMultiAnswers, $aquiescedb) or die(mysql_error());

$varSurveyID_rsScores = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsScores = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsScores = sprintf("DELETE FROM survey_scores USING survey_scores, survey_question WHERE survey_scores.questionID = survey_question.ID AND survey_question.surveyID = %s", GetSQLValueString($varSurveyID_rsScores, "int"));
$rsScores = mysql_query($query_rsScores, $aquiescedb) or die(mysql_error());


$varSurveyID_rsComments = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsComments = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("DELETE FROM survey_comments USING survey_comments, survey_question WHERE survey_comments.questionID = survey_question.ID AND survey_question.surveyID = %s", GetSQLValueString($varSurveyID_rsComments, "int"));
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());

$varSurveyID_rsSurveyID = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsSurveyID = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query = sprintf("DELETE FROM survey_session WHERE surveyID = %s", GetSQLValueString($varSurveyID_rsSurveyID, "int"));
$rs = mysql_query($query, $aquiescedb) or die(mysql_error());
?><!doctype html><html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Responses deleted"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->All responses for this survey have been deleted.<!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
