<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../../core/includes/upload.inc.php'); ?>
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

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	$_POST['imageURL'] = (isset($_POST["noimage"])) ? "" : $_POST['imageURL'];
}
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertSQL = sprintf("INSERT INTO survey_question (question_number, questionorder, questionweight, addscore, questiontext, questionnotes, questiontype, maxchoices, addcommentsbox, active, createdbyID, createddatetime, surveyID, surveysectionID, imageURL, answerrequired, passscore) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['question_number'], "text"),
                       GetSQLValueString($_POST['questionorder'], "int"),
                       GetSQLValueString($_POST['questionweight'], "int"),
                       GetSQLValueString(isset($_POST['addscore']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['questiontext'], "text"),
                       GetSQLValueString($_POST['questionnotes'], "text"),
                       GetSQLValueString($_POST['questiontype'], "int"),
                       GetSQLValueString($_POST['maxchoices'], "int"),
                       GetSQLValueString(isset($_POST['addcommentsbox']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['active'], "int"),
                       GetSQLValueString($_POST['createdbyID'], "int"),
                       GetSQLValueString($_POST['createddatetime'], "date"),
                       GetSQLValueString($_POST['surveyID'], "int"),
                       GetSQLValueString($_POST['surveysectionID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString(isset($_POST['answerrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['passscore'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertGoTo = "edit_question.php?addquestion=true&questionID=".mysql_insert_id();
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));exit;
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

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname, usesections, useweighting, survey.usecomments FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$colname_rsSections = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsSections = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT ID, sectionnumber, `description` FROM survey_section WHERE surveyID = %s ORDER BY sectionnumber ASC", GetSQLValueString($colname_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Add Question"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->


<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
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
   <div class="page surveys">
   <h1><i class="glyphicon glyphicon-education"></i> Add Question</h1>
   <h2><?php echo $row_rsThisSurvey['surveyname']; ?></h2>
   <?php if(isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p><?php } ?><form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="form2" id="form2">
   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Question</li>
       <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
     </ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
     <table class="form-table">
       <tr <?php if ($row_rsThisSurvey['usesections'] != 1) { ?> style="display:none" <?php } ?>>
         <td class="text-nowrap text-right top">Section:</td>
         <td class="form-inline"><select name="surveysectionID"  id="surveysectionID"  class="form-control">
           <option value="0">Choose section...</option>
           <?php
do {  
?>
           <option value="<?php echo $row_rsSections['ID']?>"><?php echo $row_rsSections['description']?></option>
           <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
         </select> 
           <a href="../sections/index.php?surveyID=<?php echo intval($_GET['surveyID']); ?>">Manage sections</a></td>
       </tr> <tr>
         <td class="text-nowrap text-right top form-group "><label for="question_number">Number:</label></td>
         <td class="form-inline">
           <input name="question_number" type="text"  id="question_number" size="5" maxlength="5" class="form-control"/>
           (optional)</td>
       </tr> <tr>
         <td class="text-nowrap text-right top">Question:</td>
         <td><span id="sprytextarea1">
           <textarea name="questiontext" cols="50" rows="5" class="form-control"></textarea>
          <span class="textareaRequiredMsg"><br />
          A question is required.</span></span></td>
       </tr> <tr>
         <td class="text-nowrap text-right">Answer:</td>
         <td><select name="questiontype" class="form-control" >
             <option value="1" >Multipe choice (Single answer)</option>
             <option value="2" >Multiple choice (Multiple answer)</option>
            
             <option value="4">Typed (Single line answer)</option>
              <option value="3">Typed (Single multi-line answer)</option>
             <option value="0">Typed (Multiple answer)</option>
         </select>         </td>
       </tr> <tr>
         <td class="text-nowrap text-right">&nbsp;</td>
         <td><button type="submit" class="btn btn-primary" >Add Question</button></td>
       </tr>
     </table>
     <input name="questionorder" type="hidden" value="<?php echo intval($_GET['total'])+1; ?>" />
     <input type="hidden" name="active" value="1" />
     <input type="hidden" name="MM_insert" value="form2" />
     <input type="hidden" name="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
     <input type="hidden" name="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
     <input name="surveyID" type="hidden" id="surveyID" value="<?php echo intval($_GET['surveyID']); ?>" />
  </div>
       <div class="TabbedPanelsContent">
         <table class="form-table">
          
           
           <tr<?php if ($row_rsThisSurvey['usecomments'] != 1) { ?> style="display:none" <?php } ?>>
             <td class="text-nowrap text-right top">Optional<br />
               Notes:</td>
             <td><textarea name="questionnotes" id="questionnotes" cols="50" rows="3" class="form-control"></textarea></td>
           </tr>
           <tr <?php if ($row_rsThisSurvey['useweighting'] != 1) { ?> style="display:none" <?php } ?> class="form-control ">
             <td class="text-nowrap text-right">Weight:</td>
             <td class="form-inline"><input name="questionweight" type="text"  id="questionweight" size="5" maxlength="5" class="form-control"/>
                </td>
           </tr>
           
            <tr <?php if ($row_rsThisSurvey['usescoring'] != 1) { ?> style="display:none" <?php } ?> class="form-group ">
             <td class="text-nowrap text-right">Pass score (optional):</td>
             <td class="form-inline"><input name="passscore" type="text"  id="passscore" size="5" maxlength="5" class="form-control"/>
                 <label><input name="addscore" type="checkbox" id="addscore" value="1" checked="checked" />
               Score this question</label></td>
           </tr>
           
           
           <tr> </tr><tr>
             <td>Show comments box:</td>
             <td> <input name="addcommentsbox" type="checkbox" id="addcommentsbox" <?php if (!(strcmp($row_rsThisSurvey['usecomments'],1))) {echo "checked=\"checked\"";} ?> /></td>
           </tr>
           <tr>
             <td align="right">Answer required:</td>
             <td><input type="checkbox" name="answerrequired" id="answerrequired" /></td>
           </tr>
           <tr class="form-group" >
             <td align="right">Maximum answers:</td>
             <td class="form-inline"><input name="maxchoices" type="text"  id="maxchoices" size="5" maxlength="5" class="form-control"/>
               (Multiple choice, multiple answer only)</td>
           </tr>
           <tr class="upload">
             <td class="text-nowrap text-right"> Image:</td>
             <td><input name="filename" type="file" class="fileinput" id="filename" size="20" />
               <input name="imageURL" type="hidden" id="imageURL" /></td>
           </tr>
             <tr>
               <td>&nbsp;</td>
               <td><button type="submit" class="btn btn-primary" >Add Question</button></td>
             </tr>
         </table>
       </div>
     </div>
   </div>   </form><script>
<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
</script></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsThisSurvey);

mysql_free_result($rsSections);

mysql_free_result($rsSurveyPrefs);
?>
