<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../includes/install.inc.php'); ?>
<?php 

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "10";
$MM_donotCheckaccess = "false";

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

$MM_restrictGoTo = "..//login.php?msg=".urlencode("You need to be logged in as Web Administrator to access this page.");
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
$query_rsRegions = "SELECT ID, title FROM region ORDER BY title ASC";
$rsRegions = mysql_query($query_rsRegions, $aquiescedb) or die(mysql_error());
$row_rsRegions = mysql_fetch_assoc($rsRegions);
$totalRows_rsRegions = mysql_num_rows($rsRegions);
 
if (isset($_POST['home'])) { // update posted

$mainMenu = "<ul>";
$mainMenu .= (isset($_POST['home']) && $_POST['mainmenu-home'] !="") ? "<li class=\"mainMenu_home\"><a href='/index.php' title='Click here to return to the home page'>".$_POST['mainmenu-home']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['members']) && $_POST['mainmenu-members'] !="") ? "<li class=\"mainMenu_members\"><a href='/members/index.php' title='Members log in here'>".$_POST['mainmenu-members']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['articles']) && $_POST['mainmenu-articles'] !="") ? "<li class=\"mainMenu_articles\"><a href='/articles/index.php' title='Click to view articles'>".$_POST['mainmenu-articles']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['news']) && $_POST['mainmenu-news'] !="") ? "<li class=\"mainMenu_news\"><a href='/news/index.php' title='Click here to view the latest news'>".$_POST['mainmenu-news']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['calendar']) && $_POST['mainmenu-calendar'] !="") ? "<li class=\"mainMenu_calendar\"><a href='/calendar/index.php' title='Click here to view the latest calendar'>".$_POST['mainmenu-calendar']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['booking']) && $_POST['mainmenu-booking'] !="") ? "<li class=\"mainMenu_booking\"><a href='/booking/index.php' title='Click here to view the latest bookings'>".$_POST['mainmenu-booking']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['video']) && $_POST['mainmenu-video'] !="") ? "<li class=\"mainMenu_video\"><a href='/video/index.php' title='Click to view video'>".$_POST['mainmenu-video']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['directory']) && $_POST['mainmenu-directory'] !="") ? "<li class=\"mainMenu_directory\"><a href='/directory/index.php' title='Click here to view the directory'>".$_POST['mainmenu-directory']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['blogs']) && $_POST['mainmenu-blogs'] !="") ? "<li class=\"mainMenu_blogs\"><a href='/blogs/index.php' title='Click here to view the blogs'>".$_POST['mainmenu-blogs']."</a></li>\n" : "";

$mainMenu .= (isset($_POST['surveys']) && $_POST['mainmenu-surveys'] !="") ? "<li class=\"mainMenu_surveys\"><a href='/surveys/index.php' title='Click here to take part in our surveys'>".$_POST['mainmenu-surveys']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['shop']) && $_POST['mainmenu-shop'] !="") ? "<li class=\"mainMenu_shop\"><a href='/products/index.php' title='Click here to visit the shop'>".$_POST['mainmenu-shop']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['forum']) && $_POST['mainmenu-forum'] !="") ? "<li class=\"mainMenu_forum\"><a href='/forum/index.php' title='Click here to take part in our discussion forums'>".$_POST['mainmenu-forum']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['jobs']) && $_POST['mainmenu-jobs'] !="") ? "<li class=\"mainMenu_jobs\"><a href='/jobs/index.php' title='Click here to view the latest job vacancies'>".$_POST['mainmenu-jobs']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['contact']) && $_POST['mainmenu-contact'] !="") ? "<li class=\"mainMenu_contact\"><a href='/contact/index.php' title='Click here to contact us'>".$_POST['mainmenu-contact']."</a></li>\n" : "";
$mainMenu .= (isset($_POST['regions']) && $_POST['mainmenu-regions'] !="") ? "<li class=\"mainMenu_regions\"><a href='/region/index.php' title='Click here to change the site region'>".$_POST['mainmenu-regions']."</a></li>\n" : "";

$mainMenu .= (isset($_POST['logout']) && $_POST['mainmenu-logout'] !="") ? '<li class=\"mainMenu_logout\"><a href=\'/login/logout.php?fulllogout=true\' title=\'Click here to log out fully\' onclick=\"document.returnValue =  confirm(\'Are you sure you want to log out?\n\n(You will need to log in again next time even if you checked Remember Me)\');return document.returnValue;\">'.$_POST['mainmenu-logout'].'</a></li>\n' : '';
$mainMenu .="</ul>";

$filename = "../../Connections/mainMenu.inc.php";
$handle = fopen($filename,"w");
fwrite($handle, $mainMenu);
fclose($handle);



$footerMenu = "<div class='footerMenu'><ul>";
$footerMenu .= (isset($_POST['members']) && $_POST['footermenu-members'] !="") ? "<li class=\"firstMenuItem\"><?php if(!isset(\$_SESSION['MM_Username'])) { ?><a href=\"../members/index.php\" title=\"Click here to access the members section of this web site\">Log In</a></li>\n
    <li><a href=\"../login/signup.php\" title=\"Sign up to become of a member of this web site\">Sign Up</a></li>\n
    <?php } else { ?><li class=\"firstMenuItem\"><a href=\"/login/logout.php\" onclick=\"return confirm('Are you sure you want to log out?\\n\\n(You will need to log in again next time even if you checked Remember Me)');\">Log Out</a></li>\n<?php } ?>" : "";

$footerMenu .="</ul></div>";
$footerMenu .= "<div class='footerMenu'><p align='center'>";
$footerMenu .= "&copy; 2006 - <?php echo date('Y'); ?> Full Bhuna Content Management System developed and built by <a href=\"http://www.paulegan.net/\">Paul Egan</a>";
$footerMenu .="<p></div>";
$footerMenu .= "<div class='footerMenu'><ul><li class=\"firstMenuItem\">E&amp;OE&nbsp;</li>\n";
$footerMenu .= (isset($_POST['regions']) && $_POST['footermenu-regions'] !="") ? "<li><a href='/region/index.php' title='Click here to change the site region'>".$_POST['footermenu-regions']."</a></li>\n" : "";
$footerMenu .= (isset($_POST['privacy']) && $_POST['footermenu-privacy'] !="") ? "<li><a href='/legal/privacy.php' title='Click here to view our Privacy Policy'>".$_POST['footermenu-privacy']."</a></li>\n" : "";
$footerMenu .= (isset($_POST['terms']) && $_POST['footermenu-terms'] !="") ? "<li><a href='/legal/terms.php' title='Click here to view our terms and conditions'>".$_POST['footermenu-terms']."</a></li>\n" : "";
$footerMenu .= (isset($_POST['acknowledgements']) && $_POST['footermenu-acknowledgements'] !="") ? "<li><a href='/acknowledgements/index.php' title='Click here view acknowledgements'>".$_POST['footermenu-acknowledgements']."</a></li>\n" : "";
$footerMenu .="</ul></div>";

$filename = "../../Connections/footermenu.inc.php";
$handle = fopen($filename,"w");
fwrite($handle, $footerMenu);
fclose($handle);


// Build admin menu
$adminMenu = "<div id=\"adminMenu\"><ul>\n";
$adminMenu .= "<li id=\"admin_link_dashboard\"><a href='/core/admin/'>Dashboard </a></li>\n";
        $adminMenu .= "<li id=\"admin_link_site\"><a href=\"/\" target=\"_blank\" onclick=\"openMainWindow('/'); return false;\">Open Main Site </a></li>\n";
		$adminMenu .= "<li id=\"admin_link_users\"><a href='/members/admin/index.php'>".$_POST['adminmenu-members']." </a></li>\n";
        $adminMenu .= (isset($_POST['news']) && $_POST['adminmenu-news'] !="") ? "<li id=\"admin_link_news\"><a  href='/news/admin/index.php'>".$_POST['adminmenu-news']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['calendar']) && $_POST['adminmenu-calendar'] !="") ? "<li id=\"admin_link_calendar\"><a  href='/calendar/admin/index.php'>".$_POST['adminmenu-calendar']." </a></li>\n" : "";
		$adminMenu .= (isset($_POST['booking']) && $_POST['adminmenu-booking'] !="") ? "<li id=\"admin_link_booking\"><a  href='/booking/admin/index.php'>".$_POST['adminmenu-booking']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['articles']) && $_POST['adminmenu-articles'] !="") ? "<li id=\"admin_link_articles\"><a  href='/articles/admin/index.php'>".$_POST['adminmenu-articles']." </a></li>\n" : "";
		$adminMenu .= (isset($_POST['video']) && $_POST['adminmenu-video'] !="") ? "<li id=\"admin_link_video\"><a  href='/video/admin/index.php'>".$_POST['adminmenu-video']." </a></li>\n" : "";
        
        $adminMenu .= (isset($_POST['directory']) && $_POST['adminmenu-directory'] !="") ? "<li id=\"admin_link_directory\"><a href='/directory/admin/index.php'>".$_POST['adminmenu-directory']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['forum']) && $_POST['adminmenu-forum'] !="") ? "<li id=\"admin_link_forum\"><a  href='/forum/admin/index.php'>".$_POST['adminmenu-forum']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['locations']) && $_POST['adminmenu-locations'] !="") ? "<li id=\"admin_link_locations\"><a  href='/location/admin/index.php'>".$_POST['adminmenu-locations']." </a></li>\n" : "";
		$adminMenu .= (isset($_POST['documents']) && $_POST['adminmenu-documents'] !="") ? "<li id=\"admin_link_documents\"><a  href='/documents/'>".$_POST['adminmenu-documents']." </a></li>\n" : "";
        
        $adminMenu .= (isset($_POST['surveys']) && $_POST['adminmenu-surveys'] !="") ? "<li id=\"admin_link_surveys\"><a href='/surveys/admin/index.php'>".$_POST['adminmenu-surveys']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['blogs']) && $_POST['adminmenu-blogs'] !="") ? "<li id=\"admin_link_blogs\"><a href='/blogs/admin/index.php'>".$_POST['adminmenu-blogs']." </a></li>\n" : "";
       
        $adminMenu .= (isset($_POST['regions']) && $_POST['adminmenu-regions'] !="") ? "<li id=\"admin_link_regions\"><a href='/core/region/admin/index.php'>".$_POST['adminmenu-regions']." </a> </li>\n" : "";
		
		$adminMenu .= (isset($_POST['furniture']) && $_POST['adminmenu-furniture'] !="") ? "<li id=\"furniture\"><a href='/furniture/admin/index.php'>".$_POST['adminmenu-furniture']." </a> </li>\n" : "";
		
        $adminMenu .= (isset($_POST['groupemail']) && $_POST['adminmenu-groupemail'] !="") ? "<li id=\"admin_link_groupemail\"><a href='/mail/admin/index.php'>".$_POST['adminmenu-groupemail']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['shop']) && $_POST['adminmenu-shop'] !="") ? "<li id=\"admin_link_shop\"><a href='/products/admin/index.php'>".$_POST['adminmenu-shop']." </a></li>\n" : "";
        $adminMenu .= (isset($_POST['visitors']) && $_POST['adminmenu-visitors'] !="") ? "<li id=\"admin_link_visitors\"><a href='/core/seo/admin/visitors/index.php'>".$_POST['adminmenu-visitors']." </a></li>\n" : "";
        
        $adminMenu .= "<li id=\"admin_link_logout\"><a  href='/login/logout.php?fulllogout=true'>Log Out </a></li>\n";
        $adminMenu .= "</ul></div>";
		
$filename = "../../Connections/adminmenu.inc.php";
$handle = fopen($filename,"w");
fwrite($handle, $adminMenu);
fclose($handle);
cleanUpFiles();	
$url = "/core/admin/index.php?";
$url .= isset($_POST['deleteinstall']) ? "deleteinstall=true&" : "";
$url .= $_SERVER['QUERY_STRING'];
header("location: ".$url);exit;
} ?>
<!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Full Bhuna - Site Features</title>
<!-- InstanceEndEditable -->
<?php require_once('../includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('../includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" --><form action="setup.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post" id="form1">
  <h1>Set up</h1>
  
  <h2>Choose your features...</h2>
  <p>You can, of course choose all, but it is recommended you choose only those needed to keep site simple. More can be added later, if required.</p>
  
  
 <fieldset class="form-inline">
    <label>Site:
      <select name="regionID"  id="regionID" class="form-control">
        <?php
do {  
?>
        <option value="<?php echo $row_rsRegions['ID']?>"><?php echo $row_rsRegions['title']?></option>
        <?php
} while ($row_rsRegions = mysql_fetch_assoc($rsRegions));
  $rows = mysql_num_rows($rsRegions);
  if($rows > 0) {
      mysql_data_seek($rsRegions, 0);
	  $row_rsRegions = mysql_fetch_assoc($rsRegions);
  }
?>
        </select>
      </label> <button type="submit" class="btn btn-primary">Go to your new site...</button> <label><input id="deleteinstall1" onClick="document.getElementById('deleteinstall2').checked = this.checked" name="deleteinstall" type="checkbox" value="1" checked> Delete install files</label>
   </fieldset><table class="form-table">
      <tr>
        <td colspan="2"><strong>Functionality</strong></td>
        <td><strong>Show in Main menu</strong></td>
        <td><strong>Show in Footer Menu</strong></td>
        <td colspan="2"><strong>Show in Admin Menu</strong></td>
      </tr>
      <tr>
        <td><input name="home2" type="checkbox" disabled="disabled" id="home2" value="1" checked="checked"  /></td>
        <td>Home Page<input type="hidden" name="home" id="home" value="1" /></td>
        <td><input name="mainmenu-home" type="text" class="form-control"  id="mainmenu-home" value="Home" /></td>
        <td><input name="footermenu-home" type="text" class="form-control"  id="footermenu-home" value="Home" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><input name="news" type="checkbox" id="news" value="1" /></td>
        <td>News</td>
        <td><input name="mainmenu-news" type="text" class="form-control"  id="mainmenu-news" value="News" /></td>
        <td><input name="footermenu-news" type="text" class="form-control"  id="footermenu-news" value="News" /></td>
        <td><img src="../../core/images/icons/newspaper.png" alt="Manage news" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-news" type="text" class="form-control"  id="adminmenu-news" value="Manage News" /></td>
      </tr>
      <tr>
        <td><input name="calendar" type="checkbox" id="calendar" value="1" /></td>
        <td>Events</td>
        <td><input name="mainmenu-calendar" type="text" class="form-control"  id="mainmenu-calendar" value="Events" /></td>
        <td><input name="footermenu-calendar" type="text" class="form-control"  id="footermenu-calendar" value="Events" /></td>
        <td><img src="../../core/images/icons/date.png" alt="Manage calendar" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-calendar" type="text" class="form-control"  id="adminmenu-calendar" value="Manage Events" /></td>
      </tr>
      <tr>
        <td><input name="articles" type="checkbox" id="articles" value="1" checked="checked" /></td>
        <td>Pages</td>
        <td><input name="mainmenu-articles" type="text" class="form-control"  id="mainmenu-articles" value="Articles" /></td>
        <td><input name="footermenu-articles" type="text" class="form-control"  id="footermenu-articles" value="Articles" /></td>
        <td><img src="../../core/images/icons/book_edit.png" alt="Manage articles" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-articles" type="text" class="form-control"  id="adminmenu-articles" value="Manage Pages" /></td>
      </tr>
      <tr>
        <td><input name="documents" type="checkbox" id="documents" value="1" /></td>
        <td>Documents</td>
        <td><input name="mainmenu-documents" type="text" class="form-control"  id="mainmenu-documents" value="Documents" /></td>
        <td><input name="footermenu-video2" type="text" class="form-control"  id="footermenu-documents" value="Documents" /></td>
        <td><img src="../../core/images/icons/edit-copy.png" alt="Manage documents" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-documents" type="text" class="form-control"  id="adminmenu-documents" value="Manage Documents" /></td>
      </tr>
      <tr>
        <td><input name="video" type="checkbox" id="video" value="1" /></td>
        <td>Video</td>
        <td><input name="mainmenu-video" type="text" class="form-control"  id="mainmenu-mainmenu" value="Video" /></td>
        <td><input name="footermenu-video" type="text" class="form-control"  id="footermenu-video" value="Video" /></td>
        <td><img src="../../core/images/icons/video-x-generic.png" alt="Manage articles" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-video" type="text" class="form-control"  id="adminmenu-video" value="Manage Video" /></td>
      </tr>
      <tr>
        <td><input name="directory" type="checkbox" id="directory" value="1" /></td>
        <td>Directory</td>
        <td><input name="mainmenu-directory" type="text" class="form-control"  id="mainmenu-directory" value="Directory" /></td>
        <td><input name="footermenu-directory" type="text" class="form-control"  id="footermenu-directory" value="Directory" /></td>
        <td><img src="../../core/images/icons/book_open.png" alt="Manage directory" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-directory" type="text" class="form-control"  id="adminmenu-directory" value="Manage Directory" /></td>
      </tr>
      
      <tr>
        <td><input name="blogs" type="checkbox" id="blogs" value="1" /></td>
        <td>Blogs</td>
        <td><input name="mainmenu-blogs" type="text" class="form-control"  id="mainmenu-blogs" value="Blogs" /></td>
        <td><input name="footermenu-blogs" type="text" class="form-control"  id="footermenu-blogs" value="Blogs" /></td>
        <td><img src="../../core/images/icons/comment_edit.png" alt="Manage blogs" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-blogs" type="text" class="form-control"  id="adminmenu-blogs" value="Manage Blogs" /></td>
      </tr>
    
      <tr>
        <td><input name="booking" type="checkbox" id="booking" value="1" /></td>
        <td>Booking</td>
        <td><input name="mainmenu-booking" type="text" class="form-control"  id="mainmenu-booking" value="Booking" /></td>
        <td><input name="footermenu-booking" type="text" class="form-control"  id="footermenu-booking" value="Links" /></td>
        <td><img src="../../core/images/icons/office-calendar.png" alt="Manage booking" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-booking" type="text" class="form-control"  id="adminmenu-booking" value="Manage Bookings" /></td>
      </tr>
      <tr>
        <td><input name="surveys" type="checkbox" id="surveys" value="1" /></td>
        <td>Surveys</td>
        <td><input name="mainmenu-surveys" type="text" class="form-control"  id="mainmenu-surveys" value="Surveys" /></td>
        <td><input name="footermenu-surveys" type="text" class="form-control"  id="footermenu-surveys" value="Surveys" /></td>
        <td><img src="../../core/images/icons/emoticon_smile.png" alt="Manage questionnaires" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-surveys" type="text" class="form-control"  id="adminmenu-surveys" value="Manage Polls &amp; Surveys" /></td>
      </tr>
      <tr>
        <td><input name="shop" type="checkbox" id="shop" value="1" /></td>
        <td>Shop</td>
        <td><input name="mainmenu-shop" type="text" class="form-control"  id="mainmenu-shop" value="Shop" /></td>
        <td><input name="footermenu-shop" type="text" class="form-control"  id="footermenu-shop" value="Shop" /></td>
        <td><img src="../../core/images/icons/cart.png" alt="Manage shop" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-shop" type="text" class="form-control"  id="adminmenu-shop" value="Manage Shop" /></td>
      </tr>
      <tr>
        <td><input name="forum" type="checkbox" id="forum" value="1" /></td>
        <td>Forum</td>
        <td><input name="mainmenu-forum" type="text" class="form-control"  id="mainmenu-forum" value="Forum" /></td>
        <td><input name="footermenu-forum" type="text" class="form-control"  id="footermenu-forum" value="Forum" /></td>
        <td><img src="../../core/images/icons/comments.png" alt="Manage forum" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-forum" type="text" class="form-control"  id="adminmenu-forum" value="Manage Forum" /></td>
      </tr>
      <tr>
        <td><input name="contact" type="checkbox" id="contact" value="1" /></td>
        <td>Contact Us</td>
        <td><input name="mainmenu-contact" type="text" class="form-control"  id="mainmenu-contact" value="Contact Us" /></td>
        <td><input name="footermenu-contact" type="text" class="form-control"  id="footermenu-contact" value="Contact Us" /></td>
        <td><img src="../../core/images/icons/cog.png" alt="Control Panel Home" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-contact" type="text" class="form-control"  id="adminmenu-contact" value="Control Panel Home" /></td>
      </tr>
      <tr>
        <td><input name="help" type="checkbox" id="help" value="1" /></td>
        <td>Help</td>
        <td><input name="mainmenu-help" type="text" class="form-control"  id="mainmenu-help" value="Help" /></td>
        <td><input name="footermenu-help" type="text" class="form-control"  id="footermenu-help" value="Help on this page" /></td>
        <td><img src="../../core/images/icons/help.png" alt="Manage help" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-help" type="text" class="form-control"  id="adminmenu-help" value="Manage Help" /></td>
      </tr>
      <tr>
        <td><input name="members" type="checkbox" id="members" value="1" /></td>
        <td>Members</td>
        <td><input name="mainmenu-members" type="text" class="form-control"  id="mainmenu-members" value="Members" /></td>
        <td><input name="footermenu-members" type="text" class="form-control"  id="footermenu-members" value="Members" /></td>
        <td><img src="../../core/images/icons/group.png" alt="Manage users" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-members" type="text" class="form-control"  id="adminmenu-members" value="Manage Users" /></td>
      </tr>
      <tr>
        <td><input name="regions" type="checkbox" id="regions" value="1" /></td>
        <td>Multi-sites</td>
        <td><input name="mainmenu-regions" type="text" class="form-control"  id="mainmenu-regions" value="Multi-sites" /></td>
        <td><input name="footermenu-regions" type="text" class="form-control"  id="footermenu-regions" value="Multi-sites" /></td>
        <td><img src="../../core/images/icons/world_edit.png" alt="Manage regions" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-regions" type="text" class="form-control"  id="adminmenu-regions" value="Manage Sites" /></td>
      </tr>
     
      
      <tr>
        <td><input name="furniture" type="checkbox" id="furniture" value="1" /></td>
        <td>Furniture</td>
        <td colspan="2">Admin only</td>
        <td><img src="../../core/images/icons/picture-frame.png" alt="Manage furniture" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-furniture" type="text" class="form-control"  id="adminmenu-furniture" value="Manage Furniture" /></td>
      </tr>
      <tr>
        <td><input name="logout" type="checkbox" id="logout" value="1" checked="checked" /></td>
        <td>Log Out</td>
        <td><input name="mainmenu-logout" type="text" class="form-control"  id="mainmenu-logout" value="Log Out" /></td>
        <td><input name="footermenu-logout" type="text" class="form-control"  id="footermenu-logout" value="Log Out" /></td>
        <td><img src="../../core/images/icons/logout.png" alt="Manage regions" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-logout" type="text" class="form-control"  id="adminmenu-logout" value="Log Out" /></td>
      </tr>
      
      
      <tr>
        <td><input name="groupemail" type="checkbox" id="groupemail" value="1" /></td>
        <td>Mail</td>
        <td colspan="2">Functionality Only</td>
        <td><img src='/core/images/icons/mail-reply-all.png' alt='Manage Group Email' style="vertical-align:
middle;" /></td>
        <td><input name="adminmenu-groupemail" type="text" class="form-control"  id="adminmenu-groupemail" value="Manage Mail" /></td>
      </tr>
      <tr>
        <td><input name="locations" type="checkbox" id="locations" value="1" /></td>
        <td>Locations</td>
        <td colspan="2">Functionality Only</td>
        <td><img src="../../core/images/icons/flag_blue.png" alt="Manage locations" width="16" height="16" align="absbottom" /></td>
        <td><input name="adminmenu-locations" type="text" class="form-control"  id="adminmenu-locations" value="Manage Locations" /></td>
      </tr>
      <tr>
        <td><input name="visitors" type="checkbox" id="visitors" value="1" checked="checked" /></td>
        <td>Visitor Reports</td>
        <td colspan="2">Functionality Only</td>
        <td><img src="../../core/images/icons/chart_pie.png" alt="Manage visitors" width="16" height="15" align="absbottom" /></td>
        <td><input name="adminmenu-visitors" type="text" class="form-control"  id="adminmenu-visitors" value="Manage Visitors" /></td>
      </tr>
      <tr>
        <td><input name="privacy" type="checkbox" id="privacy" value="1" /></td>
        <td>Privacy Policy</td>
        <td>&nbsp;</td>
        <td><input name="footermenu-privacy" type="text" class="form-control"  id="footermenu-privacy" value="Privacy Policy" /></td>
        <td>&nbsp;</td>
        <td><input name="adminmenu-privacy" type="text" class="form-control"  id="adminmenu-privacy" value="Legal" /></td>
      </tr>
      <tr>
        <td><input name="terms" type="checkbox" id="terms" value="1" /></td>
        <td>Terms &amp; Conditions</td>
        <td>&nbsp;</td>
        <td><input name="footermenu-terms" type="text" class="form-control"  id="footermenu-terms" value="Terms &amp; Conditions" /></td>
        <td>&nbsp;</td>
        <td>Included above</td>
      </tr>
      <tr>
        <td><input name="acknowledgements" type="checkbox" id="acknowledgements" value="1" /></td>
        <td>Acknowledgements</td>
        <td><input name="mainmenu-acknowledgements" type="text" class="form-control"  id="mainmenu-acknowledgements" value="Acknowledgements" /></td>
        <td><input name="footermenu-acknowledgements" type="text" class="form-control"  id="footermenu-acknowledgements" value="Acknowledgements" /></td>
        <td>&nbsp;</td>
        <td><input name="adminmenu-acknowledgements" type="text" class="form-control"  id="adminmenu-acknowledgements" value="Acknowledgements" /></td>
      </tr>
      <tr>
        <td><input name="emailafriend" type="checkbox" id="emailafriend" value="1" /></td>
        <td>Email a friend</td>
        <td><input name="mainmenu-email" type="text" class="form-control"  id="mainmenu-email" value="Email a friend" /></td>
        <td><input name="footermenu-email" type="text" class="form-control"  id="footermenu-email" value="Email a friend" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><input name="print" type="checkbox" id="print" value="1" /></td>
        <td>Print this page</td>
        <td><input name="mainmenu-print" type="text" class="form-control"  id="mainmenu-print" value="Print this page" /></td>
        <td><input name="footermenu-print" type="text" class="form-control"  id="footermenu-print" value="Print this page" /></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><input name="bookmarks" type="checkbox" id="bookmarks" value="1" /></td>
        <td>Social Bookmarks</td>
        <td colspan="4">Functionality Only</td>
      </tr>
      </table>
  

  <p>Now you can set up the rest of the site using the Control Panel...    </p>
  <p>
    <button type="submit" class="btn btn-primary" >Go to your new site...</button> <label><input id="deleteinstall2" onClick="document.getElementById('deleteinstall1').checked = this.checked" name="deleteinstall" type="checkbox" value="1" checked> Delete install files</label>
    </p>
</form>

<!-- InstanceEndEditable --></div>
</main>
<?php require_once('../includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsRegions);
?>