<?php require_once('../Connections/aquiescedb.php'); ?>
<?php if (!isset($_SESSION)) {
  session_start();
}
if (isset($_GET['sessionID']) && $_SESSION['MM_UserGroup'] >=8) {// get forced session from admin
$_SESSION['survey_session'] = $_GET['sessionID'];
}
if (!isset($_SESSION['survey_session']) || !isset($_GET['surveyID']) || $_GET['surveyID'] < 1) { //need a session and a survey to continue
header("location: index.php?error=".urlencode('Your session has expired, you will need to restart the questionnaire. If you were logged in your answers so far should be saved.')); }
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsQuestions = 100;
$pageNum_rsQuestions = 0;
if (isset($_GET['pageNum_rsQuestions'])) {
  $pageNum_rsQuestions = $_GET['pageNum_rsQuestions'];
}
$startRow_rsQuestions = $pageNum_rsQuestions * $maxRows_rsQuestions;

$varSurveyID_rsQuestions = "1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsQuestions = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsQuestions = sprintf("SELECT survey_question.ID, survey_question.question_number, survey_question.questiontext, survey_question.questiontype, survey_section.sectionnumber, survey_section.`description` AS section,  mainsection.`description` AS mainsection, supersection.`description` AS supersection, supersection.ID AS supersectionID, survey_question.addscore FROM survey_question  LEFT JOIN survey_section ON (survey_question.surveysectionID = survey_section.ID) LEFT JOIN survey_section AS supersection ON (survey_section.subsectionofID = supersection.ID) LEFT JOIN survey_section AS mainsection ON (supersection.subsectionofID = mainsection.ID) WHERE survey_question.active = 1 AND survey_question.surveyID = %s GROUP BY survey_question.question_number ORDER BY CAST(survey_question.question_number AS UNSIGNED)", GetSQLValueString($varSurveyID_rsQuestions, "int"));
$query_limit_rsQuestions = sprintf("%s LIMIT %d, %d", $query_rsQuestions, $startRow_rsQuestions, $maxRows_rsQuestions);
$rsQuestions = mysql_query($query_limit_rsQuestions, $aquiescedb) or die(mysql_error());
$row_rsQuestions = mysql_fetch_assoc($rsQuestions);

if (isset($_GET['totalRows_rsQuestions'])) {
  $totalRows_rsQuestions = $_GET['totalRows_rsQuestions'];
} else {
  $all_rsQuestions = mysql_query($query_rsQuestions);
  $totalRows_rsQuestions = mysql_num_rows($all_rsQuestions);
}
$totalPages_rsQuestions = ceil($totalRows_rsQuestions/$maxRows_rsQuestions)-1;

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname, usescoring FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$queryString_rsQuestions = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsQuestions") == false && 
        stristr($param, "totalRows_rsQuestions") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsQuestions = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsQuestions = sprintf("&totalRows_rsQuestions=%d%s", $totalRows_rsQuestions, $queryString_rsQuestions);

$num_rows = mysql_num_rows($rsQuestions);
$body_class = isset($body_class) ? $body_class :"";
$body_class .= " survey survey".$row_rsThisSurvey['ID']." surveysummary ";

?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $row_rsThisSurvey['surveyname']." - Question Summary"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" --> <div id = "survey" class="survey container pageBody">
    <h1> Home:    <?php echo $row_rsThisSurvey['surveyname']; ?></h1>
    <?php if (isset($_GET['start'])) { ?><p>Below is a summary of the questions. To begin, click on the magnifying glass next to the question you wish to start on:</p><?php } ?>
    <?php if (isset($_GET['finished'])) { ?>
    <p>You have reached the end of the questionnaire. Please review your questions below and click on &quot;Submit&quot; at the foot of the page once completed.</p>
    <?php } ?>
    <script> var unanswered = false; </script>
    <p><strong>Key:</strong><img src="../core/images/icons/green-light.png" alt="Green light" width="16" height="16" style="vertical-align:
middle;" />- Question completed <img src="../core/images/icons/amber-light.png" alt="Amber light" width="16" height="16" style="vertical-align:
middle;" />- Question still to be completed</p>
    <table border="0" cellpadding="0" cellspacing="0" class="listTable">
      <tr>
        <td>&nbsp;</td>
        <td><strong>No.</strong></td>
        <td><strong>Question</strong></td>
        <td><strong>Rating</strong></td>
        <td class="text-nowrap"><strong>Go to</strong></td>
      </tr>
      <?php  $prevSection = ""; $row = 0; do { ?>
         <?php if ($row_rsQuestions['supersection'] != $prevSection) { ?> <tr>
           <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><strong><?php echo strtoupper($row_rsQuestions['mainsection']).": ".$row_rsQuestions['supersection']; $prevSection = $row_rsQuestions['supersection']?></strong></td>
            <td align="center">&nbsp;</td>
            <td>&nbsp;</td>
          </tr><?php } // end section if ?>
        <tr>
          <td><?php 
		  $varSessionID_rsScore = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsScore = $_SESSION['survey_session'];
}
$varQuestionID_rsScore = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsScore = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsScore = sprintf("SELECT survey_scores.score FROM survey_scores WHERE survey_scores.sessionID = %s AND survey_scores.questionID = %s", GetSQLValueString($varSessionID_rsScore, "text"),GetSQLValueString($varQuestionID_rsScore, "int"));
$rsScore = mysql_query($query_rsScore, $aquiescedb) or die(mysql_error());
$row_rsScore = mysql_fetch_assoc($rsScore);
$totalRows_rsScore = mysql_num_rows($rsScore);

$varSessionID_rsResponsesChoice = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsResponsesChoice = $_SESSION['survey_session'];
}
$varQuestionID_rsResponsesChoice = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsResponsesChoice = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponsesChoice = sprintf("SELECT survey_response_choice.ID FROM survey_response_choice, survey_answer WHERE survey_response_choice.sessionID = %s AND survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = %s", GetSQLValueString($varSessionID_rsResponsesChoice, "text"),GetSQLValueString($varQuestionID_rsResponsesChoice, "int"));
$rsResponsesChoice = mysql_query($query_rsResponsesChoice, $aquiescedb) or die(mysql_error());
$row_rsResponsesChoice = mysql_fetch_assoc($rsResponsesChoice);
$totalRows_rsResponsesChoice = mysql_num_rows($rsResponsesChoice);

$varSessionID_rsResponseText = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsResponseText = $_SESSION['survey_session'];
}
$varQuestionID_rsResponseText = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsResponseText = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseText = sprintf("SELECT survey_response_text.ID FROM survey_response_text WHERE survey_response_text.sessionID = %s AND survey_response_text.questionID = %s", GetSQLValueString($varSessionID_rsResponseText, "text"),GetSQLValueString($varQuestionID_rsResponseText, "int"));
$rsResponseText = mysql_query($query_rsResponseText, $aquiescedb) or die(mysql_error());
$row_rsResponseText = mysql_fetch_assoc($rsResponseText);
$totalRows_rsResponseText = mysql_num_rows($rsResponseText);

?><?php if (isset($row_rsResponseText['ID']) || $totalRows_rsResponsesChoice>0) { 
		  // if  answer is filled in?>
            <img src="../core/images/icons/green-light.png" alt="This question has been answered" width="16" height="16" style="vertical-align:
middle;" />
        <?php } else { ?><img src="../core/images/icons/amber-light.png" alt="This question has still to be answered" width="16" height="16" style="vertical-align:
middle;" /><script>unanswered = true;</script>
<?php } ?></td>
          <td align="right"><?php echo $row_rsQuestions['question_number']; ?></td>
          <td><?php echo $row_rsQuestions['questiontext']; ?></td>
          <td align="center"><?php  echo (isset($row_rsScore['score']) || $row_rsQuestions['addscore'] == 0 || $row_rsThisSurvey['usescoring'] == 0) ? $row_rsScore['score'] : "-"; ?></td>
          <td><a href="question.php?pageNum_rsQuestions=<?php echo $row ; ?>&totalRows_rsQuestions=<?php echo $num_rows; ?>&surveyID=<?php echo intval($_GET['surveyID']); ?>&prevSectionID=<?php echo $row_rsQuestions['supersectionID']; ?>" class="link_view">View</a></td>
        </tr>
        <?php $row++; } while ($row_rsQuestions = mysql_fetch_assoc($rsQuestions)); ?>
    </table>
  
    
    <p>
      <?php if(isset($_SESSION['MM_Username'])) { // a user session so they can come back later ?><input name="exit" type="submit" class="button" id="exit" onClick = "javascript:if(confirm('Are you sure you want to exit?\n\n(Your answers will saved but NOT submitted)')) document.location = 'finish.php?surveyID=<?php echo intval($_GET['surveyID']); ?>';" value="Save for later"/><?php } ?>
      <input name="complete" type="submit" class="button" id="complete"  onClick = "javascript:if (unanswered != true) { if(confirm('Are you sure you want to finish?\n\n(Your answers will finally submitted and you will NOT be able to return to this questionnaire)')) window.location.href = 'finish.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&finish=true'; } else { alert('You can not submit the final questionnaire until all answers are completed with a score. Please review.'); }" value="Finish (submit answers)" />
    </p>
    <ul>
      <li>You can &quot;Save for later&quot; and return to the survey later, or &quot;Finish&quot; to submit survey.</li>
      <li>To return to the survey, click on the magnifying glass icon next to the question you wish to commence from.</li>
      <li>If you need to see the instructions again <a href="survey.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&survey_session=<?php echo $_SESSION['survey_session']; ?>">click here</a>.</li>
    </ul>
    </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsQuestions);

mysql_free_result($rsThisSurvey);

mysql_free_result($rsScore);

mysql_free_result($rsResponsesChoice);

mysql_free_result($rsResponseText);
?>
