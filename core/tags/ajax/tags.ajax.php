<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php if(!isset($_SESSION['MM_UserGroup'])) die();
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

$blogentryID = (isset($_GET['blogentryID']) && intval($_GET['blogentryID'])> 0) ? intval($_GET['blogentryID']) : -1;
$eventgroupID = (isset($_GET['eventgroupID']) && intval($_GET['eventgroupID'])> 0) ? intval($_GET['eventgroupID']) : -1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTags = "SELECT tagged.ID, tag.tagname, blogentry.blogID, tagged.eventgroupID FROM tag  LEFT JOIN tagged ON (tag.ID = tagged.tagID) LEFT JOIN blogentry ON (blogentry.ID = tagged.blogentryID) LEFT JOIN eventgroup ON (eventgroup.ID = tagged.eventgroupID) WHERE (tagged.blogentryID = ".$blogentryID." OR tagged.eventgroupID = ".$eventgroupID.") ORDER BY tag.ordernum, tagged.ID";
$rsTags = mysql_query($query_rsTags, $aquiescedb) or die(mysql_error());
$row_rsTags = mysql_fetch_assoc($rsTags);
$totalRows_rsTags = mysql_num_rows($rsTags);

 if ($totalRows_rsTags > 0) { 
 echo "<strong>Tags:</strong> ";
  do { echo "<span class=\"tag\">";
  echo (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) ? "<span class=\"delete delete_tag\" data-taggedID=\"".$row_rsTags['ID']."\">[X]</span>" : "";
  echo "<a href=\"/blogs/blog.php?blogID=".$row_rsTags['blogID']."&tagID=".$row_rsTags['ID']."\">";
    echo $row_rsTags['tagname']; 
	 echo "</a></span>";
	 } while ($row_rsTags = mysql_fetch_assoc($rsTags));
	  } 
mysql_free_result($rsTags); 
//echo $_GET['eventgroupID'].$query_rsTags;
?>