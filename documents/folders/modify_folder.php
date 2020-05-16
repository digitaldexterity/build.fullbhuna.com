<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?><?php require_once('../includes/documentfunctions.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegion = "SELECT ID FROM region";
$rsRegion = mysql_query($query_rsRegion, $aquiescedb) or die(mysql_error());
$row_rsRegion = mysql_fetch_assoc($rsRegion);
$totalRows_rsRegion = mysql_num_rows($rsRegion);


$colname_rsLoggedIn = "1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, firstname, surname, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varCategoryID_rsInFolder = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsInFolder = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsInFolder = sprintf("SELECT parentcategory.* FROM documentcategory LEFT JOIN documentcategory AS parentcategory ON (documentcategory.subCatOfID = parentcategory.ID) WHERE documentcategory.ID = %s", GetSQLValueString($varCategoryID_rsInFolder, "int"));
$rsInFolder = mysql_query($query_rsInFolder, $aquiescedb) or die(mysql_error());
$row_rsInFolder = mysql_fetch_assoc($rsInFolder);
$totalRows_rsInFolder = mysql_num_rows($rsInFolder);

$colname_rsCategory = "1";
if (isset($_GET['categoryID'])) {
  $colname_rsCategory = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategory = sprintf("SELECT documentcategory.*, users.firstname, users.surname, modifier.firstname AS modifiedfirstname, modifier.surname AS modifiedsurname FROM documentcategory LEFT JOIN users ON (documentcategory.addedbyID = users.ID) LEFT JOIN users AS modifier ON (documentcategory.modifiedbyID = users.ID)  WHERE documentcategory.ID = %s", GetSQLValueString($colname_rsCategory, "int"));
$rsCategory = mysql_query($query_rsCategory, $aquiescedb) or die(mysql_error());
$row_rsCategory = mysql_fetch_assoc($rsCategory);
$totalRows_rsCategory = mysql_num_rows($rsCategory);


// security - check if user has permission to update within this folder or target select statements must go above

$parentaccess = thisUserHasAccess($row_rsInFolder['writeaccess'], $row_rsInFolder['groupwriteID'],$row_rsLoggedIn['ID']);

if(!$parentaccess) {
	// not authorised to add 
	if(isset($_POST["MM_insert"])) {
		unset($_POST["MM_insert"]);
	}
	$submit_error = "You are not authorised to update anything within the folder: ".$row_rsInFolder['categoryname'];
}

$thisaccess = thisUserHasAccess($row_rsCategory['writeaccess'], $row_rsCategory['groupwriteID'],$row_rsLoggedIn['ID']);

if(!$parentaccess || !$thisaccess) die("No access");

if(isset($_POST["MM_update"])) {
	$select = "SELECT * FROM documentcategory WHERE ID = ".GetSQLValueString($_POST['subcatofID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
  	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	
	if(!thisUserHasAccess($row['writeaccess'], $row['groupwriteID'],$row_rsLoggedIn['ID'])) {
		// not authorised to update 
		
			unset($_POST["MM_update"]);
	
		$submit_error = "You are not authorised to move anything to the folder: ".$row['categoryname'];
		
	}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
 //check for loop
 $conclusion = 0; $categoryID = intval($_POST['ID']); $parentID = intval($_POST['subcatofID']);
 do {
	
	 if(!isset($parentID) || $parentID ==0) {
		 $conclusion = 1;
	 } else if($parentID == intval($_POST['ID'])) {
		 $conclusion = 2;
	 } else {
		 $categoryID = $parentID;
	 }
	 $select = "SELECT subcatofID FROM documentcategory WHERE ID = ".$categoryID;
	 $result = mysql_query($select, $aquiescedb) or die(mysql_error());
	 $row = mysql_fetch_assoc($result);
	 $parentID = $row['subcatofID'];
 } while ($conclusion == 0);
 
 if($conclusion ==2) {
	 unset($_POST["MM_update"]);
	 $submit_error = "Sorry, you cannot move a folder into a sub folder of itself.";
 }
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE documentcategory SET categoryname=%s, `description`=%s, subcatofID=%s, accessID=%s, active=%s, writeaccess=%s, groupreadID=%s, groupwriteID=%s, modifiedbyID=%s, modifieddatetime=%s WHERE ID=%s",
                       GetSQLValueString($_POST['categoryname'], "text"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['subcatofID'], "int"),
                       GetSQLValueString($_POST['accessID'], "int"),
                       GetSQLValueString(isset($_POST['active']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['writeaccess'], "int"),
                       GetSQLValueString($_POST['groupreadID'], "int"),
                       GetSQLValueString($_POST['groupwriteID'], "int"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
	// set region of this and child folders to region of parent
	$select = "SELECT regionID FROM documentcategory WHERE ID = ".intval($_POST['subcatofID']);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$region = mysql_fetch_assoc($result);
	
	
	
	updateFolderRegion($_POST['ID'], $region['regionID']);
  $updateGoTo = isset($_POST['referer']) ? $_POST['referer'] : "/documents/" ;
  header(sprintf("Location: %s", $updateGoTo)); exit;
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevels = "SELECT usertype.ID, usertype.name AS accesslevel FROM usertype WHERE usertype.ID > 0 ORDER BY usertype.ID ASC";
$rsAccessLevels = mysql_query($query_rsAccessLevels, $aquiescedb) or die(mysql_error());
$row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
$totalRows_rsAccessLevels = mysql_num_rows($rsAccessLevels);

$varUserGroup_rsFolders = "0";
if (isset($_SESSION['MM_UserGroup'])) {
  $varUserGroup_rsFolders = $_SESSION['MM_UserGroup'];
}
$varUserID_rsFolders = "-1";
if (isset($userID)) {
  $varUserID_rsFolders = $userID;
}
$varCategoryID_rsFolders = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsFolders = $_GET['categoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFolders = sprintf("SELECT documentcategory.ID, documentcategory.categoryname, subcat.ID AS subcat, region.title AS site FROM documentcategory LEFT JOIN documentcategory AS subcat ON (subcat.subcatofID = documentcategory.ID) LEFT JOIN region ON (documentcategory.regionID =region.ID) WHERE documentcategory.active = 1 AND (documentcategory.writeaccess <= %s OR (documentcategory.addedbyID = %s AND documentcategory.writeaccess = 99)) AND documentcategory.ID != %s AND (subcat.ID IS NULL OR subcat.subcatofID != %s) GROUP BY documentcategory.ID ORDER BY documentcategory.regionID, categoryname ASC ", GetSQLValueString($varUserGroup_rsFolders, "int"),GetSQLValueString($varUserID_rsFolders, "int"),GetSQLValueString($varCategoryID_rsFolders, "int"),GetSQLValueString($varCategoryID_rsFolders, "int"));
$rsFolders = mysql_query($query_rsFolders, $aquiescedb) or die(mysql_error());
$row_rsFolders = mysql_fetch_assoc($rsFolders);
$totalRows_rsFolders = mysql_num_rows($rsFolders);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");
?>
<!doctype html>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Update folder - ".htmlentities($row_rsCategory['categoryname'], ENT_COMPAT, "UTF-8"); echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<script src="../../SpryAssets/SpryValidationTextField.js"></script>

<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<script>
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
</script><style><!--



<?php echo ".justmeshow { display: none; }";
if($totalRows_rsGroups==0) { echo ".groups { display: none }"; } 
if($row_rsInFolder['writeaccess']==99) {
	echo ".justmehide { display: none; } .justmeshow { display: block; }";
	
} ?>
	
--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
 <div class="container pageBody documents">
                   
        
        <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="../"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="../index.php"><span itemprop="name">Documents</span></a>
       <meta itemprop="position" content="2" />
      </li>
      
	  
	  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="../index.php?categoryID=<?php echo $row_rsCategory['ID']; ?>"><span itemprop="name">
	  <?php echo isset($row_rsCategory['categoryname']) ? $row_rsCategory['categoryname']: "Home folder"; ?></span></a> <meta itemprop="position" content="3" /></li>
      
       <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  Update Folder</span></a> <meta itemprop="position" content="4" /></li>
      
      </ol></div></div>    
            
            
            
          <h1>Update Folder </h1>
  
   <?php require_once('../../core/includes/alert.inc.php'); ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
            
            <table class="form-table">
              <tr>
                <td align="right">Folder name: </td>
                <td><span id="sprytextfield1">
                  <input name="categoryname" type="text"  id="categoryname" value="<?php echo htmlentities($row_rsCategory['categoryname'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="50" class="form-control" />
                <span class="textfieldRequiredMsg">A name is required.</span></span></td>
              </tr>
              <tr>
                <td align="right" valign="top">Description:<br /></td>
                <td>
                  <textarea name="description" id="description" cols="45" rows="5" placeholder="Optional - describe what this folder is intended to contain" class="form-control"><?php echo htmlentities($row_rsCategory['description'], ENT_COMPAT, "UTF-8"); ?></textarea>
</td>
              </tr>
              <tr>
                <td align="right"><label for="subcatofID">In folder:</label></td>
                <td>
                <?php if($row_rsCategory['subcatofID']==0) { ?>
               <input type="hidden"  name="subcatofID" value="0"> This is your home folder and cannot be moved
				<?php } else { ?>
                  <select name="subcatofID" id="subcatofID" class="justmehide form-control">
              
                    <?php
do {  

$name = $row_rsFolders['categoryname'];

if($totalRows_rsRegion>1) {
	$name = isset($row_rsFolders['site']) ? $row_rsFolders['site']." > ".$name : "All sites > ".$name;
	
}

?>
                    <option value="<?php echo $row_rsFolders['ID']?>"<?php if (!(strcmp($row_rsFolders['ID'], $row_rsCategory['subcatofID']))) {echo "selected=\"selected\"";} ?>><?php echo $name; ?></option>
                    <?php
} while ($row_rsFolders = mysql_fetch_assoc($rsFolders));
  $rows = mysql_num_rows($rsFolders);
  if($rows > 0) {
      mysql_data_seek($rsFolders, 0);
	  $row_rsFolders = mysql_fetch_assoc($rsFolders);
  }
?>
                </select><?php } ?> <div class="justmeshow">Only you can view this folder as it is within a folder that is set to view by just you</div></td>
              </tr>
              <tr>
                <td align="right"><label for="accessID">Can view:</label></td>
                <td class="form-inline"><select name="accessID" id="accessID" class="justmehide form-control">
                  <option value="99" <?php if (!(strcmp(99, $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>>Just me</option>
                  <?php if($row_rsInFolder['writeaccess']!=99) { ?>
                  <option value="0" <?php if (!(strcmp(0, $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsCategory['accessID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
                  <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  } }
?>
                </select>
                  <select name="groupreadID" id="groupreadID" class="group justmehide form-control" onchange="if(this.value!=0) alert('Remember, you will need to be in the chosen group if you wish to view or edit this folder in future');">
                    <option value="0" <?php if (!(strcmp(0, $row_rsCategory['groupreadID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsCategory['groupreadID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                    <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                </select>
                  <div class="justmeshow">Only you can see this folder as it is within a folder that is set to be viewed by just you</div></td>
              </tr>
              <tr>
                <td align="right"><label for="writeaccess">Can add to:</label></td>
                <td class="form-inline">
                  <select name="writeaccess" id="writeaccess" class="justmehide form-control">
                    <option value="99"  <?php if (!(strcmp(99, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Just me</option>
                    <option value="100"  <?php if (!(strcmp(100, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Nobody</option>
                    <option value="0"  <?php if (!(strcmp(0, $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsAccessLevels['ID']?>"<?php if (!(strcmp($row_rsAccessLevels['ID'], $row_rsCategory['writeaccess']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevels['accesslevel']?></option>
                    <?php
} while ($row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels));
  $rows = mysql_num_rows($rsAccessLevels);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevels, 0);
	  $row_rsAccessLevels = mysql_fetch_assoc($rsAccessLevels);
  }
?>
                </select>
                  <select name="groupwriteID" id="groupwriteID" class="group justmehide form-control">
                    <option value="0" <?php if (!(strcmp(0, $row_rsCategory['groupwriteID']))) {echo "selected=\"selected\"";} ?>>Any group</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], $row_rsCategory['groupwriteID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
                    <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
                </select><div class="justmeshow">Only you can add to this folder</div></td>
              </tr>
              <tr>
                <td align="right">Active:</td>
                <td><input <?php if (!(strcmp($row_rsCategory['active'],1))) {echo "checked";} ?> name="active" type="checkbox" id="active" value="1" /></td>
              </tr>
              <tr>
                <td align="right"><input type="hidden" name="modifieddatetime" id="modifieddatetime" />
                  <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
<input name="ID" type="hidden" id="ID" value="<?php echo $row_rsCategory['ID']; ?>" />
                <input name="referer" type="hidden" id="referer" value="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/documents/"; ?>" /></td>
                <td><button type="submit" class="btn btn-primary" >Save Changes</button></td>
              </tr>
              <tr>
                <td align="right">&nbsp;</td>
                <td><p class="text-muted"><em>Originally created by <?php echo $row_rsCategory['firstname']; ?> <?php echo $row_rsCategory['surname']; ?> at <?php echo date('g:ia',strtotime( $row_rsCategory['createddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsCategory['createddatetime'])); ?></em></p>
            <?php if(isset($row_rsCategory['modifieddatetime'])) { ?>
    <p  class="text-muted"><em>Last updated by <?php echo $row_rsCategory['modifiedbyname']; ?> at <?php echo date('g:ia',strtotime($row_rsCategory['modifieddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsCategory['modifieddatetime']));  ?></em></p><?php } ?></td>
              </tr>
      </table>
           
      <input type="hidden" name="MM_update" value="form1" />
    </form>
    
    <script>
<!--

var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCategory);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsAccessLevels);

mysql_free_result($rsFolders);

mysql_free_result($rsGroups);

mysql_free_result($rsInFolder);
?>
