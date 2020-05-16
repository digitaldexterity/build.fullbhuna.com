<?php 
  


// ***************************** START  QUESTIONS ***************************************
$varSurveyID_rsAvSurveyQuestion = "8";
if (isset($surveyID)) {
  $varSurveyID_rsAvSurveyQuestion = $surveyID;
}
$varSectionID_rsAvSurveyQuestion = "-1";
if (isset($_GET['showsectionID'])) {
  $varSectionID_rsAvSurveyQuestion = $_GET['showsectionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAvSurveyQuestion = sprintf("SELECT survey_question.question_number, AVG(survey_scores.score) AS score, survey_question.ID, survey_section.subsectionofID,  survey_section.`description` AS section,  mainsection.ID AS mainsectionID, mainsection.`description` AS mainsection, topic.`description` AS topic, topic.ID AS topicID, AVG(survey_scores.finalscore) AS finalscore, survey_section.weight AS subtopicweight, topic.weight AS topicweight, survey_question.questionweight, survey_section.ID AS subTopicID, mainsection.weight AS sectionweight FROM survey_question LEFT JOIN survey_section ON (survey_question.surveysectionID = survey_section.ID) LEFT JOIN survey_section AS topic ON (survey_section.subsectionofID = topic.ID) LEFT JOIN survey_section AS mainsection ON (topic.subsectionofID = mainsection.ID) LEFT JOIN survey_scores ON (survey_scores.questionID = survey_question.ID) WHERE survey_question.surveyID = %s GROUP BY survey_question.ID ORDER BY LPAD(survey_question.question_number,5,'0')", GetSQLValueString($varSurveyID_rsAvSurveyQuestion, "int"));
$rsAvSurveyQuestion = mysql_query($query_rsAvSurveyQuestion, $aquiescedb) or die(mysql_error());
$row_rsAvSurveyQuestion = mysql_fetch_assoc($rsAvSurveyQuestion);
$totalRows_rsAvSurveyQuestion = mysql_num_rows($rsAvSurveyQuestion);


// *****************************************   START OUTPUT **********************************

$prevSubTopic = $row_rsAvSurveyQuestion['section']; $prevSubTopicID = $row_rsAvSurveyQuestion['subTopicID']; $prevSubTopicWeight = $row_rsAvSurveyQuestion['subtopicweight'];
$subTopicUserScore = 0; $subTopicFinalScore = 0; $subTopicMaxScore = 0;

$prevTopic = $row_rsAvSurveyQuestion['topic']; $prevTopicWeight = $row_rsAvSurveyQuestion['topicweight'];
$topicUserScore = 0; $topicFinalScore = 0; $topicMaxScore = 0;

$prevSectionID = $row_rsAvSurveyQuestion['mainsectionID']; $prevTopicID = $row_rsAvSurveyQuestion['topicID'];$prevSection = $row_rsAvSurveyQuestion['mainsection']; $prevSectionWeight = $row_rsAvSurveyQuestion['sectionweight']; $sectionScore = 0; $sectionFinalScore = 0; $sectionMaxScore = 0; $finalUserScore = 0; 

$finalFinalScore=0; $finalMaxScore = 0; $totqscore = 0; $maxqscore = 0; do { // Main question loop
  
// topic summaries (goes at end as well)  
  
if ($prevSubTopic != $row_rsAvSurveyQuestion['section']) {
$sectionAvUserPerCent[$prevSubTopicID] = $subTopicUserScore/$subTopicMaxScore*100; $sectionAvFinalPerCent[$prevSubTopicID] = $subTopicFinalScore/$subTopicMaxScore*100;
$topicUserScore += $subTopicUserScore/$subTopicMaxScore*5*$prevSubTopicWeight; $topicFinalScore += $subTopicFinalScore/$subTopicMaxScore*5*$prevSubTopicWeight; $topicMaxScore += 5* $prevSubTopicWeight;
$subTopicUserScore = 0; $subTopicFinalScore = 0; $subTopicMaxScore = 0;
$prevSubTopic = $row_rsAvSurveyQuestion['section']; $prevSubTopicID = $row_rsAvSurveyQuestion['subTopicID']; $prevSubTopicWeight = $row_rsAvSurveyQuestion['subtopicweight']; } 
     

if ($prevTopic != $row_rsAvSurveyQuestion['topic']) { 
$sectionAvUserPerCent[$prevTopicID] = $topicUserScore/$topicMaxScore*100; $sectionAvFinalPerCent[$prevTopicID] = $topicFinalScore/$topicMaxScore*100; 
$sectionUserScore += $topicUserScore/$topicMaxScore*5*$prevTopicWeight ; $sectionFinalScore += $topicFinalScore/$topicMaxScore*5*$prevTopicWeight; $sectionMaxScore += 5*$prevTopicWeight;
$topicUserScore = 0; $topicFinalScore = 0; $topicMaxScore = 0;
$prevTopic = $row_rsAvSurveyQuestion['topic']; $prevTopicID = $row_rsAvSurveyQuestion['topicID']; $prevTopicWeight = $row_rsAvSurveyQuestion['topicweight']; }
	  

if ($prevSection != $row_rsAvSurveyQuestion['mainsection']) { 
$sectionAvUserPerCent[$prevSectionID] = $sectionUserScore/$sectionMaxScore*100; $sectionAvFinalPerCent[$prevSectionID] = $sectionFinalScore/$sectionMaxScore*100;
$finalUserScore += $sectionUserScore/$sectionMaxScore*5*$prevSectionWeight; $finalFinalScore += $sectionFinalScore/$sectionMaxScore*5*$prevSectionWeight; $finalMaxScore += 5*$prevSectionWeight;
$sectionUserScore = 0; $sectionFinalScore = 0; $sectionMaxScore = 0;
$prevSection = $row_rsAvSurveyQuestion['mainsection']; $prevSectionID = $row_rsAvSurveyQuestion['mainsectionID']; $prevSectionWeight = $row_rsAvSurveyQuestion['sectionweight']; } 


 
		  //add up scores to subtopic total
		  $subTopicUserScore += $row_rsAvSurveyQuestion['score'] * $row_rsAvSurveyQuestion['questionweight'];
		  $subTopicFinalScore += $row_rsAvSurveyQuestion['finalscore'] * $row_rsAvSurveyQuestion['questionweight'];
		  $subTopicMaxScore += 5 * $row_rsAvSurveyQuestion['questionweight'];
	 // end main question loop
	

	} while ($row_rsAvSurveyQuestion = mysql_fetch_assoc($rsAvSurveyQuestion)); 
    
 $avgperCent = number_format($finalUserScore/$finalMaxScore*100,0);  

mysql_free_result($rsAvSurveyQuestion);
?>
