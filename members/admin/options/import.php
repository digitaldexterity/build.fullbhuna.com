<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
<?php require_once('../../includes/userfunctions.inc.php'); ?>
<?php require_once('../../../directory/includes/directoryfunctions.inc.php'); ?>
<?php require_once('../../../location/includes/locationfunctions.inc.php'); ?>
<?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php require_once('../includes/mergeUsers.inc.php'); 

set_time_limit(600); // 10 mins
ini_set("session.gc_maxlifetime","10800");
ini_set("max_execution_time","600"); // 10 mins
ini_set("max_input_time","600"); // 10 mins

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

if(isset($_GET['deletelast'])){

$deleteafter = isset($_GET['deleteafter']) ? 
$_GET['deleteafter'] : date('Y-m-d H:i:s', strtotime("30 MINUTES AGO"));

$delete= "DELETE users, usergroupmember FROM users LEFT JOIN usergroupmember ON (users.ID = usergroupmember.userID) WHERE addedbyID = ".$adminUser['ID']." AND dateadded >=".GetSQLValueString($deleteafter, "text");
mysql_query($delete, $aquiescedb) or die(mysql_error());
$deleted = mysql_affected_rows() ;

$msg= $deleted. " users deleted." ;
}


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroups = "SELECT ID, groupname FROM usergroup ORDER BY groupname ASC";
$rsGroups = mysql_query($query_rsGroups, $aquiescedb) or die(mysql_error());
$row_rsGroups = mysql_fetch_assoc($rsGroups);
$totalRows_rsGroups = mysql_num_rows($rsGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT ID, title FROM region WHERE statusID = 1 ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTypes = "SELECT * FROM usertype ORDER BY ID ASC";
$rsUserTypes = mysql_query($query_rsUserTypes, $aquiescedb) or die(mysql_error());
$row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
$totalRows_rsUserTypes = mysql_num_rows($rsUserTypes);
 

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded) 
	&& isset($uploaded["filename"][0]["newname"]) 
	&& $uploaded["filename"][0]["newname"]!="") { // upload	
	$filename = UPLOAD_ROOT.$uploaded["filename"][0]["newname"];
	if(is_readable($filename) && !isset($error)) { // file OK
		ini_set('auto_detect_line_endings', true);
		$handle = fopen($filename,"r");
		if($handle) { // handle
			$import_started = date('Y-m-d H:i:s');
			$linecount = 0; $log = "";
			$columns = array();
			// do integrity check first
			foreach($_POST['column'] as $key => $column) {
				if($column != "") {	array_push($columns,$column); }
			}
			while($fields = fgetcsv($handle,65535)) { // get line
				$linecount++; 
				if(count($fields) != count($columns)) {	
					$error = "<strong>Column count mismatch on line ".$linecount.".</strong><br /><br />Please check the integrity of your CSV file: each row must have the same number  of items. For example, commas within column items will cause problems."; break; 
				}
			} // end get line
			if(!isset($error)) { // no errors
				writeLog("*********************** BEGIN IMPORT ********************************");
				$linecount = 0;
				$handle = fopen($filename,"r");
				while($fields = fgetcsv($handle,65535)) { // get line
					writeLog("GET LINE");
					$linecount++;
					if(!isset($_POST['ignorefirst']) || $linecount>1) { // first line
						$sql = ""; $lsql = ""; $dsql = "";
						// build SQL
						$sql = "INSERT INTO users ("; // first part of INSERT statement
						foreach($columns as $key1 => $column) {
							if($column !="-1") { // do import
								if($column=="firstname" || $column == "surname" || $column == "email" || $column == "salutation" || $column == "telephone" || $column == "jobtitle" || $column == "username" || $column == "dob" || $column == "password") { // user sql
									 $sql .= "`".$column."`,";
								} else if ($column=="fullname") { // full name
									$sql .= "firstname,surname,"; 
								} else if($column=="name" || $column == "url") {
									$dsql .= "`".$column."`,";
								} else if ($column=="fulladdress") { 
									$lsql .= "address1,address2,address3,address4,address5,postcode,";
								} else { // normal address either directory or location sql
								
								if(isset($_POST['directory'])) {
										$column = ($column == "telephone1") ? "telephone" : $column;
										$dsql .= "`".$column."`,";
									} else {
										$lsql .= "`".$column."`,";
									}
								}
							} // end do import
						}
						$sql .= "emailoptin, usertypeID, regionID, addedbyID, dateadded) VALUES(";
						$lsql = ($lsql == "") ? "" : "INSERT INTO location (".$lsql."categoryID, userID, createdbyID, createddatetime) VALUES (";
						$dsql = ($dsql == "") ? "" : "INSERT INTO directory (".$dsql."categoryID, userID, createdbyID, createddatetime) VALUES (";
						foreach($fields as $key2 => $field) { // count through fields
							if($columns[$key2] != "-1") { // do import
								if($columns[$key2]=="firstname" || $columns[$key2] == "surname" || $columns[$key2] == "salutation" || $columns[$key2] == "telephone"  || $columns[$key2] == "jobtitle" || $columns[$key2] == "username" || $columns[$key2] == "dob" ) { // user sql
									$sql .= GetSQLValueString($field,"text").","; 
								} else if ($columns[$key2] == "email") {
									$sql .= GetSQLValueString(preg_replace("/[^A-Za-z0-9\.@_\,\-\s]/", "", $field),"text").",";
								} else if ($columns[$key2] == "password") {
									$sql .= GetSQLValueString(md5(preg_replace("/\s+/", "", $field)),"text").",";
								} else if ($columns[$key2] == "fullname") {
									$names = explode(" ",$field);
									$sql .= isset($names[0]) ? GetSQLValueString($names[0],"text")."," : "'',";
									$sql .= isset($names[1]) ? GetSQLValueString($names[1],"text")."," : "'',";
								} else if ($columns[$key2] == "name" || $columns[$key2] == "url") {
									$dsql .= GetSQLValueString($field,"text").",";
								} else if ($columns[$key2] == "fulladdress") {
									$address = explode("\n",$field);
									$lsql .= isset($address[0]) ? GetSQLValueString($address[0],"text")."," : "'',";
									$lsql .= isset($address[1]) ? GetSQLValueString($address[1],"text")."," : "'',";
									$lsql .= isset($address[2]) ? GetSQLValueString($address[2],"text")."," : "'',";
									$lsql .= isset($address[3]) ? GetSQLValueString($address[3],"text")."," : "'',";
									$lsql .= isset($address[4]) ? GetSQLValueString($address[4],"text")."," : "'',";
									$lsql .= isset($address[5]) ? GetSQLValueString($address[5],"text")."," : "'',";
								} else {// directory or location sql
									if(isset($_POST['directory'])) {
										$dsql .= GetSQLValueString($field,"text").","; 
									} else {
										$lsql .= GetSQLValueString($field,"text").","; 
									}
								}
							} // import
						} // end count through fields
						$regionID = isset($_POST['regionID']) ? intval($_POST['regionID']) : 1;
						$sql .= isset($_POST['emailoptin']) ? 1 : 0;
						$sql .= ",".GetSQLValueString($_POST['usertypeID'],"int").",".$regionID.",".GetSQLValueString($_POST['createdbyID'],"int").", NOW())";
	
						// execute SQL
						$result = mysql_query($sql, $aquiescedb) or die(mysql_error().$sql);
						$userID = mysql_insert_id();
						writeLog($sql);
						if(isset($_POST['login'])) {
							setUsernamePassword($userID);
						}					
						if($lsql !="") { // add address
							$lsql .= "0,".$userID.",".GetSQLValueString($_POST['createdbyID'],"int").", NOW())";
							$result = mysql_query($lsql, $aquiescedb) or die(mysql_error().$lsql);
							writeLog($lsql); $log .= $lsql."<br>";
							$locationID = mysql_insert_id();
							addUserToLocation($userID, $locationID, $adminUser['ID'], true, true);
						} // end add address
	
	
						if($dsql !="") { // add directory
							$dsql .= "0,".$userID.",".GetSQLValueString($_POST['createdbyID'],"int").", NOW())";
							$result = mysql_query($dsql, $aquiescedb) or die(mysql_error().$dsql);
							writeLog($dsql); 
							$directoryID = mysql_insert_id();
							addUserToDirectory($userID, $directoryID, $adminUser['ID'], false, true);
						} // end add directory
						// group?
						if(isset($_POST['groupID']) && $_POST['groupID'] > 0) {
							addUsertoGroup($userID, $_POST['groupID'], $adminUser['ID'], "", true);
						}
					}// first line
				} // end get line
				writeLog("*********************** END IMPORT ********************************");
				//mergeUsersSameEmail();
				// go and clean up...
				$returnURL = $_SERVER['PHP_SELF']."?import_started=".urlencode($import_started);
				$url = "../remove_duplicates.php?returnURL=".urlencode($returnURL);
				
				header("location: ".$url); exit;
				
			} // no errors
			
		} else { // file not OK
			$error = "Could not find the uploaded file: ".$filename;
		}
	} else { // read not OK
		$error = "Could not read the uploaded file: ".$filename;
	}
} // end upload

function selectMenu($key) {
	$html ='<select name="column['.$key.']" class="form-control" >
            <option value="" '; if (!(strcmp("", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>No data</option>
            <option value="-1" '; if (!(strcmp("-1", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Omit this column</option>
            <option value="salutation" '; if (!(strcmp("salutation", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Title</option>
            <option value="firstname" '; if (!(strcmp("firstname", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>First name</option>
            <option value="surname" '; if (!(strcmp("surname", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Surname</option>
            <option value="fullname" '; if (!(strcmp("fullname", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Full name</option>
            <option value="email" '; if (!(strcmp("email", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>email</option>
			
			
			<option value="username" '; if (!(strcmp("username", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Username</option>
			
			
			<option value="password" '; if (!(strcmp("password", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Password</option>
			
            <option value="jobtitle" '; if (!(strcmp("jobtitle", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>'.$row_rsPreferences["text_role"].'</option>
            <option value="fulladdress" '; if (!(strcmp("fulladdress", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Full address</option>
            <option value="address1" '; if (!(strcmp("address1", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Address 1</option>
            <option value="address2" '; if (!(strcmp("address2", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Address 2</option>
            <option value="address3" '; if (!(strcmp("address3", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Address 3</option>
            <option value="address4" '; if (!(strcmp("address4", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Address 4</option>
            <option value="address5" '; if (!(strcmp("address5", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Address 5</option>
            <option value="postcode" '; if (!(strcmp("postcode", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Postcode</option>
            <option value="telephone1" '; if (!(strcmp("telephone1", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Telephone</option><option value="fax" '; if (!(strcmp("fax", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Fax</option>
            <option value="telephone" '; if (!(strcmp("telephone", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Mobile telephone</option>
			<option value="dob" '; if (!(strcmp("dob", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Date of birth</option>
            <option value="name" '; if (!(strcmp("name", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Company</option>
            <option value="url" '; if (!(strcmp("url", @$_REQUEST["column"][$key]))) { $html .= 'selected="selected"';} $html .='>Web site</option>
			
			
          </select>';
		  return $html;
}

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Import Users"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>

 var fb_keepAlive = true;
 </script>
<script src="/core/scripts/formUpload.js"></script>
<style >
<!--
<?php if($totalRows_rsRegions==0) {
?> #regionID {
 display: none;
}
<?php
}
if($totalRows_rsGroups==0) {
?> #groupID {
 display: none;
}
<?php
}
?>
-->
</style>
<link href="../../css/membersDefault.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
        <div class="page users">
    <form action="import.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
      <h1><i class="glyphicon glyphicon-user"></i> Import Users</h1>
      <?php require_once('../../../core/includes/alert.inc.php'); ?>
      <p>
        <label>1. Get your data ready in the form of a CSV file and select:
          <input type="file" name="filename" id="filename" />
        </label>
        <label>
          <input type="checkbox" name="ignorefirst" id="ignorefirst" <?php if(isset($_REQUEST['ignorefirst'])) echo "checked"; ?>>
          Ignore first line (if column headers)</label>
      </p>
      <p>2. Choose the columns to import below:</p>
      <table class="form-table">
        <tr>
          <td>Column 1 (A):</td>
          <td>Column 2 (B):</td>
          <td>Column 3 (C):</td>
          <td>Column 4 (D):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(1); ?></td>
          <td><?php echo selectMenu(2); ?></td>
          <td><?php echo selectMenu(3); ?></td>
          <td><?php echo selectMenu(4); ?></td>
        </tr>
        <tr>
          <td>Column 5 (E):</td>
          <td>Column 6 (F):</td>
          <td>Column 7 (G):</td>
          <td>Column 8 (H):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(5); ?></td>
          <td><?php echo selectMenu(6); ?></td>
          <td><?php echo selectMenu(7); ?></td>
          <td><?php echo selectMenu(8); ?></td>
        </tr>
        <tr>
          <td>Column 9 (I):</td>
          <td>Column 10 (J):</td>
          <td>Column 11 (K):</td>
          <td>Column 12 (L):</td>
        </tr>
        <tr>
          <td><?php echo selectMenu(9); ?></td>
          <td><?php echo selectMenu(10); ?></td>
          <td><?php echo selectMenu(11); ?></td>
          <td><?php echo selectMenu(12); ?></td>
        </tr>
      </table>
     
      <p>
        <label>
          <input <?php if (!(strcmp(@$_REQUEST['directory'],1))) {echo "checked=\"checked\"";} ?> name="directory" type="checkbox" id="directory" value="1" />
          Address is for company</label>
      </p>
      <p>3. Choose your options:</p>
      <p class="form-inline">
        <select name="usertypeID" id="usertypeID" class="form-control">
          <option value="1" <?php if (!(strcmp(1, @$_REQUEST['usertypeID']))) {echo "selected=\"selected\"";} ?>>Choose member type...</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsUserTypes['ID']?>"<?php if (!(strcmp($row_rsUserTypes['ID'], @$_REQUEST['usertypeID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserTypes['name']?></option>
          <?php
} while ($row_rsUserTypes = mysql_fetch_assoc($rsUserTypes));
  $rows = mysql_num_rows($rsUserTypes);
  if($rows > 0) {
      mysql_data_seek($rsUserTypes, 0);
	  $row_rsUserTypes = mysql_fetch_assoc($rsUserTypes);
  }
?>
        </select>
        <select name="groupID" id="groupID" class="form-control">
          <option value="0" <?php if (!(strcmp(0, @$_REQUEST['groupID']))) {echo "selected=\"selected\"";} ?>>Add to group...</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsGroups['ID']?>"<?php if (!(strcmp($row_rsGroups['ID'], @$_REQUEST['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsGroups['groupname']?></option>
          <?php
} while ($row_rsGroups = mysql_fetch_assoc($rsGroups));
  $rows = mysql_num_rows($rsGroups);
  if($rows > 0) {
      mysql_data_seek($rsGroups, 0);
	  $row_rsGroups = mysql_fetch_assoc($rsGroups);
  }
?>
        </select>
        <select name="regionID" id="regionID" class="form-control">
          <option value="0" <?php if (!(strcmp(0, @$_REQUEST['regionID']))) {echo "selected=\"selected\"";} ?>>Add to site...</option>
          <option value="0" <?php if (!(strcmp(0, @$_REQUEST['regionID']))) {echo "selected=\"selected\"";} ?>>All sites</option>
          <?php
do {  
?>
          <option value="<?php echo $row_rsRegions['ID']?>"<?php if (!(strcmp($row_rsRegions['ID'], @$_REQUEST['regionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsRegions['title']?></option>
          <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
        </select>
        <label>
          <input name="emailoptin" type="checkbox" id="emailoptin" value="1" checked="checked" />
          email opt in</label>&nbsp;&nbsp;&nbsp;
        <label>
          <input type="checkbox" name="login" id="login" />
          Auto-generate username and password</label>
      </p>
      <p>
        <button type="submit" class="btn btn-primary" >Submit</button>
        <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $adminUser['ID']; ?>" />
        
        <a href="import.php?deletelast=true" class="btn btn-danger" onClick="return confirm('Are you sure you want to delete last import? Use with caution if other users added after import. This cannot be undone.')">Delete Last Import</a>
      </p>
    </form></div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsGroups);

mysql_free_result($rsRegions);

mysql_free_result($rsUserTypes);
?>
