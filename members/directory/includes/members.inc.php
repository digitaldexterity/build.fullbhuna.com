<?php if(!isset($aquiescedb)) {
	require_once('../../../Connections/aquiescedb.php'); 
} ?>
<?php require_once(SITE_ROOT.'core/includes/framework.inc.php');  ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_SESSION['MM_UserGroup'])) {
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

$currentPage = "/members/directory/index.php";

$maxRows_rsUsers = 20;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$varSearch_rsUsers = "%";
if (isset($_GET['search'])) {
  $varSearch_rsUsers = $_GET['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT users.ID, firstname, surname, users.email, jobtitle, users.imageURL, users.showemail, location.ID AS locationID, location.locationname, location.telephone1, users.telephone, directory.ID AS directoryID, directory.name AS dirname, directory.telephone AS dirtelephone FROM users LEFT JOIN location ON (users.defaultaddressID = location.ID AND location.active = 1 AND location.public = 1) LEFT JOIN directoryuser ON (directoryuser.userID = users.ID) LEFT JOIN directory ON (directoryuser.directoryID = directory.ID) WHERE usertypeID >0 AND usertypeID <10 AND (surname LIKE %s OR firstname LIKE %s) GROUP BY users.ID ORDER BY surname ASC", GetSQLValueString($varSearch_rsUsers . "%", "text"),GetSQLValueString($varSearch_rsUsers . "%", "text"));
$query_limit_rsUsers = sprintf("%s LIMIT %d, %d", $query_rsUsers, $startRow_rsUsers, $maxRows_rsUsers);
$rsUsers = mysql_query($query_limit_rsUsers, $aquiescedb) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);

if (isset($_GET['totalRows_rsUsers'])) {
  $totalRows_rsUsers = $_GET['totalRows_rsUsers'];
} else {
  $all_rsUsers = mysql_query($query_rsUsers);
  $totalRows_rsUsers = mysql_num_rows($all_rsUsers);
}
$totalPages_rsUsers = ceil($totalRows_rsUsers/$maxRows_rsUsers)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT memberdirectory, preferences.usercontactform, preferences.memberdirectoryemail, preferences.memberdirectoryname FROM preferences";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

$queryString_rsUsers = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUsers") == false && 
        stristr($param, "totalRows_rsUsers") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUsers = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUsers = sprintf("&totalRows_rsUsers=%d%s", $totalRows_rsUsers, $queryString_rsUsers);
 
?> <?php if ($totalRows_rsUsers == 0) { // Show if recordset empty ?>
  <p>There are no users matching your search.</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Users <?php echo ($startRow_rsUsers + 1) ?> to <?php echo min($startRow_rsUsers + $maxRows_rsUsers, $totalRows_rsUsers) ?> of <?php echo $totalRows_rsUsers ?>&nbsp;</p>
  <table class="table table-hover">
  <tbody>
    <?php do { ?>
      <tr>
        <td><a href="/members/profile/index.php?userID=<?php echo $row_rsUsers['ID']; ?>" class="fb_avatar" style="background-image:url(<?php echo isset($row_rsUsers['imageURL']) ? getImageURL($row_rsUsers['imageURL'],"thumb") : "/members/images/user-anonymous.png"; ?>);"><?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?></a></td>
        <td><strong><a href="/members/profile/index.php?userID=<?php echo $row_rsUsers['ID']; ?>" ><?php echo htmlentities($row_rsUsers['firstname']." ".$row_rsUsers['surname'], ENT_COMPAT, "UTF-8"); ?></a></strong><br />
        <span class="text-muted"><?php echo htmlentities($row_rsUsers['jobtitle'], ENT_COMPAT, "UTF-8"); ?></span></td>
       
       
        <td class = "contactmember"><?php $email = ""; 
		if($row_rsPreferences['memberdirectoryemail']==1 && $row_rsUsers['showemail']==1 && isset($row_rsUsers['email'])) { 
		$email = "<a href = \"mailto:".htmlentities($row_rsUsers['email'], ENT_COMPAT, "UTF-8")."\">".htmlentities($row_rsUsers['email'], ENT_COMPAT, "UTF-8")."</a>";
		}  
		echo nl2br(trim($email."\n".htmlentities($row_rsUsers['telephone'], ENT_COMPAT, "UTF-8"), "\n")); ?>&nbsp;</td>
        <td><?php echo isset($row_rsUsers['locationID']) ? nl2br(trim("<a href=\"/location/location.php?locationID=".$row_rsUsers['locationID']."\">".htmlentities($row_rsUsers['locationname'], ENT_COMPAT, "UTF-8")."</a>\n".htmlentities($row_rsUsers['telephone1'], ENT_COMPAT, "UTF-8"), "\n")) : "&nbsp;"; ?></td>
       
        <td><?php echo (isset($row_rsUsers['directoryID'])) ? nl2br(trim("<a href=\"/directory/directory.php?directoryID=".$row_rsUsers['directoryID']."\">".htmlentities($row_rsUsers['dirname'], ENT_COMPAT, "UTF-8")."</a>\n".$row_rsUsers['dirtelephone'], "\n")) : "&nbsp;"; ?></td>
      
         <td class = "contactmember"><?php if($row_rsUsers['showemail']==1 && isset($row_rsUsers['email'])) { ?><a href="/members/message/index.php?userID=<?php echo $row_rsUsers['ID']; ?>&key=<?php echo md5($row_rsUsers['ID'].PRIVATE_KEY); ?>" class="link_email" title="Send this user a message">Send message</a><?php } ?></td><td><a href="/members/profile/index.php?userID=<?php echo $row_rsUsers['ID']; ?>" class="link_view" title="More details on this user">View</a></td>
      </tr>
      <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, 0, $queryString_rsUsers); ?>">First</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsUsers > 0) { // Show if not first page ?>
        <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, max(0, $pageNum_rsUsers - 1), $queryString_rsUsers); ?>">Previous</a>
        <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, min($totalPages_rsUsers, $pageNum_rsUsers + 1), $queryString_rsUsers); ?>">Next</a>
        <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsUsers < $totalPages_rsUsers) { // Show if not last page ?>
        <a href="<?php printf("%s?pageNum_rsUsers=%d%s", $currentPage, $totalPages_rsUsers, $queryString_rsUsers); ?>">Last</a>
        <?php } // Show if not last page ?></td>
  </tr>
</table><?php 
mysql_free_result($rsUsers);

} ?>