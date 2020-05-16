<?php require_once('../Connections/aquiescedb.php'); ?><?php require_once('includes/documentfunctions.inc.php'); ?>
<?php if(isset($_GET['categoryID']) && strlen($_GET['categoryID']) != strlen(intval($_GET['categoryID']))) {
	//basic injection security
	header('HTTP/1.0 403 Forbidden');
	die();
}
 ?>
<?php require_once('../members/includes/userfunctions.inc.php'); ?>
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

$regionID = (isset($regionID) && intval($regionID)>0) ? intval($regionID) : 1;

getHomeFolder($regionID); // will create one if doesn't exist

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.usertypeID FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);



$varCategoryID_rsThisCategory = "0";
if (isset($_GET['categoryID'])) {
  $varCategoryID_rsThisCategory = $_GET['categoryID'];
}
$varRegionID_rsThisCategory = "1";
if (isset($regionID)) {
  $varRegionID_rsThisCategory = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsThisCategory = sprintf("SELECT documentcategory.*, usertype.name, usergroup.groupname, parent.categoryname AS parentname, parent.subcatofID AS parentsubcatofID FROM documentcategory LEFT JOIN usertype ON (documentcategory.accessID = usertype.ID) LEFT JOIN usergroup ON (documentcategory.groupreadID = usergroup.ID) LEFT JOIN documentcategory AS parent ON (documentcategory.subcatofID = parent.ID) WHERE documentcategory.active = 1 AND (documentcategory.regionID = 0 OR documentcategory.regionID = %s) AND (documentcategory.ID = %s OR (%s = 0 AND documentcategory.subcatofID IS NULL))", GetSQLValueString($varRegionID_rsThisCategory, "int"),GetSQLValueString($varCategoryID_rsThisCategory, "int"),GetSQLValueString($varCategoryID_rsThisCategory, "int"));
$rsThisCategory = mysql_query($query_rsThisCategory, $aquiescedb) or die(mysql_error());
$row_rsThisCategory = mysql_fetch_assoc($rsThisCategory);
$totalRows_rsThisCategory = mysql_num_rows($rsThisCategory);


if($varCategoryID_rsThisCategory==0 && $totalRows_rsThisCategory<1) {
	$insert = "INSERT INTO `documentcategory` (categoryname, subcatofID, accessID, regionID, addedbyID, createddatetime) VALUES ('Home',  NULL,  0, ".$regionID.",".$row_rsLoggedIn['ID'].",NOW())";
	$result = mysql_query($insert, $aquiescedb) or die(mysql_error());
	header("location: index.php");  exit;
}


$isSearch = (isset($_REQUEST['search']) && trim($_REQUEST['search']) !="") ? true : false;



mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT userscanlogin FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
?>
<?php // delete stuff...
if(isset($_GET['deleteDocumentID'])) { // delete doc
$select = "SELECT userID FROM documents WHERE ID = ".GetSQLValueString($_GET['deleteDocumentID'], "int");
mysql_select_db($database_aquiescedb, $aquiescedb);
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);
if($row_rsLoggedIn['usertypeID']>=9 || $row['userID'] == 		$row_rsLoggedIn['ID']) { // authorised to delete
 deleteDocument($_GET['deleteDocumentID']);
 
 
header("location: index.php?categoryID=".intval($_GET['categoryID'])); exit;
} // end authorised to delete
else { // not authorised
$alert = "You cannot delete the document. You need to be a manager or the creator of the document to delete it.";
} // end not authorised
} // end delete doc

if(isset($_GET['deleteFolderID'])) { // delete folder
if($_GET['deleteFolderID'] < 1) { // folder not allowed
$alert = "This folder cannot be deleted.";
} else { // allowed folder
$select = "SELECT documentcategory.ID, documentcategory.addedbyID, subcategory.ID AS subcat, documents.ID AS document FROM documentcategory LEFT JOIN documentcategory AS subcategory ON (subcategory.subcatofID = documentcategory.ID) LEFT JOIN documents ON (documents.documentcategoryID = documentcategory.ID) WHERE documentcategory.ID = ".GetSQLValueString($_GET['deleteFolderID'], "int");
mysql_select_db($database_aquiescedb, $aquiescedb);
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$row = mysql_fetch_assoc($result);
if($row_rsLoggedIn['usertypeID']>=9 || $row['addedbyID'] == $row_rsLoggedIn['ID']) { // authorised to delete
if(isset($row['subcat']) || isset($row['document'])) { // not empty
										 $alert = "You cannot delete this folder as it is not empty. Please delete all contents first.";
										 } // end not empty
										 else { // is empty
$delete = "DELETE FROM documentcategory WHERE ID = ".GetSQLValueString($_GET['deleteFolderID'], "int");
$result = mysql_query($delete, $aquiescedb) or die(mysql_error());
header("location: index.php?categoryID=".intval($_GET['categoryID'])); exit;
										 } // end is empty
} // end authorised to delete
else { // not authorised
$alert = "You cannot delete the folder. You need to be the creator of the folder to delete it.";
} // end not authorised
} // end allowed folder
} // end delete folder

if(isset($_GET['deleteFlipbookID'])) { // delete flipbook
	$select = "SELECT flipbook.createdbyID FROM flipbook  WHERE flipbook.ID = ".GetSQLValueString($_GET['deleteFlipbookID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row_rsLoggedIn['usertypeID']>=9 || $row['createdbyID'] == $row_rsLoggedIn['ID']) { // authorised to delete
		
		$delete = "DELETE FROM flipbook WHERE ID = ".GetSQLValueString($_GET['deleteShortcutID'], "int");
		mysql_query($delete, $aquiescedb) or die(mysql_error());
		header("location: index.php?categoryID=".intval($_GET['categoryID'])); exit;
												
	} // end authorised to delete
	else { // not authorised
		$alert = "You cannot delete this flipbook. You need to be the creator of the flipbook or an administrator to delete it.";
	} // end not authorised
}


if(isset($_GET['deleteShortcutID'])) { // delete folder

	$select = "SELECT documentshortcut.createdbyID FROM documentshortcut  WHERE documentshortcut.ID = ".GetSQLValueString($_GET['deleteShortcutID'], "int");
	mysql_select_db($database_aquiescedb, $aquiescedb);
	$result = mysql_query($select, $aquiescedb) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	if($row_rsLoggedIn['usertypeID']>=9 || $row['createdbyID'] == $row_rsLoggedIn['ID']) { // authorised to delete
		
		$delete = "DELETE FROM documentshortcut WHERE ID = ".GetSQLValueString($_GET['deleteShortcutID'], "int");
		mysql_query($delete, $aquiescedb) or die(mysql_error());
		header("location: index.php?categoryID=".intval($_GET['categoryID'])); exit;
												
	} // end authorised to delete
	else { // not authorised
		$alert = "You cannot delete this shortcut. You need to be the creator of the shortcut or an administrator to delete it.";
	} // end not authorised
} // end delete folder


$canView = thisUserHasAccess($row_rsThisCategory['accessID'], $row_rsThisCategory['groupreadID'],$row_rsLoggedIn['ID']);

$canEdit = thisUserHasAccess($row_rsThisCategory['writeaccess'], $row_rsThisCategory['groupwriteID'],$row_rsLoggedIn['ID']);

$canonicalURL = htmlentities($_SERVER["REQUEST_URI"], ENT_COMPAT, "UTF-8");
?>
<?php require_once(SITE_ROOT."core/includes/generate_tokens.inc.php"); ?><!doctype html>
<html lang="en" class="full_bhuna standard <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Standard.dwt.php" codeOutsideHTMLIsLocked="false" -->
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Documents - "; $pageTitle .= isset($row_rsThisCategory['categoryname']) ? $row_rsThisCategory['categoryname'] : "Home"; echo $pageTitle." | ".$site_name;?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../local/includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js" integrity="sha384-YWP9O4NjmcGo4oEJFXvvYSEzuHIvey+LbXkBNJ1Kd0yfugEZN9NCQNpRYBVC1RvA" crossorigin="anonymous"></script>
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
<script>!(jQuery.ui) && document.write('<script src="/3rdparty/jquery/jquery-ui-1.10.1.custom.min.js"><\/script>')</script>
<script> 
$(document).ready(function() {  
$(".docLink").hide();

<?php 
$clickevent = "onClick";
if(!$isSearch) { // ony allow draggable if not in search
if($row_rsDocumentPrefs['defaultview']==2 ) {
	$clickevent = "onClick=\"return false;\" ondblclick"; /* change on click to double click to work with new interface */
	 ?>
   
        $(".draggable" ).draggable({
            start: function() {
                // do nothing
            },
            drag: function() {
                // do nothing
            },
            stop: function(event, ui) {
               pos = $(this).position();
               updateCoords($(this).attr('class'), pos.left, pos.top);
			 
			}
            });
			
		
    });
	
	function updateCoords(theclass,left,top) {
	

	$("#info").load("/documents/ajax/drag_update.ajax.php?table="+getType(theclass)+"&id="+getID(theclass)+"&left="+left+"&top="+top); 
	}
	
	
<?php } else {  ?>

    // When the document is ready set up our sortable with it's inherant function(s)  
	// the id of the ul provides the DB table to sort
   
        jQuery(".sortable").sortable({ 
            update : function () { 
            var order = jQuery(this).sortable('serialize'); 
                jQuery("#info").load("/core/ajax/sort.ajax.php?table="+jQuery(this).attr('id')+"&"+order); 
            } 
        }); 
		
		
	
  

<?php } // end list view ?>

$( ".droppable" ).droppable({
            accept: ".draggable",
            activeClass: "ui-state-hover",
            hoverClass: "ui-state-active",
            drop: function( event, ui ) {
                var droppedClass = $(ui.draggable).attr("class");
				var thisClass = $(this).attr("class");
				
				dropped(droppedClass, thisClass);
            }
        });
		
  }); 
  
 <?php } // end not in search ?> 
  function dropped(droppedClass, onClass) {
		if(getType(onClass)=="documentcategory") { // action only on dropped to folder
		if(confirm('Move to this folder?')) {
			//$("#info").load("/documents/ajax/drop_update.ajax.php?table="+table+"&id="+id+"&documentcategoryID="+getID(onClass)); 
			table = getType(droppedClass);
			id = getID(droppedClass);
			url = "/documents/ajax/drop_update.ajax.php?table="+table+"&id="+id+"&documentcategoryID="+getID(onClass);
			alert(url);
			$.get(url, function(data) {
				if(data=="OK") { // success
					if(table=="documents") {
						$(".document"+id).hide();
					} else if(table=="documentcategory") {
						$(".folder"+id).hide();
					} else if(table=="documentshortcut") {
						$(".shortcut"+id).hide();
					} else if(table=="flipbook") {
						$(".flipbook"+id).hide();
					}
				} else {
					alert(data);
				}
   
});
		
		}
		}
	}
	
	
function getID(theclass) { 
folder = theclass.match(/folder(\d+)/);
	doc = theclass.match(/document(\d+)/);
	shortcut = theclass.match(/shortcut(\d+)/);
	flipbook = theclass.match(/flipbook(\d+)/);
	if(folder) {
		
		 id = folder[1];
	} else if(doc) {
		
		 id = doc[1];
		 } else if(shortcut) {
		
		 id = shortcut[1];
		 } else {
		
		 id = flipbook[1];
		 }
		 return id;
}

function getType(theclass) {
	folder = theclass.match(/folder(\d+)/);
	doc = theclass.match(/document(\d+)/);
	shortcut = theclass.match(/shortcut(\d+)/);
	flipbook = theclass.match(/flipbook(\d+)/);
	if(folder) {
		 table = "documentcategory";
		
	} else if(doc) {
		table = "documents";
		
		 } else if(shortcut) {
		table = "documentshortcut";
		
		 } else {
		table = "flipbook";
		
		 }
		 return table;
}


</script>
<style><!--
<?php if($row_rsDocumentPrefs['showsearch']!=1)  { 
	echo ".docsearch { display:none; }"; 
}

if(!$isSearch && $canEdit) { echo ".sortable .docsItem .handle { display:table-cell; }
.documents-desktop .sortable  .docsItem .handle { display:none;  }";  } 

 ?>
--></style>
<!-- InstanceEndEditable -->
</head>
<!-- ISEARCH_END_INDEX -->
<body class="bootstrap <?php echo $body_class;  ?>">
	<?php require_once('../local/includes/header.inc.php'); ?>
  	<main><!-- The content inside the <main> element should be unique to the document. It should not contain any content that is repeated across documents such as sidebars, navigation links, copyright information, site logos, and search forms. -->
      <!-- ISEARCH_BEGIN_INDEX -->
      <!-- InstanceBeginEditable name="Body" -->
          <div class="container pageBody">
           
              
              
              <div class="crumbs"><div><span class="you_are_in">You are in: </span>
      
      <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"><a itemprop="item" href="/"><span itemprop="name">Home</span></a>
      <meta itemprop="position" content="1" /></li>
      
     <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem"> 
      <a itemprop="item" href="index.php"><span itemprop="name">Documents</span></a>
       <meta itemprop="position" content="2" />
      </li>
      
	  
	<?php if (isset($row_rsThisCategory['categoryname']) && $row_rsThisCategory['categoryname'] !="") { ?>
      
      
       <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
	  <a itemprop="item" href="<?php echo $canonicalURL; ?>"><span itemprop="name">
	  <?php echo $row_rsThisCategory['categoryname'];   ?> folder</span></a> <meta itemprop="position" content="3" /></li>
      <?php } ?>
      </ol></div></div>
			  
			  
			  
			  
			  <?php require_once('includes/documents.inc.php'); ?>
          </div>

    <!-- InstanceEndEditable -->
    </main>
    <!-- ISEARCH_END_INDEX --> 
	<?php require_once('../local/includes/footer.inc.php'); ?>  
<!-- ISEARCH_BEGIN_INDEX -->
</body>
<!-- InstanceEnd --></html>
<?php


mysql_free_result($rsThisCategory);



mysql_free_result($rsPreferences);

mysql_free_result($rsLoggedIn);
?>
