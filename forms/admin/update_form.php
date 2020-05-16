<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php

$regionID = (isset($regionID) && intval($regionID)>0)  ? intval($regionID): 1;
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

$colname_rsForm = "-1";
if (isset($_GET['formID'])) {
  $colname_rsForm = $_GET['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForm = sprintf("SELECT * FROM `form` WHERE ID = %s", GetSQLValueString($colname_rsForm, "int"));
$rsForm = mysql_query($query_rsForm, $aquiescedb) or die(mysql_error());
$row_rsForm = mysql_fetch_assoc($rsForm);
$totalRows_rsForm = mysql_num_rows($rsForm);

$colname_rsFormItems = "-1";
if (isset($_GET['formID'])) {
  $colname_rsFormItems = $_GET['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFormItems = sprintf("SELECT * FROM formfield WHERE formID = %s ORDER BY ordernum ASC", GetSQLValueString($colname_rsFormItems, "int"));
$rsFormItems = mysql_query($query_rsFormItems, $aquiescedb) or die(mysql_error());
$row_rsFormItems = mysql_fetch_assoc($rsFormItems);
$totalRows_rsFormItems = mysql_num_rows($rsFormItems);

$varRegionID_rsUserGroups = "1";
if (isset($regionID)) {
  $varRegionID_rsUserGroups = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserGroups = sprintf("SELECT ID, groupname FROM usergroup WHERE statusID = 1 AND (regionID = 0 OR regionID = %s) ORDER BY groupname ASC", GetSQLValueString($varRegionID_rsUserGroups, "int"));
$rsUserGroups = mysql_query($query_rsUserGroups, $aquiescedb) or die(mysql_error());
$row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
$totalRows_rsUserGroups = mysql_num_rows($rsUserGroups);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsUserType = "SELECT * FROM usertype WHERE ID > 0 ORDER BY ID ASC";
$rsUserType = mysql_query($query_rsUserType, $aquiescedb) or die(mysql_error());
$row_rsUserType = mysql_fetch_assoc($rsUserType);
$totalRows_rsUserType = mysql_num_rows($rsUserType);

if(isset($_POST['formID'])) {
	$update = "UPDATE preferences SET recaptcha_site_key = ".GetSQLValueString($_POST['recaptcha_site_key'], "text").",
		recaptcha_secret_key = ".GetSQLValueString($_POST['recaptcha_secret_key'], "text")." WHERE ID = ".$regionID;
	mysql_query($update, $aquiescedb) or die(mysql_error());
	$statusID = isset($_POST['statusID']) ? 1 : 0;
	$sendemail = isset($_POST['sendemail']) ? 1 : 0;
	$blockwww = isset($_POST['blockwww']) ? 1 : 0;
	$noindex = isset($_POST['noindex']) ? 1 : 0;
	$adduser = isset($_POST['adduser']) ? 1 : 0;
	
	if($_POST['formID']==0) { // new form - create
		$insert = "INSERT INTO form (formname, email, regionID, ordernum, confirmationpage, confirmationpageURL, sendemail, emailsubject, emailmessage, blockwww, noindex, captcha, statusID, header,footer, pagetitle, metadescription, adduser, groupID, showlabels, showplaceholders, text_submit,text_enter_value,text_select_value,text_check_value,text_required, createdbyID, createddatetime) VALUES (".GetSQLValueString($_POST['formname'], "text").",".GetSQLValueString($_POST['email'], "text").",".$regionID.",0,".GetSQLValueString($_POST['confirmationpage'], "text").",".GetSQLValueString($_POST['confirmationpageURL'], "text").",".$sendemail.",".GetSQLValueString($_POST['emailsubject'], "text").",".GetSQLValueString($_POST['emailmessage'], "text").",".$blockwww.",".$noindex.",".GetSQLValueString($_POST['captcha'], "int").",".$statusID.",".GetSQLValueString($_POST['header'], "text").",".GetSQLValueString($_POST['footer'], "text").",".GetSQLValueString($_POST['pagetitle'], "text").",".GetSQLValueString($_POST['metadescription'], "text").",".$adduser.",".GetSQLValueString($_POST['groupID'], "int").",".GetSQLValueString($_POST['showlabels'],"int").",".GetSQLValueString(isset($_POST['showplaceholders']) ? "true" : "", "defined","1","0").",".GetSQLValueString($_POST['text_submit'], "text").",".GetSQLValueString($_POST['text_enter_value'], "text").",".GetSQLValueString($_POST['text_select_value'], "text").",".GetSQLValueString($_POST['text_check_value'], "text").",".GetSQLValueString($_POST['text_required'], "text").",".GetSQLValueString($_POST['createdbyID'], "int").",'".date('Y-m-d H:i:s')."')";
		mysql_query($insert, $aquiescedb) or die(mysql_error());
		$formID = mysql_insert_id();
		$update = "UPDATE form SET ordernum = ".$formID." WHERE ID = ".$formID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	} else {
		$formID = intval($_POST['formID']);
		$update = "UPDATE form SET formname = ".GetSQLValueString($_POST['formname'], "text").", email = ".GetSQLValueString($_POST['email'], "text").", confirmationpage = ".GetSQLValueString($_POST['confirmationpage'], "text").", confirmationpageURL = ".GetSQLValueString($_POST['confirmationpageURL'], "text").", sendemail = ".$sendemail.", emailsubject= ".GetSQLValueString($_POST['emailsubject'], "text").",emailmessage= ".GetSQLValueString($_POST['emailmessage'], "text").", blockwww=".$blockwww.", noindex=".$noindex.", captcha=".GetSQLValueString($_POST['captcha'], "int").", statusID = ".$statusID.", header= ".GetSQLValueString($_POST['header'], "text").", footer= ".GetSQLValueString($_POST['footer'], "text").",pagetitle=".GetSQLValueString($_POST['pagetitle'], "text").", metadescription=".GetSQLValueString($_POST['metadescription'], "text").",
		adduser=".$adduser.",
		groupID=".GetSQLValueString($_POST['groupID'], "int").",
		showlabels = ".GetSQLValueString($_POST['showlabels'],"int").",
		showplaceholders= ".GetSQLValueString(isset($_POST['showplaceholders']) ? "true" : "", "defined","1","0").",
		text_submit = ".GetSQLValueString($_POST['text_submit'], "text").",
		text_enter_value = ".GetSQLValueString($_POST['text_enter_value'], "text").",
		text_select_value = ".GetSQLValueString($_POST['text_select_value'], "text").",
		text_check_value = ".GetSQLValueString($_POST['text_check_value'], "text").",
		text_required = ".GetSQLValueString($_POST['text_required'], "text").",
		accessrankID = ".GetSQLValueString($_POST['accessrankID'], "int").",
		loginsignup = ".GetSQLValueString($_POST['loginsignup'], "int").",
		  modifiedbyID= ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime = '".date('Y-m-d H:i:s')."' WHERE ID = ".$formID;
		mysql_query($update, $aquiescedb) or die(mysql_error());
	}
	$order = 0;
	
	foreach($_POST['formitemID'] as $key => $formfieldID) {
		$order++;	

		$required = isset($_POST['required'][$key]) ?  1 : 0;
		$halfwidth = isset($_POST['halfwidth'][$key]) ?  1 : 0;
		$addverifyfield = isset($_POST['addverifyfield'][$key]) ?  1 : 0;
		$showinlistview = isset($_POST['showinlistview'][$key]) ?  1 : 0;
		$encryptfield = isset($_POST['encryptfield'][$key]) ?  1 : 0;
		
		if($formfieldID>0) { // exitsing form item
			$update = "UPDATE formfield SET formfieldname=".GetSQLValueString($_POST['formfieldname'][$key], "text").",formfieldplaceholder=".GetSQLValueString($_POST['formfieldplaceholder'][$key], "text").", formfieldspecialtype=".GetSQLValueString($_POST['formfieldspecialtype'][$key], "int").", required =".$required.", halfwidth =".$halfwidth.", showinlistview =".$showinlistview.", addverifyfield=".$addverifyfield.", encryptfield=".$encryptfield.", ordernum = ".$order.", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime = NOW() WHERE ID = ".intval($formfieldID);
			
			mysql_query($update, $aquiescedb) or die(mysql_error());
		} else { // new form item
			$insert = "INSERT INTO formfield (formID, formfieldtype,formfieldspecialtype, formfieldname, formfieldplaceholder,required, halfwidth, showinlistview, addverifyfield, encryptfield, ordernum, statusID ,createdbyID, createddatetime) VALUES (".$formID.",".GetSQLValueString($_POST['formfieldtype'][$key], "int").",".GetSQLValueString($_POST['formfieldspecialtype'][$key], "int").",".GetSQLValueString($_POST['formfieldname'][$key], "text").",".GetSQLValueString($_POST['formfieldplaceholder'][$key], "text").",".$required.",".$halfwidth.",".$showinlistview.",".$addverifyfield.",".$encryptfield.",".$order.",1,".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
			$formfieldID = mysql_insert_id();			
		}
		
		if($_POST['formfieldtype'][$key]>=3) {		
			foreach($_POST['formfieldchoice_'.$key] as $choicekey=> $choiceID) {
				if($choiceID>0) { //exists
					$update = "UPDATE formfieldchoice SET formfieldchoicename = ".GetSQLValueString($_POST['formfieldchoicename_'.$key][$choicekey],"text").", modifiedbyID = ".GetSQLValueString($_POST['modifiedbyID'], "int").", modifieddatetime = NOW() WHERE ID = ".$choiceID;
					mysql_query($update, $aquiescedb) or die(mysql_error());
				} else {
					$insert = "INSERT INTO formfieldchoice (formfieldID, formfieldchoicename, ordernum, statusID, createdbyID, createddatetime) VALUES (".$formfieldID.",".GetSQLValueString($_POST['formfieldchoicename_'.$key][$choicekey],"text").",0,1,".GetSQLValueString($_POST['createdbyID'], "int").",NOW())";
					mysql_query($insert, $aquiescedb) or die(mysql_error());
					$formfieldchoiceID = mysql_insert_id();
					$update = "UPDATE formfieldchoice SET ordernum = ".$formfieldchoiceID." WHERE ID = ".$formfieldchoiceID;
					mysql_query($update, $aquiescedb) or die(mysql_error());
				}
			} // end form field choices loop
		} // end is form field choices
		
	} // end form items
	header("location: index.php"); exit;
}
?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Form Builder"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><?php require_once('../../core/tinymce/tinymce.inc.php'); ?>
<style>
<!--



#formitems.table {
	width:100%;
	margin:0;
	padding:0;

}

#formitems.table .tr {
	margin:0;
	padding:0;
}

#formitems .td {
	padding: 10px 5px;
	vertical-align:top;
	position:relative;
}

#formitems .td.handle {
	width:20px;
}

#formitems input[type='text'],
#formitems textarea {
	font-size:1.2em;
	width:500px;
	padding: 3px;
}

#formitems textarea {
	height:100px;
}

#formitems input[type='text'].editor,
#formitems textarea.editor {
	border: 1px dotted #CCCCCC;
	width:250px;
	
}

#formitems .fieldlabel input[type='text'].editor
{
	text-align:right;
}

#formitems .wide {
	height:100px;
	
}

#formitems .options {
	text-align:right;
}

#formitems .wide textarea.editor{
	position:absolute;	
	width:800px;
	left:5px; /* match padding */
}

#formitems  .selectchoice {
	display:block;
}

#formitems  .options {
	position:relative;
}

#formitems  .options .popup {
	opacity:0;
	position:absolute;
	top:-999em;
	right:0;
	transition: opacity .5s;
	background:#FFFFFF;
	padding:10px;
	width:auto;
	z-index:1;
	-webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
-moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.75);
	
}

#formitems  .options:hover .popup {
	top:0;
	opacity:1;
	text-align:left;
}

#formitems  .options .popup  label {
	white-space:nowrap;
}
-->
</style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" >
<link href="../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet" >
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextarea.js"></script>
<script>

$(document).ready(function(e) {
	toggleAnswerChoices();
	toggleRecaptcha();
	$(".sortable").sortable({ 
            handle : '.handle'
           
        }); 
   
});

var itemID = <?php echo intval($totalRows_rsFormItems); ?>;
function addFormItem(formitemtypeID, choices) {
	if(formitemtypeID!="" && choices>0) {
		itemID ++;
		var html = "";
		if(formitemtypeID>0) {
			html += "<li class='tr' id='item"+itemID+"'><div class='td handle'><input type='hidden' name='formitemID["+itemID+"]' value ='0'>&nbsp;</div><div class='td fieldlabel'><input type='text' placeholder='Add question here' class='form-control editor' name='formfieldname["+itemID+"]' required ></div><div class='td formitem'>"; // start table row
		} else {
			html += "<li class='tr' id='item"+itemID+"'><div class='td handle'><input type='hidden' name='formitemID["+itemID+"]' value ='0'>&nbsp;</div><div class='td wide'><textarea class='form-control  editor ' name='formfieldname["+itemID+"]' onfocus='if(this.innerHTML==\"Add text here\") this.innerHTML =\"\"'>Add text here</textarea></div><div class='td formitem'>"; // start table row
		}
		html += "<input type='hidden' name='formfieldtype["+itemID+"]' value='"+formitemtypeID+"'>";
		if(formitemtypeID==1) {
			html +="<input type='text' name='formfieldplaceholder["+itemID+"]' class='form-control'>"; 
		} else if(formitemtypeID==2) {
			html +="<textarea name='formfieldplaceholder["+itemID+"]' class='form-control'></textarea>";
		} else if(formitemtypeID==3) {
			for(i=1; i<=choices; i++) {
				html +="<span class='text-nowrap'><input type='checkbox'><input name = 'formfieldchoicename_"+itemID+"["+i+"]' type='text' value='Add choice text' class='editor' onfocus='if(this.value==\"Add choice text\") this.value =\"\"'></span><input type='hidden' name='formfieldchoice_"+itemID+"["+i+"]' value='0'>&nbsp;&nbsp; ";			
			}			
		} else if(formitemtypeID==4) {
			for(i=1; i<=choices; i++) {
				html +="<span class='text-nowrap'><input type='radio' name='radiogroup_"+itemID+"'><input name = 'formfieldchoicename_"+itemID+"["+i+"]' type='text' value='Add choice text' class='editor' onfocus='if(this.value==\"Add choice text\") this.value =\"\"'></span><input type='hidden' name='formfieldchoice_"+itemID+"["+i+"]' value='0'>&nbsp;&nbsp; ";
			}
			
		} else if(formitemtypeID==5) {
			for(i=1; i<=choices; i++) {
				html += addSelectChoice(itemID, i);
			}
			i++;
			html += "<a onClick='insertSelectChoice(this)' data-i='+i+' data-itemid='+itemID+'>Add</a>";
			
		} else if(formitemtypeID==6) {
			html +="<input type='file' readonly>";
		} else if(formitemtypeID==7) {
			html +="<div class='form-inline'><select class='form-control'><option>DD</option></select><select  class='form-control'><option>MM</option></select><select  class='form-control'><option>YYYY</option></select></dov>";
		} 
		
		html += "</div><div class='td'><div class='btn-group'><button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Options <span class='caret'></span></button><ul class='dropdown-menu'><li>&nbsp;<label><input type='checkbox' name='required["+itemID+"]' > Required</label></li><li>&nbsp;<label><input type='checkbox' name='halfwidth["+itemID+"]' > Half width</label></li><li>&nbsp;<label><input type='checkbox' name='showinlistview["+itemID+"]' > Show in response list</label></li><li>&nbsp;<label><input type='checkbox' name='addverifyfield["+itemID+"]' > Add verify field</label></li><li>&nbsp;<label><input type='checkbox' name='encryptfield["+itemID+"]' > Encrypt Data</label></li><li>&nbsp;Special types:</li><li>&nbsp;<label><input type='radio' name='formfieldspecialtype["+itemID+"]' value='0'  checked> None</label></li><li>&nbsp;<label><input type='radio' name='formfieldspecialtype["+itemID+"]'  value='1' > Email</label></li><li>&nbsp;<label><input type='radio' name='formfieldspecialtype["+itemID+"]'  value='2' > Username</label></li><li>&nbsp;<label><input type='radio' name='formfieldspecialtype["+itemID+"]'  value='3' > Password</label></li></ul></div></div><div class='td manage'><a href='javascript:void(0);' onclick='if(confirm(\"Are you sure you want to delete this item?\")) {deleteItem("+itemID+",0);} return false;' class='btn btn-default btn-secondary'><i class='glyphicon glyphicon-trash'></i> Delete</a></div></li>"; // end table row
		$("#formitems").append(html);
	}
}

function deleteItem(itemid, formitemid) {
	if(formitemid>0) {
	url = "ajax/deleteformitem.ajax.php?formitemID="+formitemid;
		$.get( url, function( data ) {
			$("#item"+itemid).remove();
		});
	} else {
		$("#item"+itemid).remove();
	}
	
	
}

function  addSelectChoice(itemID, i) {
	return "<span class='selectchoice'><input name = 'formfieldchoicename_"+itemID+"["+i+"]'  type='text' value='Add choice text' class='editor' onfocus='if(this.value==\"Add choice text\") this.value =\"\"'><input type='hidden' name='formfieldchoice_"+itemID+"["+i+"]' value='0'>  </span>";
}

function insertSelectChoice(theAnchor) {
	
	$(theAnchor).before(addSelectChoice($(theAnchor).attr("data-itemid"), $(theAnchor).attr("data-i")));
	$(theAnchor).attr("data-i", parseInt($(theAnchor).attr("data-i"))+1);
}

function deleteChoice(formchoiceID) {
	if(formchoiceID>0) {
		url = "ajax/deleteformchoice.ajax.php?formchoiceID="+formchoiceID;
		$.get( url, function( data ) {
			$("#selectchoice_"+formchoiceID).remove();
		});
	} 
}

function toggleAnswerChoices() {
	
	if(document.getElementById('formfieldtypeID').value>2 && document.getElementById('formfieldtypeID').value<6) {
		document.getElementById('answerchoices').style.visibility = 'visible';
	} else {
		document.getElementById('answerchoices').style.visibility = 'hidden';
	}
	
}

function  toggleRecaptcha() {
	if(document.formbuild.captcha.value==2 || document.formbuild.captcha.value==3) {
		$('.recaptcha').show();
	} else {
		$('.recaptcha').hide();
	}
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
       <form action="" method="post" name="formbuild">
 <h1><i class="glyphicon glyphicon-th-list"></i> Form Builder</h1>
 <fieldset class="form-inline">
      <label>
            Form name: <span id="sprytextfield2">
            <input name="formname" type="text" class="editor form-control" value="<?php echo isset($row_rsForm['formname']) ? htmlentities($row_rsForm['formname'], ENT_COMPAT, "UTF-8") : ""; ?>" >
            <span class="textfieldRequiredMsg">A form name is required</span></span></label>
            <input type="hidden" name="formID" id="formID" value="<?php echo isset($_GET['formID']) ? intval($_GET['formID']) : 0; ?>">
             
            <label>Email to:
              <input name="email" type="email" multiple  class="editor form-control" value="<?php echo isset($row_rsForm['email']) ? htmlentities($row_rsForm['email'], ENT_COMPAT, "UTF-8") : ""; ?>" size="50" maxlength="100">
            </label>

            <label>
              <input type="checkbox" name="statusID" id="statusID" <?php if(!isset($row_rsForm['statusID']) || $row_rsForm['statusID']==1) echo "checked"; ?>>
            Active</label>
            </fieldset>
         <div id="TabbedPanels1" class="TabbedPanels">
           <ul class="TabbedPanelsTabGroup">
             <li class="TabbedPanelsTab" tabindex="0">Form items</li>
             <li class="TabbedPanelsTab" tabindex="0">Appearance</li>
             <li class="TabbedPanelsTab" tabindex="0">SEO<br>
             </li>
             <li class="TabbedPanelsTab" tabindex="0">Confirmation &amp; Email</li>
            
             <li class="TabbedPanelsTab" tabindex="0">User Options</li>
<li class="TabbedPanelsTab" tabindex="0">Security &amp; Spam control</li>
           </ul>
           <div class="TabbedPanelsContentGroup">
             <div class="TabbedPanelsContent">
               <fieldset class="form-inline">
                 <label for="formfieldtypeID">Add form item: </label>
                 <select name="formfieldtypeID" id="formfieldtypeID" onChange="toggleAnswerChoices()" class="form-control">
                   <option>Choose...</option>
                   <option value="0">Intro/explanatory text</option>
                   <option value="1">Text box (single line)</option>
                   <option value="2">Text area (multi line)</option>
                   <option value="3">Checkbox (multi choice, multi answer)</option>
                   <option value="4">Radio button (multi choice, single answer)</option>
                   <option value="5">Drop down select (multi choice, single answer)</option>
                   <option value="6">File upload</option>
                   <option value="7">Date picker</option>
                  
                 </select>
                 <label id="answerchoices">Choices:
                   <input name="choices" id="choices" type="text" size="2" maxlength="3" value="1" class="form-control">
                 </label>
                 <a href="javascript:void(0);" onclick="addFormItem(document.getElementById('formfieldtypeID').value, document.getElementById('choices').value); return false;" class="btn btn-default btn-secondary" ><i class="glyphicon glyphicon-plus-sign"></i> Add</a> (You can drag and drop order using left handles)
               </fieldset>
               <ul id="formitems" class="table sortable">
                 <?php $item = 0; if($totalRows_rsFormItems>0) { 
			do { 
			$item++;  
			if($row_rsFormItems['formfieldtype']>0) { ?>
                 <li class='tr' id='item<?php echo $item; ?>'><span class='td handle'>
                   <input type='hidden' name='formitemID[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['ID']; ?>'>
                   &nbsp;</span><span class='td fieldlabel'>
                     <input type='text' value='<?php echo $row_rsFormItems['formfieldname']; ?>' class='form-control editor' name='formfieldname[<?php echo $item; ?>]'  >
                   </span>             
                 <span class='td formitem'>
                   <?php } else { // is form item ?>
                   <li class='tr' id='item<?php echo $item; ?>'><span class='td handle'>
                     <input type='hidden' name='formfieldtype[<?php echo $item; ?>]' value='0'>
                     <input type='hidden' name='formitemID[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['ID']; ?>'>
                     &nbsp;</span><span class='td fieldlabel wide'>
                       <textarea class='form-control editor ' name='formfieldname[<?php echo $item; ?>]' ><?php echo htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8"); ?></textarea>
                       </span><span class='td formitem'>
                         <?php } ?>
                         <input type='hidden' name='formfieldtype[<?php echo $item; ?>]' value='<?php echo $row_rsFormItems['formfieldtype']; ?>'>
                         <?php if($row_rsFormItems['formfieldtype']>=3) {
				$select = "SELECT * FROM  formfieldchoice WHERE formfieldID = ".$row_rsFormItems['ID']." ORDER BY ordernum";
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$choices = mysql_num_rows($result);
			}
			if($row_rsFormItems['formfieldtype']==1) { ?>
                         <input type='text' name="formfieldplaceholder[<?php echo $item; ?>]" value="<?php echo htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8"); ?>" class="form-control">
                         <?php } else if($row_rsFormItems['formfieldtype']==2) {?>
                         <textarea name="formfieldplaceholder[<?php echo $item; ?>]" class="form-control" ><?php echo htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8"); ?></textarea>
                         <?php } else if($row_rsFormItems['formfieldtype']==3) {
					if($choices>0) { $i = 0;
						while($choice = mysql_fetch_assoc($result)) { $i++; ?>
                         <span class='text-nowrap'>
                           <input type='checkbox'>
                           <input name = 'formfieldchoicename_<?php echo $item; ?>[<?php echo $i; ?>]' type='text' value='<?php echo $choice['formfieldchoicename']; ?>' class='editor' >
                       </span>
                         <input type='hidden' name='formfieldchoice_<?php echo $item; ?>[<?php echo $i; ?>]' value='<?php echo $choice['ID']; ?>'>
                         <?php } ?>
                         <?php }?>
                         <?php 	} else if($row_rsFormItems['formfieldtype']==4) {
					if($choices>0) { $i = 0;
						while($choice = mysql_fetch_assoc($result)) { $i++; ?>
                         <span class='text-nowrap'>
                           <input type='radio' name='radiogroup_<?php echo $item; ?>'>
                           <input name = 'formfieldchoicename_<?php echo $item; ?>[<?php echo $i; ?>]' type='text' value='<?php echo $choice['formfieldchoicename']; ?>' class='editor' >
                       </span>
                         <input type='hidden' name='formfieldchoice_<?php echo $item; ?>[<?php echo $i; ?>]' value='<?php echo $choice['ID']; ?>'>
                         <?php } ?>
                         <?php }?>
                         <?php 		} else if($row_rsFormItems['formfieldtype']==5) {
					while($choice = mysql_fetch_assoc($result)) { $i++; ?>
                         <span class="selectchoice" id="selectchoice_<?php echo $choice['ID']; ?>">
                           <input name = 'formfieldchoicename_<?php echo $item; ?>[<?php echo $i; ?>]' type='text' value='<?php echo $choice['formfieldchoicename']; ?>' class='editor' >
                           <input type='hidden' name='formfieldchoice_<?php echo $item; ?>[<?php echo $i; ?>]' value='<?php echo $choice['ID']; ?>'> <a href='javascript:void(0);' onclick='if(confirm("Are you sure you want to delete this choice?\n\nAny user responses associated with this form item will also be deleted.")) { deleteChoice(<?php echo $choice['ID']; ?>);} return false;' class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a>
                       </span>
                         <?php }// end while ?>
                         <a onClick='insertSelectChoice(this)' data-i='<?php echo $i+1; ?>' data-itemid='<?php echo $item; ?>'>Add</a>
                         <?php 		} else if($row_rsFormItems['formfieldtype']==6) { ?>
                         <input type="file" >
                         <?php } // end 6 upload 
						 
						 else if($row_rsFormItems['formfieldtype']==7) { ?><div class="form-inline">
                         <select class='form-control'><option>DD</option></select><select  class='form-control'><option>MM</option></select><select  class='form-control'><option>YYYY</option></select></div>
							 
						<?php }
						   ?>
                         </span><div class="td">
                         
                         
                         
                         <div class="btn-group">
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Options <span class="caret"></span>
  </button>
  <ul class="dropdown-menu">
    <li>&nbsp;<label><input type="checkbox" name="required[<?php echo $item; ?>]" <?php if($row_rsFormItems['required']==1) echo "checked"; ?>> Required</label></li>
    <li>&nbsp;<label><input type="checkbox" name="halfwidth[<?php echo $item; ?>]" <?php if($row_rsFormItems['halfwidth']==1) echo "checked"; ?>> Half width</label></li>
    <li>&nbsp;<label><input type="checkbox" name="showinlistview[<?php echo $item; ?>]" <?php if($row_rsFormItems['showinlistview']==1) echo "checked"; ?>> Show in response list</label></li>
     <li>&nbsp;<label><input type="checkbox" name="addverifyfield[<?php echo $item; ?>]" <?php if($row_rsFormItems['addverifyfield']==1) echo "checked"; ?>> Add verify field</label></li>
      <li>&nbsp;<label><input type="checkbox" name="encryptfield[<?php echo $item; ?>]" <?php if($row_rsFormItems['encryptfield']==1) echo "checked"; ?>> Encrypt data</label></li>
    
    
    
    
    <li>&nbsp;Special types:</li>
     <li>&nbsp;<label><input type="radio" name="formfieldspecialtype[<?php echo $item; ?>]" <?php if($row_rsFormItems['formfieldspecialtype']==0) echo "checked"; ?> value="0"> None</label></li>
     
     <li>&nbsp;<label><input type="radio" name="formfieldspecialtype[<?php echo $item; ?>]" <?php if($row_rsFormItems['formfieldspecialtype']==1) echo "checked"; ?> value="1"> Email</label></li>
     <li>&nbsp;<label><input type="radio" name="formfieldspecialtype[<?php echo $item; ?>]" <?php if($row_rsFormItems['formfieldspecialtype']==2) echo "checked"; ?> value="2"> Username</label></li>
      <li>&nbsp;<label><input type="radio" name="formfieldspecialtype[<?php echo $item; ?>]" <?php if($row_rsFormItems['formfieldspecialtype']==3) echo "checked"; ?> value="3"> Password</label></li>
       
      
   
  </ul>
</div>


</div><div class='td manage'><a href='javascript:void(0);' onclick='if(confirm("Are you sure you want to delete this item?\n\nAny user responses associated with this form item will also be deleted.")) {deleteItem(<?php echo $item; ?>,<?php echo $row_rsFormItems['ID']; ?> );} return false;' class="btn btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a><br>
                           
                         </div></li>
                   <?php } while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems)); } ?>
                  </span>
               </ul>
             </div>
             <div class="TabbedPanelsContent">
             <p>Labels: <label><input name="showlabels" type="radio" value="1"  <?php if($row_rsForm['showlabels']==1 || !isset($row_rsForm['showlabels'])) echo "checked"; ?> > Before input</label> &nbsp;&nbsp;&nbsp;
             <label><input name="showlabels" type="radio" value="2"  <?php if($row_rsForm['showlabels']==2) echo "checked"; ?> > Above input</label>  &nbsp;&nbsp;&nbsp;
             <label><input name="showlabels" type="radio" value="0"  <?php if($row_rsForm['showlabels']==0) echo "checked"; ?> > None</label> 
             </p>
             <p><input name="showplaceholders" type="checkbox" value="1" <?php if($row_rsForm['showplaceholders']==1) echo "checked"; ?> >
               Show labels as placeholders
                 </label>
             </p>
               <p>
                 <label >Header introductory text:<br>
                   <textarea name="header" class="tinymce"><?php echo $row_rsForm['header']; ?></textarea>
                 </label>
               </p>
               
               <p>
                 <label >Footer text (below submit button):<br>
                   <textarea name="footer" class="tinymce"><?php echo $row_rsForm['footer']; ?></textarea>
                 </label>
               </p>
              
              <table class="form-table">
                 <tr>
                   <th scope="row"><label for="text_submit">Submit button text:</label></th>
                   <td> <input name="text_submit" type="text" id="text_submit" value="<?php echo isset($row_rsForm['text_submit']) ? $row_rsForm['text_submit'] : "Submit"; ?>" size="50" maxlength="50" class="form-control"></td>
                 </tr>
                 <tr>
                   <th scope="row"><label for="text_required">Required item:</label></th>
                   <td> <input name="text_required" type="text" id="text_required" value="<?php echo isset($row_rsForm['text_required']) ? $row_rsForm['text_required'] : "Required item"; ?>" size="50" maxlength="50" class="form-control"></td>
                 </tr>
                 <tr>
                   <th scope="row"><label for="text_enter_value">Error: text value is required:</label></th>
                   <td> <input name="text_enter_value" type="text" id="text_enter_value" value="<?php echo isset($row_rsForm['text_enter_value']) ? $row_rsForm['text_enter_value'] : "A value is required in this form field"; ?>" size="50" maxlength="50" class="form-control"></td>
                 </tr>
                 
                 <tr>
                   <th scope="row"><label for="text_select_value">Error: select value is required:</label></th>
                   <td> <input name="text_select_value" type="text" id="text_select_value" value="<?php echo isset($row_rsForm['text_select_value']) ? $row_rsForm['text_select_value'] : "Please select a value"; ?>" size="50" maxlength="50" class="form-control"></td>
                 </tr>
                 
                 <tr>
                   <th scope="row"><label for="text_check_value">Error: checkbox required:</label></th>
                   <td> <input name="text_check_value" type="text" id="text_check_value" value="<?php echo isset($row_rsForm['text_check_value']) ? $row_rsForm['text_check_value'] : "Please check box"; ?>" size="50" maxlength="50" class="form-control"></td>
                 </tr>
                 
                 
               </table>
               
             </div>
             <div class="TabbedPanelsContent">
               <table class="form-table" >
                 <tr>
                   <th scope="row" class="text-right"><label for="pagetitle">Page title:</label></th>
                   <td>
                     <input name="pagetitle" type="text" id="pagetitle" size="50" maxlength="255" value="<?php echo $row_rsForm['pagetitle']; ?>" class="form-control">
                   </td>
                 </tr>
                 <tr>
                   <th scope="row" class="top text-right"><label for="metadescription">META Description:</label></th>
                   <td>
                     <textarea name="metadescription" cols="50" rows="5" id="metadescription" class="form-control"><?php echo $row_rsForm['metadescription']; ?></textarea>
                   </td>
                 </tr>
               </table>
             </div>
             <div class="TabbedPanelsContent"> <span id="sprytextarea1">
               <textarea name="confirmationpage" id="confirmationpage" cols="45" rows="5" class="tinymce form-control"><?php echo isset($row_rsForm['confirmationpage']) ? $row_rsForm['confirmationpage'] : "<h1>Thank you</h1><p>We have received your submission.</p>"; ?></textarea>
               <span class="textareaRequiredMsg">A value is required.</span></span>
               <label ><br>
                 OR Confirmation Page URL:<br>
                 <input name="confirmationpageURL" type="text" value="<?php echo $row_rsForm['confirmationpageURL']; ?>" size="80" maxlength="255" class="form-control">
               </label>
            
                <h2>
                 Email</h2>
               <p>
                 <label>
                   <input <?php if (!(strcmp($row_rsForm['sendemail'],1))) {echo "checked=\"checked\"";} ?> name="sendemail" type="checkbox" value="1">
                   Send confirmation email to client</label>
                 (sent to first form field labelled with 'Email')</p>
               <label>Email subject:
                 <input name="emailsubject" type="text" id="emailsubject" value="<?php echo htmlentities($row_rsForm['emailsubject'], ENT_COMPAT, "UTF-8"); ?>" size="50" maxlength="100" class="form-control">
               </label>
               <label> <br>
                 Email content:<br>
                 <textarea name="emailmessage"  style="width:600px; height:100px;" class="form-control"><?php echo isset($row_rsForm['emailmessage']) ? htmlentities($row_rsForm['emailmessage'], ENT_COMPAT, "UTF-8") : ""; ?></textarea>
               </label>
             </div>
             <span class='td formitem'>
             <div class="TabbedPanelsContent"><p class="form-inline">
               <label>Restrict form access to*:&nbsp;
               
                 <select name="accessrankID" id="accessrankID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsForm['accessrankID']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsUserType['ID']?>"<?php if (!(strcmp($row_rsUserType['ID'], $row_rsForm['accessrankID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserType['name']?></option>
                   <?php
} while ($row_rsUserType = mysql_fetch_assoc($rsUserType));
  $rows = mysql_num_rows($rsUserType);
  if($rows > 0) {
      mysql_data_seek($rsUserType, 0);
	  $row_rsUserType = mysql_fetch_assoc($rsUserType);
  }
?>
                 </select>
               </label> &nbsp;via&nbsp;<label><input name="loginsignup" <?php if (!(strcmp($row_rsForm['loginsignup'],"0"))) {echo "checked=\"checked\"";} ?> type="radio" value="0">&nbsp;Login</label>&nbsp;&nbsp;<label><input name="loginsignup" type="radio" value="1" <?php if (!(strcmp($row_rsForm['loginsignup'],"1"))) {echo "checked=\"checked\"";} ?>>&nbsp;Sign up</label> page
             </p>
             
             <p class="form-inline"><label><input <?php if (!(strcmp($row_rsForm['adduser'],1))) {echo "checked=\"checked\"";} ?> name="adduser" type="checkbox" value="1"> 
             Add person who fills in form to Site Users</label> and <label>add to user group**: 
               
                 <select name="groupID" id="groupID" class="form-control">
                   <option value="" <?php if (!(strcmp("", $row_rsForm['groupID']))) {echo "selected=\"selected\"";} ?>>None</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsUserGroups['ID']?>"<?php if (!(strcmp($row_rsUserGroups['ID'], $row_rsForm['groupID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsUserGroups['groupname']?></option>
                   <?php
} while ($row_rsUserGroups = mysql_fetch_assoc($rsUserGroups));
  $rows = mysql_num_rows($rsUserGroups);
  if($rows > 0) {
      mysql_data_seek($rsUserGroups, 0);
	  $row_rsUserGroups = mysql_fetch_assoc($rsUserGroups);
  }
?>
                 </select>
               </label>
               <p>*For embedded forms user access shoould also be added to parent page    </p>           
               
               <p>**in order for this funtionality to work you must have the following EXACT field names: &quot;Your name&quot; or &quot;First name&quot; and &quot;Surname&quot;, and &quot;Email&quot;<br>
                 An opt-in checkbox to recieve site emails will also be added. You can also optionally allow them to choose a username and password with field names "Username" and "Password".</p>
             </div>
             </span>
             <div class="TabbedPanelsContent">
              
        <p><label><input <?php if (!(strcmp($row_rsForm['noindex'],1))) {echo "checked=\"checked\"";} ?> name="noindex" type="checkbox" value="1"> Ask search engines not to index</label></p>        
            <p>     Use CAPTCHA to help prevent spam:
                 
                 
                  <label><input <?php if (!isset($row_rsForm['captcha']) || $row_rsForm['captcha']==0) {echo "checked=\"checked\"";} ?> name="captcha" type="radio"  value="0" onClick="toggleRecaptcha()"> None</label>&nbsp;&nbsp;&nbsp;
                  
                   <label><input <?php if (!(strcmp($row_rsForm['captcha'],1))) {echo "checked=\"checked\"";} ?> name="captcha" type="radio"  value="1" onClick="toggleRecaptcha()"> Simple letters</label>&nbsp;&nbsp;&nbsp;
                   
                    <label><input <?php if (!(strcmp($row_rsForm['captcha'],2))) {echo "checked=\"checked\"";} ?> name="captcha" type="radio"  value="2" onClick="toggleRecaptcha()"> 
                     ReCaptcha 2</label>&nbsp;&nbsp;&nbsp;
                    
                     <label><input <?php if (!(strcmp($row_rsForm['captcha'],3))) {echo "checked=\"checked\"";} ?> name="captcha" type="radio"  value="3" onClick="toggleRecaptcha()"> 
                    Invisible ReCaptcha</label>&nbsp;&nbsp;&nbsp;
                 
                 
                </p><p class="recaptcha form-inline"><input name="recaptcha_site_key" type="text" placeholder="Site Key" value="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" size="50" maxlength="50" class="form-control"> <input name="recaptcha_secret_key" type="text" placeholder="Secret Key" value="<?php echo $row_rsPreferences['recaptcha_secret_key']; ?>" size="50" maxlength="50" class="form-control">  <a href="https://www.google.com/recaptcha/admin#list" target="_blank">API</a></p><p>
               <label>
                 <input <?php if (!(strcmp($row_rsForm['blockwww'],1))) {echo "checked=\"checked\"";} ?> name="blockwww" type="checkbox" id="blockwww" value="1">
                 Block posts with web addresses in content</label></p>
           </div>
           </div>
         </div>
           <button type="submit" class="btn btn-primary" >Save changes</button>
          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
          <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>">
      </form>
        <script>
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
        </script>
      <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsFormItems);

mysql_free_result($rsUserGroups);

mysql_free_result($rsPreferences);
?>
