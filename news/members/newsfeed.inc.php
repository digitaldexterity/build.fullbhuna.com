<?php if (!isset($_SESSION)) {
  session_start();
}

$currentPage = $_SERVER["PHP_SELF"];



if(isset($database_aquiescedb) && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>0) {
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$newscategories = (isset($newscategories) && is_array($newscategories)) ? $newscategories : array(1);
	$select_category = "";
	foreach($newscategories as $value) {
		$select_category .= " news.sectionID = ".intval($value)." OR ";
	}
	
	$userID =isset($userID) ? $userID : 0;
	
	$maxRows_rsNewsFeed =  isset($numberfeeditems) ? $numberfeeditems : 10;
	$pageNum_rsNewsFeed = 0;
	if (isset($_GET['pageNum_rsNewsFeed'])) {
  		$pageNum_rsNewsFeed = $_GET['pageNum_rsNewsFeed'];
	}
	$startRow_rsNewsFeed = $pageNum_rsNewsFeed * $maxRows_rsNewsFeed;



	$query_rsNewsFeed = "SELECT 1 AS itemtype, news.title, news.summary, news.imageURL, news.ID AS linkID, news.posteddatetime AS createddatetime, users.ID AS userID, users.firstname, users.surname, users.imageURL AS avatarURL, newssection.allowcomments, COUNT(comments.ID) AS numcomments
	FROM news LEFT JOIN newssection ON (news.sectionID = newssection.ID) LEFT JOIN users ON (news.postedbyID = users.ID) LEFT JOIN comments ON (comments.newsID = news.ID)
	WHERE (".trim($select_category," OR ").")
	AND status = 1 
	AND (news.displayfrom <= NOW() OR news.displayfrom IS NULL) 
	AND (news.displayto >= NOW() OR news.displayto IS NULL) 
	AND (newssection.accesslevel IS NULL OR newssection.accesslevel <= ".$_SESSION['MM_UserGroup'].")
	AND (".$userID." = 0 OR postedbyID = ".$userID.")
	GROUP BY news.ID
	ORDER BY createddatetime DESC";
	$query_limit_rsNewsFeed = sprintf("%s LIMIT %d, %d", $query_rsNewsFeed, $startRow_rsNewsFeed, $maxRows_rsNewsFeed);
	$rsNewsFeed = mysql_query($query_limit_rsNewsFeed, $aquiescedb) or die(mysql_error());
	$totalRows_rsNewsFeed = mysql_num_rows($rsNewsFeed); 
	
	if (isset($_GET['totalRows_rsNewsFeed'])) {
  $totalRows_rsNewsFeed = $_GET['totalRows_rsNewsFeed'];
} else {
  $all_rsNewsFeed = mysql_query($query_rsNewsFeed);
  $totalRows_rsNewsFeed = mysql_num_rows($all_rsNewsFeed);
}
$totalPages_rsNewsFeed = ceil($totalRows_rsNewsFeed/$maxRows_rsNewsFeed)-1;

$queryString_rsNewsFeed = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsNewsFeed") == false && 
        stristr($param, "totalRows_rsNewsFeed") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsNewsFeed = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsNewsFeed = sprintf("&totalRows_rsNewsFeed=%d%s", $totalRows_rsNewsFeed, $queryString_rsNewsFeed);

?>
	 <div id="memberfeed">
	<?php if($totalRows_rsNewsFeed>0) { ?>
   
	<?php	while($row_rsNewsFeed = mysql_fetch_assoc($rsNewsFeed)) { // loop 		
		$link = ""; $linkurl = "/news/story.php?newsID=".(100+$row_rsNewsFeed['linkID']);
		if($row_rsNewsFeed['itemtype'] ==1 ) {
			$link = "<a href=\"".$linkurl."\">";
			if ($row_rsNewsFeed['allowcomments']==1) {
				$link .= $row_rsNewsFeed['numcomments']." comments | Read more or comment...";
			} else {
				$link .= "Read more...";
			}
			$link .= "</a>";
		}
		 ?>
    	<div class="item">        
            	<a href="/members/profile/index.php?userID=<?php echo $row_rsNewsFeed['userID']; ?>"  style="background-image:url(<?php echo isset($row_rsNewsFeed['avatarURL']) ? getImageURL($row_rsNewsFeed['avatarURL'],"thumb") : "/members/images/user-anonymous.png"; ?>)" class="fb_avatar"><?php echo $row_rsNewsFeed['firstname']." ".$row_rsNewsFeed['surname']; ?></a>
           
            <div class="creator"><a href="/members/profile/index.php?userID=<?php echo $row_rsNewsFeed['userID']; ?>"><?php echo $row_rsNewsFeed['firstname']." ".$row_rsNewsFeed['surname']; ?></a></div>
            <div class="title"><?php echo $row_rsNewsFeed['title']; ?></div>
            <div class="summary"><?php echo $row_rsNewsFeed['summary']; ?></div>
            <?php if(isset($row_rsNewsFeed['imageURL'])) { ?><div class="image"><a href="<?php echo $linkurl; ?>"><img src="<?php echo getImageURL($row_rsNewsFeed['imageURL'],"thumb"); ?>" title="<?php echo $row_rsNewsFeed['title']; ?>"></a></div><?php } ?>
            <div class="link"><?php echo $link; ?></div>
            <div class="datetime"><abbr class="timeago" title="<?php echo $row_rsNewsFeed['createddatetime']; ?>"><?php echo date('d M Y H:i',strtotime($row_rsNewsFeed['createddatetime'])); ?></abbr></div>
        </div>			
		<?php } // end loop
	} else { ?>
		<p>No posts so far.</p>
	<?php } ?>
     <table  class="form-table">
        <tr>
          <td><?php if ($pageNum_rsNewsFeed > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsNewsFeed=%d%s", $currentPage, 0, $queryString_rsNewsFeed); ?>">First</a>
          <?php } // Show if not first page ?>          </td>
         <td><?php if ($pageNum_rsNewsFeed > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_rsNewsFeed=%d%s", $currentPage, max(0, $pageNum_rsNewsFeed - 1), $queryString_rsNewsFeed); ?>" rel="prev">Previous</a>
          <?php } // Show if not first page ?>          </td>
          <td><?php if ($pageNum_rsNewsFeed < $totalPages_rsNewsFeed) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsNewsFeed=%d%s", $currentPage, min($totalPages_rsNewsFeed, $pageNum_rsNewsFeed + 1), $queryString_rsNewsFeed); ?>" rel="next">Next</a>
          <?php } // Show if not last page ?>          </td>
          <td><?php if ($pageNum_rsNewsFeed < $totalPages_rsNewsFeed) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_rsNewsFeed=%d%s", $currentPage, $totalPages_rsNewsFeed, $queryString_rsNewsFeed); ?>">Last</a>
          <?php } // Show if not last page ?>          </td>
        </tr>
      </table>
    </div>
<?php } ?>
