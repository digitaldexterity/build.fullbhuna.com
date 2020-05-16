<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveySessions = "SELECT survey_session.ID, survey_session.sessionID, survey_session.surveyID, survey_session.userID, survey_session.startdatetime, survey_session.enddatetime FROM survey_session";
$rsSurveySessions = mysql_query($query_rsSurveySessions, $aquiescedb) or die(mysql_error());
$row_rsSurveySessions = mysql_fetch_assoc($rsSurveySessions);
$totalRows_rsSurveySessions = mysql_num_rows($rsSurveySessions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseChoice = "SELECT * FROM survey_response_choice";
$rsResponseChoice = mysql_query($query_rsResponseChoice, $aquiescedb) or die(mysql_error());
$row_rsResponseChoice = mysql_fetch_assoc($rsResponseChoice);
$totalRows_rsResponseChoice = mysql_num_rows($rsResponseChoice);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseMultiText = "SELECT * FROM survey_response_multitext";
$rsResponseMultiText = mysql_query($query_rsResponseMultiText, $aquiescedb) or die(mysql_error());
$row_rsResponseMultiText = mysql_fetch_assoc($rsResponseMultiText);
$totalRows_rsResponseMultiText = mysql_num_rows($rsResponseMultiText);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponseText = "SELECT * FROM survey_response_text";
$rsResponseText = mysql_query($query_rsResponseText, $aquiescedb) or die(mysql_error());
$row_rsResponseText = mysql_fetch_assoc($rsResponseText);
$totalRows_rsResponseText = mysql_num_rows($rsResponseText);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = "SELECT * FROM survey_comments";
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);
$totalRows_rsComments = mysql_num_rows($rsComments);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsScores = "SELECT * FROM survey_scores";
$rsScores = mysql_query($query_rsScores, $aquiescedb) or die(mysql_error());
$row_rsScores = mysql_fetch_assoc($rsScores);
$totalRows_rsScores = mysql_num_rows($rsScores);
?>
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Update survey sessions</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" --><h1>Upgrade Survey Sessions</h1>
<p>Upgrade user sessions...</p>
<table border="0" cellpadding="2" cellspacing="0" class="listTable">
  <tr>
    
    <td><strong>sessionID</strong></td><td><strong>userID</strong></td>
    
    <td><strong>surveyID</strong></td>
    <td><strong>startdatetime</strong></td>
    <td><strong>enddatetime</strong></td><td><strong>SQL</strong></td>
  </tr>
  <?php do { ?>
    <tr>
      
      <td><?php echo $row_rsSurveySessions['sessionID']; ?></td><td><?php echo $row_rsSurveySessions['userID']; ?></td>
     
      <td><?php echo $row_rsSurveySessions['surveyID']; ?></td>
      <td><?php echo $row_rsSurveySessions['startdatetime']; ?></td>
      <td><?php echo $row_rsSurveySessions['enddatetime']; ?></td> <td><?php 
	  // deal with proprietary sessions for cycling scotland and 3a
	  if(!isset($row_rsSurveySessions['userID'])) {
		  $session = explode("_",$row_rsSurveySessions['sessionID']);
	  $userID = 0;  $cfeID = 0;
	  if($session[0] == "cfe") {
		  $cfeID = $session[1];
		  $select = "SELECT userID FROM cfe WHERE ID = ".GetSQLValueString($cfeID,"int");
		  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$userID = $row['userID'];
	  $row = mysql_fetch_assoc($result);
	  } else if($session[0] == "3a") {
		  $select = "SELECT inspectorID FROM aaa_inspection WHERE ID = ".GetSQLValueString($session[4],"int");
		  $result = mysql_query($select, $aquiescedb) or die(mysql_error());

	  $row = mysql_fetch_assoc($result);
	  $userID = $row['inspectorID'];
		  
	  } else{ 
	  	if ($session[0] == "CSRR") {
		  	$username = $session[1];
		} else {
			$username = $row_rsSurveySessions['sessionID'];
		}
	  $select = "SELECT ID FROM users WHERE username = ".GetSQLValueString($username,"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());

	  $row = mysql_fetch_assoc($result);
	  $userID = $row['ID'];
	  }
	  if($userID>0) {
	  $update = "UPDATE survey_session SET userID = ".GetSQLValueString($userID,"int")." WHERE ID = ".GetSQLValueString($row_rsSurveySessions['ID'],"int");
	   $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  } 
	  } ?></td>
    </tr>
    <?php } while ($row_rsSurveySessions = mysql_fetch_assoc($rsSurveySessions)); ?>
</table><h2>Upgrade answer sessions from old to new</h2>
<p>&nbsp;</p>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>answerID</td>
    <td>sessionID</td>
    <td>createddatetime</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsResponseChoice['ID']; ?></td>
      <td><?php echo $row_rsResponseChoice['answerID']; ?></td>
      <td><?php echo $row_rsResponseChoice['sessionID']; ?></td>
      <td><?php echo $row_rsResponseChoice['createddatetime'];
	   $select = "SELECT ID FROM survey_session WHERE sessionID = ".GetSQLValueString($row_rsResponseChoice['sessionID'],"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)>0) {
	  $row = mysql_fetch_assoc($result);
	  $update = "UPDATE survey_response_choice SET sessionID = ".$row['ID']." WHERE sessionID =  ".GetSQLValueString($row_rsResponseChoice['sessionID'],"text");
	  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  } ?></td>
    </tr>
    <?php } while ($row_rsResponseChoice = mysql_fetch_assoc($rsResponseChoice)); ?>
</table>
<p>&nbsp;</p>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>answerID</td>
    <td>response</td>
    <td>sessionID</td>
    <td>createddatetime</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsResponseMultiText['ID']; ?></td>
      <td><?php echo $row_rsResponseMultiText['answerID']; ?></td>
      <td><?php echo $row_rsResponseMultiText['response']; ?></td>
      <td><?php echo $row_rsResponseMultiText['sessionID']; ?></td>
      <td><?php echo $row_rsResponseMultiText['createddatetime'];
	  $select = "SELECT ID FROM survey_session WHERE sessionID = ".GetSQLValueString($row_rsResponseMultiText['sessionID'],"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)>0) {
	  $row = mysql_fetch_assoc($result);
	  $update = "UPDATE survey_response_multitext SET sessionID = ".$row['ID']." WHERE sessionID = ".GetSQLValueString($row_rsResponseMultiText['sessionID'],"text");
	  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  } ?></td>
    </tr>
    <?php } while ($row_rsResponseMultiText = mysql_fetch_assoc($rsResponseMultiText)); ?>
</table>
<p>&nbsp;</p>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>questionID</td>
    <td>sessionID</td>
    <td>response_text</td>
    <td>createddatetime</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsResponseText['ID']; ?></td>
      <td><?php echo $row_rsResponseText['questionID']; ?></td>
      <td><?php echo $row_rsResponseText['sessionID']; ?></td>
      <td><?php echo $row_rsResponseText['response_text']; ?></td>
      <td><?php echo $row_rsResponseText['createddatetime'];
	  $select = "SELECT ID FROM survey_session WHERE sessionID = ".GetSQLValueString($row_rsResponseText['sessionID'],"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)>0) {
	  $row = mysql_fetch_assoc($result);
	  $update = "UPDATE survey_response_text SET sessionID = ".$row['ID']." WHERE sessionID = ".GetSQLValueString($row_rsResponseText['sessionID'],"text");
	  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  }
	  ?></td>
    </tr>
    <?php } while ($row_rsResponseText = mysql_fetch_assoc($rsResponseText)); ?>
</table>
<p>&nbsp;</p>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>comments</td>
    <td>sessionID</td>
    <td>questionID</td>
    <td>createddatetime</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsComments['ID']; ?></td>
      <td><?php echo $row_rsComments['comments']; ?></td>
      <td><?php echo $row_rsComments['sessionID']; ?></td>
      <td><?php echo $row_rsComments['questionID']; ?></td>
      <td><?php echo $row_rsComments['createddatetime']; 
	  $select = "SELECT ID FROM survey_session WHERE sessionID = ".GetSQLValueString($row_rsComments['sessionID'],"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)>0) {
	  $row = mysql_fetch_assoc($result);
	  $update = "UPDATE survey_comments SET sessionID = ".$row['ID']." WHERE sessionID = ".GetSQLValueString($row_rsComments['sessionID'],"text");
	  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  } ?></td>
    </tr>
    <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?>
</table>
<p>&nbsp;</p>
<table border="0" cellpadding="2" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>score</td>
    <td>finalscore</td>
    <td>createddatetime</td>
    <td>sessionID</td>
    <td>questionID</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsScores['ID']; ?></td>
      <td><?php echo $row_rsScores['score']; ?></td>
      <td><?php echo $row_rsScores['finalscore']; ?></td>
      <td><?php echo $row_rsScores['createddatetime']; ?></td>
      <td><?php echo $row_rsScores['sessionID']; ?></td>
      <td><?php echo $row_rsScores['questionID'];
	  $select = "SELECT ID FROM survey_session WHERE sessionID = ".GetSQLValueString($row_rsScores['sessionID'],"text");
	  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	  if(mysql_num_rows($result)>0) {
	  $row = mysql_fetch_assoc($result);
	  $update = "UPDATE survey_scores SET sessionID = ".$row['ID']." WHERE sessionID = ".GetSQLValueString($row_rsScores['sessionID'],"text");
	  $result = mysql_query($update, $aquiescedb) or die(mysql_error());
	  echo $update;
	  }
	  ?></td>
    </tr>
    <?php } while ($row_rsScores = mysql_fetch_assoc($rsScores)); ?>
</table>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSurveySessions);

mysql_free_result($rsResponseChoice);

mysql_free_result($rsResponseMultiText);

mysql_free_result($rsResponseText);

mysql_free_result($rsComments);

mysql_free_result($rsScores);
?>
