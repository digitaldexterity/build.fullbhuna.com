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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$uploaded = getUploads();
if (isset($uploaded) && is_array($uploaded)) {
	if(isset($uploaded["filename"][0]["newname"]) && $uploaded["filename"][0]["newname"]!="") { 
		$_POST['imageURL'] = $uploaded["filename"][0]["newname"]; 
	}
	
}

if(isset($_POST["noimage"])) {
	$_POST['imageURL'] = "";
}

if(isset($_GET['deleteExceptionID'])) { // delete an exception
mysql_select_db($database_aquiescedb, $aquiescedb);
$delete = "DELETE FROM survey_question_exception WHERE ID = ".GetSQLValueString($_GET['deleteExceptionID'],"int");
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
}

if (isset($_POST['addConstruct'])) { // new exception added, so insert
$insert = "INSERT INTO survey_question_exception (questionID, answerID, isnot, equalto, setvalue) VALUES (".GetSQLValueString($_POST['questionID'],"int").",".GetSQLValueString($_POST['answerID'],"int").",".GetSQLValueString($_POST['isnot'],"int").",".GetSQLValueString($_POST['equalto'],"int").",".GetSQLValueString($_POST['setvalue'],"int").")";
mysql_select_db($database_aquiescedb, $aquiescedb);
  $result = mysql_query($insert, $aquiescedb) or die(mysql_error());
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formquestion")) {
  $updateSQL = sprintf("UPDATE survey_question SET question_number=%s, questionweight=%s, addscore=%s, questiontext=%s, questionnotes=%s, questiontype=%s, maxchoices=%s, addcommentsbox=%s, active=%s, modifiedbyID=%s, modifieddatetime=%s, surveysectionID=%s, imageURL=%s, answerrequired=%s, passscore=%s WHERE ID=%s",
                       GetSQLValueString($_POST['question_number'], "text"),
                       GetSQLValueString($_POST['questionweight'], "int"),
                       GetSQLValueString(isset($_POST['addscore']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['questiontext'], "text"),
                       GetSQLValueString($_POST['questionnotes'], "text"),
                       GetSQLValueString($_POST['questiontype'], "int"),
                       GetSQLValueString($_POST['maxchoices'], "int"),
                       GetSQLValueString(isset($_POST['addcommentsbox']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString(isset($_POST['active']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['modifiedbyID'], "int"),
                       GetSQLValueString($_POST['modifieddatetime'], "date"),
                       GetSQLValueString($_POST['surveysectionID'], "int"),
                       GetSQLValueString($_POST['imageURL'], "text"),
                       GetSQLValueString(isset($_POST['answerrequired']) ? "true" : "", "defined","1","0"),
                       GetSQLValueString($_POST['passscore'], "int"),
                       GetSQLValueString($_POST['ID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($updateSQL, $aquiescedb) or die(mysql_error());
}
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formquestion")) {
  $updateGoTo = "../update_survey.php?surveyID=" . $_POST['surveyID'] . "";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo)); exit;
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formanswer")) {
  $insertSQL = sprintf("INSERT INTO survey_answer (questionID, answerscore, answertext, ordernum) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['questionID'], "int"),
                       GetSQLValueString($_POST['scoreanswer'], "int"),
                       GetSQLValueString($_POST['answertext'], "text"),
                       GetSQLValueString($_POST['ordernum'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());
}

if ((isset($_GET['deleteID'])) && ($_GET['deleteID'] != "")) {
  $deleteSQL = sprintf("DELETE FROM survey_answer WHERE ID=%s",
                       GetSQLValueString($_GET['deleteID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($deleteSQL, $aquiescedb) or die(mysql_error());
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

$colname_rsQuestion = "-1";
if (isset($_GET['questionID'])) {
  $colname_rsQuestion = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsQuestion = sprintf("SELECT survey_question.*, survey.surveyname, survey.useweighting, survey.usesections, survey.usescoring, survey.usecomments, survey.answerrequired AS surveyanswerrequired FROM survey_question LEFT JOIN survey ON (survey_question.surveyID = survey.ID) WHERE survey_question.ID = %s OR %s < 1 ORDER BY survey_question.createddatetime DESC LIMIT 1", GetSQLValueString($colname_rsQuestion, "int"),GetSQLValueString($colname_rsQuestion, "int"));
$rsQuestion = mysql_query($query_rsQuestion, $aquiescedb) or die(mysql_error());
$row_rsQuestion = mysql_fetch_assoc($rsQuestion);
$totalRows_rsQuestion = mysql_num_rows($rsQuestion);

// required later
$thisSurveyID = $row_rsQuestion['surveyID']; 

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsAnswers = "SELECT * FROM survey_answer WHERE questionID = ".$row_rsQuestion['ID']." ORDER BY survey_answer.ordernum,  survey_answer.ID ASC";
$rsAnswers = mysql_query($query_rsAnswers, $aquiescedb) or die(mysql_error());
$row_rsAnswers = mysql_fetch_assoc($rsAnswers);
$totalRows_rsAnswers = mysql_num_rows($rsAnswers);

$varQuestionID_rsSections = "26";
if (isset($_GET['questionID'])) {
  $varQuestionID_rsSections = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT survey_section.ID, sectionnumber, `description` FROM survey_section, survey, survey_question WHERE survey_question.ID = %s AND survey_question.surveyID = survey.ID AND survey_section.surveyID  = survey.ID ORDER BY sectionnumber ASC", GetSQLValueString($varQuestionID_rsSections, "int"));
$rsSections = mysql_query($query_rsSections, $aquiescedb) or die(mysql_error());
$row_rsSections = mysql_fetch_assoc($rsSections);
$totalRows_rsSections = mysql_num_rows($rsSections);

$varQuestionID_rsExceptions = "-1";
if (isset($_GET['questionID'])) {
  $varQuestionID_rsExceptions = $_GET['questionID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsExceptions = sprintf("SELECT survey_question_exception.ID, survey_question_exception.answerID, survey_question_exception.isnot, survey_question_exception.equalto, survey_question_exception.setvalue, survey_answer.answertext, survey_question.question_number FROM survey_question_exception LEFT JOIN survey_answer ON (survey_question_exception.answerID = survey_answer.ID) LEFT JOIN survey_question ON (survey_answer.questionID = survey_question.ID) WHERE survey_question_exception.questionID = %s", GetSQLValueString($varQuestionID_rsExceptions, "int"));
$rsExceptions = mysql_query($query_rsExceptions, $aquiescedb) or die(mysql_error());
$row_rsExceptions = mysql_fetch_assoc($rsExceptions);
$totalRows_rsExceptions = mysql_num_rows($rsExceptions);

$varSurveyID_rsSurveyQuestions = "-1";
if (isset($thisSurveyID)) {
  $varSurveyID_rsSurveyQuestions = $thisSurveyID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyQuestions = sprintf("SELECT survey_question.ID, CONCAT(survey_question.question_number,  ':', LEFT(survey_question.questiontext, 50),'...') AS question FROM survey_question WHERE survey_question.surveyID = %s ORDER BY survey_question.question_number", GetSQLValueString($varSurveyID_rsSurveyQuestions, "int"));
$rsSurveyQuestions = mysql_query($query_rsSurveyQuestions, $aquiescedb) or die(mysql_error());
$row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions);
$totalRows_rsSurveyQuestions = mysql_num_rows($rsSurveyQuestions);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);
?>
<?php // if question just added and is a text question then go back to survey question list
if (isset($_GET['addquestion']) && $row_rsQuestion['questiontype'] == 3) { 
header ("location: ../update_survey.php?surveyID=".$row_rsQuestion['surveyID'].""); exit;
} ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Edit Question"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script src="../../../SpryAssets/SpryValidationTextField.js"></script>
<script src="../../../SpryAssets/SpryTabbedPanels.js"></script>
<script src="../../../SpryAssets/SpryValidationTextarea.js"></script>
<link href="../../../SpryAssets/SpryValidationTextarea.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<link href="../../../SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  />
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=survey_answer&"+order); 
            } 
        }); 
	
    }); 
</script>
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
   <h1><i class="glyphicon glyphicon-education"></i> Edit Question <small><?php echo $row_rsQuestion['surveyname']; ?></small></h1>
<?php require_once('../../../core/includes/alert.inc.php'); ?>
   
   
    
<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data" name="formquestion" id="formquestion">
   <div id="TabbedPanels1" class="TabbedPanels">
     <ul class="TabbedPanelsTabGroup">
       <li class="TabbedPanelsTab" tabindex="0">Question</li>
       <li class="TabbedPanelsTab" tabindex="0">Advanced</li>
     </ul>
     <div class="TabbedPanelsContentGroup">
       <div class="TabbedPanelsContent">
         
           <table class="form-table">
             <tr <?php if ($row_rsQuestion['usesections'] != 1) { ?> style="display:none" <?php } ?> class="form-group">
               <td class="text-nowrap text-right top">Section:</td>
               <td><select name="surveysectionID"  id="surveysectionID" class="form-control">
                   <option value="0" <?php if (!(strcmp(0, $row_rsQuestion['surveysectionID']))) {echo "selected=\"selected\"";} ?>>Choose section...</option>
                   <?php
do {  
?>
                   <option value="<?php echo $row_rsSections['ID']?>"<?php if (!(strcmp($row_rsSections['ID'], $row_rsQuestion['surveysectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsSections['description']?></option>
                   <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
                 </select>
                 <a href="../sections/index.php?surveyID=<?php echo $row_rsQuestion['surveyID']; ?>">Manage sections</a></td>
             </tr> <tr class="form-group form-inline">
               <td class="text-nowrap text-right top">Number:</td>
               <td><label>
                 <input name="question_number" type="text"  id="question_number" value="<?php echo $row_rsQuestion['question_number']; ?>" size="5" maxlength="5" class="form-control" />
                 (optional)</label></td>
             </tr> <tr class="form-group">
               <td class="text-nowrap text-right top">Question:</td>
               <td><span id="sprytextarea1">
                 <textarea name="questiontext" cols="50" rows="5" class="form-control"><?php echo $row_rsQuestion['questiontext']; ?></textarea>
                 <span class="textareaRequiredMsg"><br />
          A question is required.</span></span></td>
             </tr> <tr class="form-group">
               <td class="text-nowrap text-right">Answer:</td>
               <td><select name="questiontype" class="form-control" onchange="javascript:if(this.value !=3) { document.getElementById('multichoice').style.display = 'block'; } else { document.getElementById('multichoice').style.display = 'none';}">
                  
                   <option value="1" <?php if (!(strcmp(1, $row_rsQuestion['questiontype']))) {echo "selected=\"selected\"";} ?>>Multipe choice (Single answer)</option>
             <option value="2" <?php if (!(strcmp(2, $row_rsQuestion['questiontype']))) {echo "selected=\"selected\"";} ?>>Multiple choice (Multiple answer)</option> <option value="4" <?php if (!(strcmp(4, $row_rsQuestion['questiontype']))) {echo "selected=\"selected\"";} ?>>Typed (Single line answer)</option>
             <option value="3" <?php if (!(strcmp(3, $row_rsQuestion['questiontype']))) {echo "selected=\"selected\"";} ?>>Typed (Single multi-line answer)</option>
            
             <option value="0" <?php if (!(strcmp(0, $row_rsQuestion['questiontype']))) {echo "selected=\"selected\"";} ?>>Typed (Multiple answer)</option>
                 </select>
                   <label>
                   <input <?php if (!(strcmp($row_rsQuestion['active'],1))) {echo "checked=\"checked\"";} ?> name="active" type="checkbox" id="active" value="1" />
                Display</label></td>
                
                
             </tr>
           </table>
          
        
       </div>
       <div class="TabbedPanelsContent">
         <table border="0" cellpadding="2" cellspacing="2" class="form-table">
           <tr<?php if ($row_rsQuestion['usecomments'] != 1) { ?> style="display:none" <?php } ?> class="form-group">
             <td class="text-nowrap text-right top">Optional Notes:</td>
             <td><textarea name="questionnotes" id="questionnotes" cols="50" rows="3" class="form-control"><?php echo $row_rsQuestion['questionnotes']; ?></textarea></td>
           </tr>
           <tr <?php if ($row_rsQuestion['useweighting'] != 1) { ?> style="display:none;" <?php } ?> class="form-control form-inline" >
             <td class="text-nowrap text-right">Weight:</td>
             <td><input name="questionweight" type="text"  id="questionweight" value="<?php echo $row_rsQuestion['questionweight']; ?>" size="5" maxlength="5" />
                </td>
           </tr>
           
           
           <tr <?php if ($row_rsQuestion['usescoring'] != 1) { ?> style="display:none" <?php } ?> class="form-group form-inline">
             <td class="text-nowrap text-right">Pass score (optional):</td>
             <td><input name="passscore" type="text"  id="passscore" value="<?php echo $row_rsQuestion['passscore']; ?>" size="5" maxlength="5" class="form-control" />
                 <label>
                 <input <?php if (!(strcmp($row_rsQuestion['addscore'],1))) {echo "checked=\"checked\"";} ?> name="addscore" type="checkbox" id="addscore" value="1" />
                   Score this question</label></td>
           </tr>
           
           
           <tr>
             <td align="right">Show comments box:</td>
             <td><input <?php if (!(strcmp($row_rsQuestion['addcommentsbox'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="addcommentsbox" id="addcommentsbox" /></td>
           </tr>
           <tr>
             <td align="right">Answer compulsary:</td>
             <td><input <?php if (!(strcmp($row_rsQuestion['answerrequired'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="answerrequired" id="answerrequired" /> <?php if($row_rsQuestion['surveyanswerrequired']) { echo "ALL ANSWERS REQUIRED AT ".strtoupper($row_rsSurveyPrefs['surveyName'])." LEVEL"; } ?></td>
           </tr>
           <tr class="form-group form-inline">
             <td align="right">Maximum answers:</td>
             <td><input name="maxchoices" type="text"  id="maxchoices" value="<?php echo $row_rsQuestion['maxchoices']; ?>" size="5" maxlength="5" class="form-control"/>
               (Multiple choice, multiple answer only)</td>
           </tr> <tr>
             <td class="text-nowrap text-right top"><input name="imageURL" type="hidden" id="imageURL" value="<?php echo $row_rsQuestion['imageURL']; ?>" />
               Image:</td>
             <td><?php if (isset($row_rsQuestion['imageURL'])) { ?>
               <img src="<?php echo getImageURL($row_rsQuestion['imageURL'],"medium"); ?>" alt="Current image" /><br />
               <input name="noImage" type="checkbox" value="1" />
               Remove image
               <?php } else { ?>
               No image associated with this question.
               <?php } ?>
               <span class="upload"><br />
               Add/change image below:<br />
               <input name="filename" type="file" class="fileinput" id="filename" size="20" /></span>
              </td>
           </tr>
         </table>
         <h2>Exceptions</h2>
         <?php if ($totalRows_rsExceptions == 0) { // Show if recordset empty ?>
           <p>There are no exceptions.</p>
           <p>You can add an exception which means this question will only show depending on the response to a previous question in this survey.</p>
           <?php } // Show if recordset empty ?>
         <?php if ($totalRows_rsExceptions > 0) { // Show if recordset not empty ?>
  <p>Show this question ONLY if all the following exceptions are satisfied:</p>
  <table border="0" cellpadding="0" cellspacing="0" class="listTable">
              <tr>
                <th>Question</th>
                <th>Answer</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
              </tr>
              <?php do { ?>
                <tr>
                  <td><?php echo $row_rsExceptions['question_number']; ?></td>
                  <td><?php echo $row_rsExceptions['answertext']; ?></td>
                  <td><?php echo ($row_rsExceptions['isnot']==1) ? "!" : ""; ?></td>
                  <td><?php echo ($row_rsExceptions['equalto']==1) ? "=" : ""; ?></td>
                  <td><?php echo $row_rsExceptions['setvalue']; ?></td>
                  <td><a onclick="return confirm('Are you sure you want to delete this rule?');" href="edit_question.php?questionID=<?php echo intval($_GET['questionID']); ?>&amp;deleteExceptionID=<?php echo $row_rsExceptions['ID']; ?>&amp;defaultTab=1" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></td>
                </tr>
                <?php } while ($row_rsExceptions = mysql_fetch_assoc($rsExceptions)); ?>
           </table>
  <?php } // Show if recordset not empty ?>

          <p>
          Add an exception depending on the answer for question:</p>
          <p class="form-inline">
            <select name="surveyquestions"  id="surveyquestions" onchange="getData('exception/answerConstruct.php?questionID='+this.value+'&amp;thisQuestionID=<?php echo intval($_GET['questionID']); ?>','answerConstruct');" class="form-control">
              <option value="0">Choose question...</option>
              <?php
do {  
?>
              <option value="<?php echo $row_rsSurveyQuestions['ID']?>"><?php echo $row_rsSurveyQuestions['question']?></option>
              <?php
} while ($row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions));
  $rows = mysql_num_rows($rsSurveyQuestions);
  if($rows > 0) {
      mysql_data_seek($rsSurveyQuestions, 0);
	  $row_rsSurveyQuestions = mysql_fetch_assoc($rsSurveyQuestions);
  }
?>
            </select>
          </p>
          <div id="answerConstruct"></div>
          
       </div>
     </div>
   </div><br /> <p>
             <input name="modifiedbyID" type="hidden" id="modifiedbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
             <input type="hidden" name="modifieddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
             <input name="ID" type="hidden" id="ID" value="<?php echo $row_rsQuestion['ID']; ?>" />
             <input type="hidden" name="MM_update" value="formquestion" />
             <input name="surveyID" type="hidden" id="surveyID" value="<?php echo $row_rsQuestion['surveyID']; ?>" />
             
    </p> </form>
  
   <div id ="multichoice" <?php if ($row_rsQuestion['questiontype'] == 3) echo "style='display:none'";  ?>>
      
    <h2>Answer Choices</h2>
        
        <?php if ($totalRows_rsAnswers == 0) { // Show if recordset empty ?>
        <p>You have not entered any answers yet...</p> <?php } // S how if recordset empty ?>
        
        
      
        <?php if ($totalRows_rsAnswers > 0) { // Show if recordset not empty ?>
        <ul class="listTable sortable">

          <?php do { $ordernum = $row_rsAnswers['ordernum']; ?>
            <li  id="listItem_<?php echo $row_rsAnswers['ID']; ?>" ><span class= "handle" data-toggle="tooltip" data-placement="right" title="Drag and drop order of pages">&nbsp;</span>
              <span><?php if ($row_rsQuestion['questiontype']==1) { ?>
                <input type="radio" name="radio" id="MCSA" value="MCSA" />
                <?php } else if ($row_rsQuestion['questiontype']==2) { ?>
              <input type="checkbox" name="MCMA" id="MCMA" />
<?php } else { ?>&nbsp;<?php } ?></span>
              <span><?php echo $row_rsAnswers['answertext']; ?><?php echo isset($row_rsAnswers['answerscore']) ? "&nbsp;(".$row_rsAnswers['answerscore'].")" : ""; ?></span>
              <span><a href="edit_question.php?questionID=<?php echo $row_rsQuestion['ID']; ?>&amp;deleteID=<?php echo $row_rsAnswers['ID']; ?>" onclick="document.returnValue = confirm('Are you sure you want to delete this answer?'); return document.returnValue;" class="link_delete"><i class="glyphicon glyphicon-trash"></i> Delete</a></span>
              <span><a href="edit_answer.php?answerID=<?php echo $row_rsAnswers['ID']; ?>&amp;usescoring=<?php echo $row_rsQuestion['usescoring']; ?>&amp;questionID=<?php echo $row_rsQuestion['ID']; ?>" class="link_view">View</a></span>
           </li>
            <?php } while ($row_rsAnswers = mysql_fetch_assoc($rsAnswers)); ?>
      </ul>
      <p id="info" class="text-muted">Drag and drop to re-order</p>
<?php } // Show if recordset not empty ?></div>
<form action="<?php echo $editFormAction; ?>" method="post" name="formanswer" id="formanswer" class="form-inline">
<div class="form-group">
            <span id="sprytextfield1">
            <input name="answertext" type="text"  id="answertext" size="45" maxlength="500" placeholder="Add your answer text here..." class="form-control" />
            
            <span class="textfieldRequiredMsg">Required.</span></span>
            <label <?php if ($row_rsQuestion['usescoring'] != 1) { ?> style="display:none;" <?php } ?>>Score:
            <input name="scoreanswer" type="text"  id="scoreanswer" size="5" maxlength="5" value="0" class="form-control" />
            <input name="ordernum" type="hidden"  value = "<?php echo intval($ordernum)+1; ?>"; />
            </label>
<button name="submitanswer" type="submit" class="btn btn-default btn-secondary" id="submitanswer" ><i class="glyphicon glyphicon-plus"></i> Add Answer</button>
     <input name="questionID" type="hidden" id="questionID" value="<?php echo $row_rsQuestion['ID']; ?>" />
     <input type="hidden" name="MM_insert" value="formanswer" /></div>
   </form>
   <button type="button" class="btn btn-primary" onClick="if(document.formanswer.answertext.value !='') { if (confirm('You have not added the last answer. Continue?')) {document.formquestion.submit(); } else { return false; }} else {document.formquestion.submit(); }">Save Changes</button>
        <script>
		<!--
var sprytextarea1 = new Spry.Widget.ValidationTextarea("sprytextarea1");
//-->
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none");
//-->
        </script><?php if (isset($_GET['defaultTab'])) { echo '<script>
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
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsQuestion);

mysql_free_result($rsAnswers);

mysql_free_result($rsSections);

mysql_free_result($rsExceptions);

mysql_free_result($rsSurveyQuestions);

mysql_free_result($rsSurveyPrefs);
?>
