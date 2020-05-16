<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
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
?><?php
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
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../../../Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Housekeeping - Find and Replace</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../../SpryAssets/SpryValidationSelect.js"></script>
<script>
function getData(url,divID,loadingDIV,loadingHTML) // loading vars added later and optional
{
	var XMLHttpRequestObject = false;
	if (window.XMLHttpRequest) {
	XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
	XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHttp");
	}
	if(XMLHttpRequestObject) {
		if(typeof(divID)!=="undefined") {
			var obj = document.getElementById(divID);		
			loadingDIV = typeof(loadingDIV)==="undefined" ? obj : document.getElementById(loadingDIV); 
			loadingHTML = typeof(loadingHTML)==="undefined" ? "<img src='/core/images/loading_16x16.gif' width='16' height='16' border='0' style='vertical-align:middle;'>" : loadingHTML;
			if(loadingHTML!="") { loadingDIV.innerHTML = loadingHTML; }
		}
		XMLHttpRequestObject.open("GET", url);
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if (XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200) {
				if(typeof(obj)!=="undefined") {
					loadingDIV.innerHTML = "";
					obj.innerHTML = XMLHttpRequestObject.responseText;
				}
			delete XMLHttpRequestObject;
			XMLHttpRequestObject = null;
			}
		}
		XMLHttpRequestObject.send(null);
	}
}

function addListener(type,callback,obj) // handles cross browser add listeners
{
	obj = (typeof(obj) === "undefined") ? window : obj; // backward compat as function initially has 2 args
	if(obj.addEventListener) {
		obj.addEventListener(type, callback, false); //FF
	} else if (obj.attachEvent) {
		obj.attachEvent("on" +type, callback,false); //IE
	}
}

addListener("load",init);

function init() {
	updateFieldSelect();
}

function updateFieldSelect() {
	getData('/install/upgrade/housekeeping/includes/tablefields.inc.php?table='+document.getElementById('table').value<?php echo isset($_POST['field']) ? "+'&selectedfield=".$_POST['field']."'" : ""; ?>,'tablefield');
}

</script>
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationSelect.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>Housekeeping - Find &amp; Replace...</h1>
<?php if(isset($_POST['table'])) {
	$count=0;
	$field = preg_replace("/[^A-Za-z0-9\.@_\-]/", "", $_POST['field']);
	$table = preg_replace("/[^A-Za-z0-9\.@_\-]/", "", $_POST['table']);
	$find = isset($_POST['utf_decode']) ? utf8_decode($_POST['find']) : $_POST['find'];
	$replace = isset($_POST['utf_decode']) ? utf8_decode($_POST['replace']) : $_POST['replace'];
	
	if (PHP_VERSION < 6) {
    	$find = get_magic_quotes_gpc() ? stripslashes($find) : $find;
		$replace = get_magic_quotes_gpc() ? stripslashes($replace) : $replace;
  	}
	mysql_select_db($database_aquiescedb, $aquiescedb);
	// GET NAME OF PRIMARY KEY
	$sql = "SELECT `COLUMN_NAME`
FROM `information_schema`.`COLUMNS`
WHERE (`TABLE_SCHEMA` = '".$database_aquiescedb."')
  AND (`TABLE_NAME` = '".$table."')
  AND (`COLUMN_KEY` = 'PRI');";
	$result = mysql_query($sql, $aquiescedb) or die(mysql_error().": ".$sql);
	$num_rows = mysql_num_rows($result );
	
	if($num_rows==0) die("Can't find a primary key on table: ".$table);
	$pk = mysql_fetch_assoc($result);
	
	$select = "SELECT `".$pk['COLUMN_NAME']."`, `".$field."` FROM `".$table.'`';
	if(isset($_POST['verbose'])) {
		echo $find ."=".$_POST['find']."<br>";
		echo $select."<br>";
	}
	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	$num_rows = mysql_num_rows($result );
	if(isset($_POST['verbose'])) {
		echo $num_rows." rows found.<br>";
	}
	if($num_rows>0) {
	while($row = mysql_fetch_assoc($result)) {
		$new= str_replace($find, $replace, $row[$field]);
		if($new !=$row[$field]) {
			$update = "UPDATE `".$table."` SET `".$field."` = ".GetSQLValueString($new, "text")." WHERE `".$pk['COLUMN_NAME']."` = ".$row[$pk['COLUMN_NAME']];
			if(!isset($_POST['dryrun'])) {
				mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
			} else {
				echo "DRY RUN<br>";
			}
			if(isset($_POST['verbose'])) {
		echo htmlentities($row[$field])."<br><strong>on row ".$row[$pk['COLUMN_NAME']]." changed to:</strong><br>".htmlentities($new)."<hr>"; 
			}
			$count++;
		}
						
	}// end while
	} // is rows
	echo "<p>Done. ".$count." replaced.</p>";
}
	?>
<form action="" method="post" id="form1">
  <table class="form-table">
    <tr>
      <td><label for="table">Table:</label></td>
      <td><label for="field">Field:</label></td>
      </tr>
    <tr>
      <td class="top"><?php mysql_select_db($database_aquiescedb, $aquiescedb);
$q = "SHOW TABLES";
$rs = mysql_query($q, $aquiescedb) or die(mysql_error());
//die($q.mysql_num_rows($rs));
 ?>
        <span id="spryselect1">
        <select name="table" id="table" onchange="updateFieldSelect()" class="form-control">
          <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?>.</option>
          <?php while ($current_table = mysql_fetch_array($rs)) { ?>
          <option<?php if(isset($_POST['table']) && $current_table[0] == $_POST['table']) { echo " selected=\"selected\" "; } ?> value="<?php echo $current_table[0]; ?>"><?php echo $current_table[0]; ?></option>
          <?php } ?>
        </select><br />
        <span class="selectRequiredMsg">Please select an item.</span></span></td>
      <td valign="top" id="tablefield">&nbsp;</td>
      </tr>
    <tr>
      <td><label for="find">Find:</label></td>
      <td><label for="replace">Replace:</label></td>
      </tr>
    <tr>
      <td class="top"><span id="sprytextarea1">
        <textarea name="find" id="find" cols="45" rows="5" class="form-control"><?php echo isset($find) ? htmlentities($find, ENT_COMPAT, "UTF-8") : ""; ?></textarea>
        <span class="textareaRequiredMsg">A value is required.</span></span></td>
      <td class="top">
        <textarea name="replace" id="replace" cols="45" rows="5" class="form-control"><?php echo isset($replace) ? htmlentities($replace, ENT_COMPAT, "UTF-8") : ""; ?></textarea></td>
      </tr>
    <tr>
      <td colspan="2"><label for="submit"></label>
        <button type="submit" class="btn btn-primary" onclick="if(!document.getElementById('dryrun').checked) { return confirm('Are you sure you want to replace all occurances? This cannot be undone.'); }">Replace...</button>
        <label>
          <input type="checkbox" name="dryrun" id="dryrun" <?php if(isset($_POST['dryrun'])) { echo "checked \"checked\""; } ?> />
          Dry run</label>
        &nbsp;&nbsp;
        <label>
          <input type="checkbox" name="verbose" id="verbose" <?php if(isset($_POST['verbose'])) { echo "checked \"checked\""; } ?> />
          Verbose</label>
        
        &nbsp;&nbsp;
        <label>
          <input type="checkbox" name="utf_decode" id="utf_decode" <?php if(isset($_POST['utf_decode'])) { echo "checked \"checked\""; } ?> />
          UTF Decode</label>
        
      </td>
      </tr>
    </table>
</form>
<script>
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
</script>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>