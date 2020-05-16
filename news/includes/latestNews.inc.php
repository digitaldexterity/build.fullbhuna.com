<?php
if(!function_exists("getImageURL")) {
	require_once(SITE_ROOT.'core/includes/framework.inc.php'); 
}
require_once(SITE_ROOT.'news/includes/newsfunctions.inc.php'); 

$regionID = isset($regionID) ? intval($regionID) : 1;
$number_news_stories = isset($number_news_stories) ? $number_news_stories : 2;
$news_sectionID = isset($news_sectionID) ? $news_sectionID : 0;
$news_tagID = isset($news_tagID) ? $news_tagID : 0;
$news_truncate = isset($news_truncate) ? $news_truncate : 0;
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLatestNews = "SELECT news.ID, news.longID, news.sectionID, news.title, summary,body, news.imageURL, newssection.longID AS sectionlongID, photogalleryID, news.redirectURL, news.attachment1, news.posteddatetime, news.displayfrom FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) LEFT JOIN tagged ON (tagged.newsID = news.ID) WHERE (newssection.regionID IS NULL OR  newssection.regionID = 0 OR newssection.regionID = ".$regionID.") AND status = 1 AND (DATE(news.displayfrom )<= CURDATE() OR news.displayfrom IS NULL) AND (DATE(news.displayto) >= CURDATE() OR news.displayto IS NULL) AND (".intval($news_sectionID)." = 0 OR news.sectionID = ".intval($news_sectionID).") AND (".$news_tagID." = 0 OR tagged.tagID = ".$news_tagID.") GROUP BY news.ID ORDER BY headline DESC, news.ordernum ASC, posteddatetime DESC LIMIT ".intval($number_news_stories);
$rsLatestNews = mysql_query($query_rsLatestNews, $aquiescedb) or die(mysql_error());
$totalRows_rsLatestNews = mysql_num_rows($rsLatestNews);


if($totalRows_rsLatestNews>0) { echo "<ul class=\"latestNews\">";
	while($row_rsLatestNews = mysql_fetch_assoc($rsLatestNews)) {
		echo "<li class=\"latestNewsItem".$row_rsLatestNews['ID']." latestNewsItem\">";
		if (strlen($row_rsLatestNews['body']) > 5 
			  || isset($row_rsLatestNews['youtube']) || isset($row_rsLatestNews['photogalleryID']) || isset($row_rsLatestNews['redirectURL']) || isset($row_rsLatestNews['attachment1'])) {
		$link = (isset($mod_rewrite) && isset($row_rsLatestNews['longID']) && isset($row_rsLatestNews['sectionlongID'])) ? "/items/".$row_rsLatestNews['sectionlongID']."/".$row_rsLatestNews['longID'] : "/news/story.php?newssectionID=".$row_rsLatestNews['sectionID']."&newsID=".($row_rsLatestNews['ID']+100);
			  } else {
				  $link = "#";
			  }
		
		if(isset($row_rsLatestNews['imageURL'])) { 
		echo "<div class=\"latestNewsImage\"><a href=\"".$link."\" params=\"lightwindow_type=external,lightwindow_width=".$lw_width.",lightwindow_height=". $lw_height."\" class=\"lightwindow\" style=\"background-image:url(".getImageURL($row_rsLatestNews['imageURL'],$row_rsNewsPrefs['imagesize_index']).")\"><img src=\"".getImageURL($row_rsLatestNews['imageURL'],$row_rsNewsPrefs['imagesize_index'])."\" class=\"thumb\" ></a></div>"; }
		echo "<div class=\"latestNewsTitle\"><a href=\"".$link."\" params=\"lightwindow_type=external,lightwindow_width=".$lw_width.",lightwindow_height=". $lw_height."\" class=\"lightwindow\">".$row_rsLatestNews['title']."</a></div>";
		echo "<div class=\"latestNewsDateTime\">";
		echo isset($row_rsLatestNews['displayfrom']) ? date('d M Y', strtotime($row_rsLatestNews['displayfrom'])): date('d M Y', strtotime($row_rsLatestNews['posteddatetime']));
		echo "</div>";
		echo "<div class=\"latestNewsSummary\">";
		echo  ($news_truncate>0 && $news_truncate<strlen($row_rsLatestNews['summary'])) ? substr(strip_tags($row_rsLatestNews['summary']),0,$news_truncate)."&hellip;" : $row_rsLatestNews['summary']."</div>";
		 if ($link != "#") { 
		echo "<div class=\"latestNewsReadMore\"><a href=\"".$link."\" params=\"lightwindow_type=external,lightwindow_width=".$lw_width.",lightwindow_height=". $lw_height."\" class=\"lightwindow\">Read more...</a></div>";
			  }
		echo "</li>";
	}echo "</ul>";
} else {
	echo "<div class=\"latestNewsNone\">There are currently no news stories.</div>";
}
?>