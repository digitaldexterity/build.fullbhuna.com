<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../includes/documentfunctions.inc.php'); ?>
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;



// upgrade any old My Documents folders

mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT ID, userID FROM documents WHERE documentcategoryID =-1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {
	$olduserID = 0;
	while($document = mysql_fetch_assoc($result)) {
		if($olduserID != $document['userID']) {
			$olduserID = $document['userID'];
			$folderID = addfolder("My Documents", "Only you have access to this folder and items within it. This folder automatically contains any documents you upload elsewhere on the site.", 0, $document['userID'], 99, 99, 0,0, $regionID);
			if($folderID>0) {
				$update = "UPDATE documents SET documentcategoryID = ".$folderID." WHERE ID = ".$document['ID'];
				mysql_query($update, $aquiescedb) or die(mysql_error());
				echo $update."<br>";
			} else {
				die("Problem adding folder");
			}
		}		
	}
}


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE documentprefs SET opennewwindow=%s, versioncontrol=%s, defaultview=%s, additionalfolders=%s, showsearch=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['opennewwindow']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['versioncontrol']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['defaultview'], "int"),
                       GetSQLValueString(isset($_POST['additionalfolders']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showsearch']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$update = "UPDATE documentcategory SET accessID = ". GetSQLValueString($_POST['accessID'],"int").", writeaccess = ". GetSQLValueString($_POST['writeaccess'],"int").", categoryname = ".GetSQLValueString($_POST['homecategoryname'],"text")." WHERE ID = 0";
	 $Result1 = mysql_query($update, $aquiescedb) or die(mysql_error());
  $updateGoTo = "/core/admin/index.php?msg=".urlencode("Document preferences have been saved")."";
  
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDocumentPrefs = "SELECT * FROM documentprefs WHERE ID = ".$regionID."";
$rsDocumentPrefs = mysql_query($query_rsDocumentPrefs, $aquiescedb) or die(mysql_error());
$row_rsDocumentPrefs = mysql_fetch_assoc($rsDocumentPrefs);
$totalRows_rsDocumentPrefs = mysql_num_rows($rsDocumentPrefs);

if($totalRows_rsDocumentPrefs<1) { // no row exists so add one and refresh...
	$insert = "INSERT INTO documentprefs (ID) VALUES (".$regionID.")";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: index.php"); exit;
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = "SELECT * FROM usertype WHERE ID > 0";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

$maxRows_rsRecentDocs = 10;
$pageNum_rsRecentDocs = 0;
if (isset($_GET['pageNum_rsRecentDocs'])) {
  $pageNum_rsRecentDocs = $_GET['pageNum_rsRecentDocs'];
}
$startRow_rsRecentDocs = $pageNum_rsRecentDocs * $maxRows_rsRecentDocs;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRecentDocs = "SELECT documents.*, documentcategory.categoryname, users.firstname, users.surname FROM documents LEFT JOIN documentcategory ON(documents.documentcategoryID = documentcategory.ID) LEFT JOIN users ON (documents.userID = users.ID) ORDER BY uploaddatetime DESC";
$query_limit_rsRecentDocs = sprintf("%s LIMIT %d, %d", $query_rsRecentDocs, $startRow_rsRecentDocs, $maxRows_rsRecentDocs);
$rsRecentDocs = mysql_query($query_limit_rsRecentDocs, $aquiescedb) or die(mysql_error());
$row_rsRecentDocs = mysql_fetch_assoc($rsRecentDocs);

if (isset($_GET['totalRows_rsRecentDocs'])) {
  $totalRows_rsRecentDocs = $_GET['totalRows_rsRecentDocs'];
} else {
  $all_rsRecentDocs = mysql_query($query_rsRecentDocs);
  $totalRows_rsRecentDocs = mysql_num_rows($all_rsRecentDocs);
}
$totalPages_rsRecentDocs = ceil($totalRows_rsRecentDocs/$maxRows_rsRecentDocs)-1;


$row_rsHomeFolder = getHomeFolder($regionID);




?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Documents"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><link href="/documents/css/documentsDefault.css" rel="stylesheet"  /><script>
addListener("load", init);
function init() {
	addListener("change", checkWriteAccess, document.getElementById('accessID'));
	addListener("change", checkReadAccess, document.getElementById('writeaccess'));
}

function checkWriteAccess() {
	if(document.getElementById('accessID').value > document.getElementById('writeaccess').value) {
		setSelectListToValue(document.getElementById('accessID').value, 'writeaccess');	}
}

function checkReadAccess() {
	if(document.getElementById('accessID').value > document.getElementById('writeaccess').value) {
		setSelectListToValue(document.getElementById('writeaccess').value, 'accessID');
	}
}
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  <h1><i class="glyphicon glyphicon-folder-open"></i>  Documents Settings</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li class="nav-item"><a href="/documents/" target="_blank" class="nav-link" rel="noopener" ><i class="glyphicon glyphicon-arrow-left"></i> Go to documents</a></li>
    <li class="nav-item"><a href="flipbooks/index.php" class="nav-link"><i class="glyphicon glyphicon-book"></i> Manage Flipbooks</a></li>
    <li class="nav-item"><a href="uploads/index.php" class="nav-link"><i class="glyphicon glyphicon-cloud-upload"></i> Manage Uploads</a></li>
     <li class="nav-item"><a href="folders/index.php" class="nav-link"><i class="glyphicon glyphicon-folder-open"></i> Manage Folders</a></li>
    <li class="nav-item"><a href="import/index.php" class="nav-link"><i class="glyphicon glyphicon-import"></i> Import Data</a></li>
    <li class="nav-item"><a href="../../core/admin/backup/index.php" class="nav-link"><i class="glyphicon glyphicon-repeat"></i> Backup</a></li>
    <?php if($_SESSION['MM_UserGroup']==10) { ?>
    <li class="nav-item"><a href="files/index.php" class="nav-link"><i class="glyphicon glyphicon-duplicate"></i> File Manager</a></li><?php } ?>
  </ul></div></nav>
  <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <p>Default view:
    <label>
      <input <?php if (!(strcmp($row_rsDocumentPrefs['defaultview'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultview" value="0"  />
      List  (small icons)</label>
   &nbsp;&nbsp;&nbsp;
    <label>
      <input <?php if (!(strcmp($row_rsDocumentPrefs['defaultview'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultview" value="1"  />
      List (large icons)</label>
        &nbsp;&nbsp;&nbsp;
       <label>
      <input <?php if (!(strcmp($row_rsDocumentPrefs['defaultview'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="defaultview" value="2"  />
      Desktop style</label>
   
  </p>
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsDocumentPrefs['opennewwindow'],1))) {echo "checked=\"checked\"";} ?> name="opennewwindow" type="checkbox" id="opennewwindow" value="1" />
        Open documents in new window when available in browser (otherwise force download)</label></p>
   <p>
      <label>
        <input <?php if (!(strcmp($row_rsDocumentPrefs['versioncontrol'],1))) {echo "checked=\"checked\"";} ?> name="versioncontrol" type="checkbox" id="versioncontrol" value="1" />
        Turn on version control</label>
    </p>
    
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsDocumentPrefs['additionalfolders'],1))) {echo "checked=\"checked\"";} ?> name="additionalfolders" type="checkbox" id="additionalfolders" value="1" />
        Allow documents to be added to additional folders</label>
    </p>
    
    <p>
      <label>
        <input <?php if (!(strcmp($row_rsDocumentPrefs['showsearch'],1))) {echo "checked=\"checked\"";} ?> name="showsearch" type="checkbox" id="showsearch" value="1" />
        Show document search</label>
    </p>
    
      <h2>Home folder </h2> <p>
      <label>Name:
        <input name="homecategoryname" type="text" id="homecategoryname" value="<?php echo $row_rsHomeFolder['categoryname']; ?>" class="form-control" />
      </label>
    </p>
        <p class="form-inline"><label>Can 
        view:
          <select name="accessID" id="accessID" class="form-control">
          <option value="0" <?php if (!(strcmp(0, $row_rsHomeFolder['accessID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsHomeFolder['accessID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['name']?></option>
          <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
        </select>
      </label>
      
      &nbsp;&nbsp;&nbsp;<label>Can add:<select name="writeaccess" id="writeaccess" class="form-control">
            <option value="99"  <?php if (!(strcmp(99, $row_rsHomeFolder['writeaccess']))) {echo "selected=\"selected\"";} ?>>Just me</option>
            <option value="100"  <?php if (!(strcmp(100, $row_rsHomeFolder['writeaccess']))) {echo "selected=\"selected\"";} ?>>Nobody</option>
            <option value="0" selected="selected" <?php if (!(strcmp(0, $row_rsHomeFolder['writeaccess']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
            <?php
do {  
?>
            <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsHomeFolder['writeaccess']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['name']?></option>
<?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
          </select></label>
          
          
    </p>
   
    <p>
      <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsDocumentPrefs['ID']; ?>" />
      <label>
        <button name="save" type="submit" class="btn btn-primary" id="save" >Save changes</button>
      </label>
    </p>
    <input type="hidden" name="MM_update" value="form1" />
  </form>
 <h2>Recently Added</h2>
 <table class="table table-hover">
 <thead>
   <tr> <th>&nbsp;</th>     <th>Uploaded</th>
     <th>By</th>

     <th>Document</th>
     <th>Folder</th>
    
    
     <th>Type</th>
     </tr></thead><tbody>
   <?php do { ?>
     <tr> <td class="status<?php echo $row_rsRecentDocs['active']; ?>">&nbsp;</td> <td><?php echo date('H:i d M Y',strtotime($row_rsRecentDocs['uploaddatetime'])); ?></td>
       <td><?php echo $row_rsRecentDocs['firstname']; ?> <?php echo $row_rsRecentDocs['surname']; ?></td>
       <td><a href="/documents/members/modify_document.php?documentID=<?php echo $row_rsRecentDocs['ID']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $row_rsRecentDocs['documentname']; ?></a></td>
       <td><a href="/documents/folders/modify_folder.php?categoryID=<?php echo $row_rsRecentDocs['documentcategoryID']; ?>" target="_blank" rel="noopener noreferrer"><?php echo $row_rsRecentDocs['categoryname']; ?></a></td>
      
    
      
       <td><?php echo $row_rsRecentDocs['type']; ?></td>
       </tr>
     <?php } while ($row_rsRecentDocs = mysql_fetch_assoc($rsRecentDocs)); ?></tbody>
 </table>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDocumentPrefs);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsRecentDocs);

mysql_free_result($rsHomeFolder);
?>
