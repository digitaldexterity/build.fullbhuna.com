<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$varQuestionID_rsQuestion = "-1";
if (isset($_GET['questionID'])) {
  $varQuestionID_rsQuestion = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsQuestion = sprintf("SELECT survey_question.questiontext, survey_question.surveyID FROM survey_question WHERE survey_question.ID = %s", GetSQLValueString($varQuestionID_rsQuestion, "int"));
$rsQuestion = mysql_query($query_rsQuestion, $aquiescedb) or die(mysql_error());
$row_rsQuestion = mysql_fetch_assoc($rsQuestion);
$totalRows_rsQuestion = mysql_num_rows($rsQuestion);

$maxRows_rsTextAnswers = isset($_GET['csv']) ? 10000 : 50;
$pageNum_rsTextAnswers = 0;
if (isset($_GET['pageNum_rsTextAnswers'])) {
  $pageNum_rsTextAnswers = $_GET['pageNum_rsTextAnswers'];
}
$startRow_rsTextAnswers = $pageNum_rsTextAnswers * $maxRows_rsTextAnswers;

$varQuestionID_rsTextAnswers = "-1";
if (isset($_GET['questionID'])) {
  $varQuestionID_rsTextAnswers = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTextAnswers = sprintf("SELECT survey_response_text.response_text, users.firstname, users.surname, users.jobtitle FROM survey_response_text LEFT JOIN users ON (survey_response_text.sessionID = users.username) WHERE survey_response_text.questionID = %s", GetSQLValueString($varQuestionID_rsTextAnswers, "int"));
$query_limit_rsTextAnswers = sprintf("%s LIMIT %d, %d", $query_rsTextAnswers, $startRow_rsTextAnswers, $maxRows_rsTextAnswers);
$rsTextAnswers = mysql_query($query_limit_rsTextAnswers, $aquiescedb) or die(mysql_error());
$row_rsTextAnswers = mysql_fetch_assoc($rsTextAnswers);

if (isset($_GET['totalRows_rsTextAnswers'])) {
  $totalRows_rsTextAnswers = $_GET['totalRows_rsTextAnswers'];
} else {
  $all_rsTextAnswers = mysql_query($query_rsTextAnswers);
  $totalRows_rsTextAnswers = mysql_num_rows($all_rsTextAnswers);
}
$totalPages_rsTextAnswers = ceil($totalRows_rsTextAnswers/$maxRows_rsTextAnswers)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

$queryString_rsTextAnswers = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsTextAnswers") == false && 
        stristr($param, "totalRows_rsTextAnswers") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsTextAnswers = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsTextAnswers = sprintf("&totalRows_rsTextAnswers=%d%s", $totalRows_rsTextAnswers, $queryString_rsTextAnswers);

if(isset($_GET['csv'])) {
	if ($totalRows_rsTextAnswers > 0) {
	csvHeaders($filename="Survey Text Answers DD-MM-YY");
	 echo $row_rsQuestion['questiontext'].",\r\n";
	 echo ",\r\n";
	do { 
	if($row_rsTextAnswers['response_text'] !="") {
       echo isset($row_rsTextAnswers['firstname']) ? formatCSV($row_rsTextAnswers['firstname']." ".$row_rsTextAnswers['surname']) : formatCSV("Anonymous"); 
	   echo ",";
	    echo formatCSV($row_rsTextAnswers['response_text']); 
		echo "\r\n";
	}
        } while ($row_rsTextAnswers = mysql_fetch_assoc($rsTextAnswers)); 

  
	} exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Survey Results - Text Answers"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
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
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page surveys">
   <h1><i class="glyphicon glyphicon-education"></i> Survey Results</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="results.php?surveyID=<?php echo $row_rsQuestion['surveyID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to Survey Results</a></li>
   </ul></div></nav>
   <p><strong>Text responses for: <?php echo $row_rsQuestion['questiontext']; ?></strong></p>
   <?php if ($totalRows_rsTextAnswers == 0) { // Show if recordset empty ?>
  <p>There are no responses to this question.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsTextAnswers > 0) { // Show if recordset not empty ?><p class="text-muted">Responses <?php echo ($startRow_rsTextAnswers + 1) ?> to <?php echo min($startRow_rsTextAnswers + $maxRows_rsTextAnswers, $totalRows_rsTextAnswers) ?> of <?php echo $totalRows_rsTextAnswers ?>&nbsp;&nbsp;&nbsp;<img src="../../../documents/images/document-application--vnd.ms-excel.png" alt="Excel" width="16" height="16" style="vertical-align:
middle;" /> <a href="text_answers.php?questionID=<?php echo intval($_GET['questionID']); ?>&amp;csv=true">Download as spreadsheet</a></p>
  
  <table class="listTable">
    <?php do { ?>
      <tr>
        <td><p><?php echo isset($row_rsTextAnswers['firstname']) ? $row_rsTextAnswers['firstname']." ".$row_rsTextAnswers['surname'] : "Anonymous"; ?><?php echo isset($row_rsTextAnswers['jobtitle']) ? " (".$row_rsTextAnswers['jobtitle'].")" : ""; ?> wrote:</p><?php echo ($row_rsTextAnswers['response_text'] !="") ? nl2br($row_rsTextAnswers['response_text']) : "(No response)"; ?><br />
          <br /></td>
      </tr>
      <?php } while ($row_rsTextAnswers = mysql_fetch_assoc($rsTextAnswers)); ?>
  </table>
  <?php } // Show if recordset not empty ?>
<table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsTextAnswers > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTextAnswers=%d%s", $currentPage, 0, $queryString_rsTextAnswers); ?>">First</a>
                <?php } // Show if not first page ?>
          </td>
          <td><?php if ($pageNum_rsTextAnswers > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsTextAnswers=%d%s", $currentPage, max(0, $pageNum_rsTextAnswers - 1), $queryString_rsTextAnswers); ?>" rel="prev">Previous</a>
                <?php } // Show if not first page ?>
          </td>
          <td><?php if ($pageNum_rsTextAnswers < $totalPages_rsTextAnswers) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTextAnswers=%d%s", $currentPage, min($totalPages_rsTextAnswers, $pageNum_rsTextAnswers + 1), $queryString_rsTextAnswers); ?>" rel="next">Next</a>
                <?php } // Show if not last page ?>
          </td>
          <td><?php if ($pageNum_rsTextAnswers < $totalPages_rsTextAnswers) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsTextAnswers=%d%s", $currentPage, $totalPages_rsTextAnswers, $queryString_rsTextAnswers); ?>">Last</a>
                <?php } // Show if not last page ?>
          </td>
        </tr>
    </table>
     </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsQuestion);

mysql_free_result($rsTextAnswers);

mysql_free_result($rsSurveyPrefs);
?>