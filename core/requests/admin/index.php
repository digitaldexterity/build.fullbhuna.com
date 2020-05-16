<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
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

$currentPage = $_SERVER["PHP_SELF"];

$_GET['show'] = isset($_GET['show']) ? $_GET['show'] : 0;

if ((isset($_GET['deleteID'])) && ($_GET['deleteID'] != "") &&$_SESSION['MM_UserGroup'] ==10) {
  $deleteSQL = sprintf("DELETE FROM changerequest WHERE ID=%s",
                       GetSQLValueString($_GET['deleteID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());

  $deleteGoTo = "index.php";
  header(sprintf("Location: %s", $deleteGoTo)); exit;
}


$maxRows_rsChangeRequests = 30;
$pageNum_rsChangeRequests = 0;
if (isset($_GET['pageNum_rsChangeRequests'])) {
  $pageNum_rsChangeRequests = $_GET['pageNum_rsChangeRequests'];
}
$startRow_rsChangeRequests = $pageNum_rsChangeRequests * $maxRows_rsChangeRequests;

$varShowAll_rsChangeRequests = "0";
if (isset($_GET['show'])) {
  $varShowAll_rsChangeRequests = $_GET['show'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsChangeRequests = sprintf("SELECT changerequest.*, users.firstname ,users.surname, developer.firstname AS devfirstname, developer.surname AS devsurname FROM changerequest LEFT JOIN users ON (changerequest.createdbyID = users.ID) LEFT JOIN users AS developer ON (changerequest.modifiedbyID = developer.ID) WHERE (changerequest.statusID = 0 AND changerequest.createddatetime >DATE_SUB(CURDATE(), INTERVAL 2 MONTH)) OR %s = 1 ORDER BY changerequest.createddatetime DESC", GetSQLValueString($varShowAll_rsChangeRequests, "int"));
$query_limit_rsChangeRequests = sprintf("%s LIMIT %d, %d", $query_rsChangeRequests, $startRow_rsChangeRequests, $maxRows_rsChangeRequests);
$rsChangeRequests = mysql_query($query_limit_rsChangeRequests, $aquiescedb) or die(mysql_error());
$row_rsChangeRequests = mysql_fetch_assoc($rsChangeRequests);

if (isset($_GET['totalRows_rsChangeRequests'])) {
  $totalRows_rsChangeRequests = $_GET['totalRows_rsChangeRequests'];
} else {
  $all_rsChangeRequests = mysql_query($query_rsChangeRequests);
  $totalRows_rsChangeRequests = mysql_num_rows($all_rsChangeRequests);
}
$totalPages_rsChangeRequests = ceil($totalRows_rsChangeRequests/$maxRows_rsChangeRequests)-1;

$queryString_rsChangeRequests = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsChangeRequests") == false && 
        stristr($param, "totalRows_rsChangeRequests") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsChangeRequests = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsChangeRequests = sprintf("&totalRows_rsChangeRequests=%d%s", $totalRows_rsChangeRequests, $queryString_rsChangeRequests);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> <?php echo $admin_name; ?> - Manage Feedback</title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<?php if($_SESSION['MM_UserGroup'] < 10) { ?>
<style>
.webadminonly { display:none; }
</style>
<?php } ?><link href="/core/requests/css/requests.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page forum">
  <h1>Feedback</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="../members/add_request.php?adminpage=true"   onclick="javascript:MM_openBrWindow('add_request.php?adminpage=true','Help','scrollbars=yes,width=400,height=400'); return false;"><i class="glyphicon glyphicon-plus-sign"></i> Add Feedback</a></li>
   <li><a href="options.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Options</a></li>
  </ul></div></nav>
<?php if ($totalRows_rsChangeRequests == 0) { // Show if recordset empty ?>
    <p>There is currently no feedback.</p>
    <?php } // Show if recordset empty ?>
  <?php if ($totalRows_rsChangeRequests > 0) { // Show if recordset not empty ?>
    <form action="index.php" method="get">
      <p>Feedback <?php echo ($startRow_rsChangeRequests + 1) ?> to <?php echo min($startRow_rsChangeRequests + $maxRows_rsChangeRequests, $totalRows_rsChangeRequests) ?> of <?php echo $totalRows_rsChangeRequests ?>
     <select name="show" onchange="this.form.submit()">
       <option value="1" <?php if (!(strcmp(1, $_GET['show']))) {echo "selected=\"selected\"";} ?>>Show all</option>
<option value="0" <?php if (!(strcmp(0, $_GET['show']))) {echo "selected=\"selected\"";} ?>>Show pending</option>
     </select> 
  
    </p>
    </form>
    <table  class="table table-hover"><thead>
      <tr>
        <td>&nbsp;</td>
        <td>Posted</td>
        <td>by</td>
        <td>Feedback</td>
        
        <td colspan="3" class="webadminonly">Actions</td>
        </tr></thead><tbody>
      <?php do { ?>
        <tr class="requesttype<?php echo $row_rsChangeRequests['requesttypeID']; ?>">
          <td valign="top" class="status<?php echo $row_rsChangeRequests['statusID']; ?>">&nbsp;</td>
          <td class="text-nowrap  top"><?php echo date('d M Y',strtotime($row_rsChangeRequests['createddatetime'])); ?><?php echo isset($row_rsChangeRequests['modifeddatetime']) ? "<br><em>Updated:<br>".date('d M Y',strtotime($row_rsChangeRequests['modifeddatetime']))."</em>" : "&nbsp;"; ?></td><td class="text-nowrap  top"><?php echo $row_rsChangeRequests['firstname']; ?> <?php echo $row_rsChangeRequests['surname']; ?></td>
          <td class="top"><?php echo nl2br($row_rsChangeRequests['requestdetails']); ?>
            <?php echo isset($row_rsChangeRequests['developernotes']) ? "<br /><br /><em>".$row_rsChangeRequests['devfirstname']." ".$row_rsChangeRequests['devsurname'].":</em><br>".nl2br($row_rsChangeRequests['developernotes']) : ""; ?></td>
          
          <td valign="top" class="webadminonly"><a href="<?php echo $row_rsChangeRequests['../../../requests/admin/URL']; ?>" target="_blank" class="link_view" rel="noopener">View</a></td>
          <td valign="top" class="webadminonly"><a href="update_request.php?requestID=<?php echo $row_rsChangeRequests['ID']; ?>" class="link_edit icon_only">Edit</a></td>
          <td valign="top"  class="webadminonly"><a href="index.php?deleteID=<?php echo $row_rsChangeRequests['ID']; ?>" onclick="return confirm('Are you sure you want to delete this request?');" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
        </tr>
        <?php } while ($row_rsChangeRequests = mysql_fetch_assoc($rsChangeRequests)); ?></tbody>
    </table>
    <?php } // Show if recordset not empty ?>
<table class="form-table">
  <tr>
    <td><?php if ($pageNum_rsChangeRequests > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsChangeRequests=%d%s", $currentPage, 0, $queryString_rsChangeRequests); ?>">First</a>
      <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsChangeRequests > 0) { // Show if not first page ?>
      <a href="<?php printf("%s?pageNum_rsChangeRequests=%d%s", $currentPage, max(0, $pageNum_rsChangeRequests - 1), $queryString_rsChangeRequests); ?>">Previous</a>
      <?php } // Show if not first page ?></td>
    <td><?php if ($pageNum_rsChangeRequests < $totalPages_rsChangeRequests) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsChangeRequests=%d%s", $currentPage, min($totalPages_rsChangeRequests, $pageNum_rsChangeRequests + 1), $queryString_rsChangeRequests); ?>">Next</a>
      <?php } // Show if not last page ?></td>
    <td><?php if ($pageNum_rsChangeRequests < $totalPages_rsChangeRequests) { // Show if not last page ?>
      <a href="<?php printf("%s?pageNum_rsChangeRequests=%d%s", $currentPage, $totalPages_rsChangeRequests, $queryString_rsChangeRequests); ?>">Last</a>
      <?php } // Show if not last page ?></td>
    </tr>
</table></div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsChangeRequests);
?>
