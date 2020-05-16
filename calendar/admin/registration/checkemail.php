<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if (!function_exists("GetSQLValueString")) {
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

function emailExists($email) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	$regionID = ($regionID=="") ? 1 : intval($regionID);
	$theValue = get_magic_quotes_gpc() ? stripslashes($_GET['email']) : $_GET['email'];
	$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$query = "SELECT ID, firstname, surname, email FROM users WHERE email = ".GetSQLValueString($theValue,"text")." AND email!='' AND regionID = ".$regionID." LIMIT 1";	
	$result = mysql_query($query, $aquiescedb) or die(mysql_error());$row = mysql_fetch_assoc($result);
	return $row;
}
$row = emailExists($_GET['email']);
if(is_array($row)) { ?>
<div id="alreadyRegistered">
<img src="/core/images/warning.gif" alt="Alert!" width="16" height="16" style="vertical-align:
middle;"><br /><br />
  <span style="color:#990000">This email is already used on the system by <?php echo $row['firstname']." ".$row['surname']; ?>.</span>
<br /><br />
An email can only be used once on the system. If this is the same person, <a href="/calendar/admin/registration/add_registrant.php?userID=<?php echo $row['ID']; ?>&eventID=<?php echo intval($_GET['eventID']); ?>">click here</a>.
</div>
<?php } exit; ?>