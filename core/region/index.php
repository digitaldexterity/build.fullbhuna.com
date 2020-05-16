<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/framework.inc.php'); ?>
<?php $regionID = (isset($regionID) && intval($regionID)>0)  ? intval($regionID) : (isset($_SESSION['regionID'])  && $_SESSION['regionID']>0? $_SESSION['regionID'] : 1 ); ?>
<?php
if (!isset($_SESSION)) { session_start(); }
if (isset($_COOKIE['regionID']) && !isset($_GET['regionID'])) { $regionID = $_COOKIE['regionID']; } // if cookie already set and no request to change then set session
if (isset($_GET['regionID'])) { 
$_GET['regionID'] = ($_GET['regionID'] > 0) ? $_GET['regionID'] : 1;
$regionID = $_GET['regionID'];// set region for session
$secure = (getProtocol()=="https") ? true : false;	
if (isset($_GET['remember']) && $_GET['remember'] ==1) { 
setcookie("regionID",$regionID,time()+60*60*24*30,"/", "", $secure, true); } else { setcookie("regionID","",time()-3600,"/", "", $secure, true); }// if requested, set cookie otherwise clear it
$redirect = (isset($_GET['referer']) && ($_GET['referer'] !="")) ? $_GET['referer'] : "../index.php";// if sent from a page redirect to it otherwise home page
header("Location: ".$redirect);exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsRegions = "SELECT fullname, regionID FROM countries WHERE statusID = 1 ORDER BY ordernum ASC, fullname ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);

$colname_rsThisRegion = "1";
if (isset($regionID)) {
  $colname_rsThisRegion = (get_magic_quotes_gpc()) ? $regionID : addslashes($regionID);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisRegion = sprintf("SELECT title AS this_region FROM region WHERE ID = '%s'", $colname_rsThisRegion);
$rsThisRegion = mysql_query($query_rsThisRegion, $aquiescedb) or die(mysql_error());
$row_rsThisRegion = mysql_fetch_assoc($rsThisRegion);
$totalRows_rsThisRegion = mysql_num_rows($rsThisRegion);
?><?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; $pageTitle = "Choose region"; ?> - Choose Region</title>
<!-- InstanceEndEditable -->
<?php require_once('../seo/includes/seo.inc.php'); ?>
<?php require_once('../../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->

<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
          
            <form  method="get" name="form2" id="form2" class="form-inline">
              <p><?php if (!isset($regionID)) { ?>
              You have not chosen your country yet.<?php } else { ?>You are in: <?php echo $row_rsThisRegion['this_region']; } ?>. Click on the map or: 
                <select name="regionID" id="regionID" class="form-control">
                  <option value="">Choose country...</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsRegions['regionID']?>"><?php echo $row_rsRegions['fullname']?></option>
                  <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
              </select>
                <button name="go" type="submit" class="btn btn-default btn-secondary" id="go" >Go</button>
                <input name="referer" type="hidden" id="referer" value="<?php echo urlencode($_GET['referer']); ?>" />
                <label>
                <input name="remember" type="checkbox" id="remember" value="1" checked="checked" />
                Remember my choice</label>
              </p>
          </form>
           
          <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);

mysql_free_result($rsThisRegion);

mysql_free_result($rsProducts);
?>
