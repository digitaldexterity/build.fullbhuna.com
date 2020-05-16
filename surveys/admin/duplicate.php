<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Duplicate Survey"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
<?php 
mysql_select_db($database_aquiescedb, $aquiescedb);
global $surveyname;
$oldsurveyID = intval($_GET['surveyID']);
$questionID = array(); // holds old (index) and new (value) questionIDs
$surveysectionID = array(); // holds old (index) and new (value) questionIDs
$answerID = array();
function getFields($table) {
	global $aquiescedb;
	$fields=array();
    $q="SHOW COLUMNS FROM ".$table;
	$rsFields = mysql_query($q, $aquiescedb) or die(mysql_error());
      
 	while ($field = mysql_fetch_assoc($rsFields)) 
   {      
		 array_push($fields,$field['Field']);	 
    }
	return $fields;
}

function duplicateEntry($table,$id,$newsurveyID=0) {
	global $aquiescedb, $surveysectionID, $questionID, $answerID, $row_rsLoggedIn, $surveyname;
	$select = "SELECT * FROM ".$table." WHERE ID = ".intval($id);
	$result = mysql_query($select, $aquiescedb);
	$row = mysql_fetch_assoc($result);
	$fields = getFields($table);
	$insertfields = "";
	$insertvalues = "";
	foreach($fields as $field) {
		if($field !="ID") {
			$insertfields .= $field.","; 
			$value = $row[$field];
			if ($field == "surveyname") {
				$value.= " (Duplicated)";
				$surveyname = $value;
			}
			$value = ($field == "createddatetime") ? date('Y-m-d H:i:s') : $value;
			$value = ($field == "createdbyID") ? $row_rsLoggedIn['ID'] : $value;
			$value = ($field == "modifieddatetime") ? "" : $value;
			$value = ($field == "modifiedbyID") ? "" : $value;
			$value = ($field == "surveyID") ? $newsurveyID : $value;
			$value = ($field == "questionID") ? $questionID[$value] : $value; // put new questionID in
			$value = ($field == "answerID") ? $answerID[$value] : $value; // put new answerID in
			if($field == "surveysectionID") {
				$value = (isset($surveysectionID[$value]) && $surveysectionID[$value]>0) ? $surveysectionID[$value] : 0; // put new sectionID in
			}
			$value = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($value) : mysql_escape_string($value);
			$insertvalues .= (strlen($value)==0) ? "NULL," : "'".$value."',";
		}
	}
	$insert = "INSERT INTO ".$table." (".trim($insertfields,",").") VALUES (".trim($insertvalues,",").")"; 
	//echo $select."<br>".$insert."<br><br>"; flush(); ob_flush();
	//return 666;
	$result = mysql_query($insert, $aquiescedb)  or die(mysql_error().$insert);
	return mysql_insert_id();
	
}


$newsurveyID = duplicateEntry("survey",$oldsurveyID);


// the other tables to add
$tables = array("survey_section","survey_question"); // must go in this order
 foreach($tables as $table) { // loop tables
 	$select = "SELECT * FROM ".$table." WHERE surveyID = ".$oldsurveyID; 
	$result = mysql_query($select, $aquiescedb);
	while((is_resource($result) || is_object($result)) && $row = mysql_fetch_assoc($result)) { // loop records
		$newID = duplicateEntry($table,$row['ID'],$newsurveyID); 
		if($table=="survey_section") {
			$surveysectionID[$row['ID']] = $newID;
		} else if($table=="survey_question") { // is question
			$oldquestionID = $row['ID'];
			$questionID[$oldquestionID] = $newID;			
				$select2 = "SELECT * FROM survey_answer WHERE questionID = ".$oldquestionID;
				$result2 = mysql_query($select2, $aquiescedb);
				while((is_object($result2) || is_resource($result2)) && $row2 = mysql_fetch_assoc($result2)) { // inner records
					$newID2 = duplicateEntry("survey_answer",$row2['ID']);
					$answerID[$row2['ID']] = $newID2;
				} // end inner loop records	
		}// end is question
	} // end loop records	 
 } // endd loop tables
 
 // exceptions last

 
foreach($questionID as $oldID => $newID) {
	$select = "SELECT * FROM survey_question_exception WHERE questionID = ".$oldID;
	//echo $select."<br>";
	$result = mysql_query($select, $aquiescedb);
	while((is_resource($result) || is_object($result) )&& $row = mysql_fetch_assoc($result)) { // inner records
		duplicateEntry("survey_question_exception",$row['ID']);	
	} // end inner loop records	
} // end each question
 
 
	
	?>
<h1><i class="glyphicon glyphicon-education"></i> Duplication Successful</h1>
    <p>The survey has now been duplicated with new title: <?php echo $surveyname; ?></p>
    <p><a href="update_survey.php?surveyID=<?php echo $newsurveyID; ?>">Edit this survey</a></p>
    <p><a href="index.php">Back to surveys</a></p>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsSurveyPrefs);
?>
