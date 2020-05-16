<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php 

$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;

$varRegionID_rsNewsPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsNewsPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsNewsPrefs = sprintf("SELECT * FROM newsprefs WHERE ID = %s", $regionID);
$rsNewsPrefs = mysql_query($query_rsNewsPrefs, $aquiescedb) or die(mysql_error());
$row_rsNewsPrefs = mysql_fetch_assoc($rsNewsPrefs);
$totalRows_rsNewsPrefs = mysql_num_rows($rsNewsPrefs);

if(!function_exists("sendNewsEmail")) {
function sendNewsEmail($newsID, $active = 1) {
	global $database_aquiescedb, $aquiescedb,  $regionID;
	$head = "";$groupemailID = "";
	if(function_exists("addGroupEmail")) {
		mysql_select_db($database_aquiescedb, $aquiescedb);	
		
		$select = "SELECT orgname, contactemail FROM preferences WHERE ID = ".intval($regionID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$prefs = mysql_fetch_assoc($result);
		
		
		$news = mysql_fetch_assoc($result);
		$select = "SELECT news.summary, news.imageURL, news.title, news.body, news.displayfrom, news.postedbyID, newssection.accesslevel, newssection.groupreadID,newssection.showpostedby, newssection.emailtemplateID, users.email, users.firstname, users.surname FROM news LEFT JOIN newssection ON (news.sectionID =  newssection.ID) LEFT JOIN users ON (news.postedbyID = users.ID) WHERE news.ID = ".intval($newsID);
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$news = mysql_fetch_assoc($result);
		$emailtemplateID = (isset($news['emailtemplateID']) && $news['emailtemplateID']>0) ? $news['emailtemplateID'] : 0;
		if($emailtemplateID>0) {
			// convert plain text into HTML
			$news['summary'] = strpos($news['summary'],"</")>0  ? $news['summary'] : nl2br($news['summary']);
			$news['summary'] = strpos($news['summary'],"</")>0  ? $news['summary'] : nl2br($news['summary']);
		
			$newsmessage =  "<p class='news-title'>".ucwords($news['title'])."</p>\n\n";
			$newsmessage .= isset($news['imageURL']) ? "<div class=\"news-image\"><img src=\"".getProtocol()."://".$_SERVER['HTTP_HOST'].getImageURL($news['imageURL'],"medium")."\" class=\"news-image\" ></div>\n\n" : "";
			$newsmessage .= "<div class='news-summary'>".$news['summary']."</div><br>";
					
						
			$newsmessage .= "To view the full post, please follow this link:<br><br>\n\n";
			// add key to link to avoid log in to view poat
			$link = getProtocol()."://". $_SERVER['HTTP_HOST']."/news/story.php?newsID=".intval(100+intval($newsID))."&key=".md5(PRIVATE_KEY.$newsID);
			$newsmessage .= "<a href=\"".$link."\">".$link."</a>";
			
		
		
		
			$select = "SELECT * FROM groupemailtemplate WHERE ID = ".intval($emailtemplateID);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());
			$template = mysql_fetch_assoc($result);
			$message = isset($template['templatemessage']) ? str_replace("{news}",$newsmessage , $template['templatemessage']) : "";			
			$html = isset($template['templateHTML']) ? str_replace("{news}",$newsmessage , $template['templateHTML']) : "";
			$head = $template['templatehead'];
			
			
		// if sending now or prior give 1 minute grace time in case any edits required
		
		
			$senddatetime = date('Y-m-d', strtotime($news['displayfrom'])) <= date('Y-m-d') ? date('Y-m-d H:i:s', strtotime("NOW + 1 MINUTES")): date('Y-m-d H:i:s', strtotime($news['displayfrom']));
			$from = $news['showpostedby']==0 ?  $prefs['contactemail'] : $news['email'];
			$friendlyfrom = $news['showpostedby']==0 ?  $prefs['orgname'] : $news['firstname']." ".$news['surname'];
		
		
		$groupemailID = addGroupEmail($news['title'], $message, $news['accesslevel'], $news['groupreadID'], $from, $friendlyfrom,  0, $head, $html, $regionID,$news['postedbyID'],1 ,$senddatetime,$active,1);
			
		} 
		
		
		
		
		
		
	}
	return $groupemailID;
}
}

if(!function_exists("addNewsComment")) {
function addNewsComment($comment, $newsID, $createdbyID=0) {
	global $database_aquiescedb, $aquiescedb;
	if($newsID>0) {
		$insert = sprintf("INSERT INTO comments (newsID, commenttext, createdbyID, createddatetime, statusID) VALUES (%s, %s, %s, %s, %s)",
				   GetSQLValueString($newsID, "int"),
				   GetSQLValueString($comment, "text"),
				   GetSQLValueString($createdbyID, "int"),
				   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				   GetSQLValueString(1, "int"));

		mysql_select_db($database_aquiescedb, $aquiescedb);
		$Result1 = mysql_query($insert, $aquiescedb) or die(mysql_error());
		// email all concerned
		$select = "SELECT firstname, surname FROM users WHERE ID = ".GetSQLValueString($createdbyID, "int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$commentuser = mysql_fetch_assoc($result);
		
		$select = "SELECT postedbyID, title FROM news WHERE ID = ".GetSQLValueString($newsID, "int");
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());
		$newsitem = mysql_fetch_assoc($result);
		
		$select = "SELECT x.userID, x.useremail FROM ((SELECT users.ID AS userID, users.email AS useremail FROM comments LEFT JOIN users ON (comments.createdbyID = users.ID) WHERE comments.newsID = ".intval($newsID).")
UNION 
(SELECT users.ID AS userID, users.email AS useremail  FROM news LEFT JOIN users ON (news.postedbyID = users.ID) WHERE news.ID = ".intval($newsID)."))
AS x GROUP BY x.useremail HAVING x.userID != ".GetSQLValueString($createdbyID, "int")." LIMIT 10";
		
		$result = mysql_query($select, $aquiescedb) or die(mysql_error());

		while($row = mysql_fetch_assoc($result)) {
			$to = $row['useremail'];
			if($to !="") { 
				$subject = "Comment added to post ".$newsitem['title'];
				$message = "A comment has been added by ".$commentuser['firstname']. " ".$commentuser['surname']." to ";
		$message .= ($createdbyID==$news['postedbyID']) ? "your post." : "the post that you commented on.";
				$message .="\n\nClick on the link below to view all comments:\n\n";
				$message .= getProtocol()."://". $_SERVER['HTTP_HOST']."/news/story.php?newsID=".(100+$newsID);
				sendMail($to, $subject, $message);	  
			}
		}
	}
}
}

if(!function_exists("newsLink")) {
	function newsLink($newsID=0, $newslongID="", $newssectionID=0, $newssectionlongID="") 
	{ 
			$url = "/";			
			if((defined("MOD_REWRITE") || isset($_SERVER['HTTP_MOD_REWRITE'])) && trim($newslongID) !="" && trim($newssectionlongID) !="") { // use mod rewrite				
				
				$url .= "items/".$newssectionlongID."/";
				$url .= ($newslongID)!="" ? $newslongID : "";				
			} else {	
				$url .= "news/story.php?newssectionID=".$newssectionID."&newsID=".($newsID+100);
			}			
		
		return $url;
	}
}




?>