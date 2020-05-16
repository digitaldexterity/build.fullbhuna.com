<?php require_once('../Connections/aquiescedb.php'); ?><?php
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

$currentPage = $_SERVER["PHP_SELF"];

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

if(isset($_GET['pageNum_rsSurveys'])) $_GET['pageNum_rsSurveys'] = intval($_GET['pageNum_rsSurveys']);
if(isset($_GET['totalRows_rsSurveys'])) $_GET['totalRows_rsSurveys'] = intval($_GET['totalRows_rsSurveys']);


$maxRows_rsSurveys = 30;
$pageNum_rsSurveys = 0;
if (isset($_GET['pageNum_rsSurveys'])) {
  $pageNum_rsSurveys = $_GET['pageNum_rsSurveys'];
}
$startRow_rsSurveys = $pageNum_rsSurveys * $maxRows_rsSurveys;

$varUserTypeID_rsSurveys = "0";
if (isset($row_rsLoggedIn['usertypeID'])) {
  $varUserTypeID_rsSurveys = $row_rsLoggedIn['usertypeID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveys = sprintf("SELECT ID, surveyname FROM survey WHERE survey.statusID = 1 AND survey.accesslevel <= %s AND (survey.startdatetime IS NULL OR survey.startdatetime <= NOW()) AND (survey.enddatetime IS NULL OR survey.enddatetime >= NOW()) ORDER BY survey.ordernum ASC", GetSQLValueString($varUserTypeID_rsSurveys, "int"));
$query_limit_rsSurveys = sprintf("%s LIMIT %d, %d", $query_rsSurveys, $startRow_rsSurveys, $maxRows_rsSurveys);
$rsSurveys = mysql_query($query_limit_rsSurveys, $aquiescedb) or die(mysql_error());
$row_rsSurveys = mysql_fetch_assoc($rsSurveys);

if (isset($_GET['totalRows_rsSurveys'])) {
  $totalRows_rsSurveys = $_GET['totalRows_rsSurveys'];
} else {
  $all_rsSurveys = mysql_query($query_rsSurveys);
  $totalRows_rsSurveys = mysql_num_rows($all_rsSurveys);
}
$totalPages_rsSurveys = ceil($totalRows_rsSurveys/$maxRows_rsSurveys)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

$queryString_rsSurveys = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsSurveys") == false && 
        stristr($param, "totalRows_rsSurveys") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsSurveys = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsSurveys = sprintf("&totalRows_rsSurveys=%d%s", $totalRows_rsSurveys, $queryString_rsSurveys);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Surveys"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
       <div id = "survey" class="survey container pageBody">
    <h1><?php echo isset($row_rsSurveyPrefs['surveyName']) ? ucwords($row_rsSurveyPrefs['surveyName']) : "Survey"; ?>s</h1><?php require_once('../core/includes/alert.inc.php'); ?>
    
    <?php if ($totalRows_rsSurveys == 0) { // Show if recordset empty ?>
      <p> You may need to <a href="/login/index.php" rel="nofollow">log in</a> to access some <?php echo isset($row_rsSurveyPrefs['surveyName']) ? $row_rsSurveyPrefs['surveyName'] : "Survey"; ?>s.</p>
      <?php } // Show if recordset empty ?>
    <?php if ($totalRows_rsSurveys > 0) { // Show if recordset not empty ?>
  <p><?php echo isset($row_rsSurveyPrefs['surveyName']) ? ucwords($row_rsSurveyPrefs['surveyName']) : "Survey"; ?>s <?php echo ($startRow_rsSurveys + 1) ?> to <?php echo min($startRow_rsSurveys + $maxRows_rsSurveys, $totalRows_rsSurveys) ?> of <?php echo $totalRows_rsSurveys ?> </p>
 <ol>

        <?php do { ?>
            <li><a href="survey.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" rel="nofollow"><?php echo $row_rsSurveys['surveyname']; ?></a></li>
            <?php } while ($row_rsSurveys = mysql_fetch_assoc($rsSurveys)); ?>
 </ol>
  <?php } // Show if recordset not empty ?>

    <table class="form-table">
      <tr>
        <td><?php if ($pageNum_rsSurveys > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, 0, $queryString_rsSurveys); ?>">First</a>
            <?php } // Show if not first page ?>
        </td>
        <td><?php if ($pageNum_rsSurveys > 0) { // Show if not first page ?>
            <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, max(0, $pageNum_rsSurveys - 1), $queryString_rsSurveys); ?>" rel="prev">Previous</a>
            <?php } // Show if not first page ?>
        </td>
        <td><?php if ($pageNum_rsSurveys < $totalPages_rsSurveys) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, min($totalPages_rsSurveys, $pageNum_rsSurveys + 1), $queryString_rsSurveys); ?>" rel="next">Next</a>
            <?php } // Show if not last page ?>
        </td>
        <td><?php if ($pageNum_rsSurveys < $totalPages_rsSurveys) { // Show if not last page ?>
            <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, $totalPages_rsSurveys, $queryString_rsSurveys); ?>">Last</a>
            <?php } // Show if not last page ?>
        </td>
      </tr>
    </table>
   </div>
<!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsSurveys);

mysql_free_result($rsSurveyPrefs);
?>
