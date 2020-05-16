<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php require_once('../../core/includes/framework.inc.php'); ?><?php 
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



if(!function_exists("deleteResponse")) {
function deleteResponse($responseID) {
	global $database_aquiescedb, $aquiescedb;
	$delete = "DELETE `formresponse`, `formfieldresponse` FROM formresponse LEFT JOIN formfieldresponse ON (formfieldresponse.formresponseID = formresponse.ID) WHERE formresponse.ID = ".intval($responseID);
	mysql_query($delete, $aquiescedb) or die(mysql_error());
}
}

if(!function_exists("getFormRow")) {

function getFormRow($formID=0, $responseID=0, $csv=false) {
	global $database_aquiescedb, $aquiescedb;
		$output="";
		
		
		mysql_select_db($database_aquiescedb, $aquiescedb);
		$select = "SELECT formfield.*, formfieldresponse.formfieldtextanswer FROM formfield LEFT JOIN formfieldresponse ON (formfieldresponse.formfieldID = formfield.ID AND formfieldresponse.formresponseID = ".GetSQLValueString($responseID, "int").") WHERE formID = ".GetSQLValueString($formID, "int");
		$select .= $csv ? "" : " AND showinlistview = 1 ";
		 $select .=" GROUP BY formfield.ID  ORDER BY ordernum ASC";
				
		$rsFormItems = mysql_query($select, $aquiescedb) or die(mysql_error().": ".$select);
		if(mysql_num_rows($rsFormItems)>0) { 
		
			
			
			while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems)) {            
            	if($row_rsFormItems['formfieldtype']>0) { 
					 $output .= $csv  ? "" : "<td>";
			  		if($row_rsFormItems['formfieldtype']==3) { //checkboxes
					  $select = "SELECT formfieldchoice.formfieldchoicename
						FROM formfieldchoice LEFT JOIN formfieldresponse ON (formfieldchoice.ID = formfieldresponse.formfieldchoiceID) WHERE formfieldresponse.formresponseID = ".$responseID."  AND formfieldresponse.formfieldID = ".$row_rsFormItems['ID']." ORDER BY formfieldchoice.ordernum";
						
						
					  
					  $result = mysql_query($select, $aquiescedb) or die(mysql_error());
					  $choices = mysql_num_rows($result);
					  $choicetext = "";
					  if($choices>0) { 						 
						  while($choice = mysql_fetch_assoc($result)) { 							
							  $choicetext .= $choice['formfieldchoicename']."; ";
						  } // end while
					   } // end is choices
					   $output .=  $csv  ? formatCSV($choicetext) : $choicetext;
				  } else  { // all other as text
					  $output .= $csv  ? formatCSV($row_rsFormItems['formfieldtextanswer']) : $row_rsFormItems['formfieldtextanswer'];
				  } // end all other 
				  $output .= $csv  ? "," : "</td>";
				} 	// IS SET FIELD			
		 	} // END WHILE
			
		 }  // is form fields
		 return $output;
}
}


if(isset($_GET['deleteResponseID']) && intval($_GET['deleteResponseID'])>0) {
	deleteResponse($_GET['deleteResponseID']);
	$msg = "1 response deleted.";
	header("location: responses.php?formID=".intval($_GET['formID'])."&msg=".urlencode($msg )); exit;
}

$currentPage = $_SERVER["PHP_SELF"];


mysql_select_db($database_aquiescedb, $aquiescedb);
	
	
	
$rows = isset($_GET['csv']) ? 10000 : 100;

$where = "";
if(isset($_GET['search']) && trim($_GET['search'])!="") {
	$join = " LEFT JOIN formfieldresponse ON (formresponse.ID = formfieldresponse .formresponseID) ";
	$where .= " AND formfieldresponse.formfieldtextanswer LIKE ".GetSQLValueString("%%".$_GET['search']."%%", "text")." ";
	
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

$maxRows_rsResponses = $rows;
$pageNum_rsResponses = 0;
if (isset($_GET['pageNum_rsResponses'])) {
  $pageNum_rsResponses = $_GET['pageNum_rsResponses'];
}
$startRow_rsResponses = $pageNum_rsResponses * $maxRows_rsResponses;

$varFormID_rsResponses = "-1";
if (isset($_GET['formID'])) {
  $varFormID_rsResponses = $_GET['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsResponses = sprintf("SELECT formresponse.ID, formresponse.createddatetime, users.firstname, users.surname, users.email, formresponse.formID FROM formresponse LEFT JOIN users ON (formresponse.createdbyID = users.ID) ".$join." WHERE formresponse.statusID = 1 AND formresponse.formID = %s ".$where." GROUP BY formresponse.ID  ORDER BY formresponse.createddatetime DESC", GetSQLValueString($varFormID_rsResponses, "int"));
$query_limit_rsResponses = sprintf("%s LIMIT %d, %d", $query_rsResponses, $startRow_rsResponses, $maxRows_rsResponses);
$rsResponses = mysql_query($query_limit_rsResponses, $aquiescedb) or die(mysql_error().": ".$query_limit_rsResponses);
$row_rsResponses = mysql_fetch_assoc($rsResponses);

if (isset($_GET['totalRows_rsResponses'])) {
  $totalRows_rsResponses = $_GET['totalRows_rsResponses'];
} else {
  $all_rsResponses = mysql_query($query_rsResponses);
  $totalRows_rsResponses = mysql_num_rows($all_rsResponses);
}
$totalPages_rsResponses = ceil($totalRows_rsResponses/$maxRows_rsResponses)-1;

$colname_rsThisForm = "-1";
if (isset($_GET['formID'])) {
  $colname_rsThisForm = $_GET['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisForm = sprintf("SELECT formname FROM `form` WHERE ID = %s", GetSQLValueString($colname_rsThisForm, "int"));
$rsThisForm = mysql_query($query_rsThisForm, $aquiescedb) or die(mysql_error());
$row_rsThisForm = mysql_fetch_assoc($rsThisForm);
$totalRows_rsThisForm = mysql_num_rows($rsThisForm);

$queryString_rsResponses = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsResponses") == false && 
        stristr($param, "totalRows_rsResponses") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsResponses = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsResponses = sprintf("&totalRows_rsResponses=%d%s", $totalRows_rsResponses, $queryString_rsResponses);

if(isset($_GET['csv']) && $totalRows_rsResponses>0) {
	
	csvHeaders("Form Responses");
	
	
	
	if($totalRows_rsFormItems>0) { 
	$query_rsFormItems = "SELECT formfield.formfieldname  FROM formfield  WHERE formID = ".intval($_GET['formID'])."  ORDER BY ordernum ASC";
	$rsFormItems = mysql_query($query_rsFormItems, $aquiescedb) or die(mysql_error());
	$row_rsFormItems = mysql_fetch_assoc($rsFormItems);
	$totalRows_rsFormItems = mysql_num_rows($rsFormItems);
		print formatCSV("DATE/TIME").","; 
		do {
			print formatCSV(strtoupper($row_rsFormItems['formfieldname'])).","; 
		} while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems));
		
		print "\n"; //end row;
	}
	do { // $$row_rsResponses
		print formatCSV(date('d M Y H:i', strtotime($row_rsResponses['createddatetime']))).",";
		print getFormRow($_GET['formID'], $row_rsResponses['ID'], true);	
		print "\n" ; //end row;	
	} while ($row_rsResponses = mysql_fetch_assoc($rsResponses));
	
	die();
}


?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Form Responses: ".$row_rsThisForm['formname']; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<style>
<!--
-->
</style>
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
      <h1><i class="glyphicon glyphicon-th-list"></i> Form Responses: <?php echo $row_rsThisForm['formname']; ?></h1><?php require_once('../../core/includes/alert.inc.php'); ?>
      <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
        <li class="nav-item"><a href="index.php?formID=<?php echo intval($_GET['formID']); ?>"  class="nav-link"><i class="glyphicon glyphicon-arrow-left"></i> Back to Forms</a></li>
        
        
        <li class="nav-item"><a href="update_form.php?formID=<?php echo intval($_GET['formID']); ?>" ><i class="glyphicon glyphicon-pencil"></i> Edit this form</a></li>
        
        
        </ul></div></nav><form action="responses.php" method="get"><fieldset class="form-inline"><input name="search" type="text" placeholder="Search forms..." value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control"> <button class="btn btn-default btn-secondary"  type="submit">Search</button>
            <input type="hidden" name="formID" value="<?php echo intval($_GET['formID']); ?>">
        </fieldset></form>
      <?php if ($totalRows_rsResponses == 0) { // Show if recordset empty ?>
  <p>There are no responses to this form so far. <?php echo $query_rsResponses; ?></p>
  <?php } // Show if recordset empty ?>
      <?php if ($totalRows_rsResponses > 0) { // Show if recordset not empty ?>
        <p class="text-muted">Responses <?php echo ($startRow_rsResponses + 1) ?> to <?php echo min($startRow_rsResponses + $maxRows_rsResponses, $totalRows_rsResponses) ?> of <?php echo $totalRows_rsResponses ?>. <a href="responses.php?formID=<?php echo $_GET['formID']; ?>&csv=true;" class="link_csv icon_with_text" >Export all form data as spreadsheet.</a></p>
        <table class="table table-hover">
        <thead>
          <tr>          
            <th>Submitted</th>
            <?php $query_rsListFormItems = "SELECT formfield.formfieldname  FROM formfield  WHERE formID = ".intval($_GET['formID'])." AND showinlistview = 1 ORDER BY ordernum ASC";
	$rsListFormItems = mysql_query($query_rsListFormItems, $aquiescedb) or die(mysql_error());
	
	if(mysql_num_rows($rsListFormItems)>0) {
	while($row_rsListFormItems = mysql_fetch_assoc($rsListFormItems)) {
	echo "<th>".$row_rsListFormItems['formfieldname']."</th>";  }} ?>
            <th>Logged in as</th>
            <th>Actions</th>
          </tr></thead><tbody>
          <?php do { ?>
            <tr>
            
              <td><?php echo date('d M Y H:i', strtotime($row_rsResponses['createddatetime'])); ?></td>
              <?php echo getFormRow($row_rsResponses['formID'], $row_rsResponses['ID']); ?>
              <td><?php echo isset($row_rsResponses['surname']) ? $row_rsResponses['firstname']." ".$row_rsResponses['surname'] : "<em>N/A</em>"; ?></td>
             <td><a href="response.php?formID=<?php echo $row_rsResponses['formID']; ?>&responseID=<?php echo $row_rsResponses['ID']; ?>" class="btn btn-sm btn-default btn-secondary" ><i class="glyphicon glyphicon-search"></i> View</a></td>
            </tr>
            <?php } while ($row_rsResponses = mysql_fetch_assoc($rsResponses)); ?></tbody>
        </table>
        <?php } // Show if recordset not empty ?>
<?php echo createPagination($pageNum_rsResponses,$totalPages_rsResponses,"rsResponses");?>  
    </div>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsLoggedIn);

mysql_free_result($rsResponses);

mysql_free_result($rsThisForm);
?>
