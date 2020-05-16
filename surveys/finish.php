<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../mail/includes/sendmail.inc.php'); ?>
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
?>
<?php
$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT firstname, surname, users.email, username, directory.name FROM users LEFT JOIN directory ON (users.ID = directory.createdbyID) WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT orgname, contactemail FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT * FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$colname_rsThisSession = "-1";
if (isset($_SESSION['survey_session'])) {
  $colname_rsThisSession = $_SESSION['survey_session'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSession = sprintf("SELECT survey_session.*, users.firstname, users.surname, directory.name FROM survey_session LEFT JOIN users ON (survey_session.userID = users.ID) LEFT JOIN directory ON (survey_session.directoryID = directory.ID) WHERE survey_session.ID = %s", GetSQLValueString($colname_rsThisSession, "text"));
$rsThisSession = mysql_query($query_rsThisSession, $aquiescedb) or die(mysql_error());
$row_rsThisSession = mysql_fetch_assoc($rsThisSession);
$totalRows_rsThisSession = mysql_num_rows($rsThisSession);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurveyPrefs = "SELECT * FROM surveyprefs";
$rsThisSurveyPrefs = mysql_query($query_rsThisSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsThisSurveyPrefs = mysql_fetch_assoc($rsThisSurveyPrefs);
$totalRows_rsThisSurveyPrefs = mysql_num_rows($rsThisSurveyPrefs);


    if (isset($_GET['finish']) || !isset($_SESSION['MM_Username'])) { // if final finish or not logged in
 $query = "UPDATE survey_session SET survey_session.enddatetime = NOW() WHERE survey_session.ID = ".GetSQLValueString($_SESSION['survey_session'],"text");
  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($query, $aquiescedb) or die(mysql_error()); // add expired session to list
  $survey_session = $_SESSION['survey_session']; // for use later
	unset($_SESSION['survey_session']); // kill session
	
	if (isset($row_rsThisSurvey['email']) && $row_rsThisSurvey['email'] !="") { 
	
	$name = isset($row_rsThisSession['surname']) ? "Completed by: ".$row_rsThisSession['firstname']." ".$row_rsThisSession['surname'] : "";
	$name .=isset($row_rsThisSession['name']) ? " (".$row_rsThisSession['name'].")" : "";
	$to = $row_rsThisSurvey['email'];
	$subject = $row_rsThisSurvey['surveyname']." ".$name;
	$message = "This is an automated message to let you know that the questionnaire '".$row_rsThisSurvey['surveyname']."' has been completed.\n\n".$name;
	
	sendMail($to,$subject,$message);  
	} // if email
} // if finish

if (is_readable(SITE_ROOT."local/surveys/finish.php")) {
	$redirectURL = "/local/surveys/finish.php";
	$redirectURL .= "?".$_SERVER['QUERY_STRING'];
	$redirectURL .= "&surveysessionID=".$survey_session;
	$redirectURL .= "&key=".md5(PRIVATE_KEY.$survey_session);
	header("location: ".$redirectURL); exit;
}

if (isset($row_rsThisSurvey['redirectURL'])) {
	$redirectURL = $row_rsThisSurvey['redirectURL'];
	$redirectURL .= (strpos($redirectURL, '?')) ? "&" : "?";
	$redirectURL .= "surveyID=".intval($_GET['surveyID']);
	$redirectURL .= "&surveysessionID=".$survey_session;
	header("location: ".$redirectURL); exit;
}

$body_class = isset($body_class) ? $body_class :"";
$body_class .= " survey survey".$row_rsThisSurvey['ID']." surveyfinish ";
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = $row_rsThisSurvey['surveyname']." - Complete"; echo $pageTitle." | ".$site_name; ?></title>
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
	   <div id = "survey" class="survey container pageBody"><?php if (isset($_GET['finish']) || !isset($_SESSION['MM_Username'])) { // if final finish or not logged in
	if($row_rsThisSurvey['confirmationemail']==1 && isset($row_rsLoggedIn['email']) && strlen($row_rsThisSurvey['confirmationemailcontent'])>1 ) { // send email 
	$to = $row_rsLoggedIn['email'];
	$subject = $row_rsThisSurvey['surveyname'];
	$message = $row_rsThisSurvey['confirmationemailcontent'];
	sendMail($to, $subject, $message);
	
	}?>
    <h1>Finished!</h1>
    <p>Thank you<?php echo isset($row_rsLoggedIn['firstname']) ? ", ".$row_rsLoggedIn['firstname']."," : ""; ?> for taking part.</p>
    <p>Your answers have been submitted. We appreciate the time you have spent answering this <?php echo $row_rsThisSurveyPrefs['surveyName']; ?>.</p>
    <?php } else { ?><h1>Come back later!</h1>
    <p>You have exited the survey, but you can come back later to finally complete and submit your answers.</p>
    
    <?php } ?>
    <p><a href="<?php echo isset($row_rsThisSurvey['redirectURL']) ? $row_rsThisSurvey['redirectURL'] : "/"; ?>">Continue to home page</a></p>
    <?php if (isset($_SESSION['MM_Username'])) { ?><p><a href="/login/logout.php" onclick="document.returnValue =  confirm('Are you sure you want to log out?\n\n(You will need to log in again next time even if you checked Remember Me)');return document.returnValue;">Log Out</a></p>
	<?php } // end logged in user ?></div><!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsPreferences);

mysql_free_result($rsThisSurvey);

mysql_free_result($rsThisSession);

mysql_free_result($rsThisSurveyPrefs);
?>
