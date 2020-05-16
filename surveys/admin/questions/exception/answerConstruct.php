<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$colname_rsAnswers = "-1";
if (isset($_GET['questionID'])) {
  $colname_rsAnswers = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAnswers = sprintf("SELECT ID, answertext FROM survey_answer WHERE questionID = %s", GetSQLValueString($colname_rsAnswers, "int"));
$rsAnswers = mysql_query($query_rsAnswers, $aquiescedb) or die(mysql_error());
$row_rsAnswers = mysql_fetch_assoc($rsAnswers);
$totalRows_rsAnswers = mysql_num_rows($rsAnswers);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);
if ($totalRows_rsAnswers > 0) { ?>
<form action="edit_question.php?questionID=<?php echo intval($_GET['thisQuestionID']); ?>&defaultTab=1" method="post" name="answerConstructForm" id="answerConstructForm" class="form-inline">
  <select name="answerID"  id="answerID" class="form-control">
    <?php if($totalRows_rsAnswers>0) {
do {  
?>
    <option value="<?php echo $row_rsAnswers['ID']?>"><?php echo $row_rsAnswers['answertext']?></option>
    <?php
} while ($row_rsAnswers = mysql_fetch_assoc($rsAnswers));
  $rows = mysql_num_rows($rsAnswers);
  if($rows > 0) {
      mysql_data_seek($rsAnswers, 0);
	  $row_rsAnswers = mysql_fetch_assoc($rsAnswers);
  }
	}
?>
  </select>
  <select name="isnot"  id="isnot" class="form-control">
    <option value="0">is</option>
    <option value="1">is not</option>
  </select>
 checked
 <input name="equalto" type="hidden" id="equalto" value="1" />
 <input name="setvalue" type="hidden" id="setvalue" value="1" />
 <button name="add" type="submit" class="btn btn-default btn-secondary" id="add" >Add</button>
 <input name="addConstruct" type="hidden" id="addConstruct" value="true">
 <input name="questionID" type="hidden" id="questionID" value="<?php echo intval($_GET['thisQuestionID']); ?>">
</form><?php } ?>
<?php
mysql_free_result($rsAnswers);

mysql_free_result($rsSurveyPrefs);
?>
