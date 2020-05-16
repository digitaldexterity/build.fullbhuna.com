<?php if(!isset($aquiescedb)) {
	require_once('../../../../Connections/aquiescedb.php');
} ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
if($_SESSION['MM_UserGroup']<7) { die(); }
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

$_GET['startdate'] = isset($_GET['startdate']) ? $_GET['startdate']: date('Y-m-d', strtotime("LAST YEAR"));
$_GET['enddate'] = isset($_GET['enddate']) ? $_GET['enddate']: date('Y-m-d');

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

$currentPage = isset($_GET['currentpage']) ? $_GET['currentpage'] : "/mail/admin/communication/";

$maxRows_rsCommunication = isset($_GET['show']) ? intval($_GET['show']) : 50;
$pageNum_rsCommunication = 0;
if (isset($_GET['pageNum_rsCommunication'])) {
  $pageNum_rsCommunication = $_GET['pageNum_rsCommunication'];
}
$startRow_rsCommunication = $pageNum_rsCommunication * $maxRows_rsCommunication;

$varStartDate_rsCommunication = "1970-01-01";
if (isset($_GET['startdate'])) {
  $varStartDate_rsCommunication = $_GET['startdate'];
}
$varFollowup_rsCommunication = "0";
if (isset($_GET['followups'])) {
  $varFollowup_rsCommunication = $_GET['followups'];
}
$varCategoryID_rsCommunication = "0";
if (isset($_GET['commcatID'])) {
  $varCategoryID_rsCommunication = $_GET['commcatID'];
}
$varCommType_rsCommunication = "0";
if (isset($_GET['commtypeID'])) {
  $varCommType_rsCommunication = $_GET['commtypeID'];
}
$varDirectoryID_rsCommunication = "0";
if (isset($_GET['directoryID'])) {
  $varDirectoryID_rsCommunication = $_GET['directoryID'];
}
$varLocationID_rsCommunication = "0";
if (isset($_GET['locationID'])) {
  $varLocationID_rsCommunication = $_GET['locationID'];
}
$varEndDate_rsCommunication = "2999-01-01";
if (isset($_GET['enddate'])) {
  $varEndDate_rsCommunication = $_GET['enddate'];
}
$varSearch_rsCommunication = "%";
if (isset($_GET['search'])) {
  $varSearch_rsCommunication = $_GET['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCommunication = sprintf("SELECT communication.*,  directory.name, client.firstname AS clientfirstname, client.surname AS clientsurname,  personell.firstname, personell.surname, location.address1, location.address2, location.address3, communicationtype.typename, communicationcategory.categoryname FROM communication LEFT JOIN directory ON (directory.ID = communication.directoryID) LEFT JOIN users AS client ON (client.ID = communication.clientID) LEFT JOIN users AS personell ON (personell.ID = communication.userID) LEFT JOIN location ON (communication.locationID = location.ID) LEFT JOIN communicationtype ON (communication.commtypeID = communicationtype.ID) LEFT JOIN communicationcategory ON (communication.commcatID = communicationcategory.ID) WHERE (%s = 0 OR communication.locationID = %s) AND (%s = 0 OR communication.directoryID = %s)  AND (%s = 0 OR communication.commtypeID = %s)   AND (%s = 0 OR communication.commcatID = %s) AND DATE(communication.thiscommdatetime) >= %s AND DATE(communication.thiscommdatetime) <= %s AND (nextcommdatetime IS NOT NULL OR %s = 0) AND communication.notes LIKE %s ORDER BY communication.createddatetime DESC", GetSQLValueString($varLocationID_rsCommunication, "int"),GetSQLValueString($varLocationID_rsCommunication, "int"),GetSQLValueString($varDirectoryID_rsCommunication, "int"),GetSQLValueString($varDirectoryID_rsCommunication, "int"),GetSQLValueString($varCommType_rsCommunication, "int"),GetSQLValueString($varCommType_rsCommunication, "int"),GetSQLValueString($varCategoryID_rsCommunication, "int"),GetSQLValueString($varCategoryID_rsCommunication, "int"),GetSQLValueString($varStartDate_rsCommunication, "date"),GetSQLValueString($varEndDate_rsCommunication, "date"),GetSQLValueString($varFollowup_rsCommunication, "int"),GetSQLValueString("%" . $varSearch_rsCommunication . "%", "text"));
$query_limit_rsCommunication = sprintf("%s LIMIT %d, %d", $query_rsCommunication, $startRow_rsCommunication, $maxRows_rsCommunication);
$rsCommunication = mysql_query($query_limit_rsCommunication, $aquiescedb) or die(mysql_error());
$row_rsCommunication = mysql_fetch_assoc($rsCommunication);

if (isset($_GET['totalRows_rsCommunication'])) {
  $totalRows_rsCommunication = $_GET['totalRows_rsCommunication'];
} else {
  $all_rsCommunication = mysql_query($query_rsCommunication);
  $totalRows_rsCommunication = mysql_num_rows($all_rsCommunication);
}
$totalPages_rsCommunication = ceil($totalRows_rsCommunication/$maxRows_rsCommunication)-1;

$queryString_rsCommunication = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsCommunication") == false && 
        stristr($param, "totalRows_rsCommunication") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsCommunication = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsCommunication = sprintf("&totalRows_rsCommunication=%d%s", $totalRows_rsCommunication, $queryString_rsCommunication);
?><?php if ($totalRows_rsCommunication == 0) { // Show if recordset empty ?>
      <p>There are no activities that match your search criteria.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsCommunication > 0) { // Show if recordset not empty ?>
      <p class="text-muted">Notes <?php echo ($startRow_rsCommunication + 1) ?> to <?php echo min($startRow_rsCommunication + $maxRows_rsCommunication, $totalRows_rsCommunication) ?> of <?php echo $totalRows_rsCommunication ?></p>
      <table class="table table-hover">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th>&nbsp;</th><th class="communicationtype">Type</th>
          <th>Follow-up</th>
          <th>Client</th>
          <th>Contact</th>
          
          <th class="communicationcategory">Category</th>
          <th>Personel</th>
          <th colspan="3">Actions</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr class="noUnderline category<?php echo $row_rsCommunication['commcatID']; ?> type<?php echo $row_rsCommunication['commtypeID']; ?> incoming<?php echo $row_rsCommunication['incoming']; ?>">
          <td class="text-nowrao top incomingicon">&nbsp;</td>
            <td class="text-nowrap  top"><strong><?php echo date('D d M Y', strtotime($row_rsCommunication['thiscommdatetime'])); ?></strong></td><td class="text-nowrap  top communicationtype"><strong><em><?php echo $row_rsCommunication['typename']; ?></em></strong></td>
            <td class="text-nowrap  top"><strong><?php echo isset($row_rsCommunication['nextcommdatetime']) ? date('D d M Y', strtotime($row_rsCommunication['nextcommdatetime'])) : "None"; if(isset($row_rsCommunication['nextcommdatetime']) && !isset($row_rsCommunication['nextcommID']) && $row_rsCommunication['nextcommdatetime'] < date("Y-m-d")) { ?> <img src="/core/images/warning.gif" alt="Overdue" width="16" height="16" style="vertical-align:
middle;" /><?php } ?>
            </strong></td>
            <td class="top"><strong>
            <?php $client = trim($row_rsCommunication['name']." ".$row_rsCommunication['address1']." ".$row_rsCommunication['address2']." ".$row_rsCommunication['address3']); echo ($client =="") ? "N/A" : "<a href=\"/directory/admin/update_directory.php?directoryID=".$row_rsCommunication['directoryID']."\">".$client."</a>"; ?>
            </strong></td>
            <td class="text-nowrap  top"><strong>
            <?php $name =  trim($row_rsCommunication['clientfirstname']." ".$row_rsCommunication['clientsurname']); echo ($name=="") ? "N/A" : $name; ?>
            </strong></td>
            
            <td class="text-nowrap  top communicationcategory"><strong><em><?php echo $row_rsCommunication['categoryname']; ?></em></strong></td>
            <td class="text-nowrap  top"><strong><?php echo $row_rsCommunication['firstname']; ?> <?php echo $row_rsCommunication['surname']; ?></strong></td>
            <td class="top"><strong><a href="/mail/admin/communication/edit_communication.php?communicationID=<?php echo $row_rsCommunication['ID']; ?>" class="link_edit icon_only">Edit</a></strong></td>
            <td class="top"><strong><a href="/mail/admin/communication/index.php?cancelfollowupID=<?php echo $row_rsCommunication['ID']; ?>" class="link_delete" onclick="return confirm('Are you sure you want to cancel this follow up?');"><i class="glyphicon glyphicon-trash"></i> Cancel</a></strong></td>
            <td class="top"><strong><a href="/mail/admin/communication/add_communication.php?followupID=<?php echo $row_rsCommunication['ID']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Follow Up</a></strong></td>
          </tr>
          <tr><td>&nbsp;</td>
            <td colspan="9" valign="top"><?php echo $row_rsCommunication['notes']; ?></td>
          </tr>
          <?php } while ($row_rsCommunication = mysql_fetch_assoc($rsCommunication)); ?></tbody>
    </table>
      <table class="form-table">
        <tr>
          <td><?php if ($pageNum_rsCommunication > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsCommunication=%d%s", $currentPage, 0, $queryString_rsCommunication); ?>">First</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsCommunication > 0) { // Show if not first page ?>
              <a href="<?php printf("%s?pageNum_rsCommunication=%d%s", $currentPage, max(0, $pageNum_rsCommunication - 1), $queryString_rsCommunication); ?>">Previous</a>
              <?php } // Show if not first page ?></td>
          <td><?php if ($pageNum_rsCommunication < $totalPages_rsCommunication) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsCommunication=%d%s", $currentPage, min($totalPages_rsCommunication, $pageNum_rsCommunication + 1), $queryString_rsCommunication); ?>">Next</a>
              <?php } // Show if not last page ?></td>
          <td><?php if ($pageNum_rsCommunication < $totalPages_rsCommunication) { // Show if not last page ?>
              <a href="<?php printf("%s?pageNum_rsCommunication=%d%s", $currentPage, $totalPages_rsCommunication, $queryString_rsCommunication); ?>">Last</a>
              <?php } // Show if not last page ?></td>
        </tr>
    </table>
      <?php } // Show if recordset not empty ?>
<?php
mysql_free_result($rsCommunication);

?>
