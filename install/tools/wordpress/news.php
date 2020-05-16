<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php 
@error_reporting(6143); // 0 = display no errors, 6143 display all
@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
	
	
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

$wp_prefix = isset($_GET['wp_prefix']) ? htmlentities($_GET['wp_prefix'],ENT_COMPAT, "UTF-8") : "wp"; 

$query = "SHOW TABLES LIKE '".$wp_prefix."_%'";
$result = mysql_query($query, $aquiescedb) or die(mysql_error());
if(mysql_num_rows($result)>0) {

$varCategory_rsWPNews = "0";
if (isset($_GET['fromcategoryID'])) {
  $varCategory_rsWPNews = $_GET['fromcategoryID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsWPNews = sprintf("SELECT ".$wp_prefix."_posts.*, ".$wp_prefix."_postmeta.meta_value, ".$wp_prefix."_postmeta.meta_key, ".$wp_prefix."_terms.name FROM ".$wp_prefix."_posts LEFT JOIN ".$wp_prefix."_postmeta ON ".$wp_prefix."_posts.ID =  ".$wp_prefix."_postmeta.post_id LEFT JOIN ".$wp_prefix."_term_relationships ON (".$wp_prefix."_term_relationships.object_id = ".$wp_prefix."_posts.ID) LEFT JOIN ".$wp_prefix."_terms ON (".$wp_prefix."_term_relationships.term_taxonomy_id = ".$wp_prefix."_terms.term_id) LEFT JOIN ".$wp_prefix."_term_taxonomy ON (".$wp_prefix."_term_relationships.term_taxonomy_id = ".$wp_prefix."_term_taxonomy.term_taxonomy_id) WHERE post_type = 'post' AND post_status = 'publish'  AND ".$wp_prefix."_term_taxonomy.taxonomy = 'Category' AND (%s  = 0 OR ".$wp_prefix."_terms.term_id = %s) GROUP BY ".$wp_prefix."_posts.ID ORDER BY post_date", GetSQLValueString($varCategory_rsWPNews, "int"),GetSQLValueString($varCategory_rsWPNews, "int"));
$rsWPNews = mysql_query($query_rsWPNews, $aquiescedb) or die(mysql_error());
$row_rsWPNews = mysql_fetch_assoc($rsWPNews);
$totalRows_rsWPNews = mysql_num_rows($rsWPNews);

//echo $query_rsWPNews; die($totalRows_rsWPNews);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = "SELECT * FROM newssection";
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsWPSections = "SELECT ".$wp_prefix."_terms.term_id, ".$wp_prefix."_terms.name FROM ".$wp_prefix."_terms LEFT JOIN ".$wp_prefix."_term_relationships ON (".$wp_prefix."_term_relationships.term_taxonomy_id = ".$wp_prefix."_terms.term_id) LEFT JOIN ".$wp_prefix."_term_taxonomy ON (".$wp_prefix."_term_relationships.term_taxonomy_id = ".$wp_prefix."_term_taxonomy.term_taxonomy_id) WHERE ".$wp_prefix."_term_taxonomy.taxonomy = 'Category' GROUP BY ".$wp_prefix."_terms.name ";
$rsWPSections = mysql_query($query_rsWPSections, $aquiescedb) or die(mysql_error());
$row_rsWPSections = mysql_fetch_assoc($rsWPSections);
$totalRows_rsWPSections = mysql_num_rows($rsWPSections);
} else {
	$error = "No WordPress tables exist with prefix ".$wp_prefix;
}

if(isset($_GET['delete'])) {
	$delete = "DELETE FROM news WHERE sectionID = ".GetSQLValueString($_GET['sectionID'].".", "int");
	mysql_query($delete, $aquiescedb) or die(mysql_error());
}
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Import WordPress</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!-- 
th, td {
	border: 1px solid rgb(153,153,153);
}
--></style><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>WordPress Posts to Full Bhuna News</h1>
 <?php if (isset($error)) { ?><p class="alert alert-danger" role="alert"><?php echo $error; ?></p>
  <?php } ?>
<p>1. import the WordPress Tables</p>
<form><?php if($totalRows_rsWPSections>0) { ?>
  <select name="fromcategoryID" id="fromcategoryID" onchange="this.form.submit();">
    <option value="0">From category...</option>
    <?php
do {  
?>
    <option value="<?php echo $row_rsWPSections['term_id']?>"<?php if (!(strcmp($row_rsWPSections['term_id'], $_GET['fromcategoryID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsWPSections['name']?></option>
    <?php
} while ($row_rsWPSections = mysql_fetch_assoc($rsWPSections));
  $rows = mysql_num_rows($rsWPSections);
  if($rows > 0) {
      mysql_data_seek($rsWPSections, 0);
	  $row_rsWPSections = mysql_fetch_assoc($rsWPSections);
  }
?>
  </select>
  <select name="sectionID">
   <option value="0">To category...</option>
  <?php do { ?>
    <option value="<?php echo $row_rsSections['ID']; ?>" <?php if($_GET['fromcategoryID']==$row_rsSections['ID']){echo "selected=\"selected\"";} ?> ><?php echo $row_rsSections['sectioname']; ?></option>
    <?php } while ($row_rsSections = mysql_fetch_assoc($rsSections)); ?></select>
<label> &nbsp;&nbsp;<input type="checkbox" name="import" value="1" /> 
Do import</label>&nbsp;&nbsp;
 |  &nbsp;&nbsp;<label><input type="checkbox" name="delete" value="1" /> Delete existing</label>&nbsp;&nbsp; |  &nbsp;&nbsp;<label><input type="checkbox" name="utf8" value="1"  <?php if(isset($_GET['utf8'])) echo "checked"; ?> /> UTF-8  encode</label>&nbsp;&nbsp; |  &nbsp;&nbsp;<label><input type="checkbox" name="nl2br" value="1" <?php if(isset($_GET['nl2br'])) echo "checked"; ?> /> Newline to &lt;br&gt; </label><?php } ?> <label>Prefix:<input name="wp_prefix" type="text" value="<?php echo isset($_GET['wp_prefix']) ? htmlentities($_GET['wp_prefix'],ENT_COMPAT, "UTF-8") : "wp"; ?>" size="10" maxlength="50"></label> <input value="Show posts" type="submit" onclick="this.form.import.value=1" /></form>
<?php if($totalRows_rsWPNews>0) { ?>
<table class="form-table" >
<tr><th>ID</th><th>Date</th>
<th>Title</th>
<th>Content</th>
<th>Name</th>
<th>Parent</th>
<th>Type</th>
<th>Status</th>
<th>Meta Key</th><th>Meta Value</th><th>Attach</th><th>Term</th></tr>
<?php do { ?>
<tr>
  <td><?php echo $row_rsWPNews['ID'];
  $content = isset($_GET['utf8']) ? utf8_encode($row_rsWPNews['post_content']) : $row_rsWPNews['post_content'];
	  $content = isset($_GET['nl2br']) ? nl2br($content) : $content;
  $select  = "SELECT ".$wp_prefix."_posts.* FROM ".$wp_prefix."_posts WHERE post_type = 'attachment' AND post_status = 'inherit'  AND  post_parent = ".$row_rsWPNews['ID']." LIMIT 1";
  $result = mysql_query($select, $aquiescedb) or die(mysql_error());

  $row = mysql_fetch_assoc($result);
 if(isset($_GET['import']) && $_GET['import']==1) { 
	$select_meta = "SELECT meta_value FROM ".$wp_prefix."_posts LEFT JOIN ".$wp_prefix."_postmeta ON ".$wp_prefix."_posts.ID =  ".$wp_prefix."_postmeta.post_id WHERE ".$wp_prefix."_postmeta.meta_key = 'additional_content'";
	 $result_meta = mysql_query($select_meta, $aquiescedb) or die(mysql_error());
	 $row_meta = mysql_fetch_assoc($result_meta);
	 
	
	  $insert = "INSERT INTO news (longID, title, summary, body,imageURL,sectionID, status, postedbyID, posteddatetime, modifieddatetime) VALUES(".GetSQLValueString($row_rsWPNews['post_name'], "text").",".GetSQLValueString($row_rsWPNews['post_title'], "text").",".GetSQLValueString($content, "text").",".GetSQLValueString($row_meta['meta_value'], "text").",".GetSQLValueString($row['guid'], "text").",".GetSQLValueString($_GET['sectionID'].".", "int").",1,0,".GetSQLValueString($row_rsWPNews['post_date'], "date").",".GetSQLValueString($row_rsWPNews['post_modified'], "date").")";
	  mysql_query($insert, $aquiescedb) or die(mysql_error());
	  //echo $insert;
 }
	  ?></td><td><?php echo $row_rsWPNews['post_date']; ?></td>
      <td><?php echo $row_rsWPNews['post_title']; ?></td>
      <td><?php echo $content; // htmlentities(substr($row_rsWPNews['post_content'],0,100)); ?></td>
      <td><?php echo $row_rsWPNews['post_name']; ?></td>
      <td><?php echo $row_rsWPNews['post_parent']; ?></td>
      <td><?php echo $row_rsWPNews['post_type']; ?></td>
       <td><?php echo $row_rsWPNews['post_status']; ?></td>
       <td><?php echo htmlentities($row_rsWPNews['meta_key']); ?></td>
        <td><?php echo htmlentities($row_rsWPNews['meta_value']); ?></td><td><?php 
		
				echo $row['guid'];
			 ?></td>
        
        <td><?php echo $row_rsWPNews['name']; ?></td>
        
    </tr>
  
  <?php } while ($row_rsWPNews = mysql_fetch_assoc($rsWPNews)); ?></table><?php } ?>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsWPNews);

mysql_free_result($rsSections);

mysql_free_result($rsWPSections);
?>
