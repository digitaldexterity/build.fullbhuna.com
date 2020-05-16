<?php require_once('../Connections/aquiescedb.php'); ?>
<?php require_once('../members/includes/userfunctions.inc.php'); ?>
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
?>
<?php
$colname_rsThisEmail = "-1";
if (isset($_GET['emailID'])) {
  $colname_rsThisEmail = $_GET['emailID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisEmail = sprintf("SELECT groupemail.subject, groupemail.usergroupID, usergroup.groupname FROM groupemail LEFT JOIN usergroup ON (groupemail.usergroupID = usergroup.ID) WHERE groupemail.ID = %s", GetSQLValueString($colname_rsThisEmail, "int"));
$rsThisEmail = mysql_query($query_rsThisEmail, $aquiescedb) or die(mysql_error());
$row_rsThisEmail = mysql_fetch_assoc($rsThisEmail);
$totalRows_rsThisEmail = mysql_num_rows($rsThisEmail);
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Forward to a friend"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<meta name="robots" content="noindex,nofollow" /><script src="../SpryAssets/SpryValidationTextField.js"></script>
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="css/mailDefault.css" rel="stylesheet" type="text/css" />
<?php 
if(isset($_POST['recipient']) && isset($_POST['emailID']) && isset($_POST['token']) && $_POST['token'] == md5(PRIVATE_KEY.$_POST['emailID'])) { // security
	$groupID = (isset($_POST['usergroupID']) && $_POST['usergroupID']>0) ? intval($_POST['usergroupID']) : 0;
	$userID = createNewUser($_POST['firstname'],$_POST['surname'],$_POST['recipient'],-1,$groupID);
	
	?>
    <script>
    getData("/mail/ajax/groupemail.ajax.php?groupemailID=<?php echo intval($_POST['emailID']); ?>&recipient=<?php echo urlencode($_POST['recipient']); ?>");
    </script>
    <?php
	$msg = "The email was successfully sent to recipient at: ".$_POST['recipient'];
} ?>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
  <?php $token = md5(PRIVATE_KEY.$_GET['emailID']);
  if(isset($_GET['emailID']) && isset($_GET['token']) && $_GET['token'] == $token) { // access control ?>
    <h1 class="mailheader">Forward to a friend</h1>
    <h2><?php echo htmlentities($row_rsThisEmail['subject']); ?></h2>
  <?php require_once('../core/includes/alert.inc.php'); ?>
    <p>If you think a friend would like to receive this email, then enter their details below and we'll send them a copy.</p>
    <form action="" method="post" name="form1" id="form1"><table border="0" cellpadding="0" cellspacing="0" class="form-table">
  <tr>
    <td align="right"><label for="firstname">First name:</label></td>
    <td><span id="sprytextfield1">
          <input name="firstname" type="text" id="firstname" size="20" maxlength="50" />
        
        <span class="textfieldRequiredMsg">A value is required.</span></span><span id="sprytextfield2">
        <label>Surname:
          <input name="surname" type="text" id="surname" size="20" maxlength="50" />
        </label><span class="textfieldRequiredMsg">A value is required.</span></span></td>
  </tr>
  <tr>
    <td align="right"><label for="email">Email: </label></td>
    <td><span id="sprytextfield3">
        
          <input name="recipient" type="text" id="recipient" size="50" maxlength="100" />
       
        <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
  </tr> <?php if($row_rsThisEmail['usergroupID']>0) { ?>
  <tr>
    <td align="right">&nbsp;</td>
    <td><label>
          <input type="checkbox" name="usergroupID" id="usergroupID" value="<?php echo $row_rsThisEmail['usergroupID']; ?>" />
          Add to distribution list for: <?php echo htmlentities($row_rsThisEmail['groupname']); ?></label></td>
  </tr><?php } ?>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submitbutton" id="submitbutton" value="Submit" />
        <input name="emailID" type="hidden" id="emailID" value="<?php echo intval($_GET['emailID']); ?>" />
        <input type="hidden" name="token" id="token" value="<?php echo htmlentities($_GET['token']); ?>" /></td>
  </tr>
</table>
      
    </form>
 <?php } else { ?>
 <p>Sorry, you do not have access to this page.</p>
 <?php } ?>
  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email");
//-->
  </script>
  <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisEmail);
?>
