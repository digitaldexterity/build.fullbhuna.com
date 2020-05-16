<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../../core/includes/framework.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "8,9,10";
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true&alert=".urlencode("You need to be logged in as an Administrator to access this page.");
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_aquiescedb, $aquiescedb);
if(isset($_GET['deleteemailID'])) {
	$delete = "DELETE FROM productemail WHERE ID = ".intval($_GET['deleteemailID']);	
 	mysql_query($delete, $aquiescedb) or die(mysql_error());
	header("location: index.php?defaultTab=2"); exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE productprefs SET saleemail=%s, confirmationemail=%s, confemailsubject=%s, confemailmessage=%s, dispatchemailsubject=%s, dispatchemailmessage=%s, dispatchemailtemplateID=%s, dispatchsms=%s, confemailcc=%s, dispatchemailcc=%s, confemailtemplateID=%s, conffreeemailtemplateID=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['saleemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['confirmationemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['confemailsubject'], "text"),
                       GetSQLValueString($_POST['confemailmessage'], "text"),
                       GetSQLValueString($_POST['dispatchemailsubject'], "text"),
					   GetSQLValueString($_POST['dispatchemailmessage'], "text"),
                       GetSQLValueString($_POST['dispatchemailtemplateID'], "int"),
					   GetSQLValueString(isset($_POST['dispatchsms']) ? "true" : "", "defined","1","0"),
					   GetSQLValueString($_POST['confemailcc'], "text"),
					   GetSQLValueString($_POST['dispatchemailcc'], "text"),
                       GetSQLValueString($_POST['confemailtemplateID'], "int"),
                       GetSQLValueString($_POST['conffreeemailtemplateID'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  
  
  if (isset($_POST["followuptemplateID"]) && $_POST["followuptemplateID"]>0) {
	$period = $_POST['multiple']*$_POST['length'];
	$viaemail = isset($_POST['viaemail']) ? 1 : 0;
	$viasms = isset($_POST['viasms']) ? 1 : 0;
	$ignoreoptout = isset($_POST['ignoreoptout']) ? 1 : 0;
	
	$insert = "INSERT INTO productemail (templateID, categoryID, period, purchasemade, viaemail, viasms, ignoreoptout, regionID, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['followuptemplateID'], "int").",".GetSQLValueString($_POST['categoryID'], "int").",".GetSQLValueString($period, "int").",".GetSQLValueString($_POST['purchasemade'], "int").",".GetSQLValueString($viaemail, "int").",".GetSQLValueString($viasms, "int").",".GetSQLValueString($ignoreoptout, "int").",".GetSQLValueString($regionID, "int").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
  	mysql_query($insert, $aquiescedb) or die(mysql_error());
	$updateGoTo = "index.php?defaultTab=2";
	
	//print_r($_POST);
	//die($insert);
	
	
}


  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductPrefs = "SELECT * FROM productprefs WHERE ID = ".$regionID . "";
$rsProductPrefs = mysql_query($query_rsProductPrefs, $aquiescedb) or die(mysql_error());
$row_rsProductPrefs = mysql_fetch_assoc($rsProductPrefs);
$totalRows_rsProductPrefs = mysql_num_rows($rsProductPrefs);


$varRegionID_rsTemplate = "1";
if (isset($regionID)) {
  $varRegionID_rsTemplate = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTemplate = sprintf("SELECT groupemailtemplate.ID, groupemailtemplate.templatename FROM groupemailtemplate WHERE groupemailtemplate.regionID = %s ORDER BY groupemailtemplate.templatename", GetSQLValueString($varRegionID_rsTemplate, "int"));
$rsTemplate = mysql_query($query_rsTemplate, $aquiescedb) or die(mysql_error());
$row_rsTemplate = mysql_fetch_assoc($rsTemplate);
$totalRows_rsTemplate = mysql_num_rows($rsTemplate);

$maxRows_rsProductEmails = 10;
$pageNum_rsProductEmails = 0;
if (isset($_GET['pageNum_rsProductEmails'])) {
  $pageNum_rsProductEmails = $_GET['pageNum_rsProductEmails'];
}
$startRow_rsProductEmails = $pageNum_rsProductEmails * $maxRows_rsProductEmails;

$varRegionID_rsProductEmails = "1";
if (isset($regionID)) {
  $varRegionID_rsProductEmails = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsProductEmails = sprintf("SELECT productemail.*, groupemailtemplate.templatename, productcategory.title FROM productemail LEFT JOIN groupemailtemplate ON (productemail.templateID = groupemailtemplate.ID) LEFT JOIN  productcategory ON (productemail.categoryID = productcategory.ID) WHERE productemail.regionID = %s OR productemail.regionID = 0", GetSQLValueString($varRegionID_rsProductEmails, "int"));
$query_limit_rsProductEmails = sprintf("%s LIMIT %d, %d", $query_rsProductEmails, $startRow_rsProductEmails, $maxRows_rsProductEmails);
$rsProductEmails = mysql_query($query_limit_rsProductEmails, $aquiescedb) or die(mysql_error());
$row_rsProductEmails = mysql_fetch_assoc($rsProductEmails);

if (isset($_GET['totalRows_rsProductEmails'])) {
  $totalRows_rsProductEmails = $_GET['totalRows_rsProductEmails'];
} else {
  $all_rsProductEmails = mysql_query($query_rsProductEmails);
  $totalRows_rsProductEmails = mysql_num_rows($all_rsProductEmails);
}
$totalPages_rsProductEmails = ceil($totalRows_rsProductEmails/$maxRows_rsProductEmails)-1;

$varRegionID_rsCategories = "1";
if (isset($regionID)) {
  $varRegionID_rsCategories = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = sprintf("SELECT productcategory.ID, productcategory.title FROM productcategory WHERE productcategory.statusID = 1 AND (productcategory.regionID = %s   OR productcategory.regionID =0) GROUP BY productcategory.ID ORDER BY productcategory.title ASC", GetSQLValueString($varRegionID_rsCategories, "int"));
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Customer Relationship Management"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../css/defaultProducts.css" rel="stylesheet"  />
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<script>$(document).ready(function(e) {
    toggleDispatchSMS();
	$("#dispatchemailtemplateID").change(function(){
		toggleDispatchSMS();
	});
});

function toggleDispatchSMS() {
	if($("#dispatchemailtemplateID").val()==0) {
		$("#dispatchSMS").hide();
	} else {
		$("#dispatchSMS").show();
	}
}
</script>
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >
<?php if(isset($body_class)) $body_class .= " products ";  ?>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
    <div class="page">
      <?php require_once('../../../core/region/includes/chooseregion.inc.php'); ?>
      <h1><i class="glyphicon glyphicon-shopping-cart"></i> Customer Relationship Management</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
          <ul class="nav navbar-nav">
            <li class="nav-item"><a href="/products/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Orders</a></li>
            <li class="nav-item"><a href="../../../mail/admin/templates/index.php" class="nav-link"><i class="glyphicon glyphicon-file"></i> Email Templates</a></li>
            <li class="nav-item"><a href="../../../mail/admin/reminders/index.php" class="nav-link"><i class="glyphicon glyphicon-time"></i> Scheduled Message Queue</a></li>
          </ul>
        </div>
      </nav>
      <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">
        <div id="TabbedPanels1" class="TabbedPanels">
          <ul class="TabbedPanelsTabGroup">
            <li class="TabbedPanelsTab" tabindex="0">On Sale</li>
            <li class="TabbedPanelsTab" tabindex="0">At Dispatch</li>
            <li class="TabbedPanelsTab" tabindex="0">Follow ups</li>
          </ul>
          <div class="TabbedPanelsContentGroup">
            <div class="TabbedPanelsContent">
              <p>
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['saleemail'],1))) {echo "checked=\"checked\"";} ?> name="saleemail" type="checkbox" id="saleemail" value="1" />
                  Send email to administrator on successful transactions</label>
              </p>
              <p class="form-inline">
                <label>
                  <input <?php if (!(strcmp($row_rsProductPrefs['confirmationemail'],1))) {echo "checked=\"checked\"";} ?> name="confirmationemail" type="checkbox" id="confirmationemail" value="1" />
                  Send  email to customer on successful transaction: </label>
                <select name="confemailtemplateID" id="confemailtemplateID" class="form-control">
                  <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['confemailtemplateID']))) {echo "selected=\"selected\"";} ?>>As below</option>
                  <?php
do {  
?>
                  <option value="<?php echo $row_rsTemplate['ID']?>"<?php if (!(strcmp($row_rsTemplate['ID'], $row_rsProductPrefs['confemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTemplate['templatename']?></option>
                  <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
  $rows = mysql_num_rows($rsTemplate);
  if($rows > 0) {
      mysql_data_seek($rsTemplate, 0);
	  $row_rsTemplate = mysql_fetch_assoc($rsTemplate);
  }
?>
                </select>
              </p>
              <table border="0" cellpadding="2" cellspacing="0" class="form-table">
                <tr>
                  <td class="text-right"><label for="confemailsubject">Subject:</label></td>
                  <td><input name="confemailsubject" type="text"  id="confemailsubject" value="<?php echo isset($row_rsProductPrefs['confemailsubject']) ? $row_rsProductPrefs['confemailsubject'] : "Successful transaction"; ?>" size="50" maxlength="50" class="form-control" /></td>
                </tr>
                <tr>
                  <td class="text-right top"><label  for="confemailmessage">Message: </label></td>
                  <td><textarea name="confemailmessage" id="confemailmessage" cols="48" rows="10" class="form-control"><?php if(isset($row_rsProductPrefs['confemailmessage'])) { echo $row_rsProductPrefs['confemailmessage']; } else { ?>Dear {firstname},
                
Thank you for your order detailed below. It will be dispatched shortly.
                
{order}

If you have any queries regarding this order please quote order code:

{code}

You can view or print an invoice here:

{invoicelink}
<?php } ?>
                </textarea></td>
                </tr>
                <tr>
                  <td class="text-right top"><label for="confemailcc" class="form-inline">BCC email:</label></td>
                  <td><input name="confemailcc" type="text" id="confemailcc" value="<?php echo $row_rsProductPrefs['confemailcc']; ?>" size="50" maxlength="100" placeholder="optional" class="form-control"></td>
                </tr>
              </table>
              <p>
                <label class="form-inline">Send alternative email for no charge orders:
                  <select name="conffreeemailtemplateID" id="conffreeemailtemplateID" class="form-control">
                    <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['conffreeemailtemplateID']))) {echo "selected=\"selected\"";} ?>>Not applicable</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsTemplate['ID']?>"<?php if (!(strcmp($row_rsTemplate['ID'], $row_rsProductPrefs['conffreeemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTemplate['templatename']?></option>
                    <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
  $rows = mysql_num_rows($rsTemplate);
  if($rows > 0) {
      mysql_data_seek($rsTemplate, 0);
	  $row_rsTemplate = mysql_fetch_assoc($rsTemplate);
  }
?>
                  </select>
                </label>
              </p>
            </div>
            <div class="TabbedPanelsContent">
              <table class="form-table">
                <tr>
                  <td class="top text-right"><label for="dispatchemailtemplateID">Send emai:</label></td>
                  <td><select name="dispatchemailtemplateID" id="dispatchemailtemplateID" class="form-control">
                      <option value="0" <?php if (!(strcmp(0, $row_rsProductPrefs['dispatchemailtemplateID']))) {echo "selected=\"selected\"";} ?>>As below</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsTemplate['ID']?>"<?php if (!(strcmp($row_rsTemplate['ID'], $row_rsProductPrefs['dispatchemailtemplateID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsTemplate['templatename']?></option>
                      <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
  $rows = mysql_num_rows($rsTemplate);
  if($rows > 0) {
      mysql_data_seek($rsTemplate, 0);
	  $row_rsTemplate = mysql_fetch_assoc($rsTemplate);
  }
?>
                    </select>
                    <div id="dispatchsms"><label><input <?php if (!(strcmp($row_rsProductPrefs['dispatchsms'],1))) {echo "checked=\"checked\"";} ?> name="dispatchsms" type="checkbox" value="1">&nbsp;Also send Template SMS</label></div></td>
                </tr>
                <tr>
                  <td class="text-right"><label for="dispatchemailsubject">Subject:</label></td>
                  <td><input name="dispatchemailsubject" type="text"  id="dispatchemailsubject" value="<?php echo isset($row_rsProductPrefs['dispatchemailsubject']) ? $row_rsProductPrefs['dispatchemailsubject'] : "Your order has been dispatched ([order])"; ?>" size="50" maxlength="50" class="form-control" /></td>
                </tr>
                <tr>
                  <td class="top text-right"><label for="dispatchemailmessage">Message:</label></td>
                  <td><textarea name="dispatchemailmessage" id="dispatchemailmessage" cols="48" rows="10" class="form-control"><?php if(isset($row_rsProductPrefs['dispatchemailmessage'])) { echo $row_rsProductPrefs['dispatchemailmessage']; } else { ?>Dear {customer},
                    
We are happy to inform you that the following items in your order have been dispatched and should be with you shortly:
            
ORDER NUMBER: {code} 
            
{dispatched}
            
DELIVERED TO:
            
{delivery}
            
This is an automated email, please do not reply. To contact us, visit our web site <?php echo getProtocol()."://". $_SERVER['HTTP_HOST']; ?>

Regards,
<?php echo $site_name; ?> Dispatch Team
<?php } ?>
                    </textarea></td>
                </tr>
                <tr>
                  <td class="top text-right">CC email:</td>
                  <td><input name="dispatchemailcc" type="text" id="dispatchemailcc" value="<?php echo $row_rsProductPrefs['dispatchemailcc']; ?>" size="50" maxlength="100"  class="form-control" placeholder="optional"></td>
                </tr>
              </table>
            </div>
            <div class="TabbedPanelsContent">
              <fieldset class="form-inline">
                <p>Emails can be sent at a set period after a visit:</p>
                <p>
                  <select name="followuptemplateID" id="followuptemplateID" class="form-control">
                    <option value="0" >Choose template...</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsTemplate['ID']?>"><?php echo $row_rsTemplate['templatename']?></option>
                    <?php
} while ($row_rsTemplate = mysql_fetch_assoc($rsTemplate));
  $rows = mysql_num_rows($rsTemplate);
  if($rows > 0) {
      mysql_data_seek($rsTemplate, 0);
	  $row_rsTemplate = mysql_fetch_assoc($rsTemplate);
  }
?>
                  </select>
                  in
                  <input name="multiple" value="1" type="text" size="5" maxlength="5"  class="form-control">
                  <select name="length"  class="form-control">
                    <option value="60" >minutes</option>
                    <option value="3600" selected>hours</option>
                    <option value="86400" >days</option>
                    <option value="604800" >weeks</option>
                  </select>
                  after
                  <select name="purchasemade"  class="form-control">
                    <option value="2" >paid transactions</option>
                    <option value="1" >all transactions</option>
                    <option value="0">checkout abandoned*</option>
                  </select>
                  in
                  <select name="categoryID" id="categoryID"  class="form-control">
                    <option value="0" selected>any category</option>
                    <?php
do {  
?>
                    <option value="<?php echo $row_rsCategories['ID']?>"><?php echo $row_rsCategories['title']?></option>
                    <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                  </select>
                <div>
                  <label>
                    <input type="checkbox" name="viaemail" checked>
                    &nbsp;Email </label>
                  &nbsp;&nbsp;
                  <label>
                    <input type="checkbox" name="viasms" >
                    &nbsp;SMS </label>
                  &nbsp;&nbsp;
                  <label>
                    <input type="checkbox" value="1" onClick="if(this.checked) alert('This will send to users who have opted out of receiving communications. Please enure this complies with GDPR or similar legislation');" name="ignoreoptout">
                    &nbsp;Override  opt-out</label>
                  <button type="submit" name="followup" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-plus-sign"></i> Add</button>
                </div>
                &nbsp;&nbsp;
                <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
                </p>
              </fieldset>
              <?php if ($totalRows_rsProductEmails > 0) { // Show if recordset not empty ?>
                <p class="text-muted">Emails <?php echo ($startRow_rsProductEmails + 1) ?> to <?php echo min($startRow_rsProductEmails + $maxRows_rsProductEmails, $totalRows_rsProductEmails) ?> of <?php echo $totalRows_rsProductEmails ?> </p>
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>&nbsp;</th>
                      <th>Template</th>
                      <th>Period</th>
                      <th>After Event</th>
                      <th>Category</th>
                      <th>Type</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php do { ?>
                      <tr>
                        <td class="status<?php echo $row_rsProductEmails['statusID']; ?>">&nbsp;</td>
                        <td><?php echo $row_rsProductEmails['templatename']; ?></td>
                        <td><?php if($row_rsProductEmails['period']>=604800 && intval($row_rsProductEmails['period']/604800) == $row_rsProductEmails['period']/604800) { 
				echo intval($row_rsProductEmails['period']/604800) ;
				 echo " weeks";
				} else if($row_rsProductEmails['period']>=86400 && intval($row_rsProductEmails['period']/86400) == $row_rsProductEmails['period']/86400) { 
				echo intval($row_rsProductEmails['period']/86400);
				 echo " days" ;
				 } else if($row_rsProductEmails['period']>=3600 && intval($row_rsProductEmails['period']/3600) == $row_rsProductEmails['period']/3600) { 
				echo intval($row_rsProductEmails['period']/3600);
				 echo " hours" ;
				 } else { echo intval($row_rsProductEmails['period']/60); echo " minutes";}
				 
				 ?></td>
                        <td><?php switch ($row_rsProductEmails['purchasemade']) {
						  case 2 : echo "Paid Transaction"; break;
						   case 1 : echo "Any Transaction"; break;
						   default  : echo "Checkout Abandoned"; }  ?></td>
                        <td><?php echo isset($row_rsProductEmails['title']) ? $row_rsProductEmails['title'] : "Any Category"; ?></td>
                        <td><?php echo ($row_rsProductEmails['viaemail']==1) ? "E" : ""; ?><?php echo ($row_rsProductEmails['viasms']==1) ? "S" : ""; ?><?php echo ($row_rsProductEmails['ignoreoptout']==1) ? "O" : ""; ?></td>
                        <td><a href="update_followup.php?followupID=<?php echo $row_rsProductEmails['ID']; ?>" class="btn btn-default"><i class="glyphicon glyphicon-pencil"></i> Edit</a> <a href="index.php?deleteemailID=<?php echo $row_rsProductEmails['ID']; ?>" class="btn btn-default" onClick="return confirm('Are you sure you want to delete this email?');"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
                      </tr>
                      <?php } while ($row_rsProductEmails = mysql_fetch_assoc($rsProductEmails)); ?>
                  </tbody>
                </table>
                <?php } // Show if recordset not empty ?>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" >Save changes</button>
        <input name="ID" type="hidden" id="ID" value="<?php echo $regionID; ?>" />
        <input type="hidden" name="MM_update" value="form1">
        <div>
          <h2>Merge fields</h2>
          <p>{customer} = customer's  name </p>
          <p>{firstname} = customer's first name</p>
          <p>{surname} = customer's surname</p>
          <p>{order} = customers order </p>
          <p>{code} = unique order code</p>
          <p>{basketlink} = link to saved basket</p>
          <p>{invoicelink} = link to print invoice</p>
          <p>{vatnumber} = your VAT number</p>
          <p>{purchaseorder} = your PO number</p>
          <p>{dispatched} = items dispatched</p>
          <p>{delivery} = delivery address</p>
          <p>{reviewlink} = link address to review product (first product in basket)</p>
          <p>{productID} = ID of first product in basket </p>
          <p>{productname} = Name of first product in basket </p>
          <p>* checkout abandoned happens after a customer enters their contact details at checkout but no sale in subsequent hour (period = 1 hour + entered value)</p>
        </div>
      </form>
    </div>
    <?php if (isset($_GET['defaultTab'])) { echo '<script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab:'.intval($_GET['defaultTab']).'});
//-->
    </script>'; } else { ?>
    <script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
    </script>
    <?php } ?>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($rsProductPrefs);

mysql_free_result($rsTemplate);

mysql_free_result($rsProductEmails);

mysql_free_result($rsCategories);

mysql_free_result($rsLoggedIn);

?>
