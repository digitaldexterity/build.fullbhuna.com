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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsArticles = "SELECT * FROM article";
$rsArticles = mysql_query($query_rsArticles, $aquiescedb) or die(mysql_error());
$row_rsArticles = mysql_fetch_assoc($rsArticles);
$totalRows_rsArticles = mysql_num_rows($rsArticles);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProducts = "SELECT * FROM product";
$rsProducts = mysql_query($query_rsProducts, $aquiescedb) or die(mysql_error());
$row_rsProducts = mysql_fetch_assoc($rsProducts);
$totalRows_rsProducts = mysql_num_rows($rsProducts);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductDetails = "SELECT * FROM productdetails";
$rsProductDetails = mysql_query($query_rsProductDetails, $aquiescedb) or die(mysql_error());
$row_rsProductDetails = mysql_fetch_assoc($rsProductDetails);
$totalRows_rsProductDetails = mysql_num_rows($rsProductDetails);


function articleLink($articleID=0, $articlelongID="", $sectionID=0, $sectionlongID="", $parentsectionID = 0, $parentsectionlongID = "", $page=0) 
{ global $mod_rewrite;
	
	if((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && ($articleID == 0 || $articlelongID !="") && ($parentsectionID==0 || $parentsectionlongID != "") && ($sectionID==0 || $sectionlongID !="")) { // use mod rewrite
		$url = "/";
		//$url .= ($parentsectionlongID !="") ? $parentsectionlongID."/" : "";
		// to make .htaccess simpler not using parent categories in url for now
		$url .= $sectionlongID."/";
		$url .= ($articlelongID!="") ? $articlelongID."/" : "";	
		$url .= ($page>0) ? "page".$page : "";	
	} else {

		
		$url = ($articleID >0) ? "/articles/article.php?sectionID=".$sectionID."&articleID=".$articleID : "/articles/index.php?sectionID=".$sectionID;
		$url .= ($page>0) ? "?pageNum_rsArticles=".$page : "";
	}
	return $url;
}

if(isset($_POST['start'])) {
	$count = 0;
	
	
	 if($totalRows_rsArticles>0) { // if articles
		 do { 
		 	$oldbody = $row_rsArticles['body'];
			$newbody = $oldbody;
			 $matchstr = '/<a\s+.*?href=[\"\'\s]?(.*?)>(.*?)<\/a>/i';
			preg_match_all($matchstr ,$row_rsArticles['body'], $matches); // find links in body
			foreach($matches[1] as $key=>$link) { // count through links
			
			$oldlink= trim($link,"\"");
			$link = str_replace("amp;","",$oldlink);
				$linkparts = explode("?sectionID=",$link);
				if(isset($linkparts[1])) { // is FULL article link
					$ids = explode("&articleID=",$linkparts[1]);
					$select = "SELECT article.longID, articlesection.longID AS sectionlongID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.ID =".intval($ids[1]);
					$result = mysql_query($select, $aquiescedb) or die(mysql_error()." ".$select);
					$row = mysql_fetch_assoc($result);
					$newlink = articleLink($articleID=0, $row['longID'], $sectionID=0, $row['sectionlongID']);
					
					$newbody = str_replace($oldlink, $newlink, $oldbody); 
					$update = "UPDATE article SET body = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsArticles['ID'];	 
			  		$result = mysql_query($update, $aquiescedb) or die( $update." ".mysql_error());
					echo "PAGE".$row_rsArticles['ID']." : ".$link ."=>".$newlink."<br><br>";
					$count++;
				
				} 
				$linkparts = explode("?articleID=",$link);
				if(isset($linkparts[1])) { // is LEGACY article link
					
					$select = "SELECT article.longID, articlesection.longID AS sectionlongID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.ID =".intval($linkparts[1]);
					$result = mysql_query($select, $aquiescedb) or die(mysql_error()." ".$select);
					$row = mysql_fetch_assoc($result);
					$newlink = articleLink($articleID=0, $row['longID'], $sectionID=0, $row['sectionlongID']);
					
					$newbody = str_replace($oldlink, $newlink, $oldbody); 
					
					$update = "UPDATE article SET body = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsArticles['ID'];	 
			  		$result = mysql_query($update, $aquiescedb) or die( $update." ".mysql_error());
					echo "OLDPAGE".$row_rsArticles['ID']." : ".$link ."=>".$newlink."<br><br>";
					
					$count++;
				
				} 
				$oldbody = $newbody;
		  
			} // end count through links
		 } while ($row_rsArticles = mysql_fetch_assoc($rsArticles));
	 } // if articles
 
 
 
 	/*  if($totalRows_rsProducts>0) {
		  do { 
	 $newbody = str_replace($_POST['olddomain'],$_POST['newdomain'],$row_rsProducts['description']); 
	  $update = "UPDATE products SET description = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsProducts['ID'];	 
	  $result = mysql_query($update, $aquiescedb) or die($update." ".mysql_error());
	  $count = $count + mysql_affected_rows();
 } while ($row_rsProducts = mysql_fetch_assoc($rsProducts));
	  }
 
 
 
 	 if($totalRows_rsProductDetails>0) {
		 do { 
	 $newbody = str_replace($_POST['olddomain'],$_POST['newdomain'],$row_rsProductDetails['tabtext']); 
	  $update = "UPDATE productdetails SET tabtext = ".GetSQLValueString($newbody,"text")." WHERE ID = ".$row_rsProductDetails['ID'];	 
	  $result = mysql_query($update, $aquiescedb) or die($update." ".mysql_error());
	  $count = $count + mysql_affected_rows();
 } while ($row_rsProductDetails = mysql_fetch_assoc($rsProductDetails));
	 }
 */
 
 $alert = "Complete. Updated links: ".$count;
}

?>
<!DOCTYPE html>
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - URL Friendly LInks</title>
<!-- InstanceEndEditable -->
<?php require_once('../../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
<h1>URL Friendly Links</h1>
<?php if(isset($alert)) { ?>
<p class="alert warning alert-warning" role="alert"><?php echo $alert; ?></p>
<?php } else { ?>
<p>This page will update existing ordinary links in articles and (soon) products to URL friendly links.</p><?php } ?>
<form action="" method="post" id="form1">
<input name="update" type="submit" class="button" id="update" value="Update" />
<input name="start" type="hidden" id="start" value="true" />
</form>
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsArticles);

mysql_free_result($rsProducts);

mysql_free_result($rsProductDetails);
?>
