<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
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

$currentPage = $_SERVER["PHP_SELF"];
$msg = "";
if(isset($_POST['action'])) {
	if(isset($_SESSION['checkbox']) && is_array($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) {
		if($_POST['action'] == "resample") {
			foreach($_SESSION['checkbox'] as $key => $value) {
				$file = explode("|",$value);
				$basename = basename($file[1]);
				$msg .= $basename."; ";
				if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$basename) && isset($image_sizes)) { 	
				
					$imagesizes = createImageSizes($file[1]);
				} // end is an image
				
			} // end for
			if($msg !="") {
				$msg ="The following files have been resampled: ".$msg;
			}
			
		} else { // default action			
			foreach($_SESSION['checkbox'] as $key => $value) {
				$uploadID = explode("|",$value);
				deleteUpload($uploadID[0]);
				//echo $uploadID."<BR>";
			}
			unset($_SESSION['checkbox']);
		}
	}
}


$maxRows_rsUploads = 100;
$pageNum_rsUploads = 0;
if (isset($_GET['pageNum_rsUploads'])) {
  $pageNum_rsUploads = $_GET['pageNum_rsUploads'];
}
$startRow_rsUploads = $pageNum_rsUploads * $maxRows_rsUploads;

$varSearch_rsUploads = "%";
if (isset($_GET['search'])) {
  $varSearch_rsUploads = $_GET['search'];
}
$varRegionID_rsUploads = "$regionID";
if (isset($_GET['regionID'])) {
  $varRegionID_rsUploads = $_GET['regionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUploads = sprintf("SELECT uploads.*,  users.firstname, users.surname, region.title AS site FROM uploads LEFT JOIN users ON (uploads.createdbyID = users.ID) LEFT JOIN region  ON (uploads.regionID = region.ID) WHERE uploads.filename LIKE %s AND uploads.regionID = %s ORDER BY createddatetime DESC", GetSQLValueString("%" . $varSearch_rsUploads . "%", "text"),GetSQLValueString($varRegionID_rsUploads, "int"));
$query_limit_rsUploads = sprintf("%s LIMIT %d, %d", $query_rsUploads, $startRow_rsUploads, $maxRows_rsUploads);
$rsUploads = mysql_query($query_limit_rsUploads, $aquiescedb) or die(mysql_error());
$row_rsUploads = mysql_fetch_assoc($rsUploads);

if (isset($_GET['totalRows_rsUploads'])) {
  $totalRows_rsUploads = $_GET['totalRows_rsUploads'];
} else {
  $all_rsUploads = mysql_query($query_rsUploads);
  $totalRows_rsUploads = mysql_num_rows($all_rsUploads);
}
$totalPages_rsUploads = ceil($totalRows_rsUploads/$maxRows_rsUploads)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$queryString_rsUploads = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUploads") == false && 
        stristr($param, "totalRows_rsUploads") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUploads = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUploads = sprintf("&totalRows_rsUploads=%d%s", $totalRows_rsUploads, $queryString_rsUploads);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Manage Uploads"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/documentsDefault.css" rel="stylesheet"  /><link href="/documents/css/documentsDefault.css" rel="stylesheet"  /><script src="../../../core/scripts/checkbox/checkboxes.js"></script>
<?php require_once('../../../core/scripts/checkbox/checkboxsession.inc.php'); ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --> <div class="page class"><?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
    <h1><i class="glyphicon glyphicon-folder-open"></i> Manage Uploads</h1>
    <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li class="nav-item"><a href="resample.php" class="nav-link"><i class="glyphicon glyphicon-repeat"></i> Resample all images</a></li>
    </ul></div></nav>
    
    <?php if(isset($msg) && trim($msg) !="") { ?>
    <p class="message alert alert-info" role="alert"><?php echo $msg; ?></p>
    <?php } ?>
    <form method="get">
    <fieldset class="form-inline"><legend>Search</legend><input name="search" type="text" value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search'], ENT_COMPAT, "UTF-8"): ""; ?>" placeholder="Filename" class="form-control"><select name="regionID" class="form-control">
      <option value="0" <?php if (isset($_GET['regionID']) && $_GET['regionID']==0) {echo "selected=\"selected\"";} ?>>All Sites</option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsRegions['ID']?>"<?php if ((isset($_GET['regionID']) && $_GET['regionID']==$row_rsRegions['ID']) || (!isset($_GET['regionID']) && $row_rsRegions['ID']==$regionID)) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
      <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
    </select> 
    <button class="btn btn-default btn-secondary" type="submit">Go</button></fieldset>
    </form>
    <?php if ($totalRows_rsUploads == 0) { // Show if recordset empty ?>
      <p>There are currently no uploads matching your search.</p>
      <?php } // Show if recordset empty ?>

<?php if ($totalRows_rsUploads > 0) { // Show if recordset not empty ?><p class="text-muted">Uploads <?php echo ($startRow_rsUploads + 1) ?> to <?php echo min($startRow_rsUploads + $maxRows_rsUploads, $totalRows_rsUploads) ?> of <?php echo $totalRows_rsUploads ?> (<span id="checkedCount"></span> selected)</p>
  <form action="index.php" method="post" name="form1" id="form1">
    <table class="table table-hover">
    <thead>
      <tr>
        <th>          <input name="checkAll" id="checkAll"  type="checkbox" onclick="checkUncheckAll(this)" />        </th>
        
        <th>File name</th> <th>Uploaded</th>
        
        
        <th>Size</th>
        <th>User</th>  <th  class="region">Site</th>
        
      </tr></thead><tbody>
      <?php do { ?>
        <tr>
          <td>
            <input type="checkbox" name="checkbox[<?php echo $row_rsUploads['ID']; ?>]" id="checkbox<?php echo $row_rsUploads['ID']; ?>" value="<?php echo $row_rsUploads['ID']; ?>|<?php echo $row_rsUploads['newfilename']; ?>" />
          </td>
          <td class="docsItem"><a href="<?php echo str_replace(SITE_ROOT,"/",$row_rsUploads['newfilename']); ?>" class="document <?php echo substr(strrchr($row_rsUploads['filename'],'.'),1,3); ?>"><abbr title="<?php echo $row_rsUploads['mimetype']; ?>"><?php echo $row_rsUploads['filename']; ?></abbr></a></td>
          
          <td><?php echo date('d M Y',strtotime($row_rsUploads['createddatetime'])); ?></td>
          <td><?php echo $row_rsUploads['filesize']; ?>b</td>
          <td><?php echo $row_rsUploads['firstname']; ?> <?php echo $row_rsUploads['surname']; ?></td>
          <td class="region"><?php echo $row_rsUploads['regionID']==0 ? "All Sites" :  $row_rsUploads['site']; ?>
          
          </td>
        </tr>
        <?php } while ($row_rsUploads = mysql_fetch_assoc($rsUploads)); ?></tbody>
    </table>
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsUploads > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUploads=%d%s", $currentPage, 0, $queryString_rsUploads); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsUploads > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsUploads=%d%s", $currentPage, max(0, $pageNum_rsUploads - 1), $queryString_rsUploads); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsUploads < $totalPages_rsUploads) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUploads=%d%s", $currentPage, min($totalPages_rsUploads, $pageNum_rsUploads + 1), $queryString_rsUploads); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsUploads < $totalPages_rsUploads) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsUploads=%d%s", $currentPage, $totalPages_rsUploads, $queryString_rsUploads); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
   <p>With selected: <button type="submit" onclick="return confirm('Are you sure you want to delete the selected uploads?\n\nRemember there may be links to these documents on the site which will become broken.');" class="btn btn-default btn-secondary">Delete</button>
     <button type="submit" name="resamplebutton" id="resamplebutton" onclick="if(confirm('Are you sure you want to resample selected uploads?\n\nImages will be resized to new set specifications. Files that are not supported images will be ignored.')) { document.getElementById('action').value='resample'} else {return false;}" class="btn btn-default btn-secondary">Resample images</button>
     <input name="action" type="hidden" id="action" value="delete" />
   </p>
  </form>
  <?php } // Show if recordset not empty ?></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUploads);

mysql_free_result($rsRegions);
?>
