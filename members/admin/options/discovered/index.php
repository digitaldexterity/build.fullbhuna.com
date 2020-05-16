<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?><?php
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

$MM_restrictGoTo = "../../../../login/index.php?notloggedin=true";
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
?><?php
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertSQL = sprintf("INSERT INTO discovered (`description`, regionID) VALUES (%s, %s)",
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['regionID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE preferences SET askhowdiscovered=%s, askhowdiscoveredother=%s WHERE ID=%s",
                       GetSQLValueString(isset($_POST['askhowdiscovered']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['askhowdiscoveredother']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

$varRegionID_rsDiscovered = "1";
if (isset($regionID)) {
  $varRegionID_rsDiscovered = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsDiscovered = sprintf("SELECT discovered.*, COUNT(users.ID) AS count FROM discovered LEFT JOIN users ON (users.discovered = discovered.ID) WHERE discovered.regionID = %s GROUP BY discovered.ID ORDER BY ordernum", GetSQLValueString($varRegionID_rsDiscovered, "int"));
$rsDiscovered = mysql_query($query_rsDiscovered, $aquiescedb) or die(mysql_error());
$row_rsDiscovered = mysql_fetch_assoc($rsDiscovered);
$totalRows_rsDiscovered = mysql_num_rows($rsDiscovered);

$varRegionID_rsPreferences = "1";
if (isset($regionID)) {
  $varRegionID_rsPreferences = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = sprintf("SELECT askhowdiscovered, preferences.askhowdiscoveredother FROM preferences WHERE ID= %s", GetSQLValueString($varRegionID_rsPreferences, "int"));
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

if($row_rsDiscovered==0) {
	duplicateMySQLRecord ("preferences", 1, "ID", $regionID) ;
	header("location: index.php"); exit;
}

?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Users discovery"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../../SpryAssets/SpryValidationTextField.js"></script>
<link href="../../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../../css/membersDefault.css" rel="stylesheet"  />
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=discovered&"+order); 
            } 
        }); 
		
    }); 
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --> <div class="page users">  <?php require_once('../../../../core/region/includes/chooseregion.inc.php'); ?>
   <h1><i class="glyphicon glyphicon-user"></i> How did users discover the site?</h1>
   <form action="<?php echo $editFormAction; ?>" method="POST" name="form1" id="form1">
    <fieldset>
    <input <?php if (!(strcmp($row_rsPreferences['askhowdiscovered'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="askhowdiscovered" id="askhowdiscovered" onChange="this.form.submit()" />
       Ask how users discovered the site during sign-up     
       <input name="ID" type="hidden" id="ID" value="1" />
    
     <input type="hidden" name="MM_update" value="form1" />&nbsp;&nbsp;&nbsp;
     <label>
       <input <?php if (!(strcmp($row_rsPreferences['askhowdiscoveredother'],1))) {echo "checked=\"checked\"";} ?> name="askhowdiscoveredother" type="checkbox" id="askhowdiscoveredother" value="1" onChange="this.form.submit()" >
       Add text box for 'Other'</label>
    </fieldset>
   </form>
   
   <?php if ($totalRows_rsDiscovered == 0) { // Show if recordset empty ?>
     <p>There are currently no discovery methods stored.</p>
     <?php } // Show if recordset empty ?>
   <?php if ($totalRows_rsDiscovered > 0) { // Show if recordset not empty ?>
   <div id="info">Drag and drop to re-order</div>
  <ul class="listTable sortable">
<li class="header">
  <span>&nbsp;</span> <span>&nbsp;</span>
  
              <span>Method</span>
          <span>Count</span>
              <span>Edit</span>
       </li>
        <?php do { ?>
          
         <li  id="listItem_<?php echo $row_rsDiscovered['ID']; ?>">   <span class="handle">&nbsp;</span>
             <span class="status<?php echo $row_rsDiscovered['statusID']; ?>">&nbsp;</span>
           
            <span><?php echo $row_rsDiscovered['description']; ?></span>
            <span><?php echo $row_rsDiscovered['count']; ?></span>
            <span><a href="update_method.php?methodID=<?php echo $row_rsDiscovered['ID']; ?>" class="link_edit icon_only">View</a></span>
          </li>
          <?php } while ($row_rsDiscovered = mysql_fetch_assoc($rsDiscovered)); ?>
     </ul>
  <?php } // Show if recordset not empty ?>
<h2>Add discovery method   </h2>
   <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?>
<form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2">
     <table class="form-table"> <tr>
         <td class="text-nowrap text-right">Method:</td>
         <td><span id="sprytextfield1">
           <input name="description" type="text"  value="" size="50" maxlength="100" />
          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit">Add method</button>
           <input name="regionID" type="hidden" id="regionID" value="<?php echo $regionID; ?>"></td>
       </tr>
     </table>
     <input type="hidden" name="MM_insert" value="form2" />
   </form>
   </div>
   <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
   </script>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsDiscovered);

mysql_free_result($rsPreferences);
?>
