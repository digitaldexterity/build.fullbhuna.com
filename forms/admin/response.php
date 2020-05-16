<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../mail/includes/sendmail.inc.php'); ?>
<?php

if(!defined("MYSQL_SALT")) define("MYSQL_SALT", PRIVATE_KEY);


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

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

$varFormID_rsFormItems = "-1";
if (isset($_GET['formID'])) {
  $varFormID_rsFormItems = $_GET['formID'];
}
$varResponseID_rsFormItems = "-1";
if (isset($_GET['responseID'])) {
  $varResponseID_rsFormItems = $_GET['responseID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFormItems = sprintf("SELECT formfield.*, formfieldresponse.ID AS formfieldresponseID, formfieldresponse.formfieldtextanswer FROM formfield LEFT JOIN formfieldresponse ON (formfieldresponse.formfieldID = formfield.ID AND formfieldresponse.formresponseID = %s) WHERE formID = %s GROUP BY formfield.ID  ORDER BY ordernum ASC", GetSQLValueString($varResponseID_rsFormItems, "int"),GetSQLValueString($varFormID_rsFormItems, "int"));
$rsFormItems = mysql_query($query_rsFormItems, $aquiescedb) or die(mysql_error());
$row_rsFormItems = mysql_fetch_assoc($rsFormItems);
$totalRows_rsFormItems = mysql_num_rows($rsFormItems);



$colname_rsThisResponse = "-1";
if (isset($_GET['responseID'])) {
  $colname_rsThisResponse = $_GET['responseID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisResponse = sprintf("SELECT formresponse.*, form.formname, form.email FROM formresponse LEFT JOIN form ON (formresponse.formID = form.ID) WHERE formresponse .ID = %s", GetSQLValueString($colname_rsThisResponse, "int"));
$rsThisResponse = mysql_query($query_rsThisResponse, $aquiescedb) or die(mysql_error());
$row_rsThisResponse = mysql_fetch_assoc($rsThisResponse);
$totalRows_rsThisResponse = mysql_num_rows($rsThisResponse);
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Form Response: ".$row_rsThisResponse['formname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
.encrypted-data{
	display:none;
}--></style>
<script>
function toggleEncrypted() {
	$(".encrypted-text").toggle();
	$(".encrypted-data").toggle();
}
</script>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
  
    <div class="page class">
      <h1><i class="glyphicon glyphicon-th-list"></i> <?php echo $row_rsThisResponse['formname']; ?> Response</h1>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li class="nav-item"><a href="responses.php?formID=<?php echo intval($_GET['formID']); ?>" class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Show All Responses</a></li></ul></div></nav>
     <p>Form submitted <?php echo date('d M Y H:i', strtotime($row_rsThisResponse['createddatetime'])); ?>:</p>   
        <?php $html = '<table class="table" id="formitems"><tbody>';
             
              $item = 0; if($totalRows_rsFormItems>0) { do { $item++;
			  // text-align for email 
             $html .= '<tr><th style="text-align:left" class="fieldlabel top ';
			 $html .= ($row_rsFormItems['formfieldtype']==0) ? 'description" colspan="2' : ''; 
			 $html .= '"';
			 $html .= ($row_rsFormItems['formfieldtype']==0) ? "colspan=2" : ""; 
			 $html .= '>';
			 
			 $html .= ($row_rsFormItems['formfieldname']!="") ? strip_tags($row_rsFormItems['formfieldname']) : $row_rsFormItems['formfieldplaceholder']; 
			 
			 $html .= "</th>";
             if($row_rsFormItems['formfieldtype']>0) { 
			 $html .= "<td class='formitem'> ";             
              if($row_rsFormItems['formfieldtype']==3) { //checkboxes
				$select = "SELECT formfieldchoice.formfieldchoicename
				  FROM formfieldchoice LEFT JOIN formfieldresponse ON (formfieldchoice.ID = formfieldresponse.formfieldchoiceID) WHERE formfieldresponse.formresponseID = ".intval($_GET['responseID'])."  AND formfieldresponse.formfieldID = ".$row_rsFormItems['ID']." ORDER BY formfieldchoice.ordernum";
				
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$choices = mysql_num_rows($result);
				if($choices>0) { 
					$i = 0;
					while($choice = mysql_fetch_assoc($result)) { 
						$i++; 
						$html .= "<i class=\"glyphicon glyphicon-ok\"></i> ".htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8").";&nbsp;&nbsp;&nbsp; ";
			    	} // end while
				 } // end is choices
			} else if($row_rsFormItems['formfieldtype']==6) {  // file
				$html .= "<a href=\"".htmlentities($row_rsFormItems['formfieldtextanswer'], ENT_COMPAT, "UTF-8")."\" target=\"_blank\">".htmlentities($row_rsFormItems['formfieldtextanswer'], ENT_COMPAT, "UTF-8")."</a>";
			} else if($row_rsFormItems['formfieldtype']==7) { // date picker
				$html .= isset($row_rsFormItems['formfieldtextanswer']) ? date('d M Y', strtotime($row_rsFormItems['formfieldtextanswer'])) : "N/A";
 			} else  { // all other 
				if($row_rsFormItems['encryptfield']==1) { // encrypted
					
					$enc_select = "SELECT formfieldtextanswer, AES_DECRYPT(formfieldtextanswer,'".MYSQL_SALT."') AS decrypted FROM formfieldresponse  WHERE ID = ".intval($row_rsFormItems['formfieldresponseID']);
					$enc_result = mysql_query($enc_select, $aquiescedb) or die(mysql_error().": ".$enc_select);
					$enc = mysql_fetch_assoc($enc_result);
					$html .= "<span class='encrypted-text'>Encrypted </span><span class='encrypted-data'>".$enc['decrypted']."</span>";
				
				
				} else {
					$html .= htmlentities($row_rsFormItems['formfieldtextanswer'], ENT_COMPAT, "UTF-8");
				}
  			} // end all other 
			$html .= "</td>";
			} 
			$html .= "</tr>";
			
			 } while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems)); }
            
            
        $html .= "</tbody></table>"; 
		
		if(isset($_GET['resend'])) {
			
			
		$header = "<html><head><style>table.box { width:100%; border-collapse:collapse; border-top-width: 1px;	border-top-style: solid;	border-top-color: #000;	border-left-width: 1px;	border-left-style: solid;	border-left-color: #000;} 
		table.box td {border-bottom-width: 1px;	border-bottom-style: solid;	border-bottom-color: #000;	border-right-width: 1px; border-right-style: solid;	border-right-color: #00; vertical-align:top; padding:5px; }
		table.box table, table.box td td { border:none; }</style></head><body><p>This is a resend of the following form which was posted on ".date('d M Y H:i', strtotime($row_rsThisResponse['createddatetime']))." from the web site:</p><table class=\"box\"><tr><td>";
		
		
		$protocol = getProtocol()."://";
		$formlink = $protocol.$_SERVER['HTTP_HOST']."/forms/admin/response.php?responseID=".intval($_GET['responseID'])."&formID=".intval($_GET['formID']);
		
		$footer = "</td></tr></table><p>You can view this and other forms online using link below:</p><p><a href=\"".$formlink."\">".$formlink."</a></p></body></html>";
		
		
			$to = $row_rsThisResponse['email'];
		
			$message = $header.$html.$footer;
			$subject = "FORM RESEND: ".$row_rsThisResponse['formname'];
			if(sendMail($to ,  $subject,"","","",$message)) {
				echo "<div class=\"alert alert-info \" role=\"alert\" >Email has been resent to ".$to.".</div>";
			}
		}
		
		echo $html; 
		?>
        <hr>
        <?php if(strpos($row_rsThisResponse['email'],"@")) { ?>
        <a href="response.php?formID=<?php echo intval($_GET['formID']); ?>&responseID=<?php echo intval($_GET['responseID']); ?>&resend=true" class='btn btn-default btn-secondary' onClick="return confirm('Are you sure you want to resend email to <?php echo $row_rsThisResponse['email']; ?>?');"><i class="glyphicon glyphicon-envelope"></i> Resend Email</a>
        <?php } ?>
        
<button type="button" class='btn btn-default btn-secondary' onClick="toggleEncrypted()"><i class='glyphicon glyphicon-eye-open'></i> Show/Hide Encrypted</button>

<a href="responses.php?formID=<?php echo intval($_GET['formID']); ?>&deleteResponseID=<?php echo intval($_GET['responseID']); ?>" class='btn btn-danger' onClick="return confirm('Are you sure you want to delete this response? This cannot be undone.');"><i class="glyphicon glyphicon-trash"></i> Delete this response</a>

    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFormItems);

mysql_free_result($rsThisForm);

mysql_free_result($rsThisResponse);
?>
