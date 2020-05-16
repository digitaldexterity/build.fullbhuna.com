<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../includes/adminAccess.inc.php'); ?>
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

if(isset($_POST['newstatus']) && $_POST['newstatus']!="") {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	foreach($_POST['country'] as $key => $value) {
		$update = "UPDATE countries SET statusID = ".intval($_POST['newstatus'])." WHERE ID = ".intval($key);
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}

$maxRows_rsCountries = 500;
$pageNum_rsCountries = 0;
if (isset($_GET['pageNum_rsCountries'])) {
  $pageNum_rsCountries = $_GET['pageNum_rsCountries'];
}
$startRow_rsCountries = $pageNum_rsCountries * $maxRows_rsCountries;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCountries = "SELECT countries.*, region.title FROM countries LEFT JOIN region ON countries.regionID = region.ID ORDER BY countries.ordernum ASC, countries.fullname ASC";
$query_limit_rsCountries = sprintf("%s LIMIT %d, %d", $query_rsCountries, $startRow_rsCountries, $maxRows_rsCountries);
$rsCountries = mysql_query($query_limit_rsCountries, $aquiescedb) or die(mysql_error());
$row_rsCountries = mysql_fetch_assoc($rsCountries);

if (isset($_GET['totalRows_rsCountries'])) {
  $totalRows_rsCountries = $_GET['totalRows_rsCountries'];
} else {
  $all_rsCountries = mysql_query($query_rsCountries);
  $totalRows_rsCountries = mysql_num_rows($all_rsCountries);
}
$totalPages_rsCountries = ceil($totalRows_rsCountries/$maxRows_rsCountries)-1;
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Countries"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/core/scripts/checkbox/checkboxes.js"></script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=countries&"+order); 
            } 
        }); 
		
    }); 
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page regions"><h1><i class="glyphicon glyphicon-globe"></i> Manage Countries</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li class="nav-item"><a href="add_country.php"class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add Country</a></li>
      <li class="nav-item"><a href="../index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Manage Sites</a></li>
    </ul></div></nav>
<p class="text-muted">Countries <?php echo ($startRow_rsCountries + 1) ?> to <?php echo min($startRow_rsCountries + $maxRows_rsCountries, $totalRows_rsCountries) ?> of <?php echo $totalRows_rsCountries ?>. <span id="info">Drag and drop to re-order.</span></p>
<form method="post" id="form1">
<table class="table table-hover">
<thead>
 <tr>
  <th class="handle" ></th>
       <th> <input type="checkbox" name="checkAll" id="checkAll" onClick="checkUncheckAll(this);" /></th>
        <th><!-- status --> </th>
        <th>Country</th>
         <th>Nationality</th>
          <th>Code</th>
        <th>ISO2</th>
        <th>ISO3</th><th>CC1</th>
        <th>Currency</th>
        <th>TLD</th>
       
        <th>Site</th>
        <th>Edit</th>
        
     </tr></thead><tbody class="sortable">
      <?php do { ?>
      
         
       <tr  id="listItem_<?php echo $row_rsCountries['ID']; ?>" ><td class= "handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of countries">&nbsp;</td>  
       
       <td class="text-nowrap">
              <input type="checkbox" name="country[<?php echo $row_rsCountries['ID']; ?>]" id="country<?php echo $row_rsCountries['ID']; ?>" value="<?php echo $row_rsCountries['ID']; ?>" />
            </td>
            
            
        <td class="status<?php echo $row_rsCountries['statusID']; ?>"><!-- status --> </td>
          <td><?php echo htmlentities($row_rsCountries['fullname'], ENT_COMPAT, "UTF-8"); ?></td>
           <td><?php echo htmlentities($row_rsCountries['nationality'], ENT_COMPAT, "UTF-8"); ?></td>
            <td><?php echo $row_rsCountries['num_code']; ?></td>
          <td><?php echo $row_rsCountries['iso2']; ?><?php 
		 
?></td>
          <td><?php echo $row_rsCountries['iso3']; ?></td> <td><?php echo $row_rsCountries['cc1']; ?></td>
          <td><?php echo $row_rsCountries['currency_code']; ?></td>
          <td><?php echo $row_rsCountries['tld']; ?></td>
         
          <td><?php echo ($row_rsCountries['regionID']=="0") ? "All sites" :  $row_rsCountries['title']; ?></td>
          <td><a href="update_country.php?countryID=<?php echo $row_rsCountries['ID']; ?>" class="link_edit icon_only">Edit</a></td>
         
       </tr>
        <?php } while ($row_rsCountries = mysql_fetch_assoc($rsCountries)); ?>
   </tbody></table>
   <fieldset>
   <p>With selected: <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to make these countries active?')) { document.getElementById('newstatus').value=1; document.getElementById('form1').submit(); } return false;">Make active</a> | <a href="javascript:void(0);" onClick="if(confirm('Are you sure you want to make these countries inactive?')) { document.getElementById('newstatus').value=0; document.getElementById('form1').submit(); } return false;">Make inactive</a></p><input type="hidden" id="newstatus" name="newstatus" value=""></fieldset>
  </form>
   
   </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCountries);
?>
