<?php require_once('../../../Connections/aquiescedb.php'); ?><?php
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
$query_rsEvents = "SELECT * FROM event WHERE eventgroupID > 1";
$rsEvents = mysql_query($query_rsEvents, $aquiescedb) or die(mysql_error());
$row_rsEvents = mysql_fetch_assoc($rsEvents);
$totalRows_rsEvents = mysql_num_rows($rsEvents);


function addEvent($eventgroupID, $eventStart, $eventEnd, $locationID = 0, $firsteventID = 0, $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$insert = "INSERT INTO event (eventgroupID, startdatetime, enddatetime, statusID, eventlocationID, firsteventID, createdbyID, createddatetime) VALUES (".GetSQLValueString($eventgroupID,"int").",".GetSQLValueString($eventStart,"date").",".GetSQLValueString($eventEnd,"date").",1,".GetSQLValueString($locationID,"int").",".intval($firsteventID).",".GetSQLValueString($createdbyID,"int").",NOW())";
	mysql_query($insert, $aquiescedb) or die(mysql_error());
	echo $insert."<br>";
	return mysql_insert_id();
}	



?>
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Events to Calendar</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Events to Calendar</h1>
<p>&nbsp;</p>
<table border="0" cellpadding="0" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>eventtitle</td>
    <td>eventlocationID</td>
    <td>eventforID</td>
    <td>eventcategoryID</td>
    <td>eventdepartmentID</td>
    <td>eventdetails</td>
    <td>startdatetime</td>
    <td>enddatetime</td>
    <td>recurringinterval</td>
    <td>recurringweekly</td>
    <td>recurringend</td>
    <td>createdbyID</td>
    <td>createddatetime</td>
    <td>modifiedbyID</td>
    <td>modifieddatetime</td>
    <td>statusID</td>
    <td>bookable</td>
    <td>bookingmax</td>
    <td>bookingnotice</td>
    <td>imageURL</td>
    <td>eventgroupID</td>
    </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsEvents['ID']; ?></td>
      <td><?php echo $row_rsEvents['eventtitle']; ?></td>
      <td><?php echo $row_rsEvents['eventlocationID']; ?></td>
      <td><?php echo $row_rsEvents['eventforID']; ?></td>
      <td><?php echo $row_rsEvents['eventcategoryID']; ?></td>
      <td><?php echo $row_rsEvents['eventdepartmentID']; ?></td>
      <td><?php echo $row_rsEvents['eventdetails']; ?></td>
      <td><?php echo $row_rsEvents['startdatetime']; ?></td>
      <td><?php echo $row_rsEvents['startdatetime']; ?></td>
      <td><?php echo $row_rsEvents['recurringinterval']; ?></td>
      <td><?php echo $row_rsEvents['recurringweekly']; ?></td>
      <td><?php echo $row_rsEvents['recurringend']; ?></td>
      <td><?php echo $row_rsEvents['createdbyID']; ?></td>
      <td><?php echo $row_rsEvents['createddatetime']; ?></td>
      <td><?php echo $row_rsEvents['modifiedbyID']; ?></td>
      <td><?php echo $row_rsEvents['modifieddatetime']; ?></td>
      <td><?php echo $row_rsEvents['statusID']; ?></td>
      <td><?php echo $row_rsEvents['bookable']; ?></td>
      <td><?php echo $row_rsEvents['bookingmax']; ?></td>
      <td><?php echo $row_rsEvents['bookingnotice']; ?></td>
      <td><?php echo $row_rsEvents['imageURL']; ?></td>
      <td><?php echo $row_rsEvents['eventgroupID']; ?></td>
    </tr>
    <tr>
      <td colspan="22"><?php  
	  if(date('Y-m-d',strtotime($row_rsEvents['enddatetime'])) > date('Y-m-d',strtotime($row_rsEvents['startdatetime']))) {
		  $row_rsEvents['enddatetime'] == $row_rsEvents['startdatetime'];
	  }
	  $insert = "INSERT INTO eventgroup (eventtitle,eventdetails,categoryID,locationID,imageURL,createdbyID,createddatetime,statusID) VALUES (".
																																									  GetSQLValueString($row_rsEvents['eventtitle'],"text").",".
																																									  GetSQLValueString($row_rsEvents['eventdetails'],"text").",".
																																									  GetSQLValueString($row_rsEvents['eventcategoryID'],"int").",".
																																									  GetSQLValueString($row_rsEvents['eventlocationID'],"int").",".
																																									  GetSQLValueString($row_rsEvents['imageURL'],"text").",".
																																									  GetSQLValueString($row_rsEvents['createdbyID'],"int").",NOW(),". 
																																									  GetSQLValueString($row_rsEvents['statusID'],"int").")"; 
	  echo $insert."<br>"; 
	  
	 mysql_query($insert, $aquiescedb) or die(mysql_error());
	  $eventgroupID = mysql_insert_id();
	  $update = "UPDATE event SET eventgroupID = ".$eventgroupID." WHERE ID = ".$row_rsEvents['ID'];
	 mysql_query($update, $aquiescedb) or die(mysql_error());
	  if($row_rsEvents['recurringweekly']==1) { // recurs
	$startdatetime = $row_rsEvents['startdatetime'];
	$enddatetime = isset($row_rsEvents['enddatetime']) ? $row_rsEvents['enddatetime'] : $startdatetime;
	$recurringend = $row_rsEvents['recurringend'];
	$recurringinterval = "DAYS";
	$recurringmultiple = 7;
	$count = 0;
	while($startdatetime<=$recurringend) { // loop
		if($count>0) { // first one already inserted
			$eventID = addEvent($eventgroupID,$startdatetime,$enddatetime,$row_rsEvents['eventlocationID'],$row_rsEvents['ID'],$row_rsEvents['createdbyID']);
		}
		$count++;
		$startdatetime=date('Y-m-d H:i:s',strtotime($startdatetime." + ".$recurringmultiple." ".$recurringinterval));
		$enddatetime=date('Y-m-d H:i:s',strtotime($enddatetime." + ".$recurringmultiple." ".$recurringinterval));
	} // end while
}// end add event
	  
	   
	  ?></td>
    </tr>
      
    <?php } while ($row_rsEvents = mysql_fetch_assoc($rsEvents)); ?>
</table>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsEvents);
?>
