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


if (isset($_GET['release'])) { // re-activate this survey for user
 $deleteSQL = "DELETE survey_session.* FROM survey_session WHERE survey_session.sessionID = ".GetSQLValueString($_GET['username'].intval($_GET['surveyID']),"text")."";
                    
  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
  
  }
  

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname, survey.introduction FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$varSurveyID_rsSurveyQuestion = "8";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsSurveyQuestion = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyQuestion = sprintf("SELECT survey_question.question_number, survey_question.questiontext, survey_question.questionnotes, survey_question.ID, survey_question.addscore, survey_section.subsectionofID,  survey_section.`description` AS section,  mainsection.ID AS mainsectionID, mainsection.`description` AS mainsection, supersection.`description` AS supersection, supersection.ID AS supersectionID FROM survey_question LEFT JOIN survey_section ON (survey_question.surveysectionID = survey_section.ID) LEFT JOIN survey_section AS supersection ON (survey_section.subsectionofID = supersection.ID) LEFT JOIN survey_section AS mainsection ON (supersection.subsectionofID = mainsection.ID) WHERE survey_question.surveyID = %s AND survey_question.active =1 ORDER BY CAST(survey_question.question_number AS UNSIGNED)", GetSQLValueString($varSurveyID_rsSurveyQuestion, "int"));
$rsSurveyQuestion = mysql_query($query_rsSurveyQuestion, $aquiescedb) or die(mysql_error());
$row_rsSurveyQuestion = mysql_fetch_assoc($rsSurveyQuestion);
$totalRows_rsSurveyQuestion = mysql_num_rows($rsSurveyQuestion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Questions for <?php echo $row_rsThisSurvey['surveyname']; ?></title>
<script src="/includes/javascript/change_class.js"></script>
<style >
<!--
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
-->
</style></head>

<body>
<h1><i class="glyphicon glyphicon-education"></i> <?php echo $row_rsThisSurvey['surveyname']; $prevSection = ""; ?> - Question Summary</h1>
<p>&nbsp; </p>
<table border="0" cellpadding="0" cellspacing="0" class="form-table" >
<?php do { ?>
  
    <tr>
      <td colspan="3" class="text-nowrap  top"><?php if (isset($row_rsSurveyQuestion['section']) && $row_rsSurveyQuestion['supersection'] != $prevSection) { ?><h2><?php $prevSection = $row_rsSurveyQuestion['supersection'];; echo strtoupper($row_rsSurveyQuestion['mainsection']); ?>: <?php echo $row_rsSurveyQuestion['supersection']; ?></h2>
	
	<?php }  ?></td>
    </tr>
    <tr>
      <td class="text-nowrap  top"><?php echo $row_rsSurveyQuestion['question_number']; ?>.</td>
      <td class="top">&nbsp;</td>
      <td class="top"><?php echo $row_rsSurveyQuestion['questiontext']; ?></td>
    </tr>
    <tr>
      <td class="text-nowrap  top">&nbsp;</td>
      <td class="top">&nbsp;</td>
      <td class="top"><?php $select = "SELECT * FROM survey_answer WHERE questionID = ".$row_rsSurveyQuestion['ID'];
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) { 
				  echo "<ul>";
				  while($row = mysql_fetch_assoc($result)) {
					  echo "<li>".$row['answertext']."</li>";
				  }
				  echo "</ul>";
				  } // end is answers 
				  else { ?><br />
        <br />
        <br />
        <br />
        <br />
       
<br /><?php } ?></td>
    </tr> 
    <?php } while ($row_rsSurveyQuestion = mysql_fetch_assoc($rsSurveyQuestion)); ?></table >

</body>
</html>
<?php
mysql_free_result($rsThisSurvey);

mysql_free_result($rsSurveyQuestion);

mysql_free_result($rsSurveyPrefs);

?>
