<?php if(!$aquiescedb) {
	require_once('../../../Connections/aquiescedb.php'); 
}
require_once(SITE_ROOT.'core/includes/framework.inc.php'); 

if (!isset($_SESSION)) {
  session_start();
}
if(isset($_SESSION['MM_UserGroup']) && intval($_SESSION['MM_UserGroup'])>6) {
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT text_role FROM preferences WHERE ID = ".$regionID;
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
}
?>
<?php 

if(isset($_GET['search'])) {	
	$orderby = "users.surname ASC, users.firstname ASC" ;
	$orderbydescription = "alphabetically";
} else {
	$orderby = "users.dateadded DESC, users.surname ASC";// surname to keep order with users added simultaneously
	$orderbydescription = "most recent first";
}


// break names up if spaces
$searches = isset($_GET['search']) ? explode(" ", trim($_GET['search'])) : array();
$clauses = array();

foreach($searches as $key => $search) {
	$clauses[$key] = "(users.surname LIKE ".GetSQLValueString($search . "%", "text")." OR users.firstname LIKE ".GetSQLValueString($search . "%", "text")." OR users.email LIKE ".GetSQLValueString("%" .$search . "%", "text").")";
	$clause=implode(' AND ' ,$clauses);
}
$where = isset($clause) ? " AND ".$clause : "";
$join = "";

if(isset($_GET['groupID']) && intval($_GET['groupID'])>0) {
	$join .= " LEFT JOIN usergroupmember ON (usergroupmember.userID = users.ID) ";
	$where .= " AND usergroupmember.groupID = ".intval($_GET['groupID'])." ";
}

$currentPage = "/members/admin/index.php";

$maxRows_rsUsers = 50;
$pageNum_rsUsers = 0;
if (isset($_GET['pageNum_rsUsers'])) {
  $pageNum_rsUsers = $_GET['pageNum_rsUsers'];
}
$startRow_rsUsers = $pageNum_rsUsers * $maxRows_rsUsers;

$usertypeID = isset($_GET['usertypeID']) ? intval($_GET['usertypeID']) : "-4";
$regionID = isset($regionID) ? intval($regionID) : 0;
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUsers = "SELECT users.ID, users.firstname, users.surname, users.warning, users.jobtitle, users.email, users.username, users.plainpassword, usertype.name,users.mobile, users.telephone, users.usertypeID, users.addedbyID,  region.title AS region FROM users  LEFT JOIN usertype ON (usertype.ID = users.usertypeID) LEFT JOIN region ON (users.regionID = region.ID) ".$join." WHERE ((".$usertypeID." = -4  AND usertypeID >=0) OR ".$usertypeID." = -3 OR users.usertypeID = ".$usertypeID.") AND (".$regionID." = 0 OR users.regionID IS NULL OR users.regionID= ".$regionID." OR (users.usertypeID <= ".$_SESSION['MM_UserGroup']." && users.regionID= 0)) ".$where." GROUP BY users.ID ORDER BY ".$orderby;
$query_limit_rsUsers = sprintf("%s LIMIT %d, %d", $query_rsUsers, $startRow_rsUsers, $maxRows_rsUsers);
$rsUsers = mysql_query($query_limit_rsUsers, $aquiescedb) or die(mysql_error().": ".$query_limit_rsUsers);
$row_rsUsers = mysql_fetch_assoc($rsUsers);



if (isset($_GET['totalRows_rsUsers'])) {
  $totalRows_rsUsers = $_GET['totalRows_rsUsers'];
} else {
  $all_rsUsers = mysql_query($query_rsUsers);
  $totalRows_rsUsers = mysql_num_rows($all_rsUsers);
}
$totalPages_rsUsers = ceil($totalRows_rsUsers/$maxRows_rsUsers)-1;

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

?>
<?php if ($totalRows_rsUsers == 0) { // Show if recordset empty ?>
  <p>There are no users on the system that meet your search criteria. </p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsUsers > 0) { // Show if recordset not empty ?>
  <form action="index.php" method="post" name="formUsers" id="formUsers">
    <input name="formaction" id="formaction" type="hidden" value="" />
    <input name="usertypeID" value="0" type="hidden" />
   
    
        <p class="text-muted">Users <?php echo ($startRow_rsUsers + 1) ?> to <?php echo min($startRow_rsUsers + $maxRows_rsUsers, $totalRows_rsUsers) ?> of <?php echo $totalRows_rsUsers ?> listed <?php echo $orderbydescription; ?> (<span id="checkedCount"></span> selected)</p>
     <div class="table-responsive">
    <table class="table table-hover">
      <thead>
      <tr> <th><input type="checkbox" name="checkAll" id="checkAll" onclick="checkUncheckAll(this);" /></th>
        <th>&nbsp;</th>
        <th>ID</th>
        <th>Name</th> 
        <th class="col_jobtitle"><?php echo $row_rsPreferences['text_role']; ?> </th>
        <th class="col_email">email</th>
         <th class="col_phone">Telephone</th>
          <th class="col_mobile">Mobile</th>
        <th class="region">Site</th>
       <th>Rank</th>
        <th >Actions</th>
       
      </tr></thead>
      <tbody>
      
      <?php do { ?>
        <tr>
        <td><input type="checkbox" value="<?php echo $row_rsUsers['ID']; ?>" id="select<?php echo $row_rsUsers['ID']; ?>" name="select[<?php echo $row_rsUsers['ID']; ?>]" <?php echo isset($_POST['select'][$row_rsUsers['ID']]) ? "checked=\"checked\"" : ""; ?> /></td>
          <td class="usertype usertypeID<?php echo $row_rsUsers['usertypeID']; ?>">&nbsp;</td>
          <td class="userID"><?php echo $row_rsUsers['ID']; ?></td>
          <td class="warning<?php echo $row_rsUsers['warning']; ?>"><a href="modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>&returnURL=<?php echo isset($_GET['returnURL']) ? urlencode($_GET['returnURL']) : urlencode($_SERVER['REQUEST_URI']); ?>" data-toggle="tooltip" title="View/edit details for <?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?>" ><?php echo $row_rsUsers['firstname']." ".$row_rsUsers['surname']; ?></a></td>
          <td class="col_jobtitle"><?php echo $row_rsUsers['jobtitle']; ?></td>
          <td class="col_email"><?php if(isset($row_rsUsers['email'])) { ?><a href="mailto:<?php echo $row_rsUsers['email']; ?>" data-toggle="tooltip" title="Send email to <?php echo $row_rsUsers['email']; ?>"><?php echo $row_rsUsers['email']; ?></a><?php } ?></td>
           <td class="col_phone"><?php echo $row_rsUsers['telephone']; ?></td>
            <td class="col_mobile"><?php echo $row_rsUsers['mobile']; ?></td>
          <td class="region"><?php echo isset($row_rsUsers['region']) ? $row_rsUsers['region'] : "All sites"; ?></td>
          <td><em><?php echo $row_rsUsers['name']; ?></em>&nbsp;
            <?php if (isset($row_rsUsers['addedbyID']) && $row_rsUsers['addedbyID'] == 0) { ?>
            &nbsp;<img src="/core/images/icons/world.png" alt="Web sign-up" width="16" height="16" align="absmiddle" title="This use signed up themselves using the web site" data-toggle="tooltip" />
            <?php } if (!isset($row_rsUsers['username'])) { ?>
              &nbsp;<img src="/core/images/icons/cross.png" alt="No login details" width="16" height="16" align="absmiddle" title="This user does not have any login credentials" data-toggle="tooltip"/>
              <?php } ?></td>
          <td nowrap > 
          
          <!-- Single button -->
<div class="btn-group">
  <button type="button" class="btn btn-sm btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="glyphicon glyphicon-cog"></i> Actions <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
  <li><a href="modify_user.php?userID=<?php echo $row_rsUsers['ID']; ?>&returnURL=<?php echo isset($_GET['returnURL']) ? urlencode($_GET['returnURL']) : urlencode($_SERVER['REQUEST_URI']); ?>" ><i class="glyphicon glyphicon-pencil"></i> View/edit details</a></li>
  <?php  if (isset($row_rsUsers['email'])) { ?>
    <li><a href="/mail/admin/email/send.php?recipient=<?php echo htmlentities($row_rsUsers['email'], ENT_COMPAT, "UTF-8"); ?>"  data-toggle="tooltip" title="Send email to <?php echo htmlentities($row_rsUsers['firstname']." ".$row_rsUsers['surname'],ENT_COMPAT, "UTF-8"); ?>"><i class="glyphicon glyphicon-envelope"></i> Send email</a></li><?php } ?>
     <?php  if (isset($row_rsUsers['username']) && $row_rsUsers['usertype']>=0) { ?>
    <li><a href="modify_user.php?userID=<?php echo intval($_GET['userID']); ?>&amp;loginas=<?php echo $row_rsUsers['username']; ?>" onClick="<?php if(isset($row_rsUsers['username'])) { ?>return confirm('Are you sure you want to log in as this user?\n\nYou will be logged out as yourself and returned the the Member Home Page.');<?php } else { ?>alert('This user does not have any log in details yet.'); return false;<?php } ?>"><i class="glyphicon glyphicon-log-in"></i> Log in as <?php echo $row_rsUsers['firstname']; ?> <?php echo $row_rsUsers['surname']; ?></a></li><?php } ?>
  </ul>
</div><!-- end button group-->
          
          
          
          
          
          
          </td>
          
        </tr>
        <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?>
   </tbody> </table></div>
  </form>
  <?php } // Show if recordset not empty ?>
<?php echo createPagination($pageNum_rsUsers,$totalPages_rsUsers,"rsUsers");?>
<?php

if(isset($rsUsers)) {
mysql_free_result($rsUsers);
}

mysql_free_result($rsPreferences);
?>
