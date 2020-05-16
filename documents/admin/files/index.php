<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../../../core/includes/upload.inc.php'); ?>
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

if(isset($_POST['item'])) {
	
	$msg = "The following images have been resampled:\n\n";
	foreach($_POST['item'] as $key => $value) {
		
		if(preg_match("/(.jpeg$|.jpg$|.gif$|.png$)/i",$value) && isset($image_sizes)) { 						 							
			$imagesizes = createImageSizes($_POST['directory'].$value);
					$msg .=" - ".$value."\n";			
							} // end is an image
		
	}
	
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php $pageTitle = "Manage Files"; echo $pageTitle." | ".$site_name; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><script src="/core/scripts/checkbox/checkboxes.js"></script><link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
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
  <?php $directory = isset($_REQUEST['directory']) ? htmlentities($_REQUEST['directory']) : UPLOAD_ROOT; 
  $directory = realpath($directory).DIRECTORY_SEPARATOR;  // removed dots ?>
    <h1><i class="glyphicon glyphicon-folder-open"></i> Manage Files</h1>
    <?php require_once('../../../core/includes/alert.inc.php'); ?>
<form action="index.php" method="post" name="filesform" id="filesform">
<p class="form-inline">
  <label>File directory: 
  
    <input name="directory" type="text" id="directory" size="80" maxlength="255" value="<?php echo $directory; ?>" class="form-control"  />
  </label></p>


    <?php 
	
	if(is_dir($directory)) {
		$files = scandir($directory);
		if(count($files)>2) { ?>
        
    <table class="table table-hover"><thead> <tr>
      <th>
              <input type="checkbox" name="selectall" id="selectall" onclick="checkUncheckAll(this)" />   </th>
      <th colspan="2"><label for="selectall">Select all files</label></th>
      </tr></thead><tbody>
    <tr>
    <?php 
	foreach($files as $key => $value) { 
	if($value !=".") {
	$file = $directory.$value; 
	$link = is_file($file) ?  str_replace(SITE_ROOT,"/",$file) : "index.php?directory=".$file.DIRECTORY_SEPARATOR;
	
	 
?>
   
      <td><?php if(is_file($file)) { 
	  ?><input type="checkbox" name="item[<?php echo $key; ?>]" id="item[<?php echo $key; ?>]" value="<?php echo $value;  ?>" /><input name="filename[<?php echo $key; ?>]" type="hidden" value="<?php echo $file; ?>" />   <?php } ?>&nbsp;</td>   
      <td><?php if(is_dir($file) || is_link($file)) { ?>
      <img src="/documents/images/folder.png" alt="Folder" width="16" height="16" style="vertical-align:
middle;" />
      <?php } else { ?>
      <img src="/documents/images/document-.png" alt="Document" width="16" height="16" style="vertical-align:
middle;" />
      <?php } ?>      </td>
      <td><a href="<?php echo $link; ?>"><?php echo $value;  ?></a></td></tr>
		
        
	<?php } // end not dot
	} // end for ?></tbody>
  </table>
    <p>With selected: 
      <button type="submit" name="resample" id="resample" class="btn btn-default btn-secondary">Resample</button>
      <a href="resample.php?directory=<?php echo $directory; ?>">Resample all in directory</a></p>
</form>
  <?php } // end is files 
  else { ?><p>There are no files in this directory.</p>
  <?php } ?>
  <?php } // end is directory
  else { ?>
  <p>Cannot find directory</p>
  <?php } ?>
  <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>