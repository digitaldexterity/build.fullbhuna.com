<?php session_set_cookie_params(3600); ?>
<?php require_once('../Connections/aquiescedb.php'); ?>
<?php if(!isset($_GET['surveyID'])) { die(); } // for safety ?>
<?php if (!isset($_SESSION)) {
  session_start();
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
$query_rsLoggedIn = sprintf("SELECT users.ID, usertypeID, firstname, surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurveyPrefs = "SELECT * FROM surveyprefs";
$rsThisSurveyPrefs = mysql_query($query_rsThisSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsThisSurveyPrefs = mysql_fetch_assoc($rsThisSurveyPrefs);
$totalRows_rsThisSurveyPrefs = mysql_num_rows($rsThisSurveyPrefs);


$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT survey.* FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

// is survey active?

if($totalRows_rsThisSurvey==0 || $row_rsThisSurvey['statusID']!=1 || (isset($row_rsThisSurvey['startdate']) && $row_rsThisSurvey['startdate']>date('Y-m-d H:i:s')) || (isset($row_rsThisSurvey['enddate']) && $row_rsThisSurvey['enddate']<date('Y-m-d H:i:s'))) {
	$msg = "Sorry, this ".$row_rsThisSurveyPrefs['surveyName']." is not currently available";
	header("location: index.php?msg=".urlencode($msg)); exit;
}


if (isset($row_rsThisSurvey['accesslevel']) && $row_rsThisSurvey['accesslevel'] >0) { //check for access
	if (!isset($row_rsLoggedIn['usertypeID']) || $row_rsLoggedIn['usertypeID'] < $row_rsThisSurvey['accesslevel']) { //no access
		//header("location: /login/signup.php?surveyID=".$row_rsThisSurvey['ID']."&accesscheck=".urlencode($_SERVER['REQUEST_URI'])); exit;
		
		header("location: /login/index.php?accesscheck=".urlencode($_SERVER['REQUEST_URI'])); exit;
	}
}



	// ability to add/update survey as someone else for admin purposes
	// if  userID create it as logged in person	or GET if admin
if(isset($_GET['surveyuserID']) && intval($_GET['surveyuserID'])>0 && $_SESSION['MM_UserGroup'] >=7) {
	$userID = intval($_GET['surveyuserID']);
} else {
	$userID = (isset($row_rsLoggedIn['ID'])) ? $row_rsLoggedIn['ID'] : "";
}

$varDirectoryID_rsThisSurveyUser = "-1";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsThisSurveyUser = $_GET['directoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurveyUser = sprintf("SELECT users.ID AS user, firstname, surname, (SELECT directory.name FROM directory LEFT JOIN directoryuser ON (directoryuser.directoryID = directory.ID) WHERE directoryuser.userID = user AND directory.ID = %s LIMIT 1) AS directoryname FROM users  WHERE users.ID = ".GetSQLValueString($userID, "int")."", GetSQLValueString($varDirectoryID_rsThisSurveyUser, "int"));
$rsThisSurveyUser = mysql_query($query_rsThisSurveyUser, $aquiescedb) or die(mysql_error());
$row_rsThisSurveyUser = mysql_fetch_assoc($rsThisSurveyUser);
$totalRows_rsThisSurveyUser = mysql_num_rows($rsThisSurveyUser);

$varUsername_rsMyDirectory = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsMyDirectory = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMyDirectory = sprintf("SELECT directory.ID, directory.name FROM directory LEFT JOIN directoryuser ON (directory.ID = directoryuser.directoryID) LEFT JOIN users ON (users.ID = directoryuser.userID) WHERE users.username = %s ORDER BY directory.name", GetSQLValueString($varUsername_rsMyDirectory, "text"));
$rsMyDirectory = mysql_query($query_rsMyDirectory, $aquiescedb) or die(mysql_error());
$row_rsMyDirectory = mysql_fetch_assoc($rsMyDirectory);
$totalRows_rsMyDirectory = mysql_num_rows($rsMyDirectory);



if($row_rsThisSurvey['requiredirectoryID']!=1 || (isset($_GET['directoryID']) && intval($_GET['directoryID'])>0)) { // we have a directory if required


	if(isset($_SESSION['survey_session'])) { // if we already have a session, check if it is for this survey and dircetory
		$select = "SELECT surveyID, directoryID FROM survey_session WHERE ID = ".intval($_SESSION['survey_session']);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if(!($row['surveyID'] == $_GET['surveyID'] && (!isset($_GET['directoryID']) || $row['directoryID'] == $_GET['directoryID']))) { //if not,  kill session
			unset($_SESSION['survey_session']);
		} // end kill
	} // end is session
	
	if(!isset($_SESSION['survey_session'])) { // no session exists
		// do we have a unfinished or finished and updateable session?
		$select = "SELECT * FROM survey_session WHERE userID IS NOT NULL AND userID = ".GetSQLValueString($userID, "int")." AND surveyID = ".intval($_GET['surveyID']);
		$select .= isset($_GET['directoryID']) ? " AND directoryID = ".intval($_GET['directoryID']) : "";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$row = mysql_fetch_assoc($result);
		if(mysql_num_rows($result)>0 && (!isset($row['enddatetime']) || $row_rsThisSurvey['canupdate']==1)) { 
		// we have a previous session we can reinstate
			$_SESSION['survey_session'] = $row['ID'];
		} // end have a previous session
		else if (mysql_num_rows($result)==0 || $row_rsThisSurvey['anonymous']==1 ||$row_rsThisSurvey['multiple']==1) { 
		// no previous updateable session for this user, 
		// so start new (but only if anonymous or multiple is on)
			$sessionname = isset($_GET['survey_session']) ? $_GET['survey_session'] : md5(rand(0,time()));
			$directoryID = isset($_GET['directoryID']) ? $_GET['directoryID'] : "";
			$registrationID = isset($_GET['registrationID']) ? $_GET['registrationID'] : "";
			$insert = "INSERT INTO survey_session (sessionID,surveyID,userID,directoryID,registrationID,startdatetime) VALUES (".GetSQLValueString($sessionname, "text").",".GetSQLValueString($_GET['surveyID'], "int").",".GetSQLValueString($userID, "int").",".GetSQLValueString($directoryID, "int").",".GetSQLValueString($registrationID, "int").",NOW())";
			$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
			$_SESSION['survey_session'] = mysql_insert_id();
		} else { // cannot start new  session
			header("location: index.php?error=".urlencode("You have already completed this survey and it can only be completed once.")); exit;
		}
	} 

} // end have directory if required

$body_class = isset($body_class) ? $body_class :"";
$body_class .= " survey survey".$row_rsThisSurvey['ID']." surveystart ";

?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $row_rsThisSurvey['surveyname']." - Introduction"; echo $pageTitle." | ".$site_name; ?></title>
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
      <!-- InstanceBeginEditable name="Body" -->
      <div id = "survey" class="survey surveystart container pageBody">
    <h1><?php echo $row_rsThisSurvey['surveyname']; ?>    </h1>
    <p><?php echo nl2br($row_rsThisSurvey['introduction']); ?></p>
    <?php if (isset($_SESSION['MM_Username']) && $row_rsThisSurvey['anonymous']!=1) { ?>
    <p class="alert warning alert-warning" role="alert">You are logged in as '<?php echo $row_rsThisSurveyUser['firstname']." ".$row_rsThisSurveyUser['surname']; ?>'<?php echo isset($row_rsThisSurveyUser['directoryname']) ? " with ".$row_rsThisSurveyUser['directoryname'] : ""; ?>. You can take a break at any point and your answers will be saved.
<?php if ($row_rsThisSurvey['accesslevel'] < 1 ) { ?> If you prefer, you can also answer this particular survey when <a href="/login/logout.php?returnURL=/surveys/survey.php?surveyID=<?php echo intval($_GET['surveyID']); ?>">logged out</a>.<?php } ?></p><?php } ?>
    <?php if ($row_rsThisSurvey['accesslevel'] < 1 && !isset($_SESSION['MM_Username'])) { ?>
    <?php } ?>
    <ul>
      <li>Use the <span>&quot;Previous&quot;</span> and <span>&quot;Next&quot;</span> buttons to navigate through the question pages.</li>
      <?php if ($row_rsThisSurvey['showsummary'] == 1) { ?><li>You can get a summary of questions answered so far by clicking the &quot;Summary&quot; button</li><?php } ?>
      <li> You can go back and answer any question at any point until you click on the <span>&quot;Finish&quot;</span> button.</li>
    </ul><?php if($row_rsThisSurvey['requiredirectoryID']==1 && (!isset($_GET['directoryID']) || $_GET['directoryID']=="")) { ?> <form action="" method="get" name="surveyStartForm">
   <p> Before you begin, <?php if($totalRows_rsMyDirectory>0) { ?>please select your organisation: 
     <select name="directoryID" id="directoryID" onchange="this.form.submit();">
       <option value="" <?php if (!(strcmp("", @$_GET['directoryID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
       <?php
do {  
?>
       <option value="<?php echo $row_rsMyDirectory['ID']?>"<?php if (!(strcmp($row_rsMyDirectory['ID'], @$_GET['directoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMyDirectory['name']?></option>
       <?php
} while ($row_rsMyDirectory = mysql_fetch_assoc($rsMyDirectory));
  $rows = mysql_num_rows($rsMyDirectory);
  if($rows > 0) {
      mysql_data_seek($rsMyDirectory, 0);
	  $row_rsMyDirectory = mysql_fetch_assoc($rsMyDirectory);
  }
?>
     </select>
   
   or, if new, <?php } ?><a href="/directory/members/add_directory.php?returnURL=<?php echo urlencode("/surveys/survey.php?surveyID=".$_GET['surveyID']); ?>">add your organisation details</a>.
   <input name="surveyID" type="hidden" id="surveyID" value="<?php echo intval($_GET['surveyID']); ?>" />
   </p>
    </form>
   <?php } else { ?>
   <div class="navigation">
    <p>Click on the &quot;Start&quot; button to begin: <button name="surveyStartButton" id="surveyStartButton" type="button" onclick="document.getElementById('loading').style.visibility = 'visible';window.location.href='<?php echo ($row_rsThisSurvey['summarystart']==1) ? "summary.php" : "question.php"; ?>?surveyID=<?php echo intval($_GET['surveyID']); ?>&amp;start=true';">Start</button>
      <span id="loading" style="visibility:hidden;">&nbsp;<img src="../core/images/loading_16x16.gif" alt="Loading - please wait" width="16" height="16" style="vertical-align:
middle;" />&nbsp;Loading...</span></p></div>
      <?php } ?>
  </div>
    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisSurvey);

mysql_free_result($rsThisSurveyUser);

mysql_free_result($rsMyDirectory);

mysql_free_result($rsThisSurveyPrefs);

?>
