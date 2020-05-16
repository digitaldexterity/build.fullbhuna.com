<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION['survey_session']) || !isset($_GET['surveyID']) || intval($_GET['surveyID']) < 1) { //need a session and a survey to continue
header("location: index.php?error=".urlencode('Your session has expired, you will need to restart the questionnaire.')); exit; }
?><?php
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
// INSERT COMMENTS, IF ANY

if (isset($_POST["comments"])) {
// delete any previous comments
 $deleteSQL = "DELETE FROM survey_comments WHERE survey_comments.questionID = ".intval($_POST['questionID'])." AND survey_comments.sessionID = ".GetSQLValueString($_SESSION['survey_session'],"text");
   mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
  
    $insertSQL = sprintf("INSERT INTO survey_comments (comments, createddatetime, questionID, sessionID) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['comments'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"),
					   GetSQLValueString($_POST['questionID'], "int"),
					   GetSQLValueString($_SESSION['survey_session'], "text"));$Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());


}

// INSERT SCORE, IF ANY

if (isset($_POST["score"])) {
// delete any previous scores
 $deleteSQL = "DELETE FROM survey_scores WHERE survey_scores.questionID = ".intval($_POST['questionID'])." AND survey_scores.sessionID = ".GetSQLValueString($_SESSION['survey_session'],"text");$Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
  
    $insertSQL = sprintf("INSERT INTO survey_scores (score, finalscore, createddatetime, questionID, sessionID) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['score'], "text"),
					   GetSQLValueString($_POST['finalscore'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"),
					   GetSQLValueString($_POST['questionID'], "int"),
					   GetSQLValueString($_SESSION['survey_session'], "text"));mysql_query($insertSQL, $aquiescedb) or die(mysql_error());


}



// INSERT MCSA
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "MCSA")) {

// delete multiple choice previous answer for same question
 $deleteSQL = "DELETE FROM survey_response_choice USING survey_response_choice, survey_answer, survey_question WHERE survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = survey_question.ID AND survey_question.ID = ".intval($_POST['questionID'])." AND sessionID = ".GetSQLValueString($_SESSION['survey_session'],"text");$Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());

if($_POST['response'] > 0) { // only insert if user has added value
  $insertSQL = sprintf("INSERT INTO survey_response_choice (answerID, sessionID, createddatetime) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['response'], "int"),
					   GetSQLValueString($_SESSION['survey_session'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"));mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
    header("Location: ".$_POST['redirect']);  exit;
}


// INSERT MCMA

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "MCMA")) {
//clear any previosuly entered stuff
 $deleteSQL = "DELETE FROM survey_response_choice USING survey_response_choice, survey_answer, survey_question WHERE survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = survey_question.ID AND survey_question.ID = ".intval($_POST['questionID'])." AND sessionID = ".GetSQLValueString($_SESSION['survey_session'],"text");mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
  
  if(isset($_POST['response'])) {// if any responses

foreach($_POST['response'] as $response) {
  $insertSQL = sprintf("INSERT INTO survey_response_choice (answerID, sessionID, createddatetime) VALUES (%s, %s, %s)",
                       GetSQLValueString($response, "int"),
					   GetSQLValueString($_SESSION['survey_session'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"));mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}
} // responses sent
    header("Location: ".$_POST['redirect']);  exit;
}




// INSERT Multitext

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "TextMA")) {
//clear any previosuly entered stuff
 $deleteSQL = "DELETE FROM survey_response_multitext USING survey_response_multitext, survey_answer, survey_question WHERE survey_response_multitext.answerID = survey_answer.ID AND survey_answer.questionID = survey_question.ID AND survey_question.ID = ".intval($_POST['questionID'])." AND sessionID = ".GetSQLValueString($_SESSION['survey_session'],"text");mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
  
  if(isset($_POST['response'])) {// if any responses

foreach($_POST['response'] as $key => $response) {
	if($response != "") { // is a value
  $insertSQL = sprintf("INSERT INTO survey_response_multitext (answerID, response, sessionID, createddatetime) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($key, "int"),
					    GetSQLValueString($response, "text"),
					   GetSQLValueString($_SESSION['survey_session'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"));

  
  mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
	} // is a value
} // end for each
} // responses sent
    header("Location: ".$_POST['redirect']);  exit;
}




// INSERT  TEXT

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "text")) {
// delete any previous answers
 $deleteSQL = "DELETE FROM survey_response_text WHERE survey_response_text.questionID = ".intval($_POST['questionID'])." AND survey_response_text.sessionID = ".GetSQLValueString($_POST['sessionID'],"text");
 mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());

  $insertSQL = sprintf("INSERT INTO survey_response_text (response_text, createddatetime, questionID, sessionID) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['response_text'], "text"),
					   GetSQLValueString($_POST['createddatetime'], "date"),
					   GetSQLValueString($_POST['questionID'], "int"),
					   GetSQLValueString($_POST['sessionID'], "text"));

  mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

    header("Location: ".$_POST['redirect']);  exit;
}



// SELECT statements go below here

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT survey.* FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);


$currentPage = "question.php";

$maxRows_rsQuestions = 1;
$pageNum_rsQuestions = 0;
if (isset($_GET['pageNum_rsQuestions'])) {
  $pageNum_rsQuestions = $_GET['pageNum_rsQuestions'];
}
$startRow_rsQuestions = $pageNum_rsQuestions * $maxRows_rsQuestions;

$colname_rsQuestions = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsQuestions = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsQuestions = sprintf("SELECT survey_question.*, survey_section.subsectionofID,  survey_section.`description` AS section,  mainsection.ID AS mainsectionID, mainsection.`description` AS mainsection, supersection.`description` AS supersection, supersection.ID AS supersectionID FROM survey_question LEFT JOIN survey_section ON (survey_question.surveysectionID = survey_section.ID) LEFT JOIN survey_section AS supersection ON (survey_section.subsectionofID = supersection.ID) LEFT JOIN survey_section AS mainsection ON (supersection.subsectionofID = mainsection.ID) WHERE survey_question.surveyID = %s AND survey_question.active = 1 ORDER BY CAST(survey_question.question_number AS UNSIGNED)", GetSQLValueString($colname_rsQuestions, "int"));
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

$varQuestionID_rsAnswers = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsAnswers = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAnswers = sprintf("SELECT survey_answer.answertext, survey_answer.ID, survey_answer.answerscore FROM survey_answer WHERE survey_answer.questionID = %s ORDER BY survey_answer.ordernum, survey_answer.ID ASC", GetSQLValueString($varQuestionID_rsAnswers, "int"));
$rsAnswers = mysql_query($query_rsAnswers, $aquiescedb) or die(mysql_error());
$row_rsAnswers = mysql_fetch_assoc($rsAnswers);
$totalRows_rsAnswers = mysql_num_rows($rsAnswers);

$varSurveySession_rsResponseText = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSurveySession_rsResponseText = $_SESSION['survey_session'];
}
$varQuestion_rsResponseText = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestion_rsResponseText = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseText = sprintf("SELECT survey_response_text.response_text, survey_response_text.ID FROM survey_response_text WHERE survey_response_text.questionID = %s AND survey_response_text.sessionID = %s", GetSQLValueString($varQuestion_rsResponseText, "int"),GetSQLValueString($varSurveySession_rsResponseText, "text"));
$rsResponseText = mysql_query($query_rsResponseText, $aquiescedb) or die(mysql_error());
$row_rsResponseText = mysql_fetch_assoc($rsResponseText);
$totalRows_rsResponseText = mysql_num_rows($rsResponseText);

$varQuestionID_rsResponseChoiceSA = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsResponseChoiceSA = $row_rsQuestions['ID'];
}
$varSessionID_rsResponseChoiceSA = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsResponseChoiceSA = $_SESSION['survey_session'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseChoiceSA = sprintf("SELECT survey_response_choice.ID, survey_response_choice.answerID FROM survey_response_choice, survey_answer WHERE survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = %s AND survey_response_choice.sessionID = %s ORDER BY survey_response_choice.createddatetime DESC LIMIT 1", GetSQLValueString($varQuestionID_rsResponseChoiceSA, "int"),GetSQLValueString($varSessionID_rsResponseChoiceSA, "text"));
$rsResponseChoiceSA = mysql_query($query_rsResponseChoiceSA, $aquiescedb) or die(mysql_error());
$row_rsResponseChoiceSA = mysql_fetch_assoc($rsResponseChoiceSA);
$totalRows_rsResponseChoiceSA = mysql_num_rows($rsResponseChoiceSA);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT usertypeID FROM users WHERE username = '%s'", GetSQLValueString($colname_rsLoggedIn, "-1"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varQuestionID_rsResponseMA = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsResponseMA = $row_rsQuestions['ID'];
}
$varSessionID_rsResponseMA = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsResponseMA = $_SESSION['survey_session'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseMA = sprintf("SELECT survey_response_choice.answerID FROM survey_response_choice, survey_answer WHERE survey_response_choice.answerID = survey_answer.ID AND survey_answer.questionID = %s AND survey_response_choice.sessionID = %s ORDER BY survey_response_choice.createddatetime", GetSQLValueString($varQuestionID_rsResponseMA, "int"),GetSQLValueString($varSessionID_rsResponseMA, "text"));
$rsResponseMA = mysql_query($query_rsResponseMA, $aquiescedb) or die(mysql_error());
$row_rsResponseMA = mysql_fetch_assoc($rsResponseMA);
$totalRows_rsResponseMA = mysql_num_rows($rsResponseMA);

$varSurveySession_rsComments = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSurveySession_rsComments = $_SESSION['survey_session'];
}
$varQuestion_rsComments = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestion_rsComments = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT survey_comments.comments FROM survey_comments WHERE survey_comments.questionID = %s AND survey_comments.sessionID = %s", GetSQLValueString($varQuestion_rsComments, "int"),GetSQLValueString($varSurveySession_rsComments, "text"));
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);
$totalRows_rsComments = mysql_num_rows($rsComments);

$varSurveySession_rsScore = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSurveySession_rsScore = $_SESSION['survey_session'];
}
$varQuestion_rsScore = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestion_rsScore = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsScore = sprintf("SELECT survey_scores.score, survey_scores.finalscore FROM survey_scores WHERE survey_scores.questionID = %s AND survey_scores.sessionID = %s", GetSQLValueString($varQuestion_rsScore, "int"),GetSQLValueString($varSurveySession_rsScore, "text"));
$rsScore = mysql_query($query_rsScore, $aquiescedb) or die(mysql_error());
$row_rsScore = mysql_fetch_assoc($rsScore);
$totalRows_rsScore = mysql_num_rows($rsScore);

$varQuestionID_rsExceptions = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsExceptions = $row_rsQuestions['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsExceptions = sprintf("SELECT answerID, isnot, equalto, setvalue FROM survey_question_exception WHERE questionID = %s", GetSQLValueString($varQuestionID_rsExceptions, "int"));
$rsExceptions = mysql_query($query_rsExceptions, $aquiescedb) or die(mysql_error());
$row_rsExceptions = mysql_fetch_assoc($rsExceptions);
$totalRows_rsExceptions = mysql_num_rows($rsExceptions);

$varQuestionID_rsResponseMultitext = "-1";
if (isset($row_rsQuestions['ID'])) {
  $varQuestionID_rsResponseMultitext = $row_rsQuestions['ID'];
}
$varSessionID_rsResponseMultitext = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSessionID_rsResponseMultitext = $_SESSION['survey_session'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseMultitext = sprintf("SELECT survey_response_multitext.answerID, survey_response_multitext.response FROM survey_response_multitext, survey_answer WHERE survey_response_multitext.answerID = survey_answer.ID AND survey_answer.questionID = %s AND survey_response_multitext.sessionID = %s ", GetSQLValueString($varQuestionID_rsResponseMultitext, "int"),GetSQLValueString($varSessionID_rsResponseMultitext, "text"));
$rsResponseMultitext = mysql_query($query_rsResponseMultitext, $aquiescedb) or die(mysql_error());
$row_rsResponseMultitext = mysql_fetch_assoc($rsResponseMultitext);
$totalRows_rsResponseMultitext = mysql_num_rows($rsResponseMultitext);

$varSurveySessionID_rsThisSession = "-1";
if (isset($_SESSION['survey_session'])) {
  $varSurveySessionID_rsThisSession = $_SESSION['survey_session'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSession = sprintf("SELECT survey_session.ID, users.firstname, users.surname, directory.name FROM survey_session LEFT JOIN users ON (users.ID = survey_session.userID) LEFT JOIN directory ON (directory.ID = survey_session.directoryID) WHERE survey_session.ID = %s ", GetSQLValueString($varSurveySessionID_rsThisSession, "int"));
$rsThisSession = mysql_query($query_rsThisSession, $aquiescedb) or die(mysql_error());
$row_rsThisSession = mysql_fetch_assoc($rsThisSession);
$totalRows_rsThisSession = mysql_num_rows($rsThisSession);

$queryString_rsQuestions = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsQuestions") == false && stristr($param, "totalRows_rsQuestions") == false && stristr($param, "previous") == false && stristr($param, "next") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsQuestions = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsQuestions = sprintf("&totalRows_rsQuestions=%d%s", $totalRows_rsQuestions, $queryString_rsQuestions);

if ($totalRows_rsExceptions >0) { // are exceptions to check
	$true = 0;
	do { // count how many conditions are true
		$select = "SELECT ID FROM survey_response_choice 
		WHERE answerID = ".GetSQLValueString($row_rsExceptions['answerID'], "int")." AND sessionID = ".GetSQLValueString($_SESSION['survey_session'], "text");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		if ((mysql_num_rows($result)>0 
			&& $row_rsExceptions['isnot']==0)
			|| (mysql_num_rows($result)==0 && $row_rsExceptions['isnot']==1)) {
			$true++; 
		} 
	 } while ($row_rsExceptions = mysql_fetch_assoc($rsExceptions));
 	if(mysql_num_rows($rsExceptions)!=$true) { 
		// all exceptions not true then move question
 		if(isset($_GET['next'])) { // go to next question
 			$url = ($totalPages_rsQuestions != $pageNum_rsQuestions) ? "question.php?pageNum_rsQuestions=".min($totalPages_rsQuestions, $pageNum_rsQuestions + 1).$queryString_rsQuestions."&next=true" : (($row_rsSurvey['showsummary']==1) ? "summary.php?surveyID=".intval($_GET['surveyID'])."&amp;finish=true" : "finish.php?surveyID=".intval($_GET['surveyID'])."&amp;finish=true");
			header("Location: ".$url); exit;
		 } else { // go back
 			header("Location: question.php?previous=true&pageNum_rsQuestions=".max(0, $pageNum_rsQuestions - 1).$queryString_rsQuestions);		
			exit;
  		} // end go back
 	} //end all exceptions true
} // end if exceptions

$maxscore = 0;
header("Expires: -1"); 

$body_class = isset($body_class) ? $body_class :"";
$body_class .= " survey survey".$row_rsThisSurvey['ID']." questionpage "; ?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $row_rsThisSurvey['surveyname']." - Q".$row_rsQuestions['question_number']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta http-equiv="Expires" content="-1" />
<link href="css/defaultSurvey.css" rel="stylesheet"  />
<script>if(!window.jQuery) alert("No jQuery");
$(document).ready(function(){
	$("body").append("<div id='overlay-message-wrapper'><div id='overlay-message'></div></div>");
	
});

function showOverlayMessage(message, canClose) {
	$("#overlay-message").append(message);
	$("#overlay-message-wrapper").show();
	if(canClose) {
		$("#overlay-message-wrapper").click(function(){ $(this).hide(); });
	}
}

var fb_keepAlive = true;
window.setInterval(function() {
				 getData('/login/ajax/keep_session_alive.ajax.php');
			 }, 60000);
var answerrequired = <?php echo ($row_rsThisSurvey['answerrequired']>0 || $row_rsQuestions['answerrequired']==1) ? "true" : "false"; ?>;
var questiontype = <?php echo $row_rsQuestions['questiontype']; ?>;
var maxChoices = <?php echo isset($row_rsQuestions['maxchoices']) ? $row_rsQuestions['maxchoices'] :0 ; ?>;

function checkSubmit(redirectURL, errorCheck) {
	error = "";	
	var numchecked=0; 
	var numselected=0; 
	var text="";
	
	for (var count = 0; count < document.forms.length; count ++) 
	{
		for (var i = 0; i < document.forms[count].length; i++)
		{
			var elem = document.forms[count].elements[i];
			if (elem) {
			
			  if (elem.type == 'checkbox' || elem.type == 'radio')  {					  
					if(elem.checked) numchecked++;
			  }
			  else if (elem.tagName == 'TEXTAREA' || elem.type == "text") {						
					text += elem.value;				 
			  }
		  }// end elem
	  } // end for
	} // end for
	
	if (maxChoices>0 && numchecked > maxChoices) error += "You can only choose a maximum of "+maxChoices+".\n\n";
	
	if (answerrequired) {  
		if((questiontype==1 || questiontype==2 ) && numchecked == 0) { // MCSA or MCMA
			error += "You need to select at least one answer.\n\n";		
		}
		if(questiontype>=3 && text.length == 0) { // text
			error += "You need to answer this question. Enter 'Not applicable' if necessary.\n\n";		
		}
		if(questiontype==0 && text.length == 0) { // text multiple
			error += "You need at least one answer this question. Enter 'Not applicable' if necessary.\n\n";		
		}
	}// end answer  required
	
	
	if (!error || !errorCheck) {
		goToPage(redirectURL);
	} else {
		alert("You cannot proceed yet because:\n\n"+error);
	}
}

function goToPage(redirectURL) {
	$(".questionLoading").show();
		document.getElementById('redirect').value =  redirectURL;
		// if final score is not set, make it the same as user score
		if (document.formAnswers.finalscore.value == "") {
			document.formAnswers.finalscore.value = document.formAnswers.score.value;
		}
		if(redirectURL.indexOf("finish=true")) {
			showOverlayMessage("Please wait whilst your answers are submitted...", false);
		}
		document.formAnswers.submit();
}
	
function updateScores(responseID,scoreValue) {
	prevScore = parseFloat(document.formAnswers.score.value); 
	if (document.formAnswers.score.value != prevScore) // if not a number reset
	{
		prevScore = 0;
	}
	isChecked = (document.getElementById(responseID).checked) ? 1 : -1;

	document.formAnswers.score.value = prevScore + (scoreValue*isChecked);
}
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->


    <div id = "survey" class="survey questionpage container pageBody">
      <h1><?php echo $row_rsThisSurvey['surveyname']; ?></h1>
      <?php // section break
if (isset($_GET['prevSectionID']) && isset($row_rsQuestions['supersectionID']) && $_GET['prevSectionID'] != $row_rsQuestions['supersectionID']) { ?>
      <h2>You are now entering a new section:</h2>
      <h2><?php echo strtoupper($row_rsQuestions['mainsection']); ?>: <?php echo $row_rsQuestions['supersection']; ?></h2>
      <p>
        <?php if ($row_rsThisSurvey['showsummary'] == 1) { ?>
        <input name="summary2" type="submit" class="button" id="summary2" onclick="checkSubmit('summary.php?surveyID=<?php echo intval($_GET['surveyID']); ?>', true);" value="Index"  />
        <?php } ?>
        <input name="next2" type="submit" class="button" id="next2" onclick = "window.location.href = 'question.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&amp;pageNum_rsQuestions=<?php echo intval($_GET['pageNum_rsQuestions']); ?>&amp;totalRows_rsQuestions=<?php echo intval($_GET['totalRows_rsQuestions']); ?>&amp;prevSectionID=<?php echo $row_rsQuestions['supersectionID']; ?>&amp;next=true';" value="Continue &gt;"/>
      </p>
      <?php } else { ?>
      <?php if ($row_rsThisSurvey['usesections']==1 && isset($row_rsQuestions['section'])) { ?>
      <h2><?php echo isset($row_rsQuestions['mainsection']) ? strtoupper($row_rsQuestions['mainsection']).": " : "";  echo isset($row_rsQuestions['supersection']) ? $row_rsQuestions['supersection'].": " : ""; ?><?php echo $row_rsQuestions['section']; ?></h2>
      <?php }  ?><form action="<?php echo $editFormAction; ?>" method="post" name="formAnswers" id="formAnswers">
      <div class="navigation top">
      <?php global $nav_instance; $nav_instance =0; include('includes/pagination.inc.php'); ?>
      </div>
       <div class="question">
      <?php do { // repeat region for question  - although only 1 just now ?>
      <div class="question-text">
      <?php if (isset($row_rsQuestions['question_number']) && $row_rsQuestions['question_number'] !="") { ?>
      <span class="question-number">
      <span class="question-word">Question </span>
	 <?php  echo $row_rsQuestions['question_number']; ?><span class="question-count">/<?php echo $totalRows_rsQuestions; ?></span>.&nbsp;</span><?php } ?>
	   <?php echo $row_rsQuestions['questiontext']; ?>
            <?php if ($row_rsQuestions['questiontype'] ==2) echo "&nbsp;(Check all answers that apply)"; else if ($row_rsQuestions['questiontype'] == 1) echo "<span class=\"question-instruction\">&nbsp;(Select one answer)</span>"; ?>
      <?php if (isset($row_rsQuestions['imageURL']) &&$row_rsQuestions['imageURL']!="" ) { ?><br /><img src="<?php echo getImageURL($row_rsQuestions['imageURL'],"large"); ?>" alt="Question image" /><?php } ?></div><!-- end question -->
      <div class="question-answers form-group">
      
        <!-- Text Answer !-->
        <?php if ($row_rsQuestions['questiontype'] == 3) { //text answer ?>
        <textarea name="response_text" id="response_text" cols="60" rows="10" class="form-control"><?php echo htmlentities($row_rsResponseText['response_text']); ?></textarea>
        <br />
        <input type="hidden" name="MM_insert" value="text" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        
        <?php } else if ($row_rsQuestions['questiontype'] == 4) { // text single line ?>
        
        <input name="response_text" type="text"  id="response_text" value="<?php echo htmlentities($row_rsResponseText['response_text']); ?>" size="50" maxlength="255" class="form-control" />
        <br />
        <input type="hidden" name="MM_insert" value="text" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <!-- End Text Answer !-->
        
        
        <!-- Multiple Choice Single Answer !-->
        <?php } else if ($row_rsQuestions['questiontype'] == 1) { //MCSA answer ?>
        <?php if ($totalRows_rsAnswers > 0) { // Show if recordset not empty ?>
      
          <?php do { ?><div class="form-check">
            <input <?php if (!(strcmp($row_rsResponseChoiceSA['answerID'],$row_rsAnswers['ID']))) {echo "checked=\"checked\"";}  if ($row_rsThisSurvey['autocalc'] == 1) { ?> onclick="document.getElementById('score').value = <?php echo $row_rsAnswers['answerscore']; ?>"<?php } ?>  type="radio" name="response" id="response[<?php echo $row_rsAnswers['ID']; ?>]" value="<?php echo $row_rsAnswers['ID']; ?>"  class="form-check-input"/> <label for="response[<?php echo $row_rsAnswers['ID']; ?>]"  class="form-check-label"><?php echo $row_rsAnswers['answertext']; ?></label><span class="score" title="The number in brackets denotes the indicative value of this descriptor.  Indicative values can be used as guidance to help you decide your score for this subject."><?php echo ($row_rsThisSurvey['usescoring'] == 1 && $row_rsThisSurvey['showscores'] == 1 && $row_rsQuestions['addscore'] == 1 && isset($row_rsAnswers['answerscore'])) ? "(".$row_rsAnswers['answerscore'].")" : "&nbsp;"; ?></span>
           </div> <?php } while ($row_rsAnswers = mysql_fetch_assoc($rsAnswers)); ?>

        <?php } else { ?>
        <p class="alert warning alert-warning" role="alert">Error - the administrator has not entered any multiple choice answers for this question</p>
        <?php } ?>
        <input name="prevResponseID" type="hidden" id="prevResponseID" value="<?php echo $row_rsResponseChoiceSA['ID']; ?>" />
        <input type="hidden" name="MM_insert" value="MCSA" />
        <!-- End Multiple Choice Single Answer !-->
        <?php } else if ($row_rsQuestions['questiontype'] == 2) { ?>
        <!-- Multiple Choice Multiple Answer !-->
        <?php if ($totalRows_rsAnswers > 0) { // Show if recordset not empty ?>
        <?php // convert record set into array
$responses = Array();
 do {
   $responses[] = $row_rsResponseMA['answerID'];
   }
   while ($row_rsResponseMA = mysql_fetch_assoc($rsResponseMA));
   
   ?>
        
          <?php do { ?>
          <div class="form-check">
            <input  type="checkbox" name="response[<?php echo $row_rsAnswers['ID']; ?>]" id="response[<?php echo $row_rsAnswers['ID']; ?>]" value="<?php echo $row_rsAnswers['ID']; ?>" <?php if (in_array($row_rsAnswers['ID'] ,$responses)) { ?>checked="checked"<?php }  if ($row_rsThisSurvey['autocalc'] == 1) { ?> onclick="updateScores('<?php echo "response[".$row_rsAnswers['ID']."]',".$row_rsAnswers['answerscore']; ?>)"<?php } ?>  class="form-check-input" /> <label for="response[<?php echo $row_rsAnswers['ID']; ?>]" class="form-check-label"><?php echo $row_rsAnswers['answertext']; ?></label> <span title="The number in brackets denotes the indicative value of this descriptor.  Indicative values can be used as guidance to help you decide your score for this subject." class="score"><?php 
			if($row_rsThisSurvey['usescoring'] == 1 && $row_rsThisSurvey['showscores'] == 1 && $row_rsQuestions['addscore'] == 1 && isset($row_rsAnswers['answerscore'])) { 
			echo "(".$row_rsAnswers['answerscore'].")"; $maxscore += intval($row_rsAnswers['answerscore']); } ?></span>
            </div>
            <?php } while ($row_rsAnswers = mysql_fetch_assoc($rsAnswers)); ?>
     
        <?php } else { ?>
        <p class="alert warning alert-warning" role="alert">Error - the administrator has not entered any multiple choice answers for this question</p>
        <?php } ?>
        <input type="hidden" name="MM_insert" value="MCMA" />  <!-- End Multiple Choice Multiple Answer !-->
        <?php } else { // multi text ?>
        <!-- Start Multiple Text Answer !-->
        <?php if ($totalRows_rsAnswers > 0) { // Show if recordset not empty ?>
        <?php // convert record set into array
$responses = Array();
 do {
   $responses[$row_rsResponseMultitext['answerID']] = $row_rsResponseMultitext['response'];
   }
   while ($row_rsResponseMultitext = mysql_fetch_assoc($rsResponseMultitext));
   
   ?>
        <table class="listTable">
          <?php do { ?>
            <tr>
              
              <td><label for="response[<?php echo $row_rsAnswers['ID']; ?>]"><?php echo htmlentities($row_rsAnswers['answertext']); ?></label>              </td><td><input name="response[<?php echo $row_rsAnswers['ID']; ?>]" type="text"  class = "form-control" id="response[<?php echo $row_rsAnswers['ID']; ?>]" value="<?php echo htmlentities(@$responses[$row_rsAnswers['ID']]); ?>" size="20" maxlength="30" /></td>
              <td title="The number in brackets denotes the indicative value of this descriptor.  Indicative values can be used as guidance to help you decide your score for this subject."><?php echo ($row_rsThisSurvey['usescoring'] == 1 && $row_rsThisSurvey['showscores'] == 1 && $row_rsQuestions['addscore'] == 1 && isset($row_rsAnswers['answerscore'])) ? "(".$row_rsAnswers['answerscore'].")" : "&nbsp;"; ?></td>
            </tr>
            <?php } while ($row_rsAnswers = mysql_fetch_assoc($rsAnswers)); ?>
        </table>
        <?php } else { ?>
        <p class="alert warning alert-warning" role="alert">Error - the administrator has not entered any answer choices for this question</p>
        <?php } ?>
        <input type="hidden" name="MM_insert" value="TextMA" />  
        <!-- End Multiple Text Answer !-->
        <?php } // end question type ?>
      </div><!-- end answers form group -->
      
        
        
        
        
        
        
        
        
        <p <?php if (!($row_rsThisSurvey['usescoring'] == 1 && $row_rsThisSurvey['showscores'] == 1 &&  $row_rsQuestions['addscore'] ==1)) { ?> style="display:none;"<?php } ?>  class="surveyscores">Score:
          <input name="score" type="text"  id="score" value="<?php echo $row_rsScore['score']; ?>" size="5" maxlength="5" <?php if ($row_rsThisSurvey['autocalc'] == 1 && $row_rsQuestions['questiontype'] < 3) { ?> readonly <?php } ?> />
          
          <?php if ($_SESSION['MM_UserGroup'] >=8) { ?>
          <label id="label_finalscore">Marked Score:
          <input name="finalscore" type="text"  id="finalscore" value="<?php echo $row_rsScore['finalscore']; ?>" size="5" maxlength="5" /></label>
          <?php } else { ?><input name="finalscore" type="hidden" id="finalscore" value="<?php echo $row_rsScore['finalscore']; ?>"  /><?php } // end not admin  ?>&nbsp;&nbsp;
         <?php echo  ($row_rsQuestions['passscore']>0) ? "Pass: <span=\"passscore\">".$row_rsQuestions['passscore']."</span>" : "";
        
          
          
       echo ($maxscore>0) ? "/<span class=\"maxscore\">".$maxscore."</span>" : ""; ?>
      <input name="maxscore" type="hidden" id="maxscore" value="<?php echo $maxscore; ?>" /> 
      <input name="passscore" type="hidden" id="passscore" value="<?php echo $row_rsQuestions['passscore']; ?>" />    
        </p>
        <?php if ($row_rsThisSurvey['usecomments'] == 1)  { // if multi-choice and use comments ?>
        <p><?php echo isset($row_rsQuestions['questionnotes']) ? $row_rsQuestions['questionnotes']."<br>" : ""; if ($row_rsQuestions['questiontype'] < 3 && $row_rsQuestions['addcommentsbox']==1) { ?><span class="commentsboxtext">Add any further relevant information, detail or  comments below (you can enter as much text as you choose):</span><br />
          <textarea name="comments" id="comments" cols="80" rows="4"><?php echo htmlentities($row_rsComments['comments'], ENT_COMPAT, "UTF-8"); ?></textarea></p><?php } // end use comments ?>
        
        <?php } // end if multi-choice ?>
		<div class="navigation bottom">
		<?php include('includes/pagination.inc.php'); ?>
        </div>
        <div class="session"><p>You are answering this survey
        <?php if(isset($row_rsThisSession['firstname'])) { ?> as <?php echo $row_rsThisSession['firstname']." ".$row_rsThisSession['surname'];
		echo isset($row_rsThisSession['name']) ? " for ".$row_rsThisSession['name'] : ""; } else { ?>anonymously<?php } ?>.</p>
</div>
        <input type="hidden" name="redirect" id="redirect" value="<?php echo $nextpage; ?>" />
        <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
        <input name="questionID" type="hidden" id="questionID" value="<?php echo $row_rsQuestions['ID']; ?>" />
        <input type="hidden" name="sessionID" id="sessionID" value="<?php echo $_SESSION['survey_session']; ?>" />
     </div><!-- end question -->
      <?php } while ($row_rsQuestions = mysql_fetch_assoc($rsQuestions)); // end repeat region for 1 question ?> 
      </form>
      <?php } //end not section break ?>
      
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

mysql_free_result($rsAnswers);

mysql_free_result($rsResponseText);

mysql_free_result($rsResponseChoiceSA);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsResponseMA);

mysql_free_result($rsComments);

mysql_free_result($rsScore);

mysql_free_result($rsExceptions);

mysql_free_result($rsResponseMultitext);

mysql_free_result($rsThisSession);

mysql_free_result($rsThisSurvey);
?>
