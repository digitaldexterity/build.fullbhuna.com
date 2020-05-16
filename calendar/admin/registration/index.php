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
?>
<?php


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

if(isset($_GET['deleteID']) && intval($_GET['deleteID'])>0) {
	$delete = "DELETE FROM eventregistration WHERE ID = ".intval($_GET['deleteID']);
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	mysql_query($delete, $aquiescedb) or die(mysql_error());
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE event SET registration=%s, registrationmulti=%s, registrationstart=%s, registrationend=%s, registrationmax=%s, registrationdob=%s, registrationteam=%s, registrationteamname=%s, registrationmedical=%s, registrationinfo=%s, registrationpayment=%s, registrationinvoice=%s, registrationcost=%s, registrationconcession=%s, registrationsequential=%s, over65=%s, teamdiscountamount=%s, teamdiscounttype=%s, teamdiscountnumber=%s, memberdiscountamount=%s, memberdiscounttype=%s, memberdiscountrank=%s, memberdiscountgroup=%s, familydiscountamount=%s, familydiscountamounttype=%s, familydiscountadults=%s, familydiscountchildren=%s, paymentinstructions=%s, registrationconfirmationURL=%s, registrationaskjobtitle=%s, registrationaskcompany=%s, registrationtshirt=%s, registrationtime=%s, registrationchoosestarttime=%s, registrationwheelchair=%s, registrationdiscovered=%s, registrationalertemail=%s, registrationfullemail=%s, registrationdietryreq=%s, registrationspecialreq=%s, registrationextraquestion=%s, registrationextraquestion2=%s, registrationextraquestion3=%s, registrationextracompulsary=%s, registrationextracompulsary2=%s, registrationextracompulsary3=%s, registrationadminemail=%s, registrationemail=%s, registrationemailmessage=%s, registrationautoaccept=%s, registrationgroupID=%s, registrationmarketingtext=%s, registrationtermstext=%s, takenpartbefore=%s, modifiedbyID=%s, modifieddatetime=%s, eventgroupID=%s, surveyID=%s, rsvp=%s, rsvpdatetime=%s, registrationtext=%s, registrationURL=%s, registrationaskaddress=%s, registrationasktelephone=%s, registrationemailnumbers=%s WHERE ID=%s",
                       GetSQLValueString($_POST['registration'], "int"),
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
                       GetSQLValueString(isset($_POST['registrationaskcompany']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationtshirt']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationtime']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationchoosestarttime']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationwheelchair']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationalertemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationfullemail']) ? "true" : "", "defined","1","0"),
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
                       GetSQLValueString($_POST['registrationtext'], "text"),
                       GetSQLValueString($_POST['registrationURL'], "text"),
                       GetSQLValueString(isset($_POST['registrationaskaddress']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationasktelephone']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['registrationemailnumbers']) ? "true" : "", "defined","1","0"),
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



$varSearch_rsRegistrants = "%";
if (isset($_GET['search'])) {
  $varSearch_rsRegistrants = $_GET['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegistrants = sprintf("SELECT eventregistration.ID, eventregistration.statusID, eventregistration.registrationnumber, eventregistration.createddatetime, users.firstname, users.surname, users.dob,eventregistration.registrationteamname, eventregistration.registrationtshirt, eventregistration.registrationmarketing,  eventgroup.eventtitle FROM eventregistration LEFT JOIN event ON (eventregistration.eventID = event.ID) LEFT JOIN eventgroup ON (event.eventgroupID = eventgroup.ID) LEFT JOIN users ON (eventregistration.userID = users.ID)   WHERE (users.surname LIKE %s OR eventregistration.registrationnumber = %s) ORDER BY eventregistration.createddatetime DESC", GetSQLValueString($varSearch_rsRegistrants . "%", "text"),GetSQLValueString($varSearch_rsRegistrants, "text"));
$rsRegistrants = mysql_query($query_rsRegistrants, $aquiescedb) or die(mysql_error());
$row_rsRegistrants = mysql_fetch_assoc($rsRegistrants);
$totalRows_rsRegistrants = mysql_num_rows($rsRegistrants);

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

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "All Event Sign Ups"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


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
    <div class="page calendar">
        <h1><i class="glyphicon glyphicon-calendar"></i> All Sign ups</h1>
   
    
     
        <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
         
          <li><a href="../index.php?eventgroupID=<?php echo $row_rsThisEvent['eventgroupID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Events</a></li>
         
           
        </ul></div></nav>
      <form action="index.php" method="get" id="searchform" role="form">
            <fieldset>
              <legend>Filter</legend>
              <span id="sprytextfield1">
              <label>
                <input name="search" type="text"  value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search']), ENT_COMPAT, "UTF-8") : ""; ?>" size="30" maxlength="30" />
              </label>
</span>
             
              <input name="eventID" type="hidden" id="eventID" value="<?php echo intval($_GET['eventID']); ?>" />
              <input type="submit" name="searchbutton" id="searchbutton" value="Search" />
              &nbsp;&nbsp;&nbsp;
            </fieldset>
    </form>
  
          <?php if ($totalRows_rsRegistrants == 0) { // Show if recordset empty ?>
          <p>There are currently no sign ups matching your criteria.</p>
          <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsRegistrants > 0) { // Show if recordset not empty ?>
          <p>Sign ups <?php echo ($startRow_rsRegistrants + 1) ?> to <?php echo min($startRow_rsRegistrants + $maxRows_rsRegistrants, $totalRows_rsRegistrants) ?> of <?php echo $totalRows_rsRegistrants ?></p>
          <table border="0" cellpadding="0" cellspacing="0" class="listTable">
            <tr>
              <th>&nbsp;</th>
              <th>Signed up</th>
              <th>Event/No.</th>
              <th>Name</th>
              <th class="columnTeamname">Team</th>
              <th colspan="2">Actions</th> 
            </tr>
            <?php do { ?>
            <tr>
              <td class="status<?php echo $row_rsRegistrants['statusID']; ?>">&nbsp;</td>
              <td><?php echo date('d M Y',strtotime($row_rsRegistrants['createddatetime'])); ?></td>
              <td><?php echo $row_rsRegistrants['eventtitle']; ?> / <?php echo $row_rsRegistrants['registrationnumber']; ?></td>
              <td><?php echo $row_rsRegistrants['firstname']; ?> <?php echo $row_rsRegistrants['surname']; ?></td>
              <td class="columnTeamname"><?php echo $row_rsRegistrants['registrationteamname']; ?></td>
              <td><a href="registrant.php?registrationID=<?php echo $row_rsRegistrants['ID']; ?>&amp;eventID=<?php echo $row_rsThisEvent['ID']; ?>" class="link_view">View</a></td>
              <td><a href="index.php?deleteID=<?php echo $row_rsRegistrants['ID']; ?>" class="link_delete" onClick="return confirm('Are you sure you want to delete this registration?')"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
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
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsRegistrants);
?>
