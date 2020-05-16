<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

$currentPage = $_SERVER["PHP_SELF"];

$colname_rsUser = "-1";
if (isset($_GET['userID'])) {
  $colname_rsUser = $_GET['userID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUser = sprintf("SELECT firstname, surname FROM users WHERE ID = %s", GetSQLValueString($colname_rsUser, "int"));
$rsUser = mysql_query($query_rsUser, $aquiescedb) or die(mysql_error());
$row_rsUser = mysql_fetch_assoc($rsUser);
$totalRows_rsUser = mysql_num_rows($rsUser);

if(isset($_GET['pageNum_rsUserTopics'])) $_GET['pageNum_rsUserTopics'] = intval($_GET['pageNum_rsUserTopics']);
if(isset($_GET['totalRows_rsUserTopics'])) $_GET['totalRows_rsUserTopics'] = intval($_GET['totalRows_rsUserTopics']);



$maxRows_rsUserTopics = 20;
$pageNum_rsUserTopics = 0;
if (isset($_GET['pageNum_rsUserTopics'])) {
  $pageNum_rsUserTopics = $_GET['pageNum_rsUserTopics'];
}
$startRow_rsUserTopics = $pageNum_rsUserTopics * $maxRows_rsUserTopics;

$varUserID_rsUserTopics = "-1";
if (isset($_GET['userID'])) {
  $varUserID_rsUserTopics = $_GET['userID'];
}
$varMyAccessGroup_rsUserTopics = "-1";
if (isset($_SESSION['MM_UserGroup'])) {
  $varMyAccessGroup_rsUserTopics = $_SESSION['MM_UserGroup'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserTopics = sprintf("SELECT DISTINCT(forumtopic.ID),  forumtopic.imageURL, forumtopic.topic, forumtopic.message, forumtopic.posteddatetime, users.firstname, users.surname, forumtopic.postedbyID, forumcomment.postedbyID FROM forumtopic LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID) LEFT JOIN users ON (forumtopic.postedbyID = users.ID) LEFT JOIN forumsection ON (forumtopic.sectionID = forumsection.ID) WHERE forumtopic.statusID = 1 AND (forumsection.accesslevel <= %s OR forumsection.accesslevel = 0) AND (forumtopic.postedbyID = %s OR forumcomment.postedbyID = %s) ORDER BY forumtopic.posteddatetime ", GetSQLValueString($varMyAccessGroup_rsUserTopics, "int"),GetSQLValueString($varUserID_rsUserTopics, "int"),GetSQLValueString($varUserID_rsUserTopics, "int"));
$query_limit_rsUserTopics = sprintf("%s LIMIT %d, %d", $query_rsUserTopics, $startRow_rsUserTopics, $maxRows_rsUserTopics);
$rsUserTopics = mysql_query($query_limit_rsUserTopics, $aquiescedb) or die(mysql_error());
$row_rsUserTopics = mysql_fetch_assoc($rsUserTopics);

if (isset($_GET['totalRows_rsUserTopics'])) {
  $totalRows_rsUserTopics = $_GET['totalRows_rsUserTopics'];
} else {
  $all_rsUserTopics = mysql_query($query_rsUserTopics);
  $totalRows_rsUserTopics = mysql_num_rows($all_rsUserTopics);
}
$totalPages_rsUserTopics = ceil($totalRows_rsUserTopics/$maxRows_rsUserTopics)-1;

$queryString_rsUserTopics = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsUserTopics") == false && 
        stristr($param, "totalRows_rsUserTopics") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsUserTopics = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsUserTopics = sprintf("&totalRows_rsUserTopics=%d%s", $totalRows_rsUserTopics, $queryString_rsUserTopics);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "User Posts"; echo $pageTitle." | ".$site_name; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
        <div class="pageBody">
    <h1><?php echo anonymiser($row_rsUser['firstname'], $row_rsUser['surname']); ?> Discussions</h1>
    
    <?php if ($totalRows_rsUserTopics == 0) { // Show if recordset empty ?>
      <p>This user has not contributed to any discussion available to you so far.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsUserTopics > 0) { // Show if recordset not empty ?>
      <p><?php echo anonymiser($row_rsUser['firstname'], $row_rsUser['surname']); ?> has contributed to the following topics:</p>
      <p>Topics <?php echo ($startRow_rsUserTopics + 1) ?> to <?php echo min($startRow_rsUserTopics + $maxRows_rsUserTopics, $totalRows_rsUserTopics) ?> of <?php echo $totalRows_rsUserTopics ?></p>
      <?php do { ?>
        <p><strong><?php echo htmlentities($row_rsUserTopics['topic']); ?></strong><br /><?php echo htmlentities(nl2br($row_rsUserTopics['message'])); ?><br /><span class="text-muted">Posted by <?php echo anonymiser($row_rsUserTopics['firstname'], $row_rsUserTopics['surname']); ?> at <?php echo date('g:i a',strtotime($row_rsUserTopics['posteddatetime'])); ?> on <?php echo date('l jS F Y',strtotime($row_rsUserTopics['posteddatetime'])); ?></span><br />
          <a href="update_topic.php?topicID=<?php echo $row_rsUserTopics['ID']; ?>">View comments</a></p>
        <?php } while ($row_rsUserTopics = mysql_fetch_assoc($rsUserTopics)); ?>
          <table class="form-table">
            <tr>
              <td><?php if ($pageNum_rsUserTopics > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsUserTopics=%d%s", $currentPage, 0, $queryString_rsUserTopics); ?>">First</a>
                  <?php } // Show if not first page ?>                              </td>
              <td><?php if ($pageNum_rsUserTopics > 0) { // Show if not first page ?>
                  <a href="<?php printf("%s?pageNum_rsUserTopics=%d%s", $currentPage, max(0, $pageNum_rsUserTopics - 1), $queryString_rsUserTopics); ?>" rel="prev">Previous</a>
                  <?php } // Show if not first page ?>                              </td>
              <td><?php if ($pageNum_rsUserTopics < $totalPages_rsUserTopics) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsUserTopics=%d%s", $currentPage, min($totalPages_rsUserTopics, $pageNum_rsUserTopics + 1), $queryString_rsUserTopics); ?>" rel="next">Next</a>
                  <?php } // Show if not last page ?>                              </td>
              <td><?php if ($pageNum_rsUserTopics < $totalPages_rsUserTopics) { // Show if not last page ?>
                  <a href="<?php printf("%s?pageNum_rsUserTopics=%d%s", $currentPage, $totalPages_rsUserTopics, $queryString_rsUserTopics); ?>">Last</a>
                  <?php } // Show if not last page ?>                              </td>
            </tr>
                  </table>
      <?php } // Show if recordset not empty ?></div>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsUser);

mysql_free_result($rsUserTopics);
?>
