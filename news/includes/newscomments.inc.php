<?php // COMMENTS

$maxRows_rsComments = 50;
$pageNum_rsComments = 0;
if (isset($_GET['pageNum_rsComments'])) {
  $pageNum_rsComments = $_GET['pageNum_rsComments'];
}
$startRow_rsComments = $pageNum_rsComments * $maxRows_rsComments;

$varNewsID_rsComments = "-1";
if (isset($row_rsNewsStory['ID'])) {
  $varNewsID_rsComments = $row_rsNewsStory['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT comments.ID, comments.commenttext, comments.createddatetime, users.firstname, users.surname, users.imageURL, comments.createdbyID FROM comments LEFT JOIN users ON comments.createdbyID = users.ID WHERE comments.newsID = %s AND comments.statusID = 1 ORDER BY comments.createddatetime ASC", GetSQLValueString($varNewsID_rsComments, "int"));
$query_limit_rsComments = sprintf("%s LIMIT %d, %d", $query_rsComments, $startRow_rsComments, $maxRows_rsComments);
$rsComments = mysql_query($query_limit_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);

if (isset($_GET['totalRows_rsComments'])) {
  $totalRows_rsComments = $_GET['totalRows_rsComments'];
} else {
  $all_rsComments = mysql_query($query_rsComments);
  $totalRows_rsComments = mysql_num_rows($all_rsComments);
}
$totalPages_rsComments = ceil($totalRows_rsComments/$maxRows_rsComments)-1;

$queryString_rsComments = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsComments") == false && 
        stristr($param, "totalRows_rsComments") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsComments = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsComments = sprintf("&totalRows_rsComments=%d%s", $totalRows_rsComments, $queryString_rsComments);


 
 if($row_rsNewsStory['allowcomments']!=0 && isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=$row_rsNewsStory['allowcomments']) { ?><a id="comments"></a>
        <h2>Comments</h2>
        <?php if ($totalRows_rsComments == 0) { // Show if recordset empty ?>
          <p>Be the first to comment on this Post:</p>
          <?php } // Show if recordset empty ?>
          <?php $formaction = strpos($editFormAction,"#")>0 ? $editFormAction :  $editFormAction."#comments"; ?>
        <form action="<?php echo $formaction; ?>" method="post" name="form1" id="form1">
         <div class="form-group">
          <textarea name="commenttext" id="commenttext" cols="60" rows="5" class="form-control"></textarea>
         </div>
          <button type="submit" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-plus-sign"></i> Add your comment...</button>
          
          <input name="newsID" type="hidden" id="newsID" value="<?php echo $row_rsNewsStory['ID']; ?>" />
          
          <input type="hidden" name="MM_insert" value="form1" />
        </form>
        <?php } // end logged in
  else if(!isset($_SESSION['MM_UserGroup']) && $row_rsNewsStory['allowcomments']==1) { ?>
        <p><a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Log in</a> or <a href="/login/signup.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">sign up</a> to post your comments</p>
        <?php } ?>
        <?php if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
        <p>Comments <?php echo ($startRow_rsComments + 1) ?> to <?php echo min($startRow_rsComments + $maxRows_rsComments, $totalRows_rsComments) ?> of <?php echo $totalRows_rsComments ?></p>
        <div id="memberfeed">
          <?php do { ?>
          <div class="item">
          <a href="/members/profile/index.php?userID=<?php echo $row_rsComments['createdbyID']; ?>" class="fb_avatar" style="background-image:url(<?php echo isset($row_rsComments['imageURL']) ? getImageURL($row_rsComments['imageURL'], "thumb") : "/members/images/user-anonymous.png"; ?>)"><?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?></a> 
          <div class="editpost mouseOutHide">
          <?php if($row_rsLoggedIn['ID']==$row_rsComments['createdbyID'] || $row_rsLoggedIn['ID']==$row_rsNewsStory['postedbyID'] || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>7)) { ?>
          <a href="/news/story.php?newsID=<?php echo intval($_GET['newsID']); ?>&amp;deletecommentID=<?php echo $row_rsComments['ID']; ?>#comments" onClick="return confirm('Are you sure you want to delete this post?');" class="link_delete" title="Delete this post"><i class="glyphicon glyphicon-trash"></i> Delete</a>
          <?php } ?>
        </div>
        <div class="creator"><a href="/members/profile/index.php?userID=<?php echo $row_rsComments['createdbyID']; ?>"><?php echo $row_rsComments['firstname']." ".$row_rsComments['surname']; ?></a></div>
        <div class="summary"><?php echo $row_rsComments['commenttext']; ?></div>
        <div class="datetime"><abbr class="timeago" title="<?php echo $row_rsComments['createddatetime']; ?>"><?php echo date('d M Y H:s',strtotime($row_rsComments['createddatetime'])); ?></abbr></div>
       
      </div>
      <!-- end item -->
      <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?>
    </div>
    <!-- end feed -->
    
    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, 0, $queryString_rsComments); ?>">First</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsComments > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, max(0, $pageNum_rsComments - 1), $queryString_rsComments); ?>">Previous</a>
            <?php } // Show if not first page ?></td>
        <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, min($totalPages_rsComments, $pageNum_rsComments + 1), $queryString_rsComments); ?>">Next</a>
            <?php } // Show if not last page ?></td>
        <td><?php if ($pageNum_rsComments < $totalPages_rsComments) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsComments=%d%s", $currentPage, $totalPages_rsComments, $queryString_rsComments); ?>">Last</a>
            <?php } // Show if not last page ?></td>
      </tr>
    </table>
    <?php } // Show if recordset not empty ?>
	<?php mysql_free_result($rsComments); ?>