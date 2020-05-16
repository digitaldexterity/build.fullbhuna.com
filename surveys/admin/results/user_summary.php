<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php set_time_limit(120);// set max execution to 2 mins to allow for many calculations
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

if(isset($_GET['deletequestionID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM survey_comments WHERE questionID= ".GetSQLValueString($_GET['deletequestionID'], "int")." AND sessionID = ".GetSQLValueString($_GET['sessionID'], "text");
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM survey_response_text WHERE questionID= ".GetSQLValueString($_GET['deletequestionID'], "int")." AND sessionID = ".GetSQLValueString($_GET['sessionID'], "text");
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$delete = "DELETE FROM survey_scores WHERE questionID= ".GetSQLValueString($_GET['deletequestionID'], "int")." AND sessionID = ".GetSQLValueString($_GET['sessionID'], "text");
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	
	$delete = "DELETE survey_response_choice FROM survey_response_choice LEFT JOIN survey_answer ON (survey_response_choice.answerID = survey_answer.ID) WHERE sessionID = ".GetSQLValueString($_GET['sessionID'], "text")." AND survey_answer.questionID = ".GetSQLValueString($_GET['deletequestionID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	
	$delete = "DELETE survey_response_multitext FROM survey_response_multitext LEFT JOIN survey_answer ON (survey_response_multitext.answerID = survey_answer.ID) WHERE sessionID = ".GetSQLValueString($_GET['sessionID'], "text")." AND survey_answer.questionID = ".GetSQLValueString($_GET['deletequestionID'], "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	
}

if(isset($_GET['edit'])) {
	$_SESSION['survey_session'] = $_GET['survey_session'];
	$url = "/surveys/survey.php?surveyID=".intval($_GET['surveyID']);
	$url .= isset($_GET['directoryID']) ? "&directoryID=".intval($_GET['directoryID']) : "";
	header("Location: ".$url);
	exit;
}

if(isset($_GET['completed']) && isset($_GET['sessionID'])) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$update = "UPDATE survey_session SET enddatetime = NOW() WHERE ID = ".intval($_GET['sessionID']);
	mysql_query($update, $aquiescedb) or die(mysql_error());
}

  

$varSessionID_rsThisSurvey = "-1";
if (isset($_GET['sessionID'])) {
  $varSessionID_rsThisSurvey = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT survey_session.ID, survey_session.surveyID, survey_session.sessionID, survey_session.userID, survey_session.directoryID, survey.surveyname, users.firstname, users.surname, survey_session.startdatetime, survey_session.enddatetime, directory.name FROM survey_session LEFT JOIN survey ON (survey_session.surveyID = survey.ID) LEFT JOIN users ON (survey_session.userID = users.ID) LEFT JOIN directory ON (survey_session.directoryID = directory.ID) WHERE survey_session.ID = %s", GetSQLValueString($varSessionID_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $row_rsThisSurvey['surveyname']; ?></title>
<style >
<!--
body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
}
td {
	background-color: #FFFFFF;
	margin: 0px;
	padding: 2px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-right-style: solid;
	border-bottom-style: solid;
	border-right-color: #000000;
	border-bottom-color: #000000;
	border-top-style: none;
	border-left-style: none;
}
tr {
	margin: 0px;
	padding: 0px;
}
table {
	margin: 0px;
	padding: 0px;
	border-top-style: solid;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: solid;
	border-top-width: 1px;
	border-left-width: 1px;
	border-top-color: #000000;
	border-left-color: #000000;
}
.pagebreakbefore {
	page-break-before: always;
}
-->
</style>
</head><body>
<h1><i class="glyphicon glyphicon-education"></i> <?php echo $row_rsThisSurvey['surveyname']; ?></h1>
<h2 class="singleuser">Name: <a href="../../../members/admin/modify_user.php?userID=<?php echo $row_rsThisSurvey['userID']; ?>"><?php echo $row_rsThisSurvey['firstname']; ?> <?php echo $row_rsThisSurvey['surname']; ?></a> <a href="../../../directory/admin/update_directory.php?directoryID=<?php echo $row_rsThisSurvey['directoryID']; ?>"><?php echo isset($row_rsThisSurvey['name']) ? "for ".$row_rsThisSurvey['name'] : ""; ?></a></h2>
<?php if(isset($row_rsThisSurvey['enddatetime'])) { ?>
<p> This survey was completed: <?php echo date('d M Y H:s', strtotime($row_rsThisSurvey['enddatetime'])); ?> <a href="user_summary.php?edit=true&surveyID=<?php echo $row_rsThisSurvey['surveyID']; ?>&survey_session=<?php echo $row_rsThisSurvey['ID']; echo isset($row_rsThisSurvey['directoryID']) ? "&directoryID=".$row_rsThisSurvey['directoryID'] : ""; ?>">Edit survey</a></p>
<?php } else { ?>
<p>This survey has not been completed. <a href="user_summary.php?sessionID=<?php echo urlencode($_GET['sessionID']); ?>&amp;completed=true">Set as completed</a>.</p>
<?php } ?>

<?php 
// ***************************** START  QUESTIONS ***************************************
$varSessionID_rsSurveyQuestion = "-1";
if (isset($_GET['sessionID'])) {
  $varSessionID_rsSurveyQuestion = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyQuestion = sprintf("SELECT  survey_question.question_number, survey_question.questiontype,  survey_comments.comments, survey_scores.score, survey_response_text.response_text, survey_question.questiontext, survey_question.questionnotes, survey_question.ID, survey_section.subsectionofID,  survey_section.`description` AS section,  mainsection.ID AS mainsectionID, mainsection.`description` AS mainsection, topic.`description` AS topic, topic.ID AS topicID, survey_scores.finalscore, survey_section.weight AS subtopicweight, topic.weight AS topicweight, survey_question.questionweight, survey_section.ID AS subTopicID, mainsection.weight AS sectionweight FROM survey_session  LEFT JOIN survey_question ON (survey_question.surveyID = survey_session.surveyID) LEFT JOIN survey_section ON (survey_question.surveysectionID = survey_section.ID) LEFT JOIN survey_section AS topic ON (survey_section.subsectionofID = topic.ID) LEFT JOIN survey_section AS mainsection ON (topic.subsectionofID = mainsection.ID) LEFT JOIN survey_comments ON (survey_comments.questionID = survey_question.ID AND survey_comments.sessionID = survey_session.ID) LEFT JOIN survey_scores ON (survey_scores.sessionID = survey_session.ID AND survey_scores.questionID = survey_question.ID)  LEFT JOIN survey_response_text ON (survey_response_text.sessionID = survey_session.ID AND survey_response_text.questionID = survey_question.ID) WHERE survey_session.ID = %s ORDER BY CAST(survey_question.question_number AS UNSIGNED)", GetSQLValueString($varSessionID_rsSurveyQuestion, "int"));
$rsSurveyQuestion = mysql_query($query_rsSurveyQuestion, $aquiescedb) or die(mysql_error());
$row_rsSurveyQuestion = mysql_fetch_assoc($rsSurveyQuestion);
$totalRows_rsSurveyQuestion = mysql_num_rows($rsSurveyQuestion);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);


// *****************************************   START OUTPUT **********************************


$prevSubTopic = ""; $subTotal = 0; $comments = "";


 ?>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">

  <?php


do { // Main question loop
  
  
// get multi choice answers
	 
	   $varQuestionID_rsAnswerChoices = "-1";
if (isset($row_rsSurveyQuestion['ID'])) {
  $varQuestionID_rsAnswerChoices = $row_rsSurveyQuestion['ID'];
}
$varSessionID_rsAnswerChoices = "-1";
if (isset($_GET['sessionID'])) {
  $varSessionID_rsAnswerChoices = $_GET['sessionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAnswerChoices = "SELECT survey_answer.answertext, survey_answer.answerscore, survey_response_choice.ID AS checked , survey_response_multitext.response
FROM survey_answer 
LEFT JOIN survey_response_choice ON (survey_answer.ID = survey_response_choice.answerID AND survey_response_choice.sessionID = ".GetSQLValueString($varSessionID_rsAnswerChoices, "int").") 
LEFT JOIN survey_response_multitext ON (survey_answer.ID = survey_response_multitext.answerID AND survey_response_multitext.sessionID = ".GetSQLValueString($varSessionID_rsAnswerChoices, "int").") 
WHERE survey_answer.questionID = ".GetSQLValueString($varQuestionID_rsAnswerChoices, "int")." GROUP BY survey_answer.ID ORDER BY survey_answer.ordernum, survey_answer.answerscore";
$rsAnswerChoices = mysql_query($query_rsAnswerChoices, $aquiescedb) or die(mysql_error());
$row_rsAnswerChoices = mysql_fetch_assoc($rsAnswerChoices);
$totalRows_rsAnswerChoices = mysql_num_rows($rsAnswerChoices);




if ($prevSubTopic != $row_rsSurveyQuestion['section']) { $prevSubTopic = $row_rsSurveyQuestion['section']; ?>

  <tr>
    <td colspan="4"><h2><?php echo $row_rsSurveyQuestion['section']; ?></h2></td>
  </tr>
  <tr><?php } // end new sub topic ?>
    <td><strong><?php echo $row_rsSurveyQuestion['question_number']; ?></strong></td>
    <td colspan="2" ><strong><?php echo $row_rsSurveyQuestion['questiontext']; $rowspan = ($totalRows_rsAnswerChoices>0) ? $totalRows_rsAnswerChoices+1 : 2; $comments .= ($row_rsSurveyQuestion['comments']!="") ? nl2br($row_rsSurveyQuestion['comments'])."<br>" : ""; ?></strong>      <?php $notes = explode(": ",$row_rsSurveyQuestion['questionnotes']); echo isset($notes[1]) ? $notes[1] : "";?></td>
    <td><a href="user_summary.php?sessionID=<?php echo urlencode($_GET['sessionID']); ?>&deletequestionID=<?php echo $row_rsSurveyQuestion['ID']; ?>">Delete answer</a></td>
  </tr>
  <?php 
	

 if ($row_rsSurveyQuestion['questiontype']<=2 && $totalRows_rsAnswerChoices > 1) { // Show if recordset not empty ?>
    <?php do { $answerText = explode("[",$row_rsAnswerChoices['answertext']); ?>
    <tr>
      <td align="right"><?php echo isset($answerText[1]) ? str_replace("]","",$answerText[1]) : ""; ?></td>
      <td align="right"><input name="" type="checkbox" value=""<?php echo (isset($row_rsAnswerChoices['checked']) || isset($row_rsAnswerChoices['response'])) ? "checked" : ""; ?> onclick="return false;" /></td>
      <td><?php echo $answerText[0]; ?><?php echo isset($row_rsAnswerChoices['response']) ? ":&nbsp;".$row_rsAnswerChoices['response'] : ""; ?></td>
      <td>&nbsp;</td>
    </tr>
    <?php } while ($row_rsAnswerChoices = mysql_fetch_assoc($rsAnswerChoices)); ?>
    <?php } // Show if recordset not empty 
	
		else { ?><tr><td>&nbsp;</td><td align="right">&nbsp;</td><td><?php echo nl2br($row_rsSurveyQuestion['response_text']); ?>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <?php } 
		
    if($row_rsSurveyQuestion['comments']!="") { 
?>
<tr><td>&nbsp;</td><td align="right">Notes:</td><td><?php echo nl2br($row_rsSurveyQuestion['comments']); ?>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
<?php }


	  
  // end main question loop
	

	} while ($row_rsSurveyQuestion = mysql_fetch_assoc($rsSurveyQuestion)); 
	
	
	?>
</table>
</body>
</html>
<?php
mysql_free_result($rsThisSurvey);


mysql_free_result($rsSurveyQuestion);

mysql_free_result($rsSurveyPrefs);


mysql_free_result($rsAnswerChoices);

?>
