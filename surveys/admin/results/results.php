<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php');
$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate'] : date('Y-m-d',strtotime("LAST YEAR"));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d'); ?>
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

$varSurveyID_rsSurveyQuestions = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsSurveyQuestions = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyQuestions = sprintf("SELECT survey_question.ID AS questionID, survey_question.questiontext, survey_question.questiontype FROM survey_question WHERE survey_question.active = 1 AND survey_question.surveyID = %s", GetSQLValueString($varSurveyID_rsSurveyQuestions, "int"));
$rsSurveyQuestions = mysql_query($query_rsSurveyQuestions, $aquiescedb) or die(mysql_error());
$row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions);
$totalRows_rsSurveyQuestions = mysql_num_rows($rsSurveyQuestions);

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

if(isset($_GET['csv'])) {
	csvHeaders($filename="Survey Results DD-MM-YY");

     do { 
	 	echo formatCSV($row_rsSurveyQuestions['questiontext']); 
	 	echo ",\r\n";
	 	if ($row_rsSurveyQuestions['questiontype'] != 3) {  // not text
		 

			mysql_select_db($database_aquiescedb, $aquiescedb);
			$query_rsResponses = "SELECT survey_answer.answertext, COUNT(survey_response_choice.ID) AS MCresponses FROM survey_answer LEFT JOIN survey_response_choice ON (survey_answer.ID = survey_response_choice.answerID) WHERE survey_answer.questionID = ".intval($row_rsSurveyQuestions['questionID'])."
			AND DATE(survey_response_choice.createddatetime) >= ".GetSQLValueString($_GET['startdate'], "date")." 
AND DATE(survey_response_choice.createddatetime) <= ". GetSQLValueString($_GET['enddate'], "date")." 
 GROUP BY survey_answer.ID";
			$rsResponses = mysql_query($query_rsResponses, $aquiescedb) or die(mysql_error());
			$row_rsResponses = mysql_fetch_assoc($rsResponses);
			$query_total = "SELECT COUNT(survey_response_choice.ID) AS TOTresponse FROM survey_question LEFT JOIN survey_answer ON (survey_question.ID = survey_answer.questionID) LEFT JOIN survey_response_choice ON (survey_answer.ID = survey_response_choice.answerID) WHERE survey_question.ID = ".intval($row_rsSurveyQuestions['questionID'])."
			AND DATE(survey_response_choice.createddatetime) >= ".GetSQLValueString($_GET['startdate'], "date")." 
AND DATE(survey_response_choice.createddatetime) <= ". GetSQLValueString($_GET['enddate'], "date")." 
 GROUP BY survey_question.ID";
			$rsTotal = mysql_query($query_total, $aquiescedb) or die(mysql_error());
			$row_rsTotal = mysql_fetch_assoc($rsTotal); 
			$total = $row_rsTotal['TOTresponse']; 
			do { 
				echo formatCSV($row_rsResponses['answertext']).","; 
				echo ($total >0) ? number_format($row_rsResponses['MCresponses']/$total*100,0) : "n/a"; 
				echo "% (".$row_rsResponses['MCresponses'].")"; 
				echo "\r\n"; 
			} while ($row_rsResponses = mysql_fetch_assoc($rsResponses));
		
		}  // not text
	} while ($row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions));
   exit;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../../../Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Survey Results"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page surveys">
   <h1><i class="glyphicon glyphicon-education"></i> Survey Results</h1>
   <h2><?php echo $row_rsThisSurvey['surveyname']; ?></h2>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Surveys</a> </li>
     <li><a href="index.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Results by User</a></li>
     <li><a href="results.php?surveyID=<?php echo intval($_GET['surveyID']); ?>&amp;csv=true" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Download as spreadsheet</a> </li>
   </ul></div></nav><form action="" method="get" name="form1" id="form1">
     <fieldset>
       <legend>Filter</legend>
       <input type="hidden" name="startdate" id="startdate"  value="<?php $setvalue = isset($_GET['startdate']) ? htmlentities($_GET['startdate']) : ""; echo $setvalue; $inputname = "startdate"; ?>" />
       <?php require('../../../core/includes/datetimeinput.inc.php'); ?>

     -
     <input type="hidden" name="enddate" id="enddate"  value="<?php $setvalue = isset($_GET['enddate']) ? htmlentities($_GET['enddate']) : ""; echo $setvalue; $inputname = "enddate"; ?>" /><?php require('../../../core/includes/datetimeinput.inc.php'); ?>
     <button type="submit" name="gobutton" id="gobutton" class="btn btn-default btn-secondary" >Go</button>
     <input name="surveyID" type="hidden" id="surveyID" value="<?php echo isset($_GET['surveyID']) ? intval($_GET['surveyID']) : ""; ?>" />
     </fieldset>
   </form>
   <table class="form-table">

   <?php do { ?>
       <tr>
         <td><strong><?php echo $row_rsSurveyQuestions['questiontext']; ?></strong></td>
      </tr><?php if ($row_rsSurveyQuestions['questiontype'] == 1 || $row_rsSurveyQuestions['questiontype'] == 2) { 
		 

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponses = "SELECT survey_answer.answertext, COUNT(survey_response_choice.ID) AS MCresponses FROM survey_answer LEFT JOIN survey_response_choice ON (survey_answer.ID = survey_response_choice.answerID) 
WHERE survey_answer.questionID = ".intval($row_rsSurveyQuestions['questionID'])." 
AND DATE(survey_response_choice.createddatetime) >= ".GetSQLValueString($_GET['startdate'], "date")." 
AND DATE(survey_response_choice.createddatetime) <= ". GetSQLValueString($_GET['enddate'], "date")." 
GROUP BY survey_answer.ID";
$rsResponses = mysql_query($query_rsResponses, $aquiescedb) or die(mysql_error());
$row_rsResponses = mysql_fetch_assoc($rsResponses);
$query_total = "SELECT COUNT(survey_response_choice.ID) AS TOTresponse FROM survey_question LEFT JOIN survey_answer ON (survey_question.ID = survey_answer.questionID) LEFT JOIN survey_response_choice ON (survey_answer.ID = survey_response_choice.answerID) WHERE survey_question.ID = ".intval($row_rsSurveyQuestions['questionID'])." 
AND DATE(survey_response_choice.createddatetime) >= ".GetSQLValueString($_GET['startdate'], "date")." 
AND DATE(survey_response_choice.createddatetime) <= ". GetSQLValueString($_GET['enddate'], "date")." 
GROUP BY survey_question.ID";
$rsTotal = mysql_query($query_total, $aquiescedb) or die(mysql_error());
$row_rsTotal = mysql_fetch_assoc($rsTotal); $total = $row_rsTotal['TOTresponse']; 
do { ?>
       <tr>
         <td><?php echo $row_rsResponses['answertext']." "; echo ($total >0) ? number_format($row_rsResponses['MCresponses']/$total*100,0) : "n/a"; echo "% (".$row_rsResponses['MCresponses'].")"; ?></td>
      </tr><?php } while ($row_rsResponses = mysql_fetch_assoc($rsResponses)); 
		} else if($row_rsSurveyQuestions['questiontype'] >=3)  { ?>
        <tr><td><a href="text_answers.php?questionID=<?php echo $row_rsSurveyQuestions['questionID']; ?>">View text responses</a> </td></tr><?php } else { ?>
        <tr>
          <td><a href="multitext_answers.php?questionID=<?php echo $row_rsSurveyQuestions['questionID']; ?>">View multi-text responses</a> </td></tr>
		<?php } ?>
       <?php } while ($row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions)); ?>
   </table></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSurveyQuestions);

mysql_free_result($rsThisSurvey);

mysql_free_result($rsSurveyPrefs);
?>