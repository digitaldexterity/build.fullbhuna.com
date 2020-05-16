<?php require_once('../../../Connections/aquiescedb.php'); ?>
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

$table = (isset($_GET['set']) && $_GET['set'] == "version") ? "version" : "finish"; 
$linktable = (isset($_GET['set']) && $_GET['set'] == "version") ? "productwithversion" : "productwithfinish";

$varProductID_rsCheckboxes = "-1";
if (isset($_GET['productID'])) {
  $varProductID_rsCheckboxes = $_GET['productID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT product".$table.".ID, product".$table.".".$table."name AS name, productwith".$table.".ID AS checked FROM product".$table." LEFT JOIN productwith".$table." ON (product".$table.".ID = productwith".$table.".".$table."ID AND productwith".$table.".productID = ".GetSQLValueString($varProductID_rsCheckboxes, "int").") ORDER BY ordernum";
$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);

if(mysql_num_rows($result)>0) {
	while($row = mysql_fetch_assoc($result)) {
		$checked = isset($row['checked']) ? " checked=\"checked\" " : "";
		echo "<label style=\"white-space:nowrap\"><input type=\"checkbox\" value=\"".htmlentities($row['name'], ENT_COMPAT, "UTF-8")."\" id=\"versioncheckbox".$row['ID']."\" ".$checked." onClick=\"updateVersion(this.value,this.checked,'".$table."')\">&nbsp;".$row['name']."</label>&nbsp;&nbsp;&nbsp; ";
	}
} else {
	echo "<p>None entered.</p>";
}

mysql_free_result($result);


?>
