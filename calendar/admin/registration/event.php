<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "6,7,8,9,10";
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



$currentPage = $_SERVER["PHP_SELF"];

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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE event SET registration=%s, registrationtext=%s, registrationURL=%s, registrationmulti=%s, registrationstart=%s, registrationend=%s, registrationmax=%s, registrationdob=%s, registrationteam=%s, registrationteamname=%s, registrationmedical=%s, registrationinfo=%s, registrationpayment=%s, registrationinvoice=%s, registrationcost=%s, registrationconcession=%s, registrationsequential=%s, over65=%s, teamdiscountamount=%s, teamdiscounttype=%s, teamdiscountnumber=%s, memberdiscountamount=%s, memberdiscounttype=%s, memberdiscountrank=%s, memberdiscountgroup=%s, familydiscountamount=%s, familydiscountamounttype=%s, familydiscountadults=%s, familydiscountchildren=%s, paymentinstructions=%s, registrationconfirmationURL=%s, registrationaskjobtitle=%s, registrationaskaddress=%s, registrationasktelephone=%s, registrationaskcompany=%s, registrationtshirt=%s, registrationtime=%s, registrationchoosestarttime=%s, registrationwheelchair=%s, registrationdiscovered=%s, registrationalertemail=%s, registrationfullemail=%s, registrationemailtemplateID=%s, registrationdietryreq=%s, registrationspecialreq=%s, registrationextraquestion=%s, registrationextraquestion2=%s, registrationextraquestion3=%s, registrationextracompulsary=%s, registrationextracompulsary2=%s, registrationextracompulsary3=%s, registrationadminemail=%s, registrationemail=%s, registrationemailnumbers=%s, registrationemailmessage=%s, registrationautoaccept=%s, registrationgroupID=%s, registrationmarketingtext=%s, registrationtermstext=%s, takenpartbefore=%s, modifiedbyID=%s, modifieddatetime=%s, eventgroupID=%s, surveyID=%s, rsvp=%s, rsvpdatetime=%s, registrationmarketingtextshow=%s, registrationtermstextshow=%s WHERE ID=%s",
                       GetSQLValueString($_POST['registration'], "int"),
                       GetSQLValueString($_POST['registrationtext'], "text"),
                       GetSQLValueString($_POST['registrationURL'], "text"),
                       GetSQLValueString($_POST['registrationmulti'], "int"),
                       GetSQLValueString($_POST['registrationstart'], "date"),
                       GetSQLValueString($_POST['registrationend'], "date"),
                       GetSQLValueString($_POST['registrationmax'], "int"),
                       GetSQLValueString(isset($_POST['registrationdob']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationteam'], "int"),
                       GetSQLValueString(isset($_POST['registrationteamname']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationmedical']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationinfo']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationpayment'], "int"),
                       GetSQLValueString(isset($_POST['registrationinvoice']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationcost'], "double"),
                       GetSQLValueString($_POST['registrationconcession'], "double"),
                       GetSQLValueString(isset($_POST['registrationsequential']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['over65']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['teamdiscountamount'], "int"),
                       GetSQLValueString($_POST['teamdiscountamountype'], "int"),
                       GetSQLValueString($_POST['teamdiscountnumber'], "int"),
                       GetSQLValueString($_POST['memberdiscountamount'], "double"),
                       GetSQLValueString($_POST['memberdiscountamounttype'], "int"),
                       GetSQLValueString($_POST['memberdiscountrank'], "int"),
                       GetSQLValueString($_POST['memberdiscountgroup'], "int"),
                       GetSQLValueString($_POST['familydiscountamount'], "double"),
                       GetSQLValueString($_POST['familydiscountamounttype'], "int"),
                       GetSQLValueString($_POST['familydiscountadults'], "int"),
                       GetSQLValueString($_POST['familydiscountchildren'], "int"),
                       GetSQLValueString($_POST['paymentinstructions'], "text"),
                       GetSQLValueString($_POST['registrationconfirmationURL'], "text"),
                       GetSQLValueString(isset($_POST['registrationaskjobtitle']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationaskaddress']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationasktelephone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationaskcompany']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationtshirt']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationtime']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationchoosestarttime']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationwheelchair']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationalertemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationfullemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationemailtemplateID'], "int"),
                       GetSQLValueString(isset($_POST['registrationdietryreq']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationspecialreq']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationextraquestion'], "text"),
                       GetSQLValueString($_POST['registrationextraquestion2'], "text"),
                       GetSQLValueString($_POST['registrationextraquestion3'], "text"),
                       GetSQLValueString(isset($_POST['registrationextracompulsary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationextracompulsary2']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationextracompulsary3']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationadminemail'], "text"),
                       GetSQLValueString(isset($_POST['registrationemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationemailnumbers']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['registrationemailmessage'], "text"),
                       GetSQLValueString(isset($_POST['registrationautoaccept']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['usergroupID'], "int"),
                       GetSQLValueString($_POST['registrationmarketingtext'], "text"),
                       GetSQLValueString($_POST['registrationtermstext'], "text"),
                       GetSQLValueString(isset($_POST['takenpartbefore']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['eventgroupID'], "int"),
                       GetSQLValueString($_POST['surveyID'], "int"),
                       GetSQLValueString(isset($_POST['rsvp']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['rsvpdatetime'], "date"),
                       GetSQLValueString(isset($_POST['registrationmarketingtextshow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationtermstextshow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "../update_calendar.php?eventgroupID=" . $_POST['eventgroupID'] . "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

$maxRows_rsThisEvent = 1000;
$pageNum_rsThisEvent = 0;
if (isset($_GET['pageNum_rsThisEvent'])) {
  $pageNum_rsThisEvent = $_GET['pageNum_rsThisEvent'];
}
$startRow_rsThisEvent = $pageNum_rsThisEvent * $maxRows_rsThisEvent;

$varEventID_rsThisEvent = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsThisEvent = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisEvent = sprintf("SELECT event.*, eventgroup.eventtitle AS eventgrouptitle, eventgroup.categoryID FROM event LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) WHERE event.ID = %s", GetSQLValueString($varEventID_rsThisEvent, "int"));
$query_limit_rsThisEvent = sprintf("%s LIMIT %d, %d", $query_rsThisEvent, $startRow_rsThisEvent, $maxRows_rsThisEvent);
$rsThisEvent = mysql_query($query_limit_rsThisEvent, $aquiescedb) or die(mysql_error());
$row_rsThisEvent = mysql_fetch_assoc($rsThisEvent);

if (isset($_GET['totalRows_rsThisEvent'])) {
  $totalRows_rsThisEvent = $_GET['totalRows_rsThisEvent'];
} else {
  $all_rsThisEvent = mysql_query($query_rsThisEvent);
  $totalRows_rsThisEvent = mysql_num_rows($all_rsThisEvent);
}
$totalPages_rsThisEvent = ceil($totalRows_rsThisEvent/$maxRows_rsThisEvent)-1;

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$maxRows_rsRegistrants = (isset($_GET['csv'])) ? 5000 : 50;
$pageNum_rsRegistrants = 0;
if (isset($_GET['pageNum_rsRegistrants'])) {
  $pageNum_rsRegistrants = $_GET['pageNum_rsRegistrants'];
}
$startRow_rsRegistrants = $pageNum_rsRegistrants * $maxRows_rsRegistrants;

$orderby = (isset($_GET['orderby']) && $_GET['orderby'] == 2) ?  "eventregistration.createddatetime" : "eventregistration.registrationnumber";

$varEventID_rsRegistrants = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsRegistrants = $_GET['eventID'];
}
$varSearch_rsRegistrants = "%";
if (isset($_GET['search'])) {
  $varSearch_rsRegistrants = $_GET['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegistrants = sprintf("SELECT eventregistration.ID, eventregistration.statusID, eventregistration.registrationnumber, eventregistration.createddatetime, users.firstname, users.surname, users.dob,eventregistration.registrationteamname, eventregistration.registrationtshirt, eventregistration.registrationmarketing, location.address1,location.address2,location.address3,location.address4,location.postcode,location.telephone1,location.telephone2, eventregistration.paymentamount, paypal_payment_info.mc_gross FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID)  LEFT JOIN paypal_payment_info ON (on1= CONCAT('registration-',eventregistration.ID))WHERE eventID = %s AND (users.surname LIKE %s OR eventregistration.registrationnumber = %s) ORDER BY ".$orderby." DESC", GetSQLValueString($varEventID_rsRegistrants, "int"),GetSQLValueString($varSearch_rsRegistrants . "%", "text"),GetSQLValueString($varSearch_rsRegistrants, "text"));
$query_limit_rsRegistrants = sprintf("%s LIMIT %d, %d", $query_rsRegistrants, $startRow_rsRegistrants, $maxRows_rsRegistrants);
$rsRegistrants = mysql_query($query_limit_rsRegistrants, $aquiescedb) or die(mysql_error());
$row_rsRegistrants = mysql_fetch_assoc($rsRegistrants);

if (isset($_GET['totalRows_rsRegistrants'])) {
  $totalRows_rsRegistrants = $_GET['totalRows_rsRegistrants'];
} else {
  $all_rsRegistrants = mysql_query($query_rsRegistrants);
  $totalRows_rsRegistrants = mysql_num_rows($all_rsRegistrants);
}
$totalPages_rsRegistrants = ceil($totalRows_rsRegistrants/$maxRows_rsRegistrants)-1;


$varRegionID_rsGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE groupsetID IS NULL and statusID =1 AND regionID = %s ORDER BY groupname ASC ", GetSQLValueString($varRegionID_rsGroups, "int"));
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);

$maxRows_rsAttendees = 1000;
$pageNum_rsAttendees = 0;
if (isset($_GET['pageNum_rsAttendees'])) {
  $pageNum_rsAttendees = $_GET['pageNum_rsAttendees'];
}
$startRow_rsAttendees = $pageNum_rsAttendees * $maxRows_rsAttendees;

$varEventID_rsAttendees = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsAttendees = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAttendees = sprintf("SELECT eventattend.ID, eventattend.statusID, users.firstname, users.surname, eventattend.createddatetime FROM eventattend LEFT JOIN users ON (eventattend.userID = users.ID) WHERE eventattend.eventID = %s ORDER BY eventattend.statusID ASC, eventattend.createddatetime DESC", GetSQLValueString($varEventID_rsAttendees, "int"));
$query_limit_rsAttendees = sprintf("%s LIMIT %d, %d", $query_rsAttendees, $startRow_rsAttendees, $maxRows_rsAttendees);
$rsAttendees = mysql_query($query_limit_rsAttendees, $aquiescedb) or die(mysql_error());
$row_rsAttendees = mysql_fetch_assoc($rsAttendees);

if (isset($_GET['totalRows_rsAttendees'])) {
  $totalRows_rsAttendees = $_GET['totalRows_rsAttendees'];
} else {
  $all_rsAttendees = mysql_query($query_rsAttendees);
  $totalRows_rsAttendees = mysql_num_rows($all_rsAttendees);
}
$totalPages_rsAttendees = ceil($totalRows_rsAttendees/$maxRows_rsAttendees)-1;

$varEventID_rsCountYes = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsCountYes = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountYes = sprintf("SELECT COUNT(eventattend.ID) AS countyes FROM eventattend WHERE eventattend.statusID = 1 AND eventattend.eventID = %s", GetSQLValueString($varEventID_rsCountYes, "int"));
$rsCountYes = mysql_query($query_rsCountYes, $aquiescedb) or die(mysql_error());
$row_rsCountYes = mysql_fetch_assoc($rsCountYes);
$totalRows_rsCountYes = mysql_num_rows($rsCountYes);

$varEventID_rsCountNo = "-1";
if (isset($_GET['eventID'])) {
  $varEventID_rsCountNo = $_GET['eventID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountNo = sprintf("SELECT COUNT(eventattend.ID) AS countno FROM eventattend WHERE eventattend.statusID = 2 AND eventattend.eventID = %s", GetSQLValueString($varEventID_rsCountNo, "int"));
$rsCountNo = mysql_query($query_rsCountNo, $aquiescedb) or die(mysql_error());
$row_rsCountNo = mysql_fetch_assoc($rsCountNo);
$totalRows_rsCountNo = mysql_num_rows($rsCountNo);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsertypes = "SELECT ID, name FROM usertype WHERE ID >= 1 ORDER BY ID ASC";
$rsUsertypes = mysql_query($query_rsUsertypes, $aquiescedb) or die(mysql_error());
$row_rsUsertypes = mysql_fetch_assoc($rsUsertypes);
$totalRows_rsUsertypes = mysql_num_rows($rsUsertypes);


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveys = "SELECT ID, surveyname FROM survey ORDER BY surveyname ASC";
$rsSurveys = mysql_query($query_rsSurveys, $aquiescedb) or die(mysql_error());
$row_rsSurveys = mysql_fetch_assoc($rsSurveys);
$totalRows_rsSurveys = mysql_num_rows($rsSurveys);

$varRegionID_rsMailTemplates = "1";
if (isset($regionID)) {
  $varRegionID_rsMailTemplates = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailTemplates = sprintf("SELECT ID, templatename FROM groupemailtemplate WHERE regionID = %s AND groupemailtemplate.statusID = 1 ORDER BY groupemailtemplate.templatename", GetSQLValueString($varRegionID_rsMailTemplates, "int"));
$rsMailTemplates = mysql_query($query_rsMailTemplates, $aquiescedb) or die(mysql_error());
$row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
$totalRows_rsMailTemplates = mysql_num_rows($rsMailTemplates);

$queryString_rsRegistrants = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsRegistrants") == false && 
        stristr($param, "totalRows_rsRegistrants") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsRegistrants = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsRegistrants = sprintf("&totalRows_rsRegistrants=%d%s", $totalRows_rsRegistrants, $queryString_rsRegistrants);

?>
<?php if(isset($_GET['csv'])) {
	require_once('../../../core/includes/framework.inc.php');
	$where = isset($_GET['teamcolumn']) ? " AND (withregistrationID = eventregistration.ID OR withregistrationID IS NULL) " : "";
	$select = "SELECT eventregistration.*, users.firstname, users.surname, users.dob, users.email,location.address1,location.address2,location.address3,location.address4,location.postcode,location.telephone1,location.telephone2, survey_session.ID AS sessionID, survey_session.surveyID, paypal_payment_info.mc_gross FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) LEFT JOIN location ON (users.defaultaddressID = location.ID) LEFT JOIN survey_session ON (survey_session.registrationID = eventregistration.ID AND survey_session.userID = eventregistration.userID) LEFT JOIN paypal_payment_info ON (on1= CONCAT('registration-',eventregistration.ID)) WHERE eventID = ".intval($_GET['eventID']).$where." GROUP BY eventregistration.ID ORDER BY eventregistration.registrationnumber ASC";
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	if(mysql_num_rows($result)>0) {
		
		csvHeaders("event_registrants");
		print(formatCSV("First name").","
						.formatCSV("Surname").","
						.formatCSV("Date of Birth").","
						.formatCSV("Email").","
						.formatCSV("Paid").","
						.formatCSV("Marketing opt-in").","
						.formatCSV("Address 1").","
						.formatCSV("Address 2").","
						.formatCSV("Address 3").","
						.formatCSV("Address 4").","
						.formatCSV("Postcode").","
						.formatCSV("Telephone 1").","
						.formatCSV("Telephone 2").","
						.formatCSV("Job Title").","
						.formatCSV("Company").","
						.formatCSV("Medical Requirements").","
						.formatCSV($row_rsThisEvent['registrationextraquestion']).","
						.formatCSV($row_rsThisEvent['registrationextraquestion2']).","
						.formatCSV($row_rsThisEvent['registrationextraquestion3']).","
						.formatCSV("Special Req").","
						.formatCSV("Dietry Req"));
						if(isset($_GET['teamcolumn'])) { 	
							print(",");
							print(formatCSV("Team members")); 
						}
						
						$row = mysql_fetch_assoc($result);
						if(isset($row['sessionID']) && $row['sessionID']>0) { // survey						
							$select = "SELECT  survey_question.ID, survey_question.question_number, survey_question.questiontext FROM survey_question  WHERE survey_question.surveyID =  ".intval($row['surveyID'])." AND survey_question.active = 1 ORDER BY CAST(survey_question.question_number AS UNSIGNED)";
							$questions = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
							print(",");
							$line = "";		
							while($question = mysql_fetch_assoc($questions)) {
								$qn= isset($question['question_number']) ? $question['question_number'].". " : "";
								$line .= formatCSV($qn.$question['questiontext']).",";
								
							}
							echo trim($line,",");
							
						}
						mysql_data_seek($result,0);
						print("\r\n");
						
				
 		while($row = mysql_fetch_assoc($result)) {
			
			print(formatCSV($row['firstname'],"capitalise").",");
			print(formatCSV($row['surname'],"capitalise").",");
			print(formatCSV($row['dob'],"date").",");
			print(formatCSV($row['email'],"text").",");
			print(formatCSV($row['mc_gross'],"text").",");
			
			print(formatCSV($row['registrationmarketing'],"capitalise").",");
			print(formatCSV($row['address1'],"capitalise").",");
			print(formatCSV($row['address2'],"capitalise").",");
			print(formatCSV($row['address3'],"capitalise").",");
			print(formatCSV($row['address4'],"capitalise").",");
			print(formatCSV($row['postcode'],"capitalise").",");
			print(formatCSV($row['telephone1'],"capitalise").",");
			print(formatCSV($row['telephone2'],"capitalise").",");
			print(formatCSV($row['registrationjobtitle'],"capitalise").",");
			print(formatCSV($row['registrationcompany'],"capitalise").",");
			print(formatCSV($row['registrationmedical'],"capitalise").",");
			print(formatCSV($row['registrationinfo'],"capitalise").",");
			print(formatCSV($row['registrationinfo2'],"capitalise").",");
			print(formatCSV($row['registrationinfo3'],"capitalise").",");
			print(formatCSV($row['registrationspecialreq'],"capitalise").",");
			print(formatCSV($row['registrationdietryreq'],"capitalise"));
			if(isset($_GET['teamcolumn'])) { // sho wteam members in column
				print(",");
				$teammembers = "";
				$select2 = "SELECT eventregistration.ID, eventregistration.statusID, registrationnumber, firstname, surname, registrationteamname, registrationtshirt FROM eventregistration LEFT JOIN users ON (eventregistration.userID = users.ID) WHERE (withregistrationID = ".$row['ID']." OR eventregistration.ID = ".$row['ID'].") ORDER BY eventregistration.registrationnumber ASC";			
				$result2 = mysql_query($select2, $aquiescedb) or die(mysql_error());
				if(mysql_num_rows($result2)>0) {
					while($row2 = mysql_fetch_assoc($result2)) {
						$tshirt = "";
						switch($row['registrationtshirt']) {
						  case 1 : $tshirt =  "XS"; break;
						  case 2 : $tshirt =  "S"; break;
						  case 3 : $tshirt =  "M"; break;
						  case 4 : $tshirt =  "L"; break;
						  case 5 : $tshirt =  "XL"; break;
						  default : $tshirt =  "-"; 
					  };
						$teammembers .= $row2['registrationnumber']." ".$row2['firstname']." ".$row2['surname']." (".$tshirt.")\r";
					}
					print(formatCSV($teammembers,"capitalise"));
				} 
			} // end team members
			if(isset($row['sessionID']) && $row['sessionID']>0) { // survey columns
				$sessionID = isset($row['sessionID']) ? $row['sessionID'] : -1;
				mysql_data_seek($questions,0); $line = "";
				print(",");
			   while($question = mysql_fetch_assoc($questions)) { 
				  $item = "";
				  $query = "SELECT survey_response_text.response_text, survey_answer.answertext, survey_response_choice.ID AS checked, survey_response_multitext.response, survey_comments.comments FROM survey_question
				  LEFT JOIN survey_response_text ON (survey_response_text.questionID = survey_question.ID AND survey_response_text.sessionID = ". $sessionID.")
				  
				  LEFT JOIN  survey_answer ON (survey_answer.questionID = survey_question.ID) 
				  LEFT JOIN survey_response_choice ON (survey_response_choice.answerID = survey_answer.ID AND survey_response_choice.sessionID = ". $sessionID." )
				  LEFT JOIN survey_response_multitext ON (survey_response_multitext.answerID = survey_answer.ID AND survey_response_multitext.sessionID = ". $sessionID." )
				  LEFT JOIN survey_comments ON (survey_comments.questionID = survey_question.ID AND survey_comments.sessionID = ".$sessionID.") 
				  
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
			
			echo trim($line.",");
			}
			print("\r\n");
			
			
 		}
	exit;
	}
}
 

	/*$headers = array("ID|hide","STATUS|hide","NUMBER|number","REGISTERED|date","FIRSTNAME|capitalise","SURNAME|capitalise","TEAM|capitalise","T-SHIRT|capitalise","OPT-IN|boolean","ADDRESS 1|capitalise","ADDRESS 2|capitalise","ADDRESS 3|capitalise","ADDRESS 4|capitalise","POST CODE|capitalise","TELEPHONE 1|number", "TELEPHONE 2|number");
	exportCSV($headers, $rsRegistrants, $filename="event_registrants");
}*/
$body_class = isset($body_class) ? $body_class : "";
$body_class .= " editregistration eventcategory".$row_rsThisEvent['categoryID'];
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Event Registration"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php require_once('../../../core/tinymce/tinymce.inc.php'); ?>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<style >
<!--
<?php if($row_rsThisEvent['registrationtshirt']!=1) { echo ".columnTshirt { display: none !important; }"; } ?>
<?php if($row_rsThisEvent['registrationteamname']!=1) { echo ".columnTeamname { display: none !important; }"; } ?>
<?php if(!isset($row_rsThisEvent['registrationpayment']) || $row_rsThisEvent['registrationpayment']==0) { echo ".columnPayment { display: none !important; }"; } ?>
<?php if($totalRows_rsSurveys==0) { echo ".survey { display: none !important; }"; } ?>
<?php if($totalRows_rsUserGroups==0) {echo "#memberdiscountgroup { display: none !important; }"; } ?>
-->
</style>
<link href="../../css/calendarDefault.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script>
$(document).ready(function(e) {
	toggleRegistration()
    
});
function toggleRegistration() {
	if($('input[name=registration]:checked').val()==1) { 
		$("#registrationURL").hide();
		$("#registrationtext").show();
	} else if($('input[name=registration]:checked').val()==2) {
		$("#registrationURL").show();
		$("#registrationtext").show();
	} else {
		$("#registrationURL").hide();
		$("#registrationtext").hide();
	}
	
	
	
}
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page calendar"><h1><i class="glyphicon glyphicon-calendar"></i> <?php echo $row_rsThisEvent['eventgrouptitle']; ?> Sign ups</h1>
   
    
     
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
          <li><a href="add_registrant.php?eventID=<?php echo $row_rsThisEvent['ID']; ?>" ><i class="glyphicon glyphicon-plus-sign"></i> Add sign up</a></li>
          <li><a href="../update_calendar.php?eventgroupID=<?php echo $row_rsThisEvent['eventgroupID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Update Event</a></li>
          <li><a href="registration_reports.php?eventID=<?php echo intval($_GET['eventID']); ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Reports</a></li>
           
        </ul></div></nav>
      <form action="index.php" method="get" id="searchform" role="form">
            <fieldset>
              <legend>Filter</legend>
              <span id="sprytextfield1">
              <label>
                <input name="search" type="text"  value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search']), ENT_COMPAT, "UTF-8") : ""; ?>" size="30" maxlength="30" />
              </label>
</span>
              <select name="orderby" id="orderby">
                <option value="1" <?php if (!(strcmp(1, @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>order by number</option>
                <option value="2" <?php if (!(strcmp(2, @$_GET['orderby']))) {echo "selected=\"selected\"";} ?>>order by date signed up</option>
              </select>
              <input name="eventID" type="hidden" id="eventID" value="<?php echo intval($_GET['eventID']); ?>" />
              <input type="submit" name="searchbutton" id="searchbutton" value="Search" />
              &nbsp;&nbsp;&nbsp;<img src="../../../documents/images/document-application--vnd.ms-excel.png" alt="Excel" width="16" height="16" style="vertical-align:
middle;" /> Download as spreadsheet: <a href="index.php?eventID=<?php echo intval($_GET['eventID']); ?>&amp;search=<?php echo isset($_GET['search']) ? urlencode(trim($_GET['search'])) : ""; ?>&amp;csv=true;">All</a> | <a href="index.php?eventID=<?php echo intval($_GET['eventID']); ?>&amp;search=<?php echo isset($_GET['search']) ? urlencode(trim($_GET['search'])) : ""; ?>&amp;csv=true&amp;teamcolumn=true;">Main only</a>
            </fieldset>
    </form>
     <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
    <div id="TabbedPanels1" class="TabbedPanels">
      <ul class="TabbedPanelsTabGroup">
        <li class="TabbedPanelsTab" tabindex="0">Sign-ups</li>
        <li class="TabbedPanelsTab" tabindex="0">Options</li>
        <li class="TabbedPanelsTab" tabindex="0">Questions</li>
        <li class="TabbedPanelsTab" tabindex="0">Start times</li>
        <li class="TabbedPanelsTab" tabindex="0">Email</li>
        <li class="TabbedPanelsTab" tabindex="0">Payment</li>
        <li class="TabbedPanelsTab" tabindex="0">RSVP</li>
</ul>
      <div class="TabbedPanelsContentGroup">
        <div class="TabbedPanelsContent"><fieldset>
        <p>Online sign-ups/booking for this event: 
          <label><input type="radio" name="registration"  value="0"  <?php if ($row_rsThisEvent['registration']==0) {echo "checked=\"checked\"";} ?>  onClick="toggleRegistration(this.value)"  /> Off</label>&nbsp;&nbsp;&nbsp;        
        
        <label><input type="radio" name="registration"  value="1"  <?php if ($row_rsThisEvent['registration']==1) {echo "checked=\"checked\"";} ?> onClick="toggleRegistration(this.value)"    /> On (on-site)</label>&nbsp;&nbsp;&nbsp;        
        
        <label><input type="radio" name="registration"  value="2"  <?php if ($row_rsThisEvent['registration']==2) {echo "checked=\"checked\"";} ?>  onClick="toggleRegistration(this.value)"  /> On (off-site)</label><label id="registrationURL">URL: <input name="registrationURL" type="text" value="<?php echo $row_rsThisEvent['registrationURL']; ?>" size="50" maxlength="255" placeholder="Booking link"></label>       
        
        
        </p>
        <label id="registrationtext">Sign up/booking/tickets text:
                <input name="registrationtext" type="text" id="registrationtext" value="<?php echo $row_rsThisEvent['registrationtext']; ?>" size="100" maxlength="255" placeholder="Enter text for registration link"></label>
           </fieldset>
          <?php if ($totalRows_rsRegistrants == 0) { // Show if recordset empty ?>
          <p>There are currently no registrations for this event.</p>
          <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsRegistrants > 0) { // Show if recordset not empty ?>
          <p>Registrants <?php echo ($startRow_rsRegistrants + 1) ?> to <?php echo min($startRow_rsRegistrants + $maxRows_rsRegistrants, $totalRows_rsRegistrants) ?> of <?php echo $totalRows_rsRegistrants ?></p>
          <table border="0" cellpadding="0" cellspacing="0" class="listTable">
            <tr>
              <th>&nbsp;</th>
              <th>Signed up</th>
              <th>No.</th>
              <th>Name</th>
              <th>DOB</th>
              <th class="columnTeamname">Team</th>
              <th class="columnTshirt text-nowrap">T-shirt</th>
              <th class="columnPayment">Payment</th>
              <th>View</th>
            </tr>
            <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsRegistrants['statusID']; ?>">&nbsp;</td>
              <td><?php echo date('d M Y',strtotime($row_rsRegistrants['createddatetime'])); ?></td>
              <td><?php echo $row_rsRegistrants['registrationnumber']; ?></td>
              <td><?php echo $row_rsRegistrants['firstname']; ?> <?php echo $row_rsRegistrants['surname']; ?></td>
              <td><?php echo isset($row_rsRegistrants['dob']) ? date('d/m/Y', strtotime($row_rsRegistrants['dob'])) : "&nbsp;"; ?></td>
              <td class="columnTeamname"><?php echo $row_rsRegistrants['registrationteamname']; ?></td>
              <td class="columnTshirt"><?php switch($row_rsRegistrants['registrationtshirt']) {
					  case 1 : echo "XS"; break;
					  case 2 : echo "S"; break;
					  case 3 : echo "M"; break;
					  case 4 : echo "L"; break;
					  case 5 : echo "XL"; break;
					  default : echo "-"; 
				  }; ?></td>
              <td class="columnPayment"><?php $paylink = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://" ;
			$paylink .= $_SERVER['HTTP_HOST']."/calendar/registration/payment.php?eventID=" . intval($_GET['eventID']) . "&registrationID=".$row_rsRegistrants['ID'];echo isset($row_rsRegistrants['mc_gross']) ? "&pound;".number_format($row_rsRegistrants['mc_gross'],2,".",",") : "<a href=\"".$paylink."\">Pay link</a>"; ?></td>
              <td><a href="registrant.php?registrationID=<?php echo $row_rsRegistrants['ID']; ?>&amp;eventID=<?php echo $row_rsThisEvent['ID']; ?>" class="link_view">View</a></td>
            </tr>
            <?php } while ($row_rsRegistrants = mysql_fetch_assoc($rsRegistrants)); ?>
          </table>
          <?php } // Show if recordset not empty ?>
          <table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsRegistrants > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsRegistrants=%d%s", $currentPage, 0, $queryString_rsRegistrants); ?>">First</a>
                <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsRegistrants > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsRegistrants=%d%s", $currentPage, max(0, $pageNum_rsRegistrants - 1), $queryString_rsRegistrants); ?>">Previous</a>
                <?php } // Show if not first page ?></td>
              <td><?php if ($pageNum_rsRegistrants < $totalPages_rsRegistrants) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsRegistrants=%d%s", $currentPage, min($totalPages_rsRegistrants, $pageNum_rsRegistrants + 1), $queryString_rsRegistrants); ?>">Next</a>
                <?php } // Show if not last page ?></td>
              <td><?php if ($pageNum_rsRegistrants < $totalPages_rsRegistrants) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsRegistrants=%d%s", $currentPage, $totalPages_rsRegistrants, $queryString_rsRegistrants); ?>">Last</a>
                <?php } // Show if not last page ?></td>
            </tr>
          </table>
        </div>
        <div class="TabbedPanelsContent">
          <table class="form-table"> 
              <td class="text-nowrap text-right"><label for="registrationmulti">Allow multiple registrations per individual:</label></td>
              <td>
              
                  <label>
                    <input <?php if (!(strcmp($row_rsThisEvent['registrationmulti'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationmulti" value="0" id="RadioGroup1_3">
                    No</label>
                 &nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsThisEvent['registrationmulti'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationmulti" value="1" id="RadioGroup1_4">
                    Yes</label>
                &nbsp;&nbsp;
                  <label>
                    <input <?php if (!(strcmp($row_rsThisEvent['registrationmulti'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationmulti" value="2" id="RadioGroup1_5">
                    Allow same email but not name</label>
                </td>
            </tr> <tr>
              <td class="text-nowrap text-right"> <label for="registrationsequential">Keep numbers sequencial:</label></td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationsequential'],1))) {echo "checked=\"checked\"";} ?> name="registrationsequential" type="checkbox" id="registrationsequential" value="1" />
               </td>
            </tr> <tr>
              <td class="text-nowrap text-right">Add registrants to user group:</td>
              <td><select name="usergroupID" id="usergroupID">
                <option value="" <?php if (!(strcmp("", $row_rsThisEvent['registrationgroupID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                <?php
do {  
?>
                <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsThisEvent['registrationgroupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
              </select>
                <a href="../../../members/admin/groups/index.php" class="link_edit icon_with_text">Edit groups</a></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Registration start:</td>
              <td><input type="hidden" name="registrationstart"  id="registrationstart" value="<?php $setvalue =  htmlentities($row_rsThisEvent['registrationstart'], ENT_COMPAT, 'UTF-8');echo $setvalue; $inputname = "registrationstart"; $time = true;  ?>" />
                <?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Registration end:</td>
              <td><input type="hidden" name="registrationend" id="registrationend" value="<?php $setvalue =  htmlentities($row_rsThisEvent['registrationend'], ENT_COMPAT, 'UTF-8'); echo $setvalue; $inputname = "registrationend"; $time = true; ?>"  />
                <?php require('../../../core/includes/datetimeinput.inc.php'); ?></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Maximum numbers:</td>
              <td><input name="registrationmax" type="text" value="<?php echo htmlentities($row_rsThisEvent['registrationmax'], ENT_COMPAT, 'UTF-8'); ?>" size="5" maxlength="5" />
                (enter zero if no maximum)</td>
            </tr> <tr>
              <td class="text-nowrap text-right">Group registration:</td>
              <td><input name="registrationteam" id="registrationteam" type="text" value="<?php echo isset($row_rsThisEvent['registrationteam']) ? htmlentities($row_rsThisEvent['registrationteam'], ENT_COMPAT, 'UTF-8') : "0"; ?>" size="5" maxlength="2" />
                (number of extra persons allowed on one registration, if any)</td>
            </tr> <tr>
              <td class="text-nowrap text-right">Automatically accept:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationautoaccept'],1))) {echo "checked=\"checked\"";} ?> name="registrationautoaccept" type="checkbox" id="registrationautoaccept" value="1" />
              </label></td>
            </tr>
            <tr class="survey">
              <td class="text-nowrap text-right"><label for="surveyID">Link to survey:</label></td>
              <td>
                <select name="surveyID" id="surveyID">
                  <option value="" <?php if (!(strcmp("", $row_rsThisEvent['surveyID']))) {echo "selected=\"selected\"";} ?>><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsSurveys['ID']?>"<?php if (!(strcmp($row_rsSurveys['ID'], $row_rsThisEvent['surveyID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSurveys['surveyname']?></option>
                  <?php
} while ($row_rsSurveys = mysql_fetch_assoc($rsSurveys));
  $rows = mysql_num_rows($rsSurveys);
  if($rows > 0) {
      mysql_data_seek($rsSurveys, 0);
	  $row_rsSurveys = mysql_fetch_assoc($rsSurveys);
  }
?>
                </select></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Confirmation URL:</td>
              <td><input name="registrationconfirmationURL" type="text"  id="registrationconfirmationURL" value="<?php echo $row_rsThisEvent['registrationconfirmationURL']; ?>" size="50" maxlength="255" /></td>
            </tr>
          </table>
          <input type="hidden" name="ID" value="<?php echo $row_rsThisEvent['ID']; ?>" />
          <input name="eventgroupID" type="hidden" value="<?php echo $row_rsThisEvent['eventgroupID']; ?>" />
          <input name="modifiedbyID" type="hidden" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
          <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
          <input type="hidden" name="MM_update" value="form1" />
        </div>
        <div class="TabbedPanelsContent">
          <table border="0" cellpadding="0" cellspacing="0" class="form-table"> 
            <tr>
              <td class="text-nowrap text-right">&nbsp;</td>
              <td>&nbsp;</td>
              <td>Mandatory<br>
(if asked)</td>
            </tr>
            <tr>
              <td class="text-nowrap text-right">Ask address:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationaskaddress'],1))) {echo "checked=\"checked\"";} ?> name="registrationaskaddress" type="checkbox" id="registrationaskaddress" value="1" /></td>
              <td valign="top">Yes</td>
            </tr>
            
            <tr>
              <td class="text-nowrap text-right">Ask telephone:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationasktelephone'],1))) {echo "checked=\"checked\"";} ?> name="registrationasktelephone" type="checkbox" id="registrationasktelephone" value="1" /></td>
              <td valign="top">Yes</td>
            </tr>
            
            <tr>
              <td class="text-nowrap text-right">Ask date of birth:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationdob'],1))) {echo "checked=\"checked\"";} ?> name="registrationdob" type="checkbox" id="registrationdob" value="1" /></td>
              <td valign="top">Yes</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask team name:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationteamname'],1))) {echo "checked=\"checked\"";} ?> name="registrationteamname" type="checkbox" id="registrationteamname" value="1" /></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask medical:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationmedical'],1))) {echo "checked=\"checked\"";} ?> name="registrationmedical" type="checkbox" id="registrationmedical" value="1" /></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask about dietry requirements:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationdietryreq'],1))) {echo "checked=\"checked\"";} ?> name="registrationdietryreq" type="checkbox" id="registrationdietryreq" value="1" /></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask if taken part before:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['takenpartbefore'],1))) {echo "checked=\"checked\"";} ?> name="takenpartbefore" type="checkbox" id="takenpartbefore" value="1" /></td>
              <td valign="top">Yes</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask t-shirt size:</td>
              <td><label>
                <input name="registrationtshirt" type="checkbox" id="registrationtshirt" value="1" <?php if (!(strcmp($row_rsThisEvent['registrationtshirt'],1))) {echo "checked=\"checked\"";} ?> />
              </label></td>
              <td valign="top">Yes</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask wheelchair  type:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationwheelchair'],1))) {echo "checked=\"checked\"";} ?> name="registrationwheelchair" type="checkbox" id="registrationwheelchair" value="1" />
              </label></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask  expected time (for races, etc):</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationtime'],1))) {echo "checked=\"checked\"";} ?> name="registrationtime" type="checkbox" id="registrationtime" value="1" />
              </label></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask how heard about event:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationdiscovered'],1))) {echo "checked=\"checked\"";} ?> name="registrationdiscovered" type="checkbox" id="registrationdiscovered" value="1" />
              </label></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask about  other special requirements:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationspecialreq'],1))) {echo "checked=\"checked\"";} ?> name="registrationspecialreq" type="checkbox" id="registrationspecialreq" value="1" />
              </label></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right"><label for="registrationaskjobtitle">Ask job title:</label></td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationaskjobtitle'],1))) {echo "checked=\"checked\"";} ?> name="registrationaskjobtitle" type="checkbox" id="registrationaskjobtitle" value="1" />
                </td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right"><label for="registrationaskcompany">Ask company name:</label></td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationaskcompany'],1))) {echo "checked=\"checked\"";} ?> name="registrationaskcompany" type="checkbox" id="registrationaskcompany" value="1" />
                </td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Ask for extra info:</td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationinfo'],1))) {echo "checked=\"checked\"";} ?> name="registrationinfo" type="checkbox" id="registrationinfo" value="1" /></td>
              <td valign="top">&nbsp;</td>
            </tr> 
            <tr>
              <td class="text-nowrap  top text-right"><label for="registrationextraquestion">Extra  question 1:<br />
                (Optional, e.g. Do you have any other information we should know?<br />
                What is your reason for taking part? <br />
                Are you bringing pets, buggies, etc)</label></td>
              <td><textarea name="registrationextraquestion" id="registrationextraquestion" cols="45" rows="5" class=""><?php echo isset($row_rsThisEvent['registrationextraquestion']) ? $row_rsThisEvent['registrationextraquestion'] : ""; ?></textarea></td>
              <td valign="top"><input <?php if (!(strcmp($row_rsThisEvent['registrationextracompulsary'],1))) {echo "checked=\"checked\"";} ?> name="registrationextracompulsary" type="checkbox" value="1">
                </td>
            </tr>
            <tr>
              <td align="right" valign="top">Extra  question 2:</td>
              <td><textarea name="registrationextraquestion2" id="registrationextraquestion2" cols="45" rows="5" class=""><?php echo isset($row_rsThisEvent['registrationextraquestion2']) ? $row_rsThisEvent['registrationextraquestion2'] : ""; ?></textarea></td>
              <td valign="top"><input <?php if (!(strcmp($row_rsThisEvent['registrationextracompulsary2'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="registrationextracompulsary2"  value="1"></td>
            </tr>
            <tr>
              <td align="right" valign="top">Extra  question 3:</td>
              <td><textarea name="registrationextraquestion3" id="registrationextraquestion3" cols="45" rows="5" class=""><?php echo isset($row_rsThisEvent['registrationextraquestion3']) ? $row_rsThisEvent['registrationextraquestion3'] : ""; ?></textarea></td>
              <td valign="top"><input <?php if (!(strcmp($row_rsThisEvent['registrationextracompulsary3'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="registrationextracompulsary3"  value="1"></td>
            </tr>
            <tr>
              <td align="right" valign="top"><p>Agree to marketing text:<br>
                (Yes/no buttons)
              </p></td>
              <td><input name="registrationmarketingtextshow" type="checkbox" value="1" <?php if (!(strcmp($row_rsThisEvent['registrationmarketingtextshow'],1))) {echo "checked=\"checked\"";} ?>><textarea name="registrationmarketingtext" id="registrationmarketingtext" cols="45" rows="5" class=""><?php echo isset($row_rsThisEvent['registrationmarketingtext']) ? $row_rsThisEvent['registrationmarketingtext'] : "Details in this application form may be used by the organisers or its agents for the purposes of marketing and PR - this might include sending out mailings on future events  and related news. Are you happy for your details to be used this way?"; ?></textarea></td>
              <td valign="top">Yes</td>
            </tr>
            <tr>
              <td align="right" valign="top">Agree to legal  terms text:<br>
                (checkbox - required to be checked before submitting)</td>
              <td><input name="registrationtermstextshow" type="checkbox" value="1" <?php if (!(strcmp($row_rsThisEvent['registrationtermstextshow'],1))) {echo "checked=\"checked\"";} ?>><textarea name="registrationtermstext" id="registrationtermstext" cols="45" rows="5" class=""><?php echo isset($row_rsThisEvent['registrationtermstext']) ? $row_rsThisEvent['registrationtermstext'] : "I declare that the information on this form is complete and correct.  I understand and agree that I participate in this event entirely at my own risk, and that I must rely on my own ability in dealing with any hazards on the route.  I accept that I must follow the instructions of marshals, officials and signs at all times. The organisers will not participate in or endorse any assessment or declaration of fitness by any participant.  It is the participant&rsquo;s responsibility to ensure that any assistance required to compensate for or overcome any physical impediment is in place prior to entering.  I agree that no liability whatsoever shall attach to the promoting organisation or to event sponsors, in respect of any injury, loss or damage, suffered by me, or by reason of the race, however caused.
         By taking part in this event, I understand that photographs will be taken and may be used by the organisers or its agents, and by the sponsors of the event for the purposes of marketing and PR.  I also agree that, as the main applicant, I am 16 or over and am granting any minors in my team permission to enter."; ?></textarea></td>
              <td valign="top">Yes</td>
            </tr>
          </table>
        </div>
        <div class="TabbedPanelsContent">
          <p>You can add sevaral choices of staggered  start times below.</p>
          <p>
            <label>
              <input <?php if (!(strcmp($row_rsThisEvent['registrationchoosestarttime'],1))) {echo "checked=\"checked\"";} ?> name="registrationchoosestarttime" type="checkbox" id="registrationchoosestarttime" value="1" />
              Allow registrant to choose their own start time</label>
          </p>
          <?php require_once('../ajax/regstarttimes.inc.php'); ?>
        </div>
        <div class="TabbedPanelsContent">
          <table border="0" cellpadding="2" cellspacing="0" class="form-table"> <tr>
              <td class="text-nowrap text-right">Email admin when:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationalertemail'],1))) {echo "checked=\"checked\"";} ?> name="registrationalertemail" type="checkbox" id="registrationalertemail" value="1" />
                someone signs up</label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsThisEvent['registrationfullemail'],1))) {echo "checked=\"checked\"";} ?> name="registrationfullemail" type="checkbox" id="registrationfullemail" value="1" />
                  registration is full</label></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Admin email:</td>
              <td><input name="registrationadminemail" type="text"  id="registrationadminemail" value="<?php echo $row_rsThisEvent['registrationadminemail']; ?>" size="50" maxlength="50" /></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Send auto email to registrant:</td>
              <td><label>
                <input <?php if (!(strcmp($row_rsThisEvent['registrationemail'],1))) {echo "checked=\"checked\"";} ?> name="registrationemail" type="checkbox" id="registrationemail" value="1" />
              </label>
              &nbsp;&nbsp;&nbsp;
              <label>Include registration numbers:
                <input <?php if (!(strcmp($row_rsThisEvent['registrationemailnumbers'],1))) {echo "checked=\"checked\"";} ?> name="registrationemailnumbers" type="checkbox" id="registrationemailnumbers" value="1" />
              </label>
              
              
              
              
              
              
              
              </td>
            </tr>
            <tr>
              <td class="text-nowrap text-right"><label for="registrationemailtemplateID">Use email message template:</label></td>
              <td>
                <select name="registrationemailtemplateID" id="registrationemailtemplateID">
                  <option value="0" <?php if (!(strcmp(0, $row_rsThisEvent['registrationemailtemplateID']))) {echo "selected=\"selected\"";} ?>>Use message below (or choose template...)</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsMailTemplates['ID']?>"<?php if (!(strcmp($row_rsMailTemplates['ID'], $row_rsThisEvent['registrationemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMailTemplates['templatename']?></option>
                  <?php
} while ($row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates));
  $rows = mysql_num_rows($rsMailTemplates);
  if($rows > 0) {
      mysql_data_seek($rsMailTemplates, 0);
	  $row_rsMailTemplates = mysql_fetch_assoc($rsMailTemplates);
  }
?>
                </select></td>
            </tr> 
            <tr>
              <td class="text-nowrap text-right">Message contents:</td>
              <td><p>Dear {firstname},</p>
                <p>Thank you, we have received your event sign up for:</p>
                <p>{event name, date and link} </p>
                <p><span id="sprytextarea1">
                  <label>
                    <textarea name="registrationemailmessage" id="registrationemailmessage" cols="50" rows="10" class=""><?php echo $row_rsThisEvent['registrationemailmessage']; ?></textarea>
                  </label>
                </span></p></td>
            </tr>
          </table>
          <h2>Available Merges</h2>
          <p>{firstname} - first name of registrant</p>
          <p>{eventname} - Name of event</p>
          <p>{eventdate} - date of event</p>
          <p>{eventtime} - time of event</p>
          <p>{eventlink} - link to event page</p>
        </div>
        <div class="TabbedPanelsContent">
          <table border="0" cellpadding="2" cellspacing="0" class="form-table">
            <tr>
              <td align="right">Payment required:</td>
              <td><p>
                <label>
                  <input <?php if (!isset($row_rsThisEvent['registrationpayment']) || !(strcmp($row_rsThisEvent['registrationpayment'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationpayment" value="0" id="RadioGroup1_0" />
                  None</label>
                &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsThisEvent['registrationpayment'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationpayment" value="1" id="RadioGroup1_1" />
                  per person</label>
               &nbsp;&nbsp;&nbsp;
                <label>
                  <input <?php if (!(strcmp($row_rsThisEvent['registrationpayment'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="registrationpayment" value="2" id="RadioGroup1_2" />
                  per registration/group</label>
               &nbsp;&nbsp;&nbsp;
              </p></td>
            </tr> <tr>
              <td class="text-nowrap text-right"><label for="registrationinvoice">Allow invoice payment:</label></td>
              <td><input <?php if (!(strcmp($row_rsThisEvent['registrationinvoice'],1))) {echo "checked=\"checked\"";} ?> name="registrationinvoice" type="checkbox" id="registrationinvoice" value="1" />
                </td>
            </tr> <tr>
              <td class="text-nowrap text-right">Cost per entry:</td>
              <td><input name="registrationcost" type="text" id="registrationcost" value="<?php echo $row_rsThisEvent['registrationcost']; ?>" size="5" maxlength="5" />
                GBP</td>
            </tr> <tr>
              <td class="text-nowrap text-right">Concession:</td>
              <td><input name="registrationconcession" type="text" id="registrationconcession" value="<?php echo $row_rsThisEvent['registrationconcession']; ?>" size="5" maxlength="5" />
                GBP (under 16 Note: you must ask for date of birth) 
                  <label>
                    <input <?php if (!(strcmp($row_rsThisEvent['over65'],1))) {echo "checked=\"checked\"";} ?> name="over65" type="checkbox" id="over65" value="1" />
                Include over 65s</label></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Group discount*:</td>
              <td><input name="teamdiscountamount" type="text" id="teamdiscountamount" value="<?php echo $row_rsThisEvent['teamdiscountamount']; ?>" size="3" maxlength="3" />
                <select name="teamdiscountamountype" id="teamdiscountamountype">
                  <option value="1" <?php if (!(strcmp(1, $row_rsThisEvent['teamdiscounttype']))) {echo "selected=\"selected\"";} ?>>%</option>
                  <option value="2" <?php if (!(strcmp(2, $row_rsThisEvent['teamdiscounttype']))) {echo "selected=\"selected\"";} ?>>GBP</option>
                </select> for
                <input name="teamdiscountnumber" type="text" id="teamdiscountnumber" value="<?php echo $row_rsThisEvent['teamdiscountnumber']; ?>" size="3" maxlength="3" />
                entries and over.</td>
            </tr> <tr>
              <td class="text-nowrap text-right">Family discount*:</td>
              <td>
                <input name="familydiscountamount" type="text" id="familydiscountamount" size="3" maxlength="3"  value="<?php echo $row_rsThisEvent['familydiscountamount']; ?>" />
                <select name="familydiscountamounttype" id="familydiscountamounttype">
                  <option value="1" <?php if (!(strcmp(1, $row_rsThisEvent['familydiscountamounttype']))) {echo "selected=\"selected\"";} ?>>%</option>
                  <option value="2" <?php if (!(strcmp(2, $row_rsThisEvent['familydiscountamounttype']))) {echo "selected=\"selected\"";} ?>>GBP</option>
                </select>
for
<label>
                <input name="familydiscountadults" type="text" id="familydiscountadults" size="3" maxlength="3"  value="<?php echo $row_rsThisEvent['familydiscountadults']; ?>" />adults, and</label><label>
                <input name="familydiscountchildren" type="text" id="familydiscountchildren" size="3" maxlength="3"  value="<?php echo $row_rsThisEvent['familydiscountchildren']; ?>" />
                children (Note: you must ask for date of birth)</label></td>
            </tr> <tr>
              <td class="text-nowrap text-right">Member discount*:</td>
              <td><input name="memberdiscountamount" type="text" id="memberdiscountamount" size="3" maxlength="3"  value="<?php echo $row_rsThisEvent['memberdiscountamount']; ?>" />
                <select name="memberdiscountamounttype" id="memberdiscountamounttype">
                  <option value="1" <?php if (!(strcmp(1, $row_rsThisEvent['memberdiscounttype']))) {echo "selected=\"selected\"";} ?>>%</option>
                  <option value="2" <?php if (!(strcmp(2, $row_rsThisEvent['memberdiscounttype']))) {echo "selected=\"selected\"";} ?>>GBP</option>
                </select> 
                for 
                <select name="memberdiscountrank" id="memberdiscountrank">
                  <option value="0" <?php if (!(strcmp(0, $row_rsThisEvent['memberdiscountrank']))) {echo "selected=\"selected\"";} ?>>No one</option>
                  <?php
do {  
?>
<option value="<?php echo $row_rsUsertypes['ID']?>"<?php if (!(strcmp($row_rsUsertypes['ID'], $row_rsThisEvent['memberdiscountrank']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUsertypes['name']?></option>
                  <?php
} while ($row_rsUsertypes = mysql_fetch_assoc($rsUsertypes));
  $rows = mysql_num_rows($rsUsertypes);
  if($rows > 0) {
      mysql_data_seek($rsUsertypes, 0);
	  $row_rsUsertypes = mysql_fetch_assoc($rsUsertypes);
  }
?>
                </select>
                AND in group:
                <select name="memberdiscountgroup" id="memberdiscountgroup">
                  <option value="0" <?php if (!(strcmp(0, $row_rsThisEvent['memberdiscountgroup']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsThisEvent['memberdiscountgroup']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
<?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
                </select></td>
            </tr> <tr>
              <td colspan="2" align="left" valign="top" class="text-nowrap"><label for="paymentinstructions">Payment instructions  (optional):
              
              </label>&nbsp;</td>
              </tr> <tr>
              <td colspan="2" align="left" valign="top" class="text-nowrap">
                <textarea name="paymentinstructions" id="paymentinstructions" cols="45" rows="5" class="tinymce"><?php echo $row_rsThisEvent['paymentinstructions']; ?></textarea></td>
              </tr>
          </table>
          <p>*Note: The discounts are mutually exclusive. Only one can be applied per registation.</p>
        </div>
<div class="TabbedPanelsContent">
          <p>
            <label>
              <input <?php if (!(strcmp($row_rsThisEvent['rsvp'],1))) {echo "checked=\"checked\"";} ?> name="rsvp" type="checkbox" id="rsvp" value="1" onClick="if(this.checked &amp;&amp; document.getElementById('registration').checked) { alert('You cannot use RSVP at the same time as registrations. Uncheck allow registration in Options tab to allow RSVP.'); return false; }" />
              Allow invitees to RSVP online</label>
            (max. 1000)
            by reply date:
            <input type="hidden" name="rsvpdatetime" id="rsvpdatetime" value="<?php $setvalue = $row_rsThisEvent['rsvpdatetime']; echo $setvalue; $inputname = "rsvpdatetime"; $time = true;  ?>" />
            <?php require('../../../core/includes/datetimeinput.inc.php'); ?>
          </p>
          <?php if ($totalRows_rsAttendees == 0) { // Show if recordset empty ?>
          <p>There are currently no RSVPs</p>
          <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsAttendees > 0) { // Show if recordset not empty ?>
          <p><img src="../../../core/images/icons/green-light.png" alt="Yes" width="16" height="16" style="vertical-align:
middle;" />YES (<?php echo $row_rsCountYes['countyes']; ?>)&nbsp;&nbsp;&nbsp;<img src="../../../core/images/icons/red-light.png" alt="No" width="16" height="16" style="vertical-align:
middle;" />NO (<?php echo $row_rsCountNo['countno']; ?>)&nbsp;&nbsp;&nbsp;<img src="../../../core/images/icons/amber-light.png" alt="Maybe" width="16" height="16" style="vertical-align:
middle;" />MAYBE (<?php echo $totalRows_rsAttendees-$row_rsCountYes['countyes']-$row_rsCountNo['countno']; ?>)</p>
          <table border="0" cellpadding="0" cellspacing="0" class="listTable">
            <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsAttendees['statusID']; ?>">&nbsp;</td>
              <td><?php echo date('d M Y', strtotime($row_rsAttendees['createddatetime'])); ?></td>
              <td><?php echo $row_rsAttendees['firstname']; ?></td>
              <td><?php echo $row_rsAttendees['surname']; ?></td>
            </tr>
            <?php } while ($row_rsAttendees = mysql_fetch_assoc($rsAttendees)); ?>
          </table>
          <?php } // Show if recordset not empty ?>
        </div>
</div>
    </div><div><button type="submit" class="btn btn-primary">Save changes</button></div></form>
<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {hint:"Add any further text you may wish to add", isRequired:false});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Reg number or surname", isRequired:false});
//-->
</script></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisEvent);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegistrants);

mysql_free_result($rsGroups);

mysql_free_result($rsProductPrefs);

mysql_free_result($rsAttendees);

mysql_free_result($rsCountYes);

mysql_free_result($rsCountNo);

mysql_free_result($rsUsertypes);

mysql_free_result($rsSurveys);

mysql_free_result($rsMailTemplates);
?>
