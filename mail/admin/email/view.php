<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "7,8,9,10";
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

$MM_restrictGoTo = "../../../login/index.php?notloggedin=true";
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

$maxRows_rsCorrespondence = 20;
$pageNum_rsCorrespondence = 0;
if (isset($_GET['pageNum_rsCorrespondence'])) {
  $pageNum_rsCorrespondence = $_GET['pageNum_rsCorrespondence'];
}
$startRow_rsCorrespondence = $pageNum_rsCorrespondence * $maxRows_rsCorrespondence;

$varCorrespondenceID_rsCorrespondence = "-1";
if (isset($_GET['correspondenceID'])) {
  $varCorrespondenceID_rsCorrespondence = $_GET['correspondenceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCorrespondence = sprintf("SELECT correspondence.ID, correspondence.recipient, correspondence.subject, correspondence.createddatetime, correspondence.mailfolderID, correspondence.autoreply, correspondence.sender, correspondence.reply_using, directory.name AS company,  correspondence.message, correspondence.sendername FROM correspondence LEFT JOIN directory ON (correspondence.directoryID = directory.ID)  WHERE correspondence.ID = %s ORDER BY createddatetime DESC", GetSQLValueString($varCorrespondenceID_rsCorrespondence, "int"));
$query_limit_rsCorrespondence = sprintf("%s LIMIT %d, %d", $query_rsCorrespondence, $startRow_rsCorrespondence, $maxRows_rsCorrespondence);
$rsCorrespondence = mysql_query($query_limit_rsCorrespondence, $aquiescedb) or die(mysql_error());
$row_rsCorrespondence = mysql_fetch_assoc($rsCorrespondence);

if (isset($_GET['totalRows_rsCorrespondence'])) {
  $totalRows_rsCorrespondence = $_GET['totalRows_rsCorrespondence'];
} else {
  $all_rsCorrespondence = mysql_query($query_rsCorrespondence);
  $totalRows_rsCorrespondence = mysql_num_rows($all_rsCorrespondence);
}
$totalPages_rsCorrespondence = ceil($totalRows_rsCorrespondence/$maxRows_rsCorrespondence)-1;

$varCorrespondenceID_rsAttachments = "-1";
if (isset($_GET['correspondenceID'])) {
  $varCorrespondenceID_rsAttachments = $_GET['correspondenceID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAttachments = sprintf("SELECT mailattachments.ID, mailattachments.filename, mailattachments.mimetype FROM mailattachments WHERE mailattachments.correspondenceID = %s", GetSQLValueString($varCorrespondenceID_rsAttachments, "int"));
$rsAttachments = mysql_query($query_rsAttachments, $aquiescedb) or die(mysql_error());
$row_rsAttachments = mysql_fetch_assoc($rsAttachments);
$totalRows_rsAttachments = mysql_num_rows($rsAttachments);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "View Email"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link href="../../../documents/css/documentsDefault.css" rel="stylesheet" type="text/css" />
<link href="../../css/mailDefault.css" rel="stylesheet" type="text/css" />
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
        <div class="page mail">
   <h1><i class="glyphicon glyphicon-envelope"></i> View Email</h1>
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
     <li><a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/mail/admin/"; ?>" class="link_undo"><i class="glyphicon glyphicon-arrow-left"></i> Back to mail</a></li>
   </ul></div></nav>
   <table  class="form-table"><tr>
       <td colspan="2"><form action="send.php" method="post" name="form1" id="form1">
         <button name="reply" type="submit" class="btn btn-default btn-secondary" id="reply"><i class="glyphicon glyphicon-share-alt"></i> Reply</button>
          <button name="delete" type="button" class="btn btn-default btn-secondary" id="delete"  onclick="if(confirm('Are you sure you want to delete this email?')) { window.location.href = '<?php $url = "/mail/admin/"; $queryChar = (strpos($url, "?")) ? "&" : "?"; $url .= $queryChar."deleteID=".$row_rsCorrespondence['ID']; echo $url; ?>'; }" ><i class="glyphicon glyphicon-trash"></i> Delete</button>
         <input name="recipient" type="hidden" id="recipient" value="<?php echo htmlentities($row_rsCorrespondence['sender']); ?>" />
         <input name="sender" type="hidden" id="sender" value="<?php echo htmlentities($row_rsCorrespondence['recipient']); ?>" />
          <input name="reply" type="hidden" id="reply" value="true" />
         <input name="message" type="hidden" id="message" value="<?php echo htmlentities($row_rsCorrespondence['message']); ?>" />
         <input name="subject" type="hidden" id="subject" value="RE: <?php echo htmlspecialchars($row_rsCorrespondence['subject']); ?>" />
         <input name="correspondenceID" type="hidden" id="correspondenceID" value="<?php echo $row_rsCorrespondence['ID']; ?>" />
         <input type="hidden" name="returnURL" id="returnURL" value="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "/mail/admin/"; ?>" />
       </form>       </td>
      </tr>
     <tr>
       <td><strong>To:</strong></td>
       <td><?php echo $row_rsCorrespondence['recipient']; ?></td>
     </tr>
     <tr>
       <td><strong>From:</strong></td>
       <td><?php echo isset($row_rsCorrespondence['sendername']) ? $row_rsCorrespondence['sendername'] : $row_rsCorrespondence['sender']; ?></td>
     </tr>
     <tr>
       <td><strong>Subject:</strong></td>
       <td><?php echo $row_rsCorrespondence['subject']; ?></td>
     </tr>
     <tr>
       <td><strong>Date:</strong></td>
       <td><?php echo date('g:ia, l jS F Y',strtotime($row_rsCorrespondence['createddatetime'])); ?></td>
     </tr>
     <tr>
       <td colspan="2" bgcolor="#FFFFFF"><?php echo (strpos($row_rsCorrespondence['message'],"</")===false) ? nl2br($row_rsCorrespondence['message']) : $row_rsCorrespondence['message']; ?>
         <?php if ($totalRows_rsAttachments > 0) { // Show if recordset not empty ?>
           <p><strong>Attachments</strong>:</p>
           <table border="0" cellpadding="0" cellspacing="0" class="form-table">
             <?php do { ?>
               <tr>
                 <td><div class="docsItem <?php echo preg_replace("/[^a-zA-Z0-9]/", "",$row_rsAttachments['mimetype']); ?>">&nbsp;</div></td>
                 <td><a href="<?php echo $row_rsAttachments['filename']; ?>" target="_blank" rel="noopener"><?php echo $row_rsAttachments['filename']; ?></a></td>
             </tr>
               <?php } while ($row_rsAttachments = mysql_fetch_assoc($rsAttachments)); ?>
          </table>
        <?php } // Show if recordset not empty ?></td>
     </tr>
     
   </table></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsCorrespondence);

mysql_free_result($rsAttachments);
?>


