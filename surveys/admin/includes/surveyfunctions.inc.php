<?php

function deleteSurvey($sessionID) {
	global $database_aquiescedb, $aquiescedb;

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $query_rsTextAnswers = sprintf("DELETE FROM survey_response_text WHERE survey_response_text.sessionID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query_rsTextAnswers, $aquiescedb) or die(mysql_error());
  
  
  
  $query_rsMultiAnswers = sprintf("DELETE FROM survey_response_choice WHERE survey_response_choice.sessionID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query_rsMultiAnswers, $aquiescedb) or die(mysql_error());
  
  $query_rsMultiAnswers = sprintf("DELETE FROM survey_response_multitext  WHERE survey_response_multitext.sessionID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query_rsMultiAnswers, $aquiescedb) or die(mysql_error());
  
  
  $query_rsScores = sprintf("DELETE FROM survey_scores WHERE survey_scores.sessionID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query_rsScores, $aquiescedb) or die(mysql_error());
  
  
  $query_rsComments = sprintf("DELETE FROM survey_comments WHERE survey_comments.sessionID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
  
  
  $query = sprintf("DELETE FROM survey_session WHERE ID = %s", GetSQLValueString($sessionID, "text"));
  mysql_query($query, $aquiescedb) or die(mysql_error());
}
?>