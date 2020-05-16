<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../login/includes/login.inc.php'); ?><?php require_once('../includes/framework.inc.php'); ?>
<?php require_once('../includes/adminAccess.inc.php'); ?><?php 
if(is_readable('../../products/includes/productFunctions.inc.php')) {
	require_once('../../products/includes/productFunctions.inc.php'); 
}?>
<?php
if (!isset($_SESSION)) {
  session_start();
}$MM_authorizedUsers = "6,7,8,9,10";
$MM_donotCheckaccess = "false";

$regionID = isset($regionID) ? $regionID : 1;

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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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



if(isset($_GET['deleteinstall'])) {
	deleteDirectory(SITE_ROOT."install");
	if(is_readable(SITE_ROOT."local/additions.sql")) {
		unlink(SITE_ROOT."local/additions.sql");
	}
	$msg = "Install files deleted.";
}



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID =".intval($regionID)."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);


$varRegionID_rsProductPrefs = "1";
if (isset($regionID)) {
  $varRegionID_rsProductPrefs = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = sprintf("SELECT productprefs.shopstatus FROM productprefs WHERE ID = %s", GetSQLValueString($varRegionID_rsProductPrefs, "int"));
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);



$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID, users.firstname,users.username, users.password, users.surname, users.changepassword, users.lastlogin FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUnreadCorrespondence = "SELECT COUNT(correspondence.ID) AS numCorrespondence FROM correspondence, mailprefs WHERE correspondence.createddatetime > mailprefs.lastViewed";
$rsUnreadCorrespondence = mysql_query($query_rsUnreadCorrespondence, $aquiescedb) or die(mysql_error());
$row_rsUnreadCorrespondence = mysql_fetch_assoc($rsUnreadCorrespondence);
$totalRows_rsUnreadCorrespondence = mysql_num_rows($rsUnreadCorrespondence);

$varUsername_rsFavourites = "-1";
if (isset($_SESSION['MM_Username'])) {
  $varUsername_rsFavourites = $_SESSION['MM_Username'];
}
$varRegionID_rsFavourites = "0";
if (isset($regionID)) {
  $varRegionID_rsFavourites = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFavourites = sprintf("SELECT favourites.*, createdby.firstname, createdby.surname FROM favourites LEFT JOIN users ON (favourites.userID = users.ID) LEFT JOIN users AS createdby ON (favourites.createdbyID = createdby.ID) WHERE (favourites.userID = 0 OR users.username = %s) AND (favourites.regionID=0 OR favourites.regionID=%s) AND favourites.statusID = 1", GetSQLValueString($varUsername_rsFavourites, "text"),GetSQLValueString($varRegionID_rsFavourites, "int"));
$rsFavourites = mysql_query($query_rsFavourites, $aquiescedb) or die(mysql_error());
$row_rsFavourites = mysql_fetch_assoc($rsFavourites);
$totalRows_rsFavourites = mysql_num_rows($rsFavourites);
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLastBackup = "SELECT createddatetime, createdbyID, autobackuptype FROM backup WHERE statusID = 1 ORDER BY createddatetime DESC LIMIT 1";
$rsLastBackup = mysql_query($query_rsLastBackup, $aquiescedb) or die(mysql_error());
$row_rsLastBackup = mysql_fetch_assoc($rsLastBackup);
$totalRows_rsLastBackup = mysql_num_rows($rsLastBackup);



if($_SESSION['MM_UserGroup']==10 && isset($_POST['sql_statement']) && trim($_POST['sql_statement'])!="") {
	$sql= $_POST['sql_statement'];
	$mysqlresult = mysql_query($sql, $aquiescedb);
	if(!$mysqlresult) {
		 $error = "MySQL error: ".mysql_error();
	} else {
		$msg = "MySQL query executed: ".$sql."<br>".mysql_affected_rows()." rows affected.";
		if(is_resource($mysqlresult)) {
			$msg .= "<table>";
			$header = false;
			while($row=mysql_fetch_assoc($mysqlresult)) {
				if(!$header) {
					$msg .= "<tr>";
					$header = true;
					foreach($row as $key=>$value) {
						$msg .= "<th>".$key."</th>";
					}
					$msg .= "</tr>";
				}
				$msg .= "<tr>";
				foreach($row as $key=>$value) {
					$msg .= "<td>".$value."</td>";
				}
				$msg .= "</tr>";
			}
			$msg .= "</table>";
		} // is result
	}
	
	
}
 


if ($row_rsLoggedIn['changepassword'] == 1) { 
	$url ="/members/profile/change_password.php?compulsary=true&returnURL=/core/admin/";
	header("Location: ".$url);exit;
}

if(isset($row_rsPreferences['controlpanelURL']) && $row_rsPreferences['controlpanelURL'] !="") { 
	header("location: ".$row_rsPreferences['controlpanelURL']); exit; 
}



if(isset($_GET['stayloggedin']) && function_exists("stayLoggedIn")) {
	// this needs to ask for password again for security
	stayLoggedIn($row_rsLoggedIn['username'],$row_rsLoggedIn['password']);
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Dashboard"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../seo/includes/seo.inc.php'); ?>
<?php require_once('../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
$(document).ready(function(e) {
	phoneHome("<?php echo $row_rsPreferences['license_key']; ?>", "<?php echo $_SERVER['HTTP_HOST']; ?>" , "<?php echo $row_rsPreferences['installdatetime']; ?>");
	getData("/core/seo/admin/visitors/includes/gethostbyaddress.inc.php?remote_address=<?php echo $_SERVER['SERVER_ADDR']; ?>","serveraddress");
	getData("/core/seo/admin/visitors/includes/gethostbyaddress.inc.php?remote_address=<?php echo getClientIP(); ?>","hostaddress");
	getData( "ajax/hits.ajax.php", "stats");    
});
	//http://linkedin.github.io/hopscotch/

    var tour = {
      id: "hello-hopscotch",
      steps: [
        {
          title: "Welcome",
          content: "Introducing Full Bhuna - your complete online. Take a tour of the features.",
          target: "header",
          placement: "bottom"
        },
        {
          title: "Menu",
          content: "All sections of your control panel are accessed via the menu.",
          target: "adminMenu",
          placement: "right"
        }
		,
        {
          title: "Create and Edit Pages",
          content: "To edit any standard page on the site, click on Manage Pages.",
          target: "admin_link_articles",
          placement: "right"
        }
      ],
	  onEnd : function() {
		  alert();
	  },
	  onClose :function() {
		  alert();
	  }
    };

    // Start the tour!
	// Function by me. Tour stays active until you close or click done (even with page refresh)
  /*
	if(!getCookie("hopscotch-classroom")) {
	  	showHopscotch();
	}
	 
	 
	 
	function hopscotchOnce() {
		   setCookie("hopscotch-once", true, 365);
	}
	
	function showHopscotch() {
		deleteCookie( "hopscotch-once" );
		hopscotch.startTour(tour);
	}
*/
</script>
<style><!--

--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><div class="page dashboard">
   <?php require_once('../region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-dashboard"></i> Dashboard</h1>
    <h2>Good <?php if (date('H') <12) echo "morning"; else  if (date('H') <17) echo "afternoon"; else echo "evening"; echo ", ".$row_rsLoggedIn['firstname']; ?>. You&#8217;re in control...</h2> 
   <p class="lead"><?php if(isset($_SESSION['lastlogin'])) { ?>You last logged in as <strong><?php echo $_SESSION['MM_Username']; ?></strong> at <?php echo date('g:ia',strtotime($_SESSION['lastlogin'])); ?> on <?php echo date('jS F Y',strtotime($_SESSION['lastlogin'])); ?>.<?php } else { ?><strong>Welcome!</strong>
      <?php } ?>
      
      <?php 
	  
	  $cookenameprepend  = getProtocol()=="https" ? "__Secure-" : "";
	

if(isset($_COOKIE[$cookenameprepend.'cookieusername']) || isset($_GET['stayloggedin'])) { ?>You are permanently logged in until you click log out.<?php } else { ?>You will be logged out after <?php echo intval(ini_get("session.gc_maxlifetime")/60); ?> minutes of inactivity. <a href="index.php?stayloggedin=true"  onClick="if(this.checked) { return confirm('You will now stay permanently logged in on this computer until you click log out.\n\nNote: This feature is added for your convenience by storing a cookie on your computer, but for security we do not recommend using this functionality on a shared computer.'); }" class="stayloggedin" >Stay permanently logged in</a>.<?php } ?></p><hr>
  <?php require_once('../includes/alert.inc.php'); ?>
  <?php if(is_readable(SITE_ROOT.'local/includes/control_panel.inc.php'))  {
   require_once('../../local/includes/control_panel.inc.php'); 
    } ?>
  
  <?php if($row_rsProductPrefs['shopstatus']==1 && function_exists("getPeriodSales")) { ?>
    <h3><i class="glyphicon glyphicon-shopping-cart"></i> Your Shop Today</h3>
    <table class="table table-bordered">
    <tr>
    <td class="text-right"><strong>Day<br>Sales<br>Amount GBP</strong></td> <td  class="text-right">
    <?php  $daySales = getPeriodSales(); ?><strong>Today</strong><br>
   <?php echo $daySales['num_orders']; ?><br><?php echo number_format($daySales['total_amount'],2, ".",","); ?></td>
   <td  class="text-right">
    <?php $daySales = getPeriodSales("Yesterday"); ?><strong>Yesterday</strong><br>
   <?php echo $daySales['num_orders']; ?><br><?php echo number_format($daySales['total_amount'],2, ".",","); ?></td>
   <td  class="text-right">
    <?php $daySales = getPeriodSales("Now", "Now - 7 days"); ?><strong>Last 7 days</strong><br>
   <?php echo $daySales['num_orders']; ?><br><?php echo number_format($daySales['total_amount'],2, ".",","); ?></td></tr></table> <p><a href="/products/admin/index.php" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-shopping-cart"></i> View orders</a> <a href="/products/admin/products/index.php" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-tags"></i> Manage  Products</a></p>
   <?php } ?>
    <div class="row row-equal-height-md flex">
    <div class="col-md-3"><div><h3><i class="glyphicon glyphicon-pencil"></i> Page Editor</h3> 
      <p>Start here to edit the content of your site.</p>
      <p><a href="/articles/admin/" class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-file"></i>Update Pages</a></p>
      </div>
      </div>
      
      
      <div class="col-md-3 rank7">            
    <div>
      <h3><i class="glyphicon glyphicon-comment"></i> Communication</h3><p> You have <?php echo $row_rsUnreadCorrespondence['numCorrespondence'] > 0 ? "<strong>".$row_rsUnreadCorrespondence['numCorrespondence']."</strong>" : "no"; ?> new messages.</p>
      <p> <a href="../../mail/admin/index.php" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-envelope"></i> Messages &amp; settings</a></p></div>
</div>
    
     


    
 

<div class="col-md-3"><div><h3><i class="glyphicon glyphicon-globe"></i> Visitors</h3>
<div id="stats"></div>
<p><a href="/core/seo/admin/visitors/index.php" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-search"></i> View Detail</a></p></div>
</div>


<div class="col-md-3 rank8">
<div><h3><i class="glyphicon glyphicon-wrench"></i> Site Settings</h3><p>This site is for <strong><?php echo $row_rsPreferences['orgname']; ?></strong>.</p><p><a href="preferences.php" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-cog"></i> Update Settings</a></p></div>
</div>



<div class="col-md-3">   
<div>
  <h3><i class="glyphicon glyphicon-heart"></i>  Favourites</h3> <?php if ($totalRows_rsFavourites == 0) { // Show if recordset empty ?>
  <p>You currently have no favourites. Favourites are shortcuts to your most frequently used pages.</p>
  <?php } // Show if recordset empty ?>
               

    <?php if ($totalRows_rsFavourites > 0) { // Show if recordset not empty ?>
      <ul>
        <?php do { ?>
          <li><a href="<?php echo $row_rsFavourites['url']; ?>" <?php if($row_rsFavourites['newwindow']==1) echo "target=\"_blank\""; ?> ><?php echo $row_rsFavourites['pagetitle']; ?> <?php if($row_rsFavourites['newwindow']==1) { ?><i class="glyphicon glyphicon-new-window"></i><?php } ?></a></li>
          <?php } while ($row_rsFavourites = mysql_fetch_assoc($rsFavourites)); ?>
    </ul>
      <?php } // Show if recordset not empty ?>
      <p>Add a page to this list by clicking on link in the footer on the  page you wish to add.</p><p><a href="favourites/index.php"  class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-heart"></i> Update Favourites</a>.</div>
      </div>
             
  





 <div class="col-md-3 rank9">
    
    <div><h3><i class="glyphicon glyphicon-saved"></i> Backup</h3><p><?php if($totalRows_rsLastBackup>0) { ?>Your last successful backup was <?php echo ($row_rsLastBackup['createdbyID']==0) ? "automatically" : "manually"; ?> completed on <strong><?php echo date('d F Y', strtotime($row_rsLastBackup['createddatetime'])); ?></strong>.<?php } else { ?>Data not currently backed up.</p><?php } ?><p><a href="backup/index.php" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-repeat"></i> Backups</a></p></div>
</div>

 
 
    
  </div><form method="post" class="rank10"><fieldset>
  <legend>Web Administrator tools</legend>
  <p><a href="update/index.php">UPDATE BETA</a></p>
<div class="row">
<div class="col-md-6">
<h4>Client info</h4>

<p><strong>IP address:</strong> <?php echo getClientIP(); ?> <span id="hostaddress"></span></p><p><strong>Screen size:</strong> <script>document.write(screen.width+" x "+screen.height); </script></p>
</div><div class="col-md-6"><h4>Server info</h4>
<p><strong>IP address:</strong> <?php echo $_SERVER['SERVER_ADDR']; ?> <span id="serveraddress"></span></p><p><strong>Max upload:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
<p><strong>Max post:</strong> <?php echo ini_get('post_max_size'); ?></p>
<p><strong>Max execution time:</strong> <?php echo floor(ini_get('max_execution_time')/60); ?> mins</p>
<p><strong>Max input time:</strong> <?php echo floor(ini_get('max_input_time')/60); ?> mins</p>



<?php if($_SESSION['MM_UserGroup'] ==10) {
	
$select = "SELECT NOW() AS thetime;"; 
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result); 
echo "<p><strong>PHP Time:</strong> ".date('d M Y H:i')."</p><p><strong>MySQL Time:</strong> ".date('d M Y H:i', strtotime($row['thetime']))."</p>
<p><strong>Session Time:</strong> ".intval(ini_get("session.gc_maxlifetime")/60)." minutes</p>";?>
<p><strong>Web root:</strong> <?php echo __DIR__; ?></p>

<p><a href="webadmin/show_phpinfo.php" target="_blank" rel="noopener">PHP Info</a></p><p><a href="log.php" target="_blank" rel="noopener">Full Bhuna Log</a></p></div>
</div>
  <?php if(isset($sqlresult)) echo $sqlresult; ?>
  <textarea placeholder="Run SQL..." name="sql_statement" class="sql form-control"><?php echo isset($_POST['sql_statement']) ? htmlentities($_POST['sql_statement'], ENT_COMPAT, "UTF-8") : ""; ?></textarea><button type="submit" class="btn btn-default btn-secondary" >Run</button>
<?php } ?></fieldset>
</form>
<p><em>Powered by Full Bhuna: Your Complete Online. Is anyone still using WordPress?</em></p>
  </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsUnreadCorrespondence);

mysql_free_result($rsFavourites);

mysql_free_result($rsLastBackup);

mysql_free_result($rsProductPrefs);

?>
