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


$currentPage = $_SERVER["PHP_SELF"];

$maxRows_rsSurveys = 100;
$pageNum_rsSurveys = 0;
if (isset($_GET['pageNum_rsSurveys'])) {
  $pageNum_rsSurveys = $_GET['pageNum_rsSurveys'];
}
$startRow_rsSurveys = $pageNum_rsSurveys * $maxRows_rsSurveys;

$varShowAll_rsSurveys = "0";
if (isset($_GET['showall'])) {
  $varShowAll_rsSurveys = $_GET['showall'];
}
$varSearch_rsSurveys = "%";
if (isset($_GET['search'])) {
  $varSearch_rsSurveys = trim($_GET['search']);
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveys = sprintf("SELECT survey.ID, survey.surveyname, survey.startdatetime, survey.enddatetime, survey.statusID, usertype.name AS accesslevel, usertype.ID AS usertypeID, COUNT(survey_question.ID) AS questions FROM survey LEFT JOIN usertype ON survey.accesslevel = usertype.ID LEFT JOIN survey_question ON (survey.ID = survey_question.surveyID) WHERE (%s = 1 OR (statusID <=1 AND (startdatetime IS NULL OR startdatetime <= NOW()) AND (enddatetime IS NULL OR enddatetime >= NOW() ))) AND surveyname LIKE %s GROUP BY survey.ID ORDER BY ordernum", GetSQLValueString($varShowAll_rsSurveys, "int"),GetSQLValueString($varSearch_rsSurveys . "%", "text"));
$query_limit_rsSurveys = sprintf("%s LIMIT %d, %d", $query_rsSurveys, $startRow_rsSurveys, $maxRows_rsSurveys);
$rsSurveys = mysql_query($query_limit_rsSurveys, $aquiescedb) or die(mysql_error());
$row_rsSurveys = mysql_fetch_assoc($rsSurveys);

if (isset($_GET['totalRows_rsSurveys'])) {
  $totalRows_rsSurveys = $_GET['totalRows_rsSurveys'];
} else {
  $all_rsSurveys = mysql_query($query_rsSurveys);
  $totalRows_rsSurveys = mysql_num_rows($all_rsSurveys);
}
$totalPages_rsSurveys = ceil($totalRows_rsSurveys/$maxRows_rsSurveys)-1;

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSurveyPrefs = "SELECT * FROM surveyprefs";
$rsSurveyPrefs = mysql_query($query_rsSurveyPrefs, $aquiescedb) or die(mysql_error());
$row_rsSurveyPrefs = mysql_fetch_assoc($rsSurveyPrefs);
$totalRows_rsSurveyPrefs = mysql_num_rows($rsSurveyPrefs);

$queryString_rsSurveys = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsSurveys") == false && 
        stristr($param, "totalRows_rsSurveys") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsSurveys = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsSurveys = sprintf("&totalRows_rsSurveys=%d%s", $totalRows_rsSurveys, $queryString_rsSurveys);
?><!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Manage ".ucwords($row_rsSurveyPrefs['surveyName'])."s"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<script> 
    // When the document is ready set up our sortable with it's inherant function(s) 
    $(document).ready(function() { 
	
        $(".sortable").sortable({ 
            handle : '.handle', 
            update : function () { 
            var order = $(this).sortable('serialize'); 
                $("#info").load("/core/ajax/sort.ajax.php?table=survey&"+order); 
            } 
        }); 
	
    }); 
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
    <!-- InstanceBeginEditable name="Body" --><div class="page surveys">
   <h1><i class="glyphicon glyphicon-education"></i> Manage <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?>s
   </h1>
   
   <?php if ($totalRows_rsSurveys == 0) { // Show if recordset empty ?>
     <p>There are currently no surveys entered.</p>
     <?php } // Show if recordset empty ?>
     <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
       <li><a href="add_survey.php" ><i class="glyphicon glyphicon-plus-sign"></i> Add <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?></a></li>
       <li><a href="options/index.php" class="link_manage"><i class="glyphicon glyphicon-cog"></i> <?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?> Options</a></li>
    </ul></div></nav>
     <form  method="get" name="form1" id="form1">
       <fieldset class="form-group form-inline">
         <legend>Search <?php echo $row_rsSurveyPrefs['surveyName']; ?>s</legend>
         <input name="search" type="text" id="search" value="<?php echo isset($_GET['search']) ? htmlentities(trim($_GET['search'])) : ""; ?>" size="20" maxlength="50" class="form-control"/>
         <button type="submit" name="go" id="go" class="btn btn-default btn-secondary">Go</button>
         <label>
           <input <?php if (!(strcmp(@$_GET['showall'],1))) {echo "checked=\"checked\"";} ?> name="showall" type="checkbox" id="showall" value="1" />
         Show archived <?php echo $row_rsSurveyPrefs['surveyName']; ?>s</label>
       </fieldset>
     </form>
     <?php if ($totalRows_rsSurveys > 0) { // Show if recordset not empty ?>
  <p class="text-muted"><?php echo ucwords($row_rsSurveyPrefs['surveyName']); ?> <?php echo ($startRow_rsSurveys + 1) ?> to <?php echo min($startRow_rsSurveys + $maxRows_rsSurveys, $totalRows_rsSurveys) ?> of <?php echo $totalRows_rsSurveys ?>. <span id="info">Drag and drop to re-order.</span></p>
  <table class="table table-hover">

  <thead>     
            <tr>
              <th>&nbsp;</th> <th>&nbsp;</th>
              <th>Survey</th>
               <th>Qs</th>
             
              <th colspan="4">Actions</th> <th colspan="2">Results</th>
            </tr> </thead><tbody class="sortable">
            <?php do { ?>
          <tr id="listItem_<?php echo $row_rsSurveys['ID']; ?>">
        <td class="handle" >&nbsp;</td> <td class="top"><?php //
		if ($row_rsSurveys['statusID']==0 || ($row_rsSurveys['statusID']==1 && $row_rsSurveys['startdatetime'] > date('Y-m-d H:i:s'))) { ?><img src="../../core/images/icons/amber-light.png" alt="Survey pending" width="16" height="16" /><?php } else if ($row_rsSurveys['statusID']==1 && (!isset($row_rsSurveys['startdatetime']) || $row_rsSurveys['startdatetime'] <= date('Y-m-d H:i:s')) && (!isset($row_rsSurveys['enddatetime']) || $row_rsSurveys['enddatetime'] > date('Y-m-d H:i:s'))) { ?><img src="../../core/images/icons/green-light.png" alt="Survey Active" width="16" height="16" /><?php } else { ?><img src="../../core/images/icons/red-light.png" alt="Survey inactive" width="16" height="16" /><?php } ?></td>
        <td class="top"><a href="update_survey.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" ><?php echo $row_rsSurveys['surveyname']; ?></a><br />
          <em class="text-muted">For <?php echo ($row_rsSurveys['usertypeID'] < 1) ? "Everyone" : $row_rsSurveys['accesslevel']."s"; ?><?php echo isset($row_rsSurveys['startdatetime']) ?  " from ".date('d M y H:i',strtotime($row_rsSurveys['startdatetime'])) : ""; ?>
          <?php if (isset( $row_rsSurveys['enddatetime']))  { ?> 
          until <?php echo date('d M y H:i',strtotime($row_rsSurveys['enddatetime'])); } ?></em></td>
        
        <td class="top"><a href="update_survey.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>"  title="Edit this survey">(<?php echo $row_rsSurveys['questions']; ?>)</a></td>
      
      
      
        <td class="top"><a href="update_survey.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" class="link_edit icon_only" title="Edit this survey">Edit</a></td>
        <td class="top"><a href="download.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" class="link_csv" title="Download as spreadsheet">Download</a></td>
        <td class="top"><a href="duplicate.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" class="link_add icon_only" onclick="return confirm('Are you sure you want to duplicate this survey?\n\nA new survey will be added with identical questions to this one, which can then be updated as required.');" title="Duplicate this survey">Duplicate</a></td>
        <td class="top"><a href="/surveys/survey.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>" title="View this survey" target="_blank" class="link_view" rel="noopener" onclick="openMainWindow(this.href); return false;">View</a></td>  <td class="top"><a href="results/index.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>">By User</a></td>
        <td class="top"><a href="results/results.php?surveyID=<?php echo $row_rsSurveys['ID']; ?>">By Question</a></td>
          </tr>
      
      <?php } while ($row_rsSurveys = mysql_fetch_assoc($rsSurveys)); ?></tbody>
    </table>
  <?php } // Show if recordset not empty ?>

  
<table class="form-table">
     <tr>
       <td><?php if ($pageNum_rsSurveys > 0) { // Show if not first page ?>
           <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, 0, $queryString_rsSurveys); ?>">First</a>
           <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsSurveys > 0) { // Show if not first page ?>
           <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, max(0, $pageNum_rsSurveys - 1), $queryString_rsSurveys); ?>" rel="prev">Previous</a>
           <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsSurveys < $totalPages_rsSurveys) { // Show if not last page ?>
           <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, min($totalPages_rsSurveys, $pageNum_rsSurveys + 1), $queryString_rsSurveys); ?>" rel="next">Next</a>
           <?php } // Show if not last page ?>       </td>
       <td><?php if ($pageNum_rsSurveys < $totalPages_rsSurveys) { // Show if not last page ?>
           <a href="<?php printf("%s?pageNum_rsSurveys=%d%s", $currentPage, $totalPages_rsSurveys, $queryString_rsSurveys); ?>">Last</a>
           <?php } // Show if not last page ?>       </td>
     </tr>
   </table>
   </div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsSurveys);

mysql_free_result($rsSurveyPrefs);
?>
