<?php if(isset($_GET['ajax'])) { //  ajax call, so we need for include
	require_once('../../../Connections/aquiescedb.php');
} ?>
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

if(isset($_GET['useremail']) && $_GET['useremail'] !="") { // new email posted
	$insert = "INSERT INTO useremail (userID, email, createdbyID, createddatetime) VALUES (".GetSQLValueString($_GET['userID'], "int").",".GetSQLValueString($_GET['useremail'], "text").",".GetSQLValueString($_GET['modifiedbyID'], "int").",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
} // new email posted

if(isset($_GET['deleteemailID'])) { // delete email
$delete = "DELETE FROM useremail WHERE ID = ".GetSQLValueString($_GET['deleteemailID'], "int");
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}// end delete email


$varUserID_rsDefaultEmail = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsDefaultEmail = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDefaultEmail = sprintf("SELECT users.email FROM users WHERE users.ID = %s", GetSQLValueString($varUserID_rsDefaultEmail, "int"));
$rsDefaultEmail = mysql_query($query_rsDefaultEmail, $aquiescedb) or die(mysql_error());
$row_rsDefaultEmail = mysql_fetch_assoc($rsDefaultEmail);
$totalRows_rsDefaultEmail = mysql_num_rows($rsDefaultEmail);

$colname_rsOtherEmails = "-1";
if (isset($_GET['userID'])) {
  $colname_rsOtherEmails = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOtherEmails = sprintf("SELECT ID, email FROM useremail WHERE userID = %s", GetSQLValueString($colname_rsOtherEmails, "int"));
$rsOtherEmails = mysql_query($query_rsOtherEmails, $aquiescedb) or die(mysql_error());
$row_rsOtherEmails = mysql_fetch_assoc($rsOtherEmails);
$totalRows_rsOtherEmails = mysql_num_rows($rsOtherEmails);

if ($totalRows_rsOtherEmails == 0) { // Show if recordset empty ?>
              <p>This user has no alternative email addresses. You can add alternative email addresses in case they lose access to their original.</p>
              <?php } // Show if recordset empty ?>
             
              
              
              <?php if ($totalRows_rsOtherEmails > 0) { // Show if recordset not empty ?>
                <?php do { ?>
                  
                  <div class="clearfix"><div class="fltlft"><?php echo $row_rsOtherEmails['email']; ?>&nbsp;</div><a href="mailto:<?php echo $row_rsOtherEmails['email']; ?>" title="Send email to this address" class="link_email fltlft">Email</a>&nbsp;<a href="javascript:void(0);" onclick="if( confirm('Are you sure you want to delete this email address?')) { getData('/members/admin/ajax/emailaddresses.inc.php?ajax=true&userID=<?php echo intval($_GET['userID']); ?>&deleteemailID=<?php echo $row_rsOtherEmails['ID']; ?>','emailaddresses'); } return false;" class="link_delete fltlft">Delete</a></div>
                  <?php } while ($row_rsOtherEmails = mysql_fetch_assoc($rsOtherEmails)); ?>
                <?php } // Show if recordset not empty ?>
<?php
mysql_free_result($rsDefaultEmail);

mysql_free_result($rsOtherEmails);
?>
