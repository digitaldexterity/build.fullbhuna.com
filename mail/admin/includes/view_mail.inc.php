<?php 
require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php
if(is_readable('../../../local/includes/view_mail.inc.php')) {
	require_once('../../../local/includes/view_mail.inc.php');
} else {
if(!isset($_SESSION['MM_UserGroup']) || $_SESSION['MM_UserGroup']<7) die("Not authorised");
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
$regionID = isset($regionID) ? $regionID : 1;

$maxRows_rsCorrespondence = 100;
$pageNum_rsCorrespondence = 0;
if (isset($_GET['pageNum_rsCorrespondence'])) {
  $pageNum_rsCorrespondence = $_GET['pageNum_rsCorrespondence'];
}
$startRow_rsCorrespondence = $pageNum_rsCorrespondence * $maxRows_rsCorrespondence;

$varSearch_rsCorrespondence = "%";
if (isset($_GET['mailsearch'])) {
  $varSearch_rsCorrespondence = $_GET['mailsearch'];
}
$varRegionID_rsCorrespondence = "1";
if (isset($regionID)) {
  $varRegionID_rsCorrespondence = $regionID;
}
$varRecipientID_rsCorrespondence = "0";
if (isset($_GET['recipientID'])) {
  $varRecipientID_rsCorrespondence = $_GET['recipientID'];
}
$varStartDate_rsCorrespondence = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsCorrespondence = $_GET['startdate'];
}
$varEndDate_rsCorrespondence = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsCorrespondence = $_GET['enddate'];
}
$varFolder_rsCorrespondence = "1";
if (isset($_GET['mailfolderID'])) {
  $varFolder_rsCorrespondence = $_GET['mailfolderID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCorrespondence = sprintf("SELECT correspondence.ID, correspondence.sessionID, correspondence.recipient, correspondence.subject, correspondence.createddatetime, correspondence.mailfolderID, correspondence.autoreply, correspondence.sender, correspondence.reply_using, directory.name AS company, correspondence.sendername, correspondence.sentdatetime FROM correspondence LEFT JOIN directory ON (correspondence.directoryID = directory.ID) WHERE correspondence.regionID = %s AND mailfolderID = %s AND (%s = 0 OR correspondence.recipientID = %s) AND (%s  IS NULL OR %s >= DATE(correspondence.createddatetime)) AND (%s IS NULL OR %s <= DATE(correspondence.createddatetime)) AND  (correspondence.sender LIKE %s OR correspondence.recipient LIKE %s) ORDER BY sentdatetime DESC, createddatetime DESC", GetSQLValueString($varRegionID_rsCorrespondence, "int"),GetSQLValueString($varFolder_rsCorrespondence, "int"),GetSQLValueString($varRecipientID_rsCorrespondence, "int"),GetSQLValueString($varRecipientID_rsCorrespondence, "int"),GetSQLValueString($varEndDate_rsCorrespondence, "date"),GetSQLValueString($varEndDate_rsCorrespondence, "date"),GetSQLValueString($varStartDate_rsCorrespondence, "date"),GetSQLValueString($varStartDate_rsCorrespondence, "date"),GetSQLValueString("%" . $varSearch_rsCorrespondence . "%", "text"),GetSQLValueString("%" . $varSearch_rsCorrespondence . "%", "text"));
$query_limit_rsCorrespondence = sprintf("%s LIMIT %d, %d", $query_rsCorrespondence, $startRow_rsCorrespondence, $maxRows_rsCorrespondence);
$rsCorrespondence = mysql_query($query_limit_rsCorrespondence, $aquiescedb) or die(mysql_error());
$row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence);

if (isset($_GET['totalRows_rsCorrespondence'])) {
  $totalRows_rsCorrespondence = $_GET['totalRows_rsCorrespondence'];
} else {
  $all_rsCorrespondence = mysql_query($query_rsCorrespondence);
  $totalRows_rsCorrespondence = mysql_num_rows($all_rsCorrespondence);
}
$totalPages_rsCorrespondence = ceil($totalRows_rsCorrespondence/$maxRows_rsCorrespondence)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT enableGroupEmail, mailprefs.lastViewed FROM mailprefs";
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);

$queryString_rsCorrespondence = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsCorrespondence") == false && 
        stristr($param, "totalRows_rsCorrespondence") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsCorrespondence = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsCorrespondence = sprintf("&totalRows_rsCorrespondence=%d%s", $totalRows_rsCorrespondence, $queryString_rsCorrespondence);



$currentPage ="index.php";



?>
<?php if(isset($_GET['deleteID']) && $_GET['deleteID'] > 0) { 
$delete = "DELETE FROM correspondence WHERE ID = ".GetSQLValueString($_GET['deleteID'], "int");
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
header("location: index.php"); exit;
} ?><?php if ($totalRows_rsCorrespondence == 0) { // Show if recordset empty ?>
     <p>No messages. </p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsCorrespondence > 0) { // Show if recordset not empty ?>
     <p class="text-muted">Site correspondence  <?php echo ($startRow_rsCorrespondence + 1) ?> to <?php echo min($startRow_rsCorrespondence + $maxRows_rsCorrespondence, $totalRows_rsCorrespondence) ?> of <?php echo $totalRows_rsCorrespondence ?> (<span id="checkedCount"></span> selected)</p>
     
     <table class="table table-hover">
     <thead>
       <tr>
         <th><input name="checkAll" id="checkAll" type="checkbox" onClick="checkUncheckAll(this);"></th>
         <th>&nbsp;</th>
          <th>Date</th><th>Time</th>
          
          <th>From</th>
          <th class="recipient"><strong>To</strong></th>
          <th  class="subject">Subject</th>
          <th align="center">
          Auto</th>
          <th>View</th>
        </tr></thead><tbody>
       <?php do { ?>
         <tr><td valign="top"><input name="checkbox[<?php echo $row_rsCorrespondence['ID']; ?>]" id="checkbox<?php echo $row_rsCorrespondence['ID']; ?>" type="checkbox" value="<?php echo $row_rsCorrespondence['ID']; ?>"></td>
            <td class="top"><?php if($row_rsCorrespondence['mailfolderID'] == 1) { ?>
              <img src="/core/images/icons/mail-message-new.png" alt="Incoming mail message" width="16" height="16" style="vertical-align:
middle;" />
              <?php } else { ?>
            <img src="/core/images/icons/edit-undo.png" alt="Outgoing mail message" width="16" height="16" style="vertical-align:
middle;" />              <?php } ?></td>
           <td class="text-nowrap  top"><?php echo ($row_rsCorrespondence['sentdatetime']>'2001') ? date('d M',strtotime($row_rsCorrespondence['sentdatetime'])) : date('d M',strtotime($row_rsCorrespondence['createddatetime'])); ?></td>
           <td class="text-nowrap  top"><?php echo ($row_rsCorrespondence['sentdatetime']>'2001') ? date('H:i',strtotime($row_rsCorrespondence['sentdatetime'])) :date('H:i',strtotime($row_rsCorrespondence['createddatetime'])); ?></td>
           <td class="top"><a href="mailto:<?php echo $row_rsCorrespondence['sender']; ?>" title="Click on this link to send a reply using your mail client (e.g. Outlook)"><?php $sender = isset($row_rsCorrespondence['sendername']) ? $row_rsCorrespondence['sendername'] : $row_rsCorrespondence['sender']; echo (strlen($sender)>30) ? substr($sender,0,27)."&hellip;" : $sender; ?>
           </a></td><td valign="top"  class="recipient"><?php $row_rsCorrespondence['recipient'] = isset($row_rsCorrespondence['company']) ? $row_rsCorrespondence['company'] : $row_rsCorrespondence['recipient']; echo (strlen($row_rsCorrespondence['recipient'])>130) ? substr($row_rsCorrespondence['recipient'],0,27)."&hellip;" : $row_rsCorrespondence['recipient']; ?>
              </td>
           
           <td valign="top" class="subject"><?php echo (strlen($row_rsCorrespondence['subject'])>115) ? substr($row_rsCorrespondence['subject'],0,13)."&hellip;" : $row_rsCorrespondence['subject']; ?></td>
           <td align="center" valign="top"><?php if($row_rsCorrespondence['autoreply'] == 1) { ?>
             <img src="/core/images/icons/mail-reply-sender.png" alt="This message has been auto-replied to" width="16" height="16" style="vertical-align:
middle;" />
             <?php } else { ?>
             &nbsp;
             <?php } ?></td>
           <td class="top"><a href="email/view.php?correspondenceID=<?php echo $row_rsCorrespondence['ID']; ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-search"></i> View</a></td>
         </tr>
         <?php } while ($row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence)); ?></tbody>
     </table>
     <?php } // Show if recordset not empty ?>
     <?php 
	 $ajaxcallURL = isset($_GET['ajaxcallURL']) ? $_GET['ajaxcallURL'] : ""; echo createPagination($pageNum_rsCorrespondence,$totalPages_rsCorrespondence,"rsCorrespondence",20,$ajaxcallURL); ?>
<?php
mysql_free_result($rsCorrespondence);

mysql_free_result($rsMailPrefs);
}
?>