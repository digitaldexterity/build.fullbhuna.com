<?php if(!function_exists("trackPage")) {
	require_once(SITE_ROOT.'core/seo/includes/seo.inc.php'); 
}

mysql_select_db($database_aquiescedb, $aquiescedb);

$regionID = isset($regionID) ? $regionID : (isset($_SESSION['regionID']) ? $_SESSION['regionID'] : 1);
$console = isset($console) ? $console : "";


$select = "SELECT * FROM articleprefs WHERE ID = ".$regionID;
$result = mysql_query($select, $aquiescedb);
$row_rsArticlePrefs = mysql_fetch_assoc($result);


if(!function_exists("articleLink")) {
	function articleLink($articleID=0, $articlelongID="", $sectionID="", $sectionlongID="", $parentsectionID = 0, $parentsectionlongID = "", $page=0) 
	{ 
	global $mod_rewrite;
	
		
		$url = "/";
		if($sectionID=="" || intval($sectionID)!=0) { // not home
			if((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && $sectionID !="" && ($articleID == 0 || trim($articlelongID) !="") && ($parentsectionID==0 || trim($parentsectionlongID) != "") && ($sectionID==0 || trim($sectionlongID) !="")) { // use mod rewrite				
				//$url .= ($parentsectionlongID !="") ? $parentsectionlongID."/" : "";
				// to make .htaccess simpler not using parent categories in url for now	
				//* bug in earlier mysql UNION made longID = space (not null) to indicate not page			
				$url .= $sectionlongID."/";
				$url .= ($articlelongID)!="" ? $articlelongID."/" : "";	
				$url .= ($page>0) ? "page".$page : "";	
				
			} else {	
				$url .= ($articleID>0) ? "articles/article.php?" : "articles/index.php?";		
				$url .= ($parentsectionID >0) ? "parentsectionID=".intval($parentsectionID)."&" : "";
				$url .= ($sectionID!="") ? "sectionID=".$sectionID."&" : "";
				$url .= "articleID=".$articleID;
				$url .= ($page>0) ? "?pageNum_rsArticles=".$page : "";
			}			
		} // not home
		return $url;
	}
}


if(!function_exists("buildMenu")) {
	function buildMenu($sectionID=0,$depth=0, $class="", $minimum = 2,  $extras = array()) { 	
		// $minimum - how many required before sub menu shows (to stop single items in sub menus)
		// $extras a multi-use array only on top level as not passed by recursion - sitemap - is site map so show those links
		
		
	
		global $aquiescedb, $site_name, $regionID, $console, $row_rsArticlePrefs;
		$showlink = (isset($extras['sitemap'])) ? -1 : 0;
		$html = "";
		$class= ($class!="") ? " class = \"".$class."\" " : "";
		$accesslevel = isset($_SESSION['MM_UserGroup']) ? intval($_SESSION['MM_UserGroup']) : 0;		
		$sectionID = preg_replace("/[^a-zA-Z0-9_\-]/", "", $sectionID); // clean
		
		$select = "(SELECT 0 AS articleID, LPAD('',100,'') AS articlelongID, articlesection.ID AS sectionID, articlesection.longID AS sectionlongID, parentsection.ID AS parentsectionID, parentsection.longID AS parentsectionlongID, articlesection.description AS title, articlesection.description AS linktitle, articlesection.ordernum, NULL AS redirectURL, 0 AS newWindow, articlesection.newWindow AS sectionNewWindow, articlesection.linkaction, articlesection.class,  articlesection.showlink, articlesection.ordernum AS o FROM articlesection LEFT JOIN articlesection AS parentsection ON(articlesection.subsectionofID = parentsection.ID) WHERE articlesection.subsectionofID = '".$sectionID."'  AND articlesection.showlink > ".$showlink."  
  AND articlesection.accesslevel <= ".$accesslevel. " AND (articlesection.regionID = 0 OR articlesection.regionID = ".$regionID.")) 
			UNION DISTINCT
			(SELECT article.ID AS articleID, article.longID AS articlelongID, article.sectionID,  articlesection.longID AS sectionlongID, parentsection.ID AS parentsectionID, parentsection.longID AS parentsectionlongID, article.title, article.linktitle, article.ordernum, article.redirectURL, article.newWindow, articlesection.newWindow AS sectionNewWindow, articlesection.linkaction, articlesection.class, article.showlink, article.ordernum+".($row_rsArticlePrefs['articlesectionorder']*10000)." AS o FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) LEFT JOIN articlesection AS parentsection ON(articlesection.subsectionofID = parentsection.ID)  WHERE sectionID = '".$sectionID."' AND article.accesslevel <= ".$accesslevel. "  AND sectionID !=0 AND article.statusID = 1  AND article.versionofID IS NULL AND (articlesection.regionID = 0 OR articlesection.regionID = ".$regionID.") AND article.showlink >= ".$showlink.") ORDER BY o "; 
		
		//if($_SESSION['MM_UserGroup']==10 && $minimum ==1) die($select);
		//$console .= $select."\n"; 
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
		$minimum = $sectionID == 0 ? 1 : $minimum;
		if (mysql_num_rows($result)>=$minimum) { 
			$html .= ($html == "") ? "<ul".$class." itemscope itemtype=\"http://www.schema.org/SiteNavigationElement\">\r\n" : "<ul".$class.">\r\n"; 
			$html .= isset($extras['prepend']) ? $extras['prepend'] : "";
			$item = 0;
			if($sectionID == 0) {
				$select = "SELECT article.title, article.linktitle FROM article WHERE versionofID IS NULL AND sectionID = 0 AND (regionID = 0 OR regionID = ".$regionID.") LIMIT 1";
				$console .= $select."\n\n"; 
				$homeresult = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
				$home = mysql_fetch_assoc($homeresult);
				$html .= "<li itemprop=\"name\" class=\"item0 section section0 article0";
				$html .= ($_SERVER['PHP_SELF'] == "/index.php" || (isset($_GET['sectionID']) && $_GET['sectionID']===0)) ? " selected active " : "";
				$html .= "\"><a itemprop=\"url\" href=\"/\" title=\"".$home['linktitle']."\">".$home['title']."</a></li>";
			}
			while ($row = mysql_fetch_assoc($result)) { // loop items
				$type = ($row['articleID']==0) ? "section" : "article";$item ++; $class="";
				$target = ($row['newWindow']==1 || $row['sectionNewWindow']==1 ) ? "_blank\" rel=\"nofollow" : "_self";
				
				if($row['redirectURL'] !="") {
					$class .= " redirect ";
					$class .= substr($row['redirectURL'],0,1) == "#" ? " anchor " : "";
					$link = "<a itemprop=\"url\" href=\"".$row['redirectURL']."\" title=\"".$row['linktitle']."\" target=\"".$target."\" >" .$row['title']."</a>";
				} else {
					$link = ($row['articleID']==0 && $row['linkaction']==3) ? "<a>".$row['title']."</a>" : "<a itemprop=\"url\" href=\"".articleLink($row['articleID'],$row['articlelongID'], $row['sectionID'], $row['sectionlongID'],$row['parentsectionID'],$row['parentsectionlongID'],0)."\" title=\"".$row['linktitle']."\" target=\"".$target."\">" .$row['title']."</a>";
				}
			
				$html .= "<li itemprop=\"name\" class=\"item".$item." ".$type.$class." section".$row['sectionID']." article".$row['articleID']." ".$row['class'];
				$html .= (($type=="section" && isset($_GET['sectionID']) && ($_GET['sectionID'] == $row['sectionID'] || $_GET['sectionID'] == $row['sectionlongID'] || @$_GET['parentsectionID'] == $row['sectionID'] || @$row_rsArticle['parentsectionID'] == $row['sectionID']))
				 || ($type == "article" && (@$_GET['articleID'] == $row['articleID'] || @$_GET['articleID']== $row['articlelongID']))) ? " selected" : "";
				$html .= "\">".$link;
				// look for sub cats if below max depth
				if($row['articleID']==0 && $depth>=1) {	// is section and depth remaining		
					$html .= buildMenu($row['sectionID'],$depth-1, "",$minimum, $extras); // here is the recursion   	
				}
				
				$html .= "</li>";			
			} // end loop items
			if(isset($extras['append'])) {
				$html .=  $extras['append'];
				unset($extras['append']);
			}
			
			$html .= "</ul>\r\n";
		}
		return $html;
	}
}

if(!function_exists("nextArticleID")) {
	function nextArticleID($articleID = 0, $allsections = true, $regionID = 0) {
	global $database_aquiescedb, $aquiescedb, $console;
	mysql_select_db($database_aquiescedb, $aquiescedb);
		if(intval($articleID)>0) { 
			// get current status
			$select = "SELECT article.sectionID, article.ordernum, articlesection.ordernum AS sectionordernum FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.ID =  ".intval($articleID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
			if(mysql_num_rows($result)>0) {  // found article
				$thisArticle = mysql_fetch_assoc($result);
			 	// try within section
				$select = "SELECT article.ID FROM article WHERE sectionID = ".$thisArticle['sectionID']." AND versionofID IS NULL AND statusID = 1 AND ordernum > ".intval($thisArticle['ordernum'])." ORDER BY article.ordernum LIMIT 1";				
				$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
				if(mysql_num_rows($result)>0) {
					$article = mysql_fetch_assoc($result);
					return $article['ID'];
				} else if($allsections) {// all sections
				
					$select = "SELECT article.ID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID)  WHERE  versionofID IS NULL AND statusID = 1 AND articlesection.ordernum > ".intval($thisArticle['sectionordernum'])." ORDER BY articlesection.ordernum, article.ordernum LIMIT 1";				
					$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
					if(mysql_num_rows($result)>0) {
						$article = mysql_fetch_assoc($result);
						return $article['ID'];
					}
				} // end all sections
			}  // end found article
		}  //  article ID		
		return false;
	} // end func
}


if(!function_exists("prevArticleID")) {
	function prevArticleID($articleID = 0, $allsections = true, $regionID = 0) {
	global $database_aquiescedb, $aquiescedb, $console;
	mysql_select_db($database_aquiescedb, $aquiescedb);
		if($articleID>0) { 
			// get current status
			$select = "SELECT article.sectionID, article.ordernum, articlesection.ordernum AS sectionordernum FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID) WHERE article.ID =  ".intval($articleID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
			if(mysql_num_rows($result)>0) {  // found article
			$thisArticle = mysql_fetch_assoc($result);
			 // try within section
				$select = "SELECT article.ID FROM article WHERE sectionID = ".$thisArticle['sectionID']." AND versionofID IS NULL AND statusID = 1 AND ordernum < ".intval($thisArticle['ordernum'])." ORDER BY article.ordernum DESC LIMIT 1";				
				$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
				if(mysql_num_rows($result)>0) {
					$article = mysql_fetch_assoc($result);
					return $article['ID'];
				} else if($allsections) {// all sections
					$select = "SELECT article.ID FROM article LEFT JOIN articlesection ON (article.sectionID = articlesection.ID)  WHERE  versionofID IS NULL AND statusID = 1 AND articlesection.ordernum < ".intval($thisArticle['sectionordernum'])." ORDER BY articlesection.ordernum DESC, article.ordernum DESC LIMIT 1";				
					$result = mysql_query($select, $aquiescedb) or die(mysql_error().$select);
					if(mysql_num_rows($result)>0) {
						$article = mysql_fetch_assoc($result);
						return $article['ID'];
					}
				} // end all sections
			}  // end found article
		}  //  article ID		
		return false;
	} // end func
}

if(!function_exists("copyArticlesToRegion")) {
	function copyArticlesToRegion($newRegion = 0, $fromRegion = 1) {
		global $database_aquiescedb, $aquiescedb, $console;
		mysql_select_db($database_aquiescedb, $aquiescedb);
		// REQUIRES framework.inc
		if($newRegion>0 && $fromRegion>0) { // need region numbers but possibilty to use 0 later
			// copy product prefs
			//duplicateMySQLRecord ("productPrefs", $fromRegion, "ID", $newRegion );
			$oldnewcategories = array();
			
			// copy product categories
			$select = "SELECT * FROM articlesection WHERE regionID = ".intval($fromRegion);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)>0) {
				while($row = mysql_fetch_assoc($result)) {
					$newID = duplicateMySQLRecord ("articlesection", $row['ID'], "ID");
					$update = "UPDATE articlesection SET createdbyID= 0, createddatetime = NOW(), regionID = ".$newRegion." WHERE ID = ".$newID;
					$console .=  ":\n".$update."\n\n"; 
					mysql_query($update, $aquiescedb) or die(mysql_error().$errorsql);
					$oldnewcategories[$row['ID']] = $newID;
					
				}
				// update sub cat of ID
				foreach($oldnewcategories as $key=> $value) {				
					$update = "UPDATE articlesection SET subsectionofID = ".$value." WHERE subsectionofID = ".$key." AND regionID = ".$newRegion;
					mysql_query($update, $aquiescedb) or die(mysql_error());			
				}
			}
			
			
			
			
			// copy articles
			$select = "SELECT * FROM article WHERE versionofID IS NULL AND statusID = 1 AND regionID = ".intval($fromRegion);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			if(mysql_num_rows($result)>0) {
				while($row = mysql_fetch_assoc($result)) {
					$newID = duplicateMySQLRecord ("article", $row['ID'], "ID");
					$update = "UPDATE article SET createdbyID= 0, createddatetime = NOW(), regionID = ".$newRegion.", sectionID = ".intval($oldnewcategories[$row['sectionID']])." WHERE ID = ".$newID;
					mysql_query($update, $aquiescedb) or die(mysql_error());				
				}
			}			
		}		
	}
}

if(!function_exists("saveArticleVersion")) {
function saveArticleVersion($articleID) {
	global $database_aquiescedb, $aquiescedb;
	if($articleID>0) {
		$versionID = duplicateMySQLRecord ("article", $articleID);
		$update = "UPDATE article SET versionofID = ".intval($articleID)." WHERE ID = ".$versionID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
}
}



if(!function_exists("revertToVersion")) {
function revertToVersion($currentversionID, $newversionID, $modifiedbyID=0) {
	global $database_aquiescedb, $aquiescedb;	
	// back LIVE version up 
	$savedversionID = duplicateMySQLRecord ("article", $currentversionID);
	$update = "UPDATE article SET versionofID = ".intval($currentversionID)." WHERE ID = ".$savedversionID;
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	
	
	// delete LIVE article and replace with new
	$delete = "DELETE FROM article WHERE ID = ".intval($currentversionID);
	mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	$update = "UPDATE article SET ID = ".intval($currentversionID).", versionofID = NULL, draft = 0, modifieddatetime = NOW(), modifiedbyID = ".GetSQLValueString($modifiedbyID,"int")." WHERE ID = ".intval($newversionID);
	mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
	// update ID of last back up
}	
}


if(!function_exists("cleanArticleHistory")) {
function cleanArticleHistory($articleID, $action=1) {
	global $database_aquiescedb, $aquiescedb, $regionID;


if($action==0) { // delete all history except drafts
		$delete = "DELETE FROM article WHERE draft = 0 AND versionofID =".intval($articleID);
		mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
	} else if($action==1) { // keep monthly
		$select = "SELECT article.ID, article.modifieddatetime, article.createddatetime FROM article WHERE article.statusID = 1 AND versionofID = ".intval($articleID)." ORDER BY modifieddatetime DESC";
		$result = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($result)>0) {
			$thismonth = date('Ym'); $month = "";
			while($version = mysql_fetch_assoc($result)) {
				$datetime =  isset($version['modifieddatetime']) ? strtotime($version['modifieddatetime']) : strtotime($version['createddatetime']); 
				if($month != date('Ym', $datetime)) { // new month
					$count = 1;
					$month = date('Ym', $datetime);						
				}// end new month
				if($month !=$thismonth && $count > 1) {
					$delete = "DELETE FROM article WHERE ID = ".$version['ID']." AND versionofID IS NOT NULL AND draft = 0";
					mysql_query($delete, $aquiescedb) or die(mysql_error().": ".$delete);
				}
				$count ++;
			} // end while					
		} // is versions
	} //  delete monthly
} // action = 3 - keep all so do nothing
}

if(!function_exists("articleMerge")) {
function articleMerge($html) {
	global $database_aquiescedb, $aquiescedb, $regionID, $row_rsArticle;	
	
	$varRegionID_rsMerge = "1";
	if (isset($regionID)) {
	  $varRegionID_rsMerge = $regionID;
	}
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$select = "(SELECT mergename, mergetext, mergeincludeURL, NULL AS formID FROM merge WHERE statusID >0 AND (".GetSQLValueString($varRegionID_rsMerge, "int")." = regionID OR regionID = 0)) UNION (SELECT formname AS mergename, NULL AS mergetext, NULL AS mergeincludeURL, ID AS formID FROM form WHERE statusID >0)";
	$rsMerge = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row_rsMerge = mysql_fetch_assoc($rsMerge);
	
	
	if(mysql_num_rows($rsMerge)>0) {
		do{			
			if(isset($row_rsMerge['formID']) && $row_rsMerge['formID']>0) {
				$row_rsMerge['mergename'] = "{formbuilder".$row_rsMerge['formID']."}";
				$url = SITE_ROOT."forms/includes/form.inc.php";
				if(is_readable($url)) {				
					ob_start();
					$formID = $row_rsMerge['formID'];
					include( $url);
					$row_rsMerge['mergetext'] = ob_get_clean(); 
					// gets content, discards buffer	
						
						
				} else {
					if(defined("DEBUG")) die("Can not read include (".htmlentities($url).")");
				}
			} else if(trim($row_rsMerge['mergeincludeURL'])!="") {
				$url = SITE_ROOT.$row_rsMerge['mergeincludeURL'];
				if(is_readable($url)) {				
					ob_start();
					include( $url);
					$row_rsMerge['mergetext'] = ob_get_clean(); 
					// gets content, discards buffer			
				} else {
					if(defined("DEBUG")) die("Can not read include (".htmlentities($url).")");
				}
			}		
			$html = str_replace($row_rsMerge['mergename'], $row_rsMerge['mergetext'],$html);
		} while($row_rsMerge = mysql_fetch_assoc($rsMerge));
	}
	mysql_free_result($rsMerge);
	return $html;
}
}

if(!function_exists("mergeExists")) {
function mergeExists($merge, $mergeregionID=0, $excludeID=0) {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	$select = "SELECT ID FROM merge WHERE (regionID = 0 OR regionID = ".intval($regionID)." OR ".GetSQLValueString($mergeregionID, "int")." = 0)  AND mergename LIKE ".GetSQLValueString($merge, "text")." AND ID != ".intval($excludeID);
	
  	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
  	if(mysql_num_rows($result)>0) {
	  	return true;
  	}
	return false;
}
}

if(!function_exists("createDefaultMerges")) {
function createDefaultMerges() {
	global $database_aquiescedb, $aquiescedb, $regionID;
	mysql_select_db($database_aquiescedb, $aquiescedb);	
	$mergeincludes = array(
	
	"{submenu}"=>"articles/includes/submenu.inc.php",
	"{share}"=>"core/share/includes/share.inc.php",	
	"{googlemap}"=>"location/includes/googlemap.inc.php",	
	"{products}"=>"products/includes/products.inc.php",
	"{addtobasket}"=>"products/includes/addtobasket.inc.php",
	"{productprice}"=>"products/includes/price.inc.php",
	"{viewedproducts}"=>"products/includes/viewedProducts.inc.php",
	"{relatedproducts}"=>"products/includes/relatedProducts.inc.php",
	"{featuredproducts}"=>"products/includes/featuredProducts.inc.php",
	"{productphotos}"=>"products/includes/productphotos.inc.php",
	"{productfilters}"=>"products/includes/categoryMenu.inc.php",
	"{productreviews}"=>"products/includes/review.inc.php",
	"{producttitle}"=>"products/includes/producttitle.inc.php",
	"{producttabs}"=>"products/includes/producttabs.inc.php",
	"{productdescription}"=>"products/includes/productdescription.inc.php"
	);
	foreach($mergeincludes as $key=>$value) {
		if(!mergeExists($key)) {
			$insert = "INSERT INTO merge (mergename,mergeincludeURL,statusID,createdbyID,createddatetime) VALUES ('".$key."','".$value."',0,0,'".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}
	
	$mergetext = array(
	"{organisation}"=>$site_name,
	"{developer}"=>'<a href="https://www.digitaldexerity.co.uk/" target="_blank" title="Web Designers in Glasgow">Digital Dexterity</a>');
	foreach($mergetext as $key=>$value) {
		if(!mergeExists($key)) {
			$insert = "INSERT INTO merge (mergename,mergetext,statusID,createdbyID,createddatetime) VALUES ('".$key."','".$value."',1,0,'".date('Y-m-d H:i:s')."')";
			mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		}
	}
}
}

if(!function_exists("createArticleSection")) {
function createArticleSection($title="",$regionID=1,$createdbyID=0,$showlink=-1, $metakeywords="",$metadescription="",$accesslevel=0,$subsectionofID=0) {
	 global $database_aquiescedb, $aquiescedb;
	$longID = createURLname("", $title, "-",  "articlesection");
	$metakeywords = trim($metakeywords!="") ? $metakeywords : $title; 
	$metadescription = trim($metadescription!="") ? $metadescription : $title; 
	
	 mysql_select_db($database_aquiescedb, $aquiescedb);

$insert = "INSERT INTO articlesection(description,longID,showlink,metakeywords,metadescription,ordernum,subsectionofID,accesslevel,regionID) VALUES (".
		GetSQLValueString($title, "text").",".
		GetSQLValueString($longID, "text").",".
		GetSQLValueString($showlink, "int").",".
		GetSQLValueString($metakeywords, "text").",".
		GetSQLValueString($metadescription , "text").",999,".intval($subsectionofID).",".intval($accesslevel).",".
		GetSQLValueString($_POST['regionID'], "int").")";
		mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
		$sectionID = mysql_insert_id();
		return $sectionID;
	}
	
}


if(!function_exists("createArticle")) {
function createArticle($articletype=1,   $regionID=1, $title="Untitled",  $body="", $metakeywords="", $metadescription="", $showlink=1, $statusID=1, $redirectURL="", $sectionID=0, $headHTML="", $createdbyID=0) {
 global $database_aquiescedb, $aquiescedb, $site_name;
 
 $longID = createURLname("", $title, "-",  "article");
 
 mysql_select_db($database_aquiescedb, $aquiescedb);
$select = "SELECT MAX(ID)+1 AS maxID FROM article";
$rsArticleNextID = mysql_query($select, $aquiescedb) or die(mysql_error());
$row_rsArticleNextID = mysql_fetch_assoc($rsArticleNextID);
$ordernum = isset($row_rsArticleNextID['maxID']) ? $row_rsArticleNextID['maxID'] : 0;

$query_rsArticlePrefs = "SELECT * FROM articleprefs WHERE ID = ".$regionID;
$rsArticlePrefs = mysql_query($query_rsArticlePrefs, $aquiescedb) or die(mysql_error());
$row_rsArticlePrefs = mysql_fetch_assoc($rsArticlePrefs);
$totalRows_rsArticlePrefs = mysql_num_rows($rsArticlePrefs);


 
$select = "SELECT title FROM region WHERE ID = ".intval($regionID);
$region_result = mysql_query($select, $aquiescedb) or die(mysql_error());
$region = mysql_fetch_assoc($region_result);


$seotitle = $title." | ";
	$seotitle .= isset($region['title']) ? $region['title'] : $site_name;
	
	
		if($body == "") {
			if($row_rsArticlePrefs['addtitle']==1) {
				$body="<h1>".$_POST['title']."</h1>";
			}
			$body.="<p>&nbsp;</p>";
		}
		if(isset($row_rsArticlePrefs['containerclass']) && $row_rsArticlePrefs['containerclass'] !="") {
			$body="<div class=\"".$row_rsArticlePrefs['containerclass']."\">".$body."</div>";
		}
	
	
	


$insert = "INSERT INTO article (articletype, longID, ordernum, regionID, title, seotitle, body, metakeywords, metadescription, showlink, statusID, redirectURL, sectionID, headHTML, linktitle, createdbyID, createddatetime) VALUES (".
                       GetSQLValueString($articletype, "int").",".
                       GetSQLValueString($longID, "text").",".
                       GetSQLValueString($ordernum, "int").",".
                       GetSQLValueString($regionID, "int").",".
                       GetSQLValueString($title, "text").",".
                       GetSQLValueString($seotitle, "text").",".
                       GetSQLValueString($body, "text").",".
                       GetSQLValueString($metakeywords, "text").",".
                       GetSQLValueString($metadescription, "text").",".
                       GetSQLValueString($showlink, "int").",".
                       GetSQLValueString($statusID, "int").",".
                       GetSQLValueString($redirectURL, "text").",".
                       GetSQLValueString($sectionID, "int").",".
                       GetSQLValueString($headHTML, "text").",".
					   GetSQLValueString($title, "text").",".
                       GetSQLValueString($createdbyID, "int").",NOW())";
                       

  
  mysql_query($insert, $aquiescedb) or die(mysql_error().": ".$insert);
  $articleID  = mysql_insert_id();
  return  $articleID;
}
}
?>