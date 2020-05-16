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

function scramble($phrase) {
	if(strpos($phrase,"@")>0) { //email address
		$parts = explode("@", $phrase);
		$phrase = str_shuffle($parts[0])."@".$parts[1];
	} else {
		$phrase = ucwords(str_shuffle(strtolower($phrase)));
	}
	return $phrase;
}

$_POST['type'] = isset($_POST['type']) ? $_POST['type'] : 1;
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../../../Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Housekeeping - Find and Replace</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
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
	
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "SELECT ID, ".$field." FROM ".$table;	
	$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
	while($row = mysql_fetch_assoc($result)) {
		$new_field = ($_POST['type']==1) ? scramble($row[$field]) : "Anonymous";
		$update = "UPDATE ".$table." SET ".$field." = ".GetSQLValueString($new_field, "text")." WHERE ID = ".$row['ID'];
		mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		echo htmlentities($row[$field])."<br><strong>on row ".$row['ID']." changed to:</strong><br>".htmlentities($new_field)."<hr>"; $count++;
								
	}	
	echo "<p>Done. ".$count." replaced.</p>";
}
	?>
<form action="" method="post" id="form1">
  <table border="0" cellpadding="0" cellspacing="0" class="form-table">
    <tr>
      <td><label for="table">Table:</label></td>
      <td><label for="field">Field:</label></td>
      </tr>
    <tr>
      <td class="top"><?php mysql_select_db($database_aquiescedb, $aquiescedb);
$q = "SHOW TABLES FROM ".$database_aquiescedb;
$rs = mysql_query($q, $aquiescedb) or die(mysql_error());
$selected_table = isset($_POST['table']) ? $_POST['table'] : "";
 ?>
        <span id="spryselect1">
        <select name="table" id="table" onchange="updateFieldSelect()">
          <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?>.</option>
          <?php while ($current_table = mysql_fetch_array($rs)) { ?>
          <option<?php if($current_table[0] == $selected_table) echo " selected=\"selected\" "; ?> value="<?php echo $current_table[0]; ?>"><?php echo $current_table[0]; ?></option>
          <?php } ?>
        </select><br />
        <span class="selectRequiredMsg">Please select an item.</span></span></td>
      <td valign="top" id="tablefield">&nbsp;</td>
      </tr>
    <tr>
      <td><label for="submit"></label>
        <input type="submit" value="Obfuscate..." onclick="return confirm('Are you sure you want to replace all occurances? This cannot be undone.');" /></td>
      <td><p>
        <label>
          <input <?php if (!(strcmp($_POST['type'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="type" value="1" id="type_0" />
          Scramble</label>
      &nbsp;&nbsp;&nbsp;
        <label>
          <input <?php if (!(strcmp($_POST['type'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="type" value="2" id="type_1" />
          Replace with 'Anonymous'</label>
       
      </p></td>
    </tr>
    </table>
</form>
<script>
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
</script>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>