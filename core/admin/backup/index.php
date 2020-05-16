<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../includes/adminAccess.inc.php'); ?>
<?php require_once('../../includes/framework.inc.php'); ?>
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

$MM_restrictGoTo = "/login/index.php?notloggedin=true";
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	 $encrypted = encrypt($_POST['backupftppassword']);
	 // fall back if encryption doesn't work
	 $_POST['backupftppassword'] = strlen($encrypted)>10  ? $encrypted : $_POST['backupftppassword'];
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE backupprefs SET autobackup=%s, autobackupdestination=%s, backupftpserver=%s, backupftpuser=%s, backupftppassword=%s, backupftppath=%s, backupstart=%s, backupfrequency=%s, backupemail=%s, ftptype=%s, remoteclientURL=%s, backupzip=%s, backupnotes=%s, backupfiles=%s WHERE ID=%s",
                       GetSQLValueString($_POST['autobackup'], "int"),
                       GetSQLValueString($_POST['autobackupdestination'], "int"),
                       GetSQLValueString($_POST['backupftpserver'], "text"),
                       GetSQLValueString($_POST['backupftpusername'], "text"),
                       GetSQLValueString($_POST['backupftppassword'], "text"),
                       GetSQLValueString($_POST['backupftppath'], "text"),
                       GetSQLValueString($_POST['backupstart'], "date"),
                       GetSQLValueString($_POST['backupfrequency'], "int"),
                       GetSQLValueString($_POST['backupemail'], "text"),
                       GetSQLValueString($_POST['ftptype'], "int"),
                       GetSQLValueString($_POST['remoteclientURL'], "text"),
                       GetSQLValueString(isset($_POST['backupzip']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['backupnotes'], "text"),
                       GetSQLValueString(isset($_POST['backupfiles']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	
  $updateGoTo = ($_POST['backupnow']==1) ? "backup.php?backuptype=" . $_POST['backuptype'] . "" : "index.php?msg=Settings+saved.";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsBackupPrefs = "SELECT * FROM backupprefs";
$rsBackupPrefs = mysql_query($query_rsBackupPrefs, $aquiescedb) or die(mysql_error());
$row_rsBackupPrefs = mysql_fetch_assoc($rsBackupPrefs);
$totalRows_rsBackupPrefs = mysql_num_rows($rsBackupPrefs);

if($totalRows_rsBackupPrefs==0) {
	mysql_query("INSERT INTO backupprefs (ID) VALUES (1)", $aquiescedb) or die(mysql_error());
	header("location:index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Back-up Manager"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../seo/includes/seo.inc.php'); ?>
<?php require_once('../../includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script>
$(document).ready(function(e) {
    

$(".toggle-password").click(function() {

  $(this).find("i").toggleClass("glyphicon-eye-open glyphicon-eye-close");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});

toggleAutoBackup();
	toggleFTP();

});


function toggleAutoBackup() {
	if(getRadioValue("autobackup") == 1) {
		document.getElementById('divautobackup').style.display = 'block';
		document.getElementById('divcron').style.display = 'block';
		document.getElementById('divpageaccess').style.display = 'none';
	} else if(getRadioValue("autobackup") == 2)  {
		document.getElementById('divautobackup').style.display = 'block';
		document.getElementById('divcron').style.display = 'none';
		document.getElementById('divpageaccess').style.display = 'block';
	} else {
		document.getElementById('divautobackup').style.display = 'none';
		document.getElementById('backupemail').value = '';
	}
}

</script>

<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
            <div class="page backup">
  <h1><i class="glyphicon glyphicon-saved"></i> Back-up Manager</h1>
  <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
      <li><a href="log.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Backup Log</a></li>
      <li><a href="restore.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> Restore</a></li>
    </ul></div></nav>
	<?php if(isset($_GET['msg'])) { ?>
  <p class="message alert alert-info" role="alert"><?php echo htmlentities($_GET['msg'], ENT_COMPAT, "UTF-8"); ?></p>
  <?php } ?><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
  <p>You can set up a regular FTP back up of all the database content on your server or download a back up file. Note this does not include any files you upload - keep your own copies of these.</p>
  <p>
    <textarea name="backupnotes" id="backupnotes" placeholder="Add any notes regarding your backup routine here" class="form-control"><?php echo $row_rsBackupPrefs['backupnotes']; ?></textarea>
  </p>
  <p>
  <label>
     
        <input <?php if (!(strcmp(@$row_rsBackupPrefs['backupfiles'],1))) {echo "checked=\"checked\"";} ?> name="backupfiles" type="checkbox" id="backupfiles" value="1"> 
        Backup Uploaded Files  
      </label>
    
    &nbsp;&nbsp;&nbsp;
    
    <label>
      <input <?php if (!(strcmp(@$row_rsBackupPrefs['backupzip'],1))) {echo "checked=\"checked\"";} ?> name="backupzip" type="checkbox" id="backupzip" value="1">
      ZIP database backup (if supported by server)</label>
  </p>
  <h2>Back up now</h2>
  <p>Click on the link below  to save the backup file to your computer. Depending on the amount of data, this could take several minutes.</p>
  
    <p>
      <input name="backupnow" type="hidden" id="backupnow" value="0" />
     <label>
        
        <input name="backuptype" type="radio"  value="1" checked="checked" />
        Download file&nbsp;&nbsp;&nbsp;
        </label>
        
        
        
        
        
      
      <label>
        <input type="radio" name="backuptype" value="2" id="backuptype_1" />
         Backup Server (settings below)&nbsp;&nbsp;&nbsp;</label> 
        
                
        
        
        <span><button type="submit" class="btn btn-default btn-secondary" onclick="document.getElementById('backupnow').value=1;">Back up now...</button></span>
        
         
        
        </p><h2>Auto back up</h2>
      <p>Automatic backups will use Backup Server settings below</p>
      <p>When:
        <label>
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackup'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackup" value="0" id="autobackup_0" onclick="toggleAutoBackup()" />
        Never</label>
        &nbsp;&nbsp;&nbsp;
        <label data-toggle="tooltip" title="Recommended method. 'Cron' - is short for chronological events on your server. If you have access to your server settings you can set up a backup to occur at regular intervals.">
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackup'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackup" value="1" id="autobackup_1" onclick="toggleAutoBackup()" />
          Cron</label>
        &nbsp;&nbsp;&nbsp;
        <label data-toggle="tooltip" title="If you do not have access to your server settings you can set up a backup to occur at regular intervals when triggered by a vistor to your site. The downside is that if there are no visitors a backup will not be triggered.">
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackup'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackup" value="2" id="autobackup_2" onclick="toggleAutoBackup()" />
          Page access</label>
      </p>
      <p>Where: <label>
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackupdestination'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackupdestination" value="0" onclick="toggleAutoBackupDestination()" />
        Not set</label>
        &nbsp;&nbsp;&nbsp;
        <label>
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackupdestination'],"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackupdestination" value="1" onclick="toggleAutoBackupDestination()" />
        FTP</label>
        &nbsp;&nbsp;&nbsp;
        <label  >
          <input <?php if (!(strcmp($row_rsBackupPrefs['autobackupdestination'],"2"))) {echo "checked=\"checked\"";} ?> type="radio" name="autobackupdestination" value="2"  onclick="toggleAutoBackupDestination()" />
          Dropbox</label></p>
          
           <div id="divautobackup">
    <fieldset><legend>Options</legend>
      
      
        <div id="divcron">Set a cron job to execute: php <?php echo SITE_ROOT; ?>core/admin/backup/backup.php</div> 
        <div id="divpageaccess">
          <p><label>Start time:
            <input name="backupstart" type="hidden" id="backupstart" value="<?php $setvalue = $row_rsBackupPrefs['backupstart']; echo $setvalue; $inputname="backupstart"; $time = true; ?>" />
            <?php require_once('../../includes/datetimeinput.inc.php'); ?></label>
            <br />
            <label>Frequency:
              <select name="backupfrequency" id="backupfrequency" class="form-control">
                <option value="0" <?php if (!(strcmp(0, $row_rsBackupPrefs['backupfrequency']))) {echo "selected=\"selected\"";} ?>>Once</option>
                
                <option value="3600" <?php if (!(strcmp(3600, $row_rsBackupPrefs['backupfrequency']))) {echo "selected=\"selected\"";} ?>>Hourly</option><option value="86400" <?php if (!(strcmp(86400, $row_rsBackupPrefs['backupfrequency']))) {echo "selected=\"selected\"";} ?>>Daily</option>
                
                <option value="604800" <?php if (!(strcmp(604800, $row_rsBackupPrefs['backupfrequency']))) {echo "selected=\"selected\"";} ?>>Weekly</option>
                
                <option value="2419200" <?php if (!(strcmp(2419200, $row_rsBackupPrefs['backupfrequency']))) {echo "selected=\"selected\"";} ?>>4-Weekly</option>
                </select>
          </label></p></div>
        <p>
        <label class="form-inline">Send 
          email once backup has completed to:
          <input name="backupemail" id="backupemail" type="text" value="<?php echo $row_rsBackupPrefs['backupemail']; ?>" size="50" maxlength="100" autocomplete='off' readonly onfocus="this.removeAttribute('readonly');" class="form-control" />
        </label>
</p>
      
      </fieldset>
      </div>
      <fieldset>
      <legend>Dropbox settings</legend>
      <p>Add DROPBOX_ACCESS_TOKEN to Preferences file</p>
      </fieldset>
      
      
    <fieldset>
      <legend>FTP settings</legend>
      
     
     
        
 
          
        <table class="form-table">
          <tr>
          <td align="right"><label for="backupftpserver">FTP server:</label></td>
          <td><input name="backupftpserver" type="text" id="backupftpserver" value="<?php echo $row_rsBackupPrefs['backupftpserver']; ?>" size="40" maxlength="100" class="form-control" /></td>
          <td align="right"><label for="backupftppath">FTP Path: </label></td>
          <td><input name="backupftppath" type="text" id="backupftppath" value="<?php echo $row_rsBackupPrefs['backupftppath']; ?>" size="40" maxlength="100" class="form-control" /></td>
        </tr>
        <tr>
          <td align="right"><label for="backupftpusername">FTP User: </label></td>
          <td><input name="backupftpusername" type="text" id="backupftpusername" value="<?php echo $row_rsBackupPrefs['backupftpuser']; ?>" size="40" maxlength="50" autocomplete="off" readonly onFocus="this.removeAttribute('readonly');" placeholder="fbbackup" class="form-control" /></td>
          <td align="right"><label for="backupftppassword">FTP Pass:</label></td>
          <td><div class="input-group"><input name="backupftppassword" type="password" id="backupftppassword" value="<?php if(isset($row_rsBackupPrefs['backupftppassword'])) {
			  $decrypt =  decrypt($row_rsBackupPrefs['backupftppassword']); 
			  echo strlen($decrypt)>1 ? $decrypt : $row_rsBackupPrefs['backupftppassword'];
		  }?>" size="40" maxlength="50" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" placeholder="e.g. Fullbhuna@3***" class="form-control" /><span class="input-group-btn"><button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#backupftppassword"><i class="glyphicon glyphicon-eye-open  "></i></button>
      </span> </div></td>
        </tr>
    </table>
     
    </fieldset> 
    <p>
      <span><button type="submit" name="savebutton" id="savebutton" class="btn btn-primary" >Save changes</button></span>
      
      <input type="hidden" name="MM_update" value="form1" />
      <input name="ID" type="hidden" id="ID" value="1" />
    </p>
    
    <p>NOTE: Visitor sessions or search spider data are not backed up due to large volume of data involved.</p>
    
  </form>
  
 </div>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsBackupPrefs);
?>
