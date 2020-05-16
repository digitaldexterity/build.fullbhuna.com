<?php require_once('../../../Connections/aquiescedb.php'); ?>
<?php require_once('../../../core/includes/adminAccess.inc.php'); ?>

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
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO survey_section (surveyID, sectionnumber, weight, `description`, subsectionofID) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['surveyID'], "int"),
                       GetSQLValueString($_POST['sectionnumber'], "text"),
                       GetSQLValueString($_POST['weight'], "int"),
                       GetSQLValueString($_POST['description'], "text"),
                       GetSQLValueString($_POST['subsectionofID'], "int"));

  mysql_select_db($database_aquiescedb, $aquiescedb);
  $Result1 = mysql_query($insertSQL, $aquiescedb) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_rsThisSurvey = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsThisSurvey = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisSurvey = sprintf("SELECT surveyname FROM survey WHERE ID = %s", GetSQLValueString($colname_rsThisSurvey, "int"));
$rsThisSurvey = mysql_query($query_rsThisSurvey, $aquiescedb) or die(mysql_error());
$row_rsThisSurvey = mysql_fetch_assoc($rsThisSurvey);
$totalRows_rsThisSurvey = mysql_num_rows($rsThisSurvey);

$colname_rsSections = "-1";
if (isset($_GET['surveyID'])) {
  $colname_rsSections = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsSections = sprintf("SELECT survey_section.sectionnumber, survey_section.`description`, survey_section.ID, duplicate.`description` AS subsection, survey_section.weight FROM survey_section LEFT JOIN survey_section AS duplicate ON (survey_section.subsectionofID = duplicate.ID) WHERE survey_section.surveyID = %s ORDER BY survey_section.sectionnumber ASC", GetSQLValueString($colname_rsSections, "int"));
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
<!-- InstanceBeginEditable name="doctitle" --><title><?php $pageTitle = "Survey Sections"; echo $site_name." ".$admin_name." - ".$pageTitle; ?></title><!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
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
   <h1><i class="glyphicon glyphicon-education"></i> Survey Sections</h1>
   <p>for:  <a href="../update_survey.php?surveyID=<?php echo intval($_GET['surveyID']); ?>"><?php echo $row_rsThisSurvey['surveyname']; ?></a></p>
   <p>Add a section:</p>

   
      <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-inline">
        <table class="form-table"> <tr>
         <td colspan="2" class="text-nowrap">Section No.
           <input name="sectionnumber" type="text"  value="" size="5" maxlength="5" class="form-control" />
          Name:
          <input name="description" type="text"  value="" size="40" maxlength="50" class="form-control"/></td>
         </tr> <tr>
         <td colspan="2"class="text-nowrap ">Weight:
           <input name="weight" type="text"  id="weight" value="" size="5" maxlength="5"class="form-control" /> 
           Sub-section of:
           <select name="subsectionofID" class="form-control">
             <option value="0">None</option>
             <?php
do {  
?><option value="<?php echo $row_rsSections['ID']?>"><?php echo $row_rsSections['description']?></option>
             <?php
} while ($row_rsSections = mysql_fetch_assoc($rsSections));
  $rows = mysql_num_rows($rsSections);
  if($rows > 0) {
      mysql_data_seek($rsSections, 0);
	  $row_rsSections = mysql_fetch_assoc($rsSections);
  }
?>
            </select>
           <button type="submit" class="btn btn-primary" >Add Section</button></td>
         </tr>
     </table>
     <input type="hidden" name="surveyID" value="<?php echo intval($_GET['surveyID']); ?>" />
     <input type="hidden" name="MM_insert" value="form1" />
   </form>
   
      <?php if ($totalRows_rsSections == 0) { // Show if recordset empty ?>
        <p>There are no sections added so far.</p>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsSections > 0) { // Show if recordset not empty ?>
  <table  class="table table-hover">
  <thead>
    <tr>
      <th>No.</th>
          <th>Description</th>
          <th><em>Sub-section of</em></th>
          <th>Weight</th>
          <th>&nbsp;</th>
      </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td><?php echo $row_rsSections['sectionnumber']; ?></td>
        <td><?php echo $row_rsSections['description']; ?></td>
        <td><em><?php echo $row_rsSections['subsection']; ?></em></td>
        <td><?php echo $row_rsSections['weight']; ?></td>
        <td><a href="update.php?sectionID=<?php echo $row_rsSections['ID']; ?>&amp;surveyID=<?php echo intval($_GET['surveyID']); ?>" class="link_view">View</a></td>
      </tr>
      <?php } while ($row_rsSections = mysql_fetch_assoc($rsSections)); ?></tbody>
  </table>
  <?php } // Show if recordset not empty ?>
</div>
   <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsThisSurvey);

mysql_free_result($rsSections);

mysql_free_result($rsSurveyPrefs);
?>

