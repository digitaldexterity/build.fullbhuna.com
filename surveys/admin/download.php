<?php require_once('../../Connections/aquiescedb.php');
 require_once('../../core/includes/framework.inc.php'); 

if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) { ?>
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

$varSurveyID_rsSurveyResponses = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsSurveyResponses = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyResponses = sprintf("SELECT users.firstname, users.surname,  survey_session.ID AS sessionID, startdatetime, enddatetime, directory.name FROM survey_session LEFT JOIN users ON (survey_session.userID = users.ID) LEFT JOIN directory ON (survey_session.directoryID = directory.ID)  WHERE survey_session.surveyID = %s", GetSQLValueString($varSurveyID_rsSurveyResponses, "int"));// AND survey_session.enddatetime IS NOT NULL
$rsSurveyResponses = mysql_query($query_rsSurveyResponses, $aquiescedb) or die(mysql_error());
$row_rsSurveyResponses = mysql_fetch_assoc($rsSurveyResponses);
$totalRows_rsSurveyResponses = mysql_num_rows($rsSurveyResponses);

$select = "SELECT  survey_question.ID, survey_question.question_number, survey_question.questiontext FROM survey_question  WHERE survey_question.surveyID =  ".intval($_GET['surveyID'])." AND survey_question.active = 1 ORDER BY CAST(survey_question.question_number AS UNSIGNED)";
$questions = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);

if($totalRows_rsSurveyResponses>0) {
	csvHeaders("Survey");
	
	$line = "Date, Respondant,";
	
	while($question = mysql_fetch_assoc($questions)) {
		$line .= formatCSV($question['question_number'].". ".$question['questiontext']).",";
	}
	echo $line."\r\n";
	mysql_data_seek($questions,0);

 do { // responsant loop
 $line = formatCSV($row_rsSurveyResponses['startdatetime'],"date").",";
 $respondant = $row_rsSurveyResponses['firstname']." ".$row_rsSurveyResponses['surname'];
 $respondant .= isset($row_rsSurveyResponses['name']) ? " (".$row_rsSurveyResponses['name'].")" : "";
 
  $line .= formatCSV($respondant).","; 
  // show answers 
  while($question = mysql_fetch_assoc($questions)) { 
  	$item = "";
  	$query = "SELECT survey_response_text.response_text, survey_answer.answertext, survey_response_choice.ID AS checked, survey_response_multitext.response, survey_comments.comments FROM survey_question
	LEFT JOIN survey_response_text ON (survey_response_text.questionID = survey_question.ID AND survey_response_text.sessionID = ". GetSQLValueString($row_rsSurveyResponses['sessionID'],"text").")
	
	LEFT JOIN  survey_answer ON (survey_answer.questionID = survey_question.ID) 
	LEFT JOIN survey_response_choice ON (survey_response_choice.answerID = survey_answer.ID AND survey_response_choice.sessionID = ". GetSQLValueString($row_rsSurveyResponses['sessionID'],"text")." )
	LEFT JOIN survey_response_multitext ON (survey_response_multitext.answerID = survey_answer.ID AND survey_response_multitext.sessionID = ". GetSQLValueString($row_rsSurveyResponses['sessionID'],"text")." )
	LEFT JOIN survey_comments ON (survey_comments.questionID = survey_question.ID AND survey_comments.sessionID = ". GetSQLValueString($row_rsSurveyResponses['sessionID'],"text").") 
	
	WHERE survey_question.ID = ".$question['ID'];
	$answers = mysql_query($query, $aquiescedb) or die(mysql_error().": ".$query);
	while($answer = mysql_fetch_assoc($answers)) { // answerloop
		
		$item .= isset($answer['checked']) ? $answer['answertext']."; " : "";
		$item .= isset($answer['response_text']) ? $answer['response_text'] : "";
		$item .= isset($answer['response']) ? $answer['answertext'].": ".$answer['response']."; " : "";
		$comments = isset($answer['comments']) ? " [".$answer['comments']."]" : "";
		
	} // end answerloop
	$item .= $comments;
	$line .= formatCSV(trim($item,"; ")).",";
  } // end question loop
  mysql_data_seek($questions,0);
  echo $line."\r\n";
  } while ($row_rsSurveyResponses = mysql_fetch_assoc($rsSurveyResponses)); 
   // end respondant loop
}
mysql_free_result($rsSurveyResponses);
}
?>