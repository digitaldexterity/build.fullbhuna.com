<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) {
	// security - only access if admin
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


$maxRows_rsUsers = 50;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$varSearch_rsUsers = "-1";
if (isset($_GET['search'])) {
  $varSearch_rsUsers = $_GET['search'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = sprintf("SELECT ID, firstname, surname, email FROM users WHERE usertypeID > 0 AND surname LIKE %s ORDER BY surname ASC, firstname ASC", GetSQLValueString($varSearch_rsUsers . "%", "text"));
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


?><ul><?php if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?><?php do { ?><li><a href="javascript:void(0);" onclick="selectuserID('<?php echo intval($_GET['inputID']); ?>',<?php echo $row_rsUsers['ID']; ?>, '<?php echo htmlentities($row_rsUsers['firstname']." ".$row_rsUsers['surname'], ENT_COMPAT, "UTF-8"); ?>')"><?php echo $row_rsUsers['firstname']."&nbsp;".$row_rsUsers['surname']; echo isset($row_rsUsers['email']) ?  "&nbsp;(".$row_rsUsers['email'].")" :""; ?></a> <a class="link_view" href="/members/admin/modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>">View</a></li><?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?><?php } else {?>
<li>Not found</li>
<?php }?></ul><?php 
mysql_free_result($rsUsers);

} // end can access ?>