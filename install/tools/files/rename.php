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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = "SELECT ID, body FROM article";
$rsArticles = mysql_query($query_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);
$totalRows_rsArticles = mysql_num_rows($rsArticles);
//(SELECT 'product' AS tbl, 'imageURL' AS fld, product.ID, product.imageURL AS filename, uploads.ID AS uploadID FROM product LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',product.imageURL) = uploads.newfilename) WHERE product.imageURL IS NOT NULL AND uploads.systemversion IS NULL ) 
//UNION (SELECT 'photos' AS tbl, 'imageURL' AS fld, photos.ID, photos.imageURL AS filename, uploads.ID AS uploadID FROM photos LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',photos.imageURL) = uploads.newfilename) WHERE photos.imageURL IS NOT NULL AND uploads.systemversion IS NULL ) ;

$limit = (isset($_GET['page']) && intval($_GET['page'])>1) ? " LIMIT ".((intval($_GET['page']-1)*1000)).", 1000" : " LIMIT 1000";


mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsALLfiles = "

(SELECT 'photos' AS tbl, 'imageURL' AS fld, photos.ID, photos.imageURL AS filename, uploads.ID AS uploadID FROM photos LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',photos.imageURL) = uploads.newfilename) WHERE photos.imageURL IS NOT NULL AND uploads.systemversion IS NULL )

UNION (SELECT 'documents' AS tbl, 'filename' AS fld, documents.ID, documents.filename AS filename, uploads.ID AS uploadID FROM documents LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',documents.filename) = uploads.newfilename) WHERE documents.filename IS NOT NULL AND uploads.systemversion IS NULL ) 

UNION (SELECT 'users' AS tbl, 'imageURL' AS fld, users.ID, users.imageURL AS filename, uploads.ID AS uploadID FROM users LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',users.imageURL) = uploads.newfilename) WHERE users.imageURL IS NOT NULL AND uploads.systemversion IS NULL ) 


UNION (SELECT 'news' AS tbl, 'imageURL' AS fld, news.ID, news.imageURL AS filename, uploads.ID AS uploadID FROM news LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',news.imageURL) = uploads.newfilename) WHERE news.imageURL IS NOT NULL AND uploads.systemversion IS NULL ) 

UNION (SELECT 'news' AS tbl, 'attachment1' AS fld, news.ID, news.attachment1 AS filename, uploads.ID AS uploadID FROM news LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',news.attachment1) = uploads.newfilename) WHERE news.attachment1 IS NOT NULL AND uploads.systemversion IS NULL ) 


UNION (SELECT 'news' AS tbl, 'imageURL2' AS fld, news.ID, news.imageURL2 AS filename, uploads.ID AS uploadID FROM news LEFT JOIN uploads ON (CONCAT('".UPLOAD_ROOT."',news.imageURL2) = uploads.newfilename) WHERE news.imageURL2 IS NOT NULL AND uploads.systemversion IS NULL ) 


".$limit;
$rsALLfiles = mysql_query($query_rsALLfiles, $aquiescedb) or die(mysql_error());
$row_rsALLfiles = mysql_fetch_assoc($rsALLfiles);
$totalRows_rsALLfiles = mysql_num_rows($rsALLfiles);
?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>'); // if not already loaded</script><style><!--
table {
	border-collapse:collapse;
}

table td {
	border: 1px solid #999;
}
--></style><script>
var table = new Array();
var field = new Array();
var id = new Array();
var oldname = new Array();
var newname = new Array();
var findreplace = new Array();
</script><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>File Rename</h1>
<p><?php echo $totalRows_rsALLfiles." results"; $i = 0;  ?></p>

<table border="0" cellpadding="0" cellspacing="0" class="form-table">
  <tr>
  <td>ID</td>
    <td>tbl</td>
    <td>imageURL</td>
    <td>New Name</td>
  </tr>
  
  <?php  do { ?>
    <tr>
     <td><?php echo $row_rsALLfiles['ID']; ?></td>
      <td><?php echo $row_rsALLfiles['tbl']; ?></td>
      <td><?php echo $row_rsALLfiles['filename']; ?></td>
      <td><?php if($row_rsALLfiles['filename'] !="" && strpos($row_rsALLfiles['filename'],"/")===false)  { // in root
	  $i ++;
		  $parts = explode("_",$row_rsALLfiles['filename']);
		  $time = $parts[0];
		  if($time >1000000000) { // we have a time 
		  
		   $newname =  date("Y", $time)."/".date("m", $time)."/".date("d", $time)."/".$row_rsALLfiles['filename']; } // have time
		   else {$newname = "nodate/".$row_rsALLfiles['filename']; }  echo $newname; ?>
      <script>
	  id[<?php echo $i; ?>] = '<?php echo $row_rsALLfiles['ID']; ?>';
	   table[<?php echo $i; ?>] = '<?php echo $row_rsALLfiles['tbl']; ?>';
	   field[<?php echo $i; ?>] = '<?php echo $row_rsALLfiles['fld']; ?>';
	  oldname[<?php echo $i; ?>] = '<?php echo $row_rsALLfiles['filename']; ?>';
	  findreplace[<?php echo $i; ?>] = 0;
	   newname[<?php echo $i; ?>] = '<?php echo $newname; ?>';
	  </script><div id="result<?php echo $i; ?>"></div><?php 
	  } // in root  ?></td>
    </tr>
    <?php } while ($row_rsALLfiles = mysql_fetch_assoc($rsALLfiles));
	?>
</table>

<table border="0" cellpadding="0" cellspacing="0" class="form-table">
  <tr>
    <td>ID</td>
    <td>body</td>
  </tr>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rsArticles['ID']; ?></td>
      <td><?php // echo htmlentities($row_rsArticles['body'], ENT_COMPAT, "UTF-8"); 
	  $images = preg_match_all("/<img .*?(?=src)src=\"([^\"]+)\"/si",$row_rsArticles['body'],$matches); 
	 if(count($matches[1])>0) { // is links ?><table class="form-table">
	  <?php foreach($matches[1] as $key => $src) { // for each?><tr><td>
      <?php echo $src; $newname = "&nbsp;"; ?></td><td>
		 <?php  if(substr($src,0,9) == "/Uploads/") { // is local
		  $src = str_replace("/Uploads/","",$src); //remove Uploads folder from filename ?>
          <?php 
		   echo htmlentities($src, ENT_COMPAT, "UTF-8")."<br>"; 
		   
		   if($src !="" && strpos($src,"/")===false)  { // in root
	  $i ++;
		  $parts = explode("_",$src);
		  $time = strlen($parts[0])>  5 ? $parts[0] : $parts[1] ;
		  if($time >1000000000) { // we have a time 
		  
		   $newname =  date("Y", $time)."/".date("m", $time)."/".date("d", $time)."/".$src; } // have time
		   else {$newname = "nodate/".$src; }  echo $newname; ?>
           <?php if(isset($_GET['links'])) { // process ?>
      <script>
	  id[<?php echo $i; ?>] = '<?php echo $row_rsArticles['ID']; ?>';
	   table[<?php echo $i; ?>] = 'article';
	   field[<?php echo $i; ?>] = 'body';
	  oldname[<?php echo $i; ?>] = '<?php echo $src; ?>';
	  findreplace[<?php echo $i; ?>] = 1;
	   newname[<?php echo $i; ?>] = '<?php echo $newname; ?>';
	  </script><div id="result<?php echo $i; ?>"></div><?php } // end process
		  
	  } // in root ?>
      
     <?php   } // is local
	 echo $newname; ?>
     </td>  </tr>
	
	<?php   } // end foreach
	 ?></table>  <?php } // is links ?></td>
    </tr>
    <?php } while ($row_rsArticles = mysql_fetch_assoc($rsArticles)); ?>
</table>
<form method="get" id="form1" ><input name="" type="submit" value="Submit..." /><input name="rename" type="hidden" value="true" />
  <label>
    <input type="checkbox" name="links" id="links" />
    Include embedded souce and links</label>
</form>
<?php if(isset($_GET['rename'])) { ?>
		<script>
		var i=0;
		function renameFile() {
		i++;
        var tester = $.ajax({
                url: "rename.ajax.php?table="+table[i]+"&field="+field[i]+"&ID="+id[i]+"&oldfilename="+oldname[i]+"&newfilename="+newname[i]+"&findreplace="+findreplace[i],
                success: function (data) {
                    document.getElementById('result'+i).innerHTML = data;
                    renameFile(); 
                }
            });
   }
		
		renameFile();
		
		</script>
	<?php } ?>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticles);

mysql_free_result($rsALLfiles);
?>
