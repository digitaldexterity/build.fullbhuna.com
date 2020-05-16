<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
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

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE survey SET surveyname=%s, introduction=%s, accesslevel=%s, statusID=%s, canupdate=%s, anonymous=%s, requiredirectoryID=%s, multiple=%s, usesections=%s, usescoring=%s, showscores=%s, useweighting=%s, usecomments=%s, autocalc=%s, redirectURL=%s, email=%s, showsummary=%s, summarystart=%s, summaryend=%s, confirmationemail=%s, confirmationemailcontent=%s, startdatetime=%s, enddatetime=%s, modifiedbyID=%s, modifieddatetime=%s, answerrequired=%s, maxscore=%s, passscore=%s WHERE ID=%s",
                       GetSQLValueString($_POST['surveyname'], "text"),
                       GetSQLValueString($_POST['introduction'], "text"),
                       GetSQLValueString($_POST['accesslevel'], "int"),
                       GetSQLValueString($_POST['statusID'], "int"),
                       GetSQLValueString(isset($_POST['canupdate']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['anonymous']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['requiredirectoryID']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['multiple']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usesections']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usescoring']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['showscores']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['useweighting']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['usecomments']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['autocalc']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['redirectURL'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString(isset($_POST['showsummary']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['summarystart']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['summaryend']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['confirmationemail']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['comfirmationemailcontent'], "text"),
                       GetSQLValueString($_POST['startdatetime'], "date"),
                       GetSQLValueString($_POST['enddatetime'], "date"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString(isset($_POST['answerrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['maxscore'], "int"),
                       GetSQLValueString($_POST['passscore'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
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

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAccessLevel = "SELECT * FROM usertype WHERE ID >= 1 ORDER BY ID ASC";
$rsAccessLevel = mysql_query($query_rsAccessLevel, $aquiescedb) or die(mysql_error());
$row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
$totalRows_rsAccessLevel = mysql_num_rows($rsAccessLevel);

$colname_rsSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurvey = sprintf("SELECT * FROM survey WHERE ID = %s LIMIT 1", GetSQLValueString($colname_rsSurvey, "int"),GetSQLValueString($colname_rsSurvey, "int"));
$rsSurvey = mysql_query($query_rsSurvey, $aquiescedb) or die(mysql_error());
$row_rsSurvey = mysql_fetch_assoc($rsSurvey);
$totalRows_rsSurvey = mysql_num_rows($rsSurvey);

$maxRows_rsQuestions = 500;
$pageNum_rsQuestions = 0;
if (isset($_GET['pageNum_rsQuestions'])) {
  $pageNum_rsQuestions = $_GET['pageNum_rsQuestions'];
}
$startRow_rsQuestions = $pageNum_rsQuestions * $maxRows_rsQuestions;

$varSurveyID_rsQuestions = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsQuestions = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsQuestions = sprintf("SELECT survey_question.ID, survey_question.questionorder, survey_question.questiontext, survey_question.active, survey_question.question_number, questiontype , COUNT(survey_answer.ID) AS answers FROM survey_question LEFT JOIN survey_answer ON (survey_answer.questionID = survey_question.ID) WHERE survey_question.surveyID = %s  GROUP BY survey_question.ID ORDER BY survey_question.questionorder", GetSQLValueString($varSurveyID_rsQuestions, "int"));
$query_limit_rsQuestions = sprintf("%s LIMIT %d, %d", $query_rsQuestions, $startRow_rsQuestions, $maxRows_rsQuestions);
$rsQuestions = mysql_query($query_limit_rsQuestions, $aquiescedb) or die(mysql_error());
$row_rsQuestions = mysql_fetch_assoc($rsQuestions);

if (isset($_GET['totalRows_rsQuestions'])) {
  $totalRows_rsQuestions = $_GET['totalRows_rsQuestions'];
} else {
  $all_rsQuestions = mysql_query($query_rsQuestions);
  $totalRows_rsQuestions = mysql_num_rows($all_rsQuestions);
}
$totalPages_rsQuestions = ceil($totalRows_rsQuestions/$maxRows_rsQuestions)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

$queryString_rsQuestions = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsQuestions") == false && 
        stristr($param, "totalRows_rsQuestions") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsQuestions = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsQuestions = sprintf("&totalRows_rsQuestions=%d%s", $totalRows_rsQuestions, $queryString_rsQuestions);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Edit ".ucwords($row_rsSurveyPrefs['surveyName']). " - ".$row_rsSurvey['surveyname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js"></script>
<script >
addListener("load", init);
function init() {
	toggleEmail(document.getElementById('confirmationemail'))
}
function toggleEmail(theCheckbox) {
	if(theCheckbox.checked) { 
	document.getElementById('emailcontent').style.display = 'block'; 
	} else { 
	document.getElementById('emailcontent').style.display = 'none'; }
    
    }
    </script>
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=survey_question&field=questionorder&"+order); 
            } 
        }); 
	
    }); 
</script>
<link href="../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
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
        <div class="page surveys">
   <h1><i class="glyphicon glyphicon-education"></i> Edit <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?>   <small><?php echo $row_rsSurvey['surveyname']; ?></small></h1>         <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
   <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
   <li class="nav-item"><a href="questions/add_question.php?surveyID=<?php echo $row_rsSurvey['ID']; ?>&amp;total=<?php echo $totalRows_rsQuestions; ?>"  class="nav-link"><i class="glyphicon glyphicon-plus-sign"></i> Add question</a></li>
  
   
   <li class="nav-item"><a href="/surveys/admin/" class="nav-link" ><i class="glyphicon glyphicon-arrow-left"></i> Manage <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?>s</a></li> 
   <li class="nav-item"><a href="/surveys/survey.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" target="_blank" class="nav-link" rel="noopener"><i class="glyphicon glyphicon-search"></i> View <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?></a></li>
   </ul></div></nav>

   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Questions</li>
       <li class="TabbedPanelsTab" tabindex="0"><?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?> Options</li>
       <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
     </ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
        
       
         <?php if ($totalRows_rsQuestions == 0) { // Show if recordset empty ?>
           <p>There are no questions added to this <?php echo $row_rsSurveyPrefs['surveyName']; ?> yet.</p>
           <?php } // Show if recordset empty ?>

         
         <?php if ($totalRows_rsQuestions > 0) { // Show if recordset not empty ?>
           <p class="text-muted">Questions <?php echo ($startRow_rsQuestions + 1) ?> to <?php echo min($startRow_rsQuestions + $maxRows_rsQuestions, $totalRows_rsQuestions) ?> of <?php echo $totalRows_rsQuestions ?>. <span id="info">Drag and drop to reorder</span></p>
           <table class="table table-hover">
           <tbody class="sortable">
              <?php do { ?>
                <tr  id="listItem_<?php echo $row_rsQuestions['ID']; ?>" ><td class= "handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of questions">&nbsp;</td>
                  <td><?php if ($row_rsQuestions['active']==1) { ?><img src="../../core/images/icons/green-light.png" alt="Active" width="16" height="16" style="vertical-align:
middle;" /><?php } else { ?><img src="../../core/images/icons/red-light.png" alt="Inactive" width="16" height="16" style="vertical-align:
middle;" /><?php } ?></td>
                  <td><?php echo $row_rsQuestions['question_number']; ?></td>
                  <td><a href="questions/edit_question.php?questionID=<?php echo $row_rsQuestions['ID']; ?>" ><?php echo $row_rsQuestions['questiontext']; ?></a></td>
                  <td><em>
                 <?php switch($row_rsQuestions['questiontype']) { 
                  
                 
             case 1 : echo "Multipe choice (Single answer from ".$row_rsQuestions['answers'].")"; break;
            case 2 : echo "Multiple choice (Multiple answer from ".$row_rsQuestions['answers'].")"; break;
            
             case 4 : echo "Typed (Single line answer)"; break;
              case 3 : echo "Typed (Single multi-line answer)"; break;
             case 0: echo "Typed (Multiple answer from ".$row_rsQuestions['answers'].")"; break;
         
				 } ?>
         
         </em></td>
                  <td><a href="questions/edit_question.php?questionID=<?php echo $row_rsQuestions['ID']; ?>" class="link_edit icon_only" title="Click to view or edit this question">Edit</a></td>
           </tr>
                <?php } while ($row_rsQuestions = mysql_fetch_assoc($rsQuestions)); ?>
</tbody></table>
           <?php } // Show if recordset not empty ?>
           
       </div>
       <div class="TabbedPanelsContent">
         <?php if(isset($submit_error)) { ?>
         <p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
         <?php } ?>
           <table class="form-table"> <tr>
               <td class="text-nowrap text-right">Title:</td>
               <td colspan="2" class="text-nowrap"><span id="sprytextfield1">
                 <input name="surveyname" type="text"  value="<?php echo $row_rsSurvey['surveyname']; ?>" size="50" maxlength="100" class="form-control" />
                 <span class="textfieldRequiredMsg"><br />
                   A title is required.</span></span></td>
             </tr> <tr>
               <td class="text-nowrap text-right">Access level:</td>
               <td colspan="2" class="text-nowrap form-inline"><select name="accesslevel" class="form-control" >
                   <option value="0" <?php if (!(strcmp(0, $row_rsSurvey['accesslevel']))) {echo "selected=\"selected\"";} ?>>Everyone</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsAccessLevel['ID']?>"<?php if (!(strcmp($row_rsAccessLevel['ID'], $row_rsSurvey['accesslevel']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsAccessLevel['name']?>s</option>
                   <?php
} while ($row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel));
  $rows = mysql_num_rows($rsAccessLevel);
  if($rows > 0) {
      mysql_data_seek($rsAccessLevel, 0);
	  $row_rsAccessLevel = mysql_fetch_assoc($rsAccessLevel);
  }
?>
                 </select> &nbsp;&nbsp;&nbsp; <label>
                 <input <?php if (!(strcmp($row_rsSurvey['anonymous'],1))) {echo "checked=\"checked\"";} ?> name="anonymous" type="checkbox" id="anonymous" value="1" />
                 All users  answer anonymously </label>
                  </td>
             </tr> <tr>
               <td class="text-nowrap text-right">Restrictions:</td>
               <td colspan="2">
                 
                 <label>
                   <input <?php if (!(strcmp($row_rsSurvey['multiple'],1))) {echo "checked=\"checked\"";} ?> name="multiple" type="checkbox" id="multiple" value="1" />
                   Users can complete multiple times</label> &nbsp;&nbsp;&nbsp;
                 
                 <label><input <?php if (!(strcmp($row_rsSurvey['canupdate'],1))) {echo "checked=\"checked\"";} ?> name="canupdate" type="checkbox" id="canupdate" value="1" />
                   User can change answers after completion</label>
                 &nbsp;&nbsp;&nbsp;
                 
                 
                 <label><input <?php if (!(strcmp($row_rsSurvey['requiredirectoryID'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="requiredirectoryID" id="requiredirectoryID" />
                   Pre-require organisation details (members only)</label>
                 </td>
             </tr> <tr>
               <td class="text-nowrap text-right">Starts (optional):</td>
               <td colspan="2" class="text-nowrap form-inline"><input name="startdatetime" type="hidden" id="startdatetime" value="<?php echo $row_rsSurvey['startdatetime']; ?>" />
                 <?php $inputname="startdatetime"; $setvalue=$row_rsSurvey['startdatetime']; $time = true; require('../../core/includes/datetimeinput.inc.php'); ?>&nbsp;&nbsp;&nbsp;<label>              
                  
                    Status: 
                    <select name="statusID" class="form-control"  >
                      <option value="0" <?php if (!(strcmp(0, $row_rsSurvey['statusID']))) {echo "selected=\"selected\"";} ?>>Draft</option>
                      <option value="1" <?php if (!(strcmp(1, $row_rsSurvey['statusID']))) {echo "selected=\"selected\"";} ?>>Live</option>
                      <option value="2" <?php if (!(strcmp(2, $row_rsSurvey['statusID']))) {echo "selected=\"selected\"";} ?>>Archived</option>
                    </select>
                 </label></td>
             </tr> <tr>
               <td class="text-nowrap text-right">Ends (optional):</td>
               <td colspan="2" class="text-nowrap"><input name="enddatetime" type="hidden" id="enddatetime" value="<?php echo $row_rsSurvey['enddatetime']; ?>" />
                 <?php $inputname="enddatetime"; $setvalue=$row_rsSurvey['enddatetime']; $time = true; require('../../core/includes/datetimeinput.inc.php'); ?>
</td>
             </tr> <tr>
               <td class="text-nowrap text-right top">Introduction:</td>
               <td colspan="2"><textarea name="introduction" id="introduction" cols="45" rows="5" class="form-control" ><?php echo $row_rsSurvey['introduction']; ?></textarea></td>
             </tr> <tr>
               <td class="text-nowrap text-right">Sections</td>
               <td colspan="2"><label>
                 <input <?php if (!(strcmp($row_rsSurvey['usesections'],1))) {echo "checked=\"checked\"";} ?> name="usesections" type="checkbox" id="usesections" value="1" />
                 (optionally add the ability to divide <?php echo $row_rsSurveyPrefs['surveyName']; ?> up into sections)</label></td>
             </tr> 
             <tr>
               <td class="text-nowrap text-right">Answers compulsary:</td>
               <td colspan="2"><input <?php if (!(strcmp($row_rsSurvey['answerrequired'],1))) {echo "checked=\"checked\"";} ?> name="answerrequired" type="checkbox" id="answerrequired" value="1"> 
                 (overrides same option on individual question)</td>
             </tr>
             <tr>
               <td class="text-nowrap text-right">Use scoring:</td>
               <td colspan="2"><input <?php if (!(strcmp($row_rsSurvey['usescoring'],1))) {echo "checked=\"checked\"";} ?> name="usescoring" type="checkbox" id="usescoring" value="1" onchange="if(this.checked==true){document.form1.showscores.disabled = false; document.form1.autocalc.disabled = false; } else { document.form1.showscores.disabled = true; document.form1.showscores.checked = false; document.form1.autocalc.disabled = true; document.form1.autocalc.checked = false;}  " />
                 (tick to optionally add scoring system)</td>
             </tr> <tr>
               <td class="text-nowrap text-right">&nbsp;</td>
               <td align="right">Show answer scores:</td>
               <td><input <?php if (!(strcmp($row_rsSurvey['showscores'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="showscores" id="showscores" /> &nbsp;&nbsp; <label>
                 Show calculated question score (for multi-choice): 
                     <input name="autocalc" type="checkbox" id="autocalc" value="1" <?php if (!(strcmp($row_rsSurvey['autocalc'],1))) {echo "checked=\"checked\"";} ?> /></label></td>
             </tr> 
             <tr>
               <td class="text-nowrap text-right">&nbsp;</td>
               <td align="right">Max <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?> score:</td>
               <td class="form-inline"><label>
                 <input name="maxscore" type="text" id="maxscore" value="<?php echo $row_rsSurvey['maxscore']; ?>" size="5" maxlength="5" class="form-control" > (0=auto-calc) 
                 </label> &nbsp;&nbsp; <label>Pass score: 
                   <input name="passscore" type="text" id="passscore" value="<?php echo $row_rsSurvey['passscore']; ?>" size="5" maxlength="5" class="form-control" ></label>% (0=not applicable) 
                 </td>
             </tr> 
             <tr>
               <td class="text-nowrap text-right">Weighting:</td>
               <td colspan="2"><label>
                 <input <?php if (!(strcmp($row_rsSurvey['useweighting'],1))) {echo "checked=\"checked\"";} ?> name="useweighting" type="checkbox" id="useweighting" value="1" />
                 (optionally add ability to weight each question)</label></td>
             </tr> <tr>
               <td class="text-nowrap text-right">Comments:</td>
               <td colspan="2"><label>
                 <input <?php if (!(strcmp($row_rsSurvey['usecomments'],1))) {echo "checked=\"checked\"";} ?> name="usecomments" type="checkbox" id="usecomments" value="1" />
                 (optionally add comments  below every question)</label></td>
             </tr> <tr>
               <td class="text-nowrap text-right">Question index:</td>
               <td colspan="2"><label>
                 <input <?php if (!(strcmp($row_rsSurvey['showsummary'],1))) {echo "checked=\"checked\"";} ?> name="showsummary" type="checkbox" id="showsummary" value="1" />
                 link on each page 
                 <input <?php if (!(strcmp($row_rsSurvey['summaryend'],1))) {echo "checked=\"checked\"";} ?> name="summaryend" type="checkbox" id="summaryend" value="1" />
                 at end of <?php echo $row_rsSurveyPrefs['surveyName']; ?>
                 <input <?php if (!(strcmp($row_rsSurvey['summarystart'],1))) {echo "checked=\"checked\"";} ?> name="summarystart" type="checkbox" id="summarystart" value="1" />
                 at start of <?php echo $row_rsSurveyPrefs['surveyName']; ?></label></td>
             </tr> <tr>
               <td class="text-nowrap text-right">&nbsp;</td>
               <td colspan="2" class="text-nowrap "><button type="submit" class="btn btn-primary" >Save Changes</button></td>
             </tr>
           </table>
           <input type="hidden" name="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
           <input type="hidden" name="modifieddatetime" value="<?php echo $row_rsLoggedIn['']; ?>" />
           <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsSurvey['ID']; ?>" />
           <table class="form-table">
           </table>
           <input type="hidden" name="MM_update" value="form1" />
         
       </div>
       <div class="TabbedPanelsContent">
         <p> <label for="redirectURL">The page you wish to direct to once <?php echo $row_rsSurveyPrefs['surveyName']; ?> has been completed: </label></p>
         <p>
          
           <input name="redirectURL" type="text"  id="redirectURL" value="<?php echo $row_rsSurvey['redirectURL']; ?>" size="60" maxlength="255" class="form-control"/>
          
         </p>
         <p>Notification of each completion of <?php echo $row_rsSurveyPrefs['surveyName']; ?> to the following email address:</p>
         <p>
           <input name="email" type="email" multiple  id="email" value="<?php echo $row_rsSurvey['email']; ?>" size="60" maxlength="255" class="form-control"/>
         </p>
         <p>
           <label>
             <input type="checkbox" name="confirmationemail" id="confirmationemail" value="1" onclick="toggleEmail(this)" />
             Send confirmation email (if user is logged in)</label>
         </p>
         <p id="emailcontent">
           <label>Email content:<br />
<textarea name="comfirmationemailcontent" id="comfirmationemailcontent" cols="45" rows="5" class="form-control"></textarea>
           </label>
         </p>
         <p>
           <button type="submit" class="btn btn-primary" >Save Changes</button>
         </p>
         <h2>Reset</h2>
         <p>Resetting the <?php echo $row_rsSurveyPrefs['surveyName']; ?> will delete all responses to this survey so far. This is useful if it has been tested and you need to start afresh. </p>
         <p><a href="reset_survey.php?surveyID=<?php echo $row_rsSurvey['ID']; ?>" onclick="document.returnValue = confirm('Are you sure you want to delete all answers to this survey?'); return document.returnValue;">Reset <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?></a></p>
         <p>If you want to delete indiviual responders, you can do so <a href="results/index.php?surveyID=<?php echo $row_rsSurvey['ID']; ?>">here</a>.</p>
       </div>
     </div>
   </div></form>
   <br><br>
 <pre><i class="glyphicon glyphicon-link"></i>  <a href="/surveys/survey.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" onclick="openMainWindow(this.href); return false;"><?php echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://"; echo $_SERVER['HTTP_HOST']."/surveys/survey.php?surveyID=".$_GET['surveyID']; ?></a></pre>
           <p><a href="questions/question_summary_print.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" class="btn btn-default btn-secondary">View printable summary</a> <a href="questions/questions_print.php?surveyID=<?php echo intval($_GET['surveyID']); ?>" class="btn btn-default btn-secondary">View printable full version</a></p>
   <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {hint:"Enter a title for the survey"});
//-->
</script>
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
<?php } ?></div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsAccessLevel);

mysql_free_result($rsSurvey);

mysql_free_result($rsQuestions);

mysql_free_result($rsSurveyPrefs);
?>
