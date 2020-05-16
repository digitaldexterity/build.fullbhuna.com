<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('../core/includes/framework.inc.php'); ?>
<!doctype html><?php
$currentPage = $_SERVER["PHP_SELF"];

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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "comments")) {
  $insertSQL = sprintf("INSERT INTO photocomments (userID, photoID, comment, datetimeposted, IPaddress) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['userID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"),
                       GetSQLValueString($_POST['comment'], "text"),
                       GetSQLValueString($_POST['datetimeposted'], "date"),
                       GetSQLValueString($_POST['IPaddess'], "text"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

$colname_rsThisPhoto = "-1";
if (isset($_GET['photoID'])) {
  $colname_rsThisPhoto = $_GET['photoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisPhoto = sprintf("SELECT photos.*, users.ID AS userID, users.firstname, users.surname, users.email AS posters_email, photocategories.accesslevel FROM photos LEFT JOIN users ON (photos.userID = users.ID) LEFT JOIN photocategories ON (photos.categoryID = photocategories.ID) WHERE photos.ID = %s", GetSQLValueString($colname_rsThisPhoto, "int"));
$rsThisPhoto = mysql_query($query_rsThisPhoto, $aquiescedb) or die(mysql_error());
$row_rsThisPhoto = mysql_fetch_assoc($rsThisPhoto);
$totalRows_rsThisPhoto = mysql_num_rows($rsThisPhoto);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, usertypeID, firstname, surname, email, username FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$colname_rsComments = "1";
if (isset($_GET['photoID'])) {
  $colname_rsComments = $_GET['photoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsComments = sprintf("SELECT photocomments.comment, users.firstname, users.surname FROM photocomments LEFT JOIN users ON (photocomments.userID = users.ID) WHERE photoID = %s AND photocomments.active = 1 ORDER BY photocomments.ID ASC ", GetSQLValueString($colname_rsComments, "int"));
$rsComments = mysql_query($query_rsComments, $aquiescedb) or die(mysql_error());
$row_rsComments = mysql_fetch_assoc($rsComments);
$totalRows_rsComments = mysql_num_rows($rsComments);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMediaPrefs = "SELECT * FROM mediaprefs";
$rsMediaPrefs = mysql_query($query_rsMediaPrefs, $aquiescedb) or die(mysql_error());
$row_rsMediaPrefs = mysql_fetch_assoc($rsMediaPrefs);
$totalRows_rsMediaPrefs = mysql_num_rows($rsMediaPrefs);

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

 

?>
<!-- Web design by Paul Egan, Jim Campbell -->
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php  $pageTitle = "Photograph - ".$row_rsThisPhoto['title']; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="/SpryAssets/SpryValidationTextarea.js"></script>

<link href="/SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="/core/seo/css/defaultShare.css" rel="stylesheet"  />
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>
<script src="/core/seo/scripts/twittershare.js"></script>
   <meta name="title" content="<?php echo $row_rsThisPhoto['title']; ?>" />
   <link rel="image_src" href="<?php echo getProtocol()."://".$_SERVER['HTTP_HOST'].getImageURL($row_rsThisPhoto['imageURL'],"large"); ?>" /  />
    <meta name="description" content="<?php echo $row_rsThisPhoto['description']; ?>" />
<link href="css/defaultGallery.css" rel="stylesheet"  />
<script>
function MM_validateForm() { //v4.0
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
    for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!="") {
        if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
          if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
        } else if (test!='R') { num = parseFloat(val);
          if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
    } if (errors) alert('The following error(s) occurred:\n'+errors);
    document.MM_returnValue = (errors == '');
} }
</script>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
    <div class="container pageBody photo">
  <?php $row_rsLoggedIn['usertypeID'] = isset($row_rsLoggedIn['usertypeID']) ? $row_rsLoggedIn['usertypeID'] : 0;
  $row_rsThisPhoto['accesslevel'] = isset($row_rsThisPhoto['accesslevel']) ? $row_rsThisPhoto['accesslevel'] : 0;
  
if ($row_rsLoggedIn['usertypeID']>=$row_rsThisPhoto['accesslevel'] ) { // OK to access ?>
  
<h1><?php echo $row_rsThisPhoto['title']; ?></h1></td><nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
    <li><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to gallery </a></li>
   <?php if ($row_rsLoggedIn['ID'] == $row_rsThisPhoto['userID'] || $row_rsLoggedIn['usertypeID'] >=9) { // authorised to edit ?> <li>
      <a href="members/update_photo.php?photoID=<?php echo $row_rsThisPhoto['ID']; ?>" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Update this photo 
      
      </a></li>  <?php } ?>
  </ul></div></nav>
           <img src="<?php echo getImageURL($row_rsThisPhoto['imageURL'],"large"); ?>" class="large" />
            <p><?php echo nl2br($row_rsThisPhoto['description']); ?></p>
            
    <p>This photo was posted by <?php echo $row_rsThisPhoto['firstname']; ?> <?php echo $row_rsThisPhoto['surname']; ?>. <img src="../core/images/icons/group.png" alt="Profile" width="16" height="16" style="vertical-align:
middle;" />&nbsp;<a href="/members/profile/index.php?userID=<?php echo $row_rsThisPhoto['userID']; ?>">View profile</a>. </p>
    <?php if ($row_rsMediaPrefs['showcomments']==1) { ?>
    <h2><a name="comments" id="comments"></a>Comments</h2>
          <?php if ($totalRows_rsComments == 0) { // Show if recordset empty ?>
          <p>There are no comments posted so far on this photo.</p>
          <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsComments > 0) { // Show if recordset not empty ?>
          
        
            <?php do { ?>
            <div><strong><?php echo $row_rsComments['firstname']; ?> <?php echo $row_rsComments['surname']; ?> says:</strong><br /><?php echo nl2br($row_rsComments['comment']); ?></div>
            
            <?php } while ($row_rsComments = mysql_fetch_assoc($rsComments)); ?>
       
    <?php } // Show if recordset not empty ?>
         
          <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="comments" id="comments">
            <p><span id="sprytextarea1">
              <textarea name="comment" cols="60" rows="4" id="comment" onchange="MM_validateForm('comment','','R');return document.MM_returnValue"></textarea>
            <span class="textareaRequiredMsg">A comment is required.</span></span></p>
            <p>
              <input type="submit" class="button" value="Post Comment" /> 
              Posted by <?php echo $row_rsLoggedIn['firstname']; ?> <?php echo $row_rsLoggedIn['surname']; ?>.
              <input name="userID" type="hidden" id="userID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
              <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsThisPhoto['ID']; ?>" />
              <input name="datetimeposted" type="hidden" id="datetimeposted" value="<?php echo date('Y-m-d H:i:s'); ?>" />
              <input name="IPaddess" type="hidden" id="IPaddess" value="<?php echo getClientIP(); ?>" />
              <input name="posters_email" type="hidden" id="posters_email" value="<?php echo $row_rsThisPhoto['posters_email']; ?>" />
              <input name="firstname" type="hidden" id="firstname" value="<?php echo $row_rsLoggedIn['firstname']; ?>" />
              <input name="surname" type="hidden" id="surname" value="<?php echo $row_rsLoggedIn['surname']; ?>" />
              <input name="users_email" type="hidden" id="users_email" value="<?php echo $row_rsLoggedIn['email']; ?>" />
            </p>
            <input type="hidden" name="MM_insert" value="comments" />
</form><?php } // end comments ?><?php require_once('../core/share/includes/share.inc.php'); ?>
           <?php } else { //not OK to access ?>
    <p class="alert alert-danger" role="alert">The photo galleries are only available to members. Please <a href="../members/index.php">log in</a>.</p><?php } ?>
      <script>
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1", {hint:"Add a comment..."});
      </script></div>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisPhoto);

mysql_free_result($rsLoggedIn);

mysql_free_result($rsComments);

mysql_free_result($rsMediaPrefs);
?>

