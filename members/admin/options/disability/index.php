<?php require_once('../../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$regionID = (isset($regionID ) && $regionID>0)   ? intval($regionID ) : 1;

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO disability (disabilityname) VALUES (%s)",
                       GetSQLValueString($_POST['disabilityname'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateSQL = sprintf("UPDATE preferences SET askdisabilitytext=%s WHERE ID=%s",
                       GetSQLValueString($_POST['askdisabilitytext'], "text"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
  $updateGoTo = "../index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	$newID = mysql_insert_id();
	$update = "UPDATE disability SET ordernum = ".$newID ." WHERE ID = ".$newID ;
	mysql_query($update, $aquiescedb) or die(mysql_error());
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

$maxRows_rsDisability = 100;
$pageNum_rsDisability = 0;
if (isset($_GET['pageNum_rsDisability'])) {
  $pageNum_rsDisability = $_GET['pageNum_rsDisability'];
}
$startRow_rsDisability = $pageNum_rsDisability * $maxRows_rsDisability;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDisability = "SELECT disability.*, COUNT(users.ID) AS number FROM disability LEFT JOIN users ON disability.ID = users.disabilityID GROUP BY disability.ID ORDER BY disability.ordernum, ID";
$query_limit_rsDisability = sprintf("%s LIMIT %d, %d", $query_rsDisability, $startRow_rsDisability, $maxRows_rsDisability);
$rsDisability = mysql_query($query_limit_rsDisability, $aquiescedb) or die(mysql_error());
$row_rsDisability = mysql_fetch_assoc($rsDisability);

if (isset($_GET['totalRows_rsDisability'])) {
  $totalRows_rsDisability = $_GET['totalRows_rsDisability'];
} else {
  $all_rsDisability = mysql_query($query_rsDisability);
  $totalRows_rsDisability = mysql_num_rows($all_rsDisability);
}
$totalPages_rsDisability = ceil($totalRows_rsDisability/$maxRows_rsDisability)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountDisability = "SELECT COUNT(users.ID) AS total  FROM users WHERE users.disabilityID IS NOT NULL";
$rsCountDisability = mysql_query($query_rsCountDisability, $aquiescedb) or die(mysql_error());
$row_rsCountDisability = mysql_fetch_assoc($rsCountDisability);
$totalRows_rsCountDisability = mysql_num_rows($rsCountDisability);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = $regionID";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Disability"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=disability&"+order); 
            } 
        }); 
		
    }); 
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page users">
          <h1><i class="glyphicon glyphicon-user"></i> Manage Disability</h1><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
            <li><a href="../index.php" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> User Options</a></li>
          </ul></div></nav><form name="form" action="<?php echo $editFormAction; ?>" method="POST"><fieldset class="form-inline"><legend>Prompt</legend><input name="askdisabilitytext" type="text" value="<?php echo $row_rsPreferences['askdisabilitytext']; ?>" size="50" maxlength="100" class="form-control">
              <button type="submit" name="button" id="button" class="btn btn-default btn-secondary" >Save</button>
              <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>">
          </fieldset>
            <input type="hidden" name="MM_update" value="form">
          </form>
          <form name="form1" method="POST" action="<?php echo $editFormAction; ?>">
            <fieldset class="form-inline">
              <legend>Add disability type</legend>
              <label>Disability name:
                <input name="disabilityname" type="text" id="disabilityname" size="50" maxlength="50" class="form-control">
              </label>
              <button type="submit" class="btn btn-default btn-secondary"  >Add</button>
            </fieldset>
            <input type="hidden" name="MM_insert" value="form1">
          </form>
          <?php if ($totalRows_rsDisability == 0) { // Show if recordset empty ?>
  <p>There are currently no entries</p>
  <?php } // Show if recordset empty ?>
<?php if ($totalRows_rsDisability > 0) { // Show if recordset not empty ?>
  <p class="text-muted">Items <?php echo ($startRow_rsDisability + 1) ?> to <?php echo min($startRow_rsDisability + $maxRows_rsDisability, $totalRows_rsDisability) ?> of <?php echo $totalRows_rsDisability ?> <span id="info">(Drag and drop to reorder)</span> </p>
  <table class="table table-hover">
  <tbody class="sortable">
    <?php do { ?>
      
      <tr id="listItem_<?php echo $row_rsDisability['ID']; ?>" ><td class= "handle" title="Drag and drop order of pages">&nbsp;</td><td class="status<?php echo $row_rsDisability['statusID']; ?>">&nbsp;</td><td><?php echo $row_rsDisability['disabilityname']; ?> (<?php echo $row_rsDisability['number']; if($row_rsCountDisability['total']>0) echo " - ".number_format(($row_rsDisability['number']/$row_rsCountDisability['total']*100),2)."%"; ?>)</td><td><a href="update_disability.php?disabilityID=<?php echo $row_rsDisability['ID']; ?>" class="link_edit icon_only">Edit</a></td></tr>
      <?php } while ($row_rsDisability = mysql_fetch_assoc($rsDisability)); ?>
  </tbody></table>
  <?php } // Show if recordset not empty ?>
        </div><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsDisability);

mysql_free_result($rsCountDisability);

mysql_free_result($rsPreferences);
?>
