<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php require_once('documentfunctions.inc.php'); ?>
<style><!--
<?php if(!isset($_SESSION['MM_UserGroup'])) { 
	echo ".icon_text_useraccess { display:none; }"; 
} ?>
--></style>
<?php 
$categoryID =isset($row_rsThisCategory['ID']) ? $row_rsThisCategory['ID']  : 0;
$search = (isset($_REQUEST['search']) && trim($_REQUEST['search']) !="") ? $_REQUEST['search'] : ""; 
$rsDocuments = getDocuments($categoryID, $search); 
$rsFolders = getFolders($categoryID);
$rsFlipbooks = getFlipbooks($categoryID);
$rsShortcuts = getShortcuts($categoryID);
$home = getHomeFolder($regionID); // will create one if doen't exist
$host = getProtocol()."://".  $_SERVER['HTTP_HOST']


?><link href="/documents/css/documentsDefault.css" rel="stylesheet"  />
<div class="documents"><?php //Check authorised access
	if($canView)
		
			  { //is authorised to view ?>
            
            <h1>Documents </h1>
            <?php if(isset($_SESSION['MM_UserGroup'])) { ?>
            <div id="documentsMenu" >
              <nav class="navbar navbar-default navbar-expand-lg navbar-light bg-light"><div class="container-fluid"><ul class="nav navbar-nav">
                <?php 
		
		if($canEdit)
	 { ?>
                <li class="nav-item"><a href="/documents/members/add_documents.php?categoryID=<?php echo isset($row_rsThisCategory['ID']) ? $row_rsThisCategory['ID'] : 0; ?>" class="nav-link" title="Add one or more documents to this folder"><i class="glyphicon glyphicon-file"></i> Add  documents</a></li>
                
                <li class="nav-item"><a href="/documents/folders/add_folder.php?categoryID=<?php echo isset($row_rsThisCategory['ID']) ? $row_rsThisCategory['ID'] : 0; ?>" class="nav-link"><i class="glyphicon glyphicon-folder-open"></i> Add folder</a></li>
                <li class="nav-item"><a href="/documents/shortcut/add_shortcut.php?categoryID=<?php echo isset($row_rsThisCategory['ID']) ? $row_rsThisCategory['ID'] : 0; ?>" class="nav-link"><i class="glyphicon glyphicon-link"></i> Add shortcut/link</a></li>
                 <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?>
                <li class="nav-item"><a href="/documents/admin/flipbooks/add_flipbook.php?categoryID=<?php echo $row_rsThisCategory['ID']; ?>" target="_blank" class="nav-link" rel="noopener"><i class="glyphicon glyphicon-book"></i> Add Flipbook</a></li>
                <?php } ?>
                <?php } // end can edit
				
				 if (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { // is admin ?>
                <li class="nav-item"><a href="/documents/admin/index.php" class="nav-link"><i class="glyphicon glyphicon-cog"></i> Settings</a></li>
                <?php }  if($row_rsPreferences['userscanlogin']==1 && !isset($_SESSION['MM_UserGroup'])) {  ?><li><a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="link_users">Log in</a></li><?php } ?>
              </ul></div></nav>
            </div><?php } ?>
            <form method="get" class="docsearch">
             
              <fieldset class="form-inline"><legend>Search Documents</legend>
              
                <input name="search" type="text" size="50" maxlength="50" value="<?php echo isset($_REQUEST['search']) ? htmlentities($_REQUEST['search'], ENT_COMPAT, "UTF-8") : ""; ?>" placeholder="Enter word from document name"  class="livesearch form-control"/>

                <button type="submit" class="btn btn-default btn-secondary" >Search</button>
                 
            </fieldset> </form>
            <?php if($search=="") { // only show if no search ?>
            <div id="folderheader">
              <h2><a href="/documents/"><?php echo isset($home['categoryname']) ? $home['categoryname'] : "Home"; ?></a><?php if(isset($row_rsThisCategory['parentsubcatofID']) && isset($row_rsThisCategory['parentname'])) {				  
				  echo ($row_rsThisCategory['parentsubcatofID']==0) ?    "&nbsp;&rsaquo; " : "&nbsp;&rsaquo; ";
				  ?><a href="/documents/index.php?categoryID=<?php echo $row_rsThisCategory['subcatofID']; ?>"><?php echo htmlentities($row_rsThisCategory['parentname'], ENT_COMPAT, "UTF-8"); ?></a><?php   }   if (isset($row_rsThisCategory['subcatofID']) && isset($row_rsThisCategory['categoryname'])) { 
			
				echo "&nbsp;&rsaquo; ".$row_rsThisCategory['categoryname']; }  ?>
                <span>folder</span> <?php if($canEdit && isset($row_rsThisCategory['categoryname'])) { ?><a href="/documents/folders/modify_folder.php?categoryID=<?php echo $row_rsThisCategory['ID']; ?>" class="btn btn-default btn-secondary "><i class="glyphicon glyphicon-pencil"></i> Update</a><?php } ?></h2>
                <?php if ((isset($row_rsThisCategory['accessID']) && $row_rsThisCategory['accessID'] > 0) || isset($row_rsThisCategory['groupname'])) { ?>
                <p class="accesslevel<?php echo $row_rsThisCategory['accessID']; ?>"><img src="/core/images/icons/no-entry.png" alt="Restricted access" style="vertical-align:middle;width:auto;"> Access to this folder is restricted to
      <?php if ($row_rsThisCategory['accessID'] == 99)  echo 'just you.'; else { echo isset($row_rsThisCategory['name']) ? $row_rsThisCategory['name']." rank and above" : " everyone"; }  echo isset($row_rsThisCategory['groupname']) ? " ".$row_rsThisCategory['groupname']." group. " : ".";  ?></p>
                <?php } ?>
              <?php if (isset($row_rsThisCategory['description'])) { ?>
                <p><?php echo nl2br($row_rsThisCategory['description']); ?></p>
                <?php } ?>
             
            </div>
            <?php } // end header ?><?php require_once(SITE_ROOT.'core/includes/alert.inc.php'); ?>
            
        <div class="<?php if($row_rsDocumentPrefs['defaultview']==2) echo " documents-desktop ";   if($row_rsDocumentPrefs['defaultview']>0) echo " large-icons "; ?>"><!-- start docs wrapper  -->
           
            
            <!-- ****************FOLDERS*******************-->
            
            <?php if((is_object($rsFolders) || is_resource($rsFolders))  && $search=="") { ?>
            <ul class="docs-list sortable" id="documentcategory">
              <?php while ($row_rsFolders = mysql_fetch_assoc($rsFolders)) { ?>
                <li data-ID="<?php echo $row_rsFolders['ID']; ?>" class="docsItem draggable droppable folder<?php echo $row_rsFolders['ID']; ?>" id="listItem_1.<?php echo $row_rsFolders['ID']; ?>" <?php echo "style=\"left: ".$row_rsFolders['left']."px; top: ".$row_rsFolders['top']."px;\"";  ?>  >
                 
                  <span> <a href="/documents/index.php?categoryID=<?php echo $row_rsFolders['ID']; ?>"  title="<?php echo $row_rsFolders['folderdetails']; ?>" data-toggle="tooltip" class="folder " <?php echo $clickevent; ?>="document.location='/documents/index.php?categoryID=<?php echo $row_rsFolders['ID']; ?>'; return false;"> <?php echo htmlentities($row_rsFolders['categoryname'], ENT_COMPAT, "UTF-8"); ?></a></span><span class="showonhover"><a href="javascript:void(0);" class="btn btn-default btn-secondary" title="Link to this folder" onClick="prompt('You can copy the link for this folder to your clipboard below:','<?php echo $host."/documents/index.php?categoryID=".$row_rsFolders['ID']; ?>')"><i class="glyphicon glyphicon-link"></i> Link</a></span>
                  <?php if ($row_rsLoggedIn['ID'] == $row_rsFolders['addedbyID'] || $row_rsLoggedIn['usertypeID'] >=9) { //  authorised to edit ?>
                  <span class="showonhover"><a href="/documents/folders/modify_folder.php?categoryID=<?php echo $row_rsFolders['ID']; ?>" class="btn btn-default btn-secondary" title="Edit this folder"><i class="glyphicon glyphicon-pencil"></i> Update</a></span><span class="showonhover"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?categoryID=<?php echo isset($_GET['categoryID']) ? $_GET['categoryID'] : 0; ?>&amp;deleteFolderID=<?php echo $row_rsFolders['ID']; ?>" onclick="return confirm('Are you sure you want to delete the folder:\n&quot;<?php echo $row_rsFolders['categoryname']; ?>&quot;?')" class="btn btn-default btn-secondary " title="Delete this folder"><i class="glyphicon glyphicon-trash"></i> Delete</a></span>
                  <?php } else { //not authorised to edit ?>
                  <span>&nbsp;</span>  <span>&nbsp;</span>
                  <?php } ?>
                  <span class="icon_text_useraccess showonhover">
                  <?php $name = ($row_rsFolders['userID'] == $row_rsLoggedIn['ID']) ? "you" : $row_rsFolders['firstname']." ".$row_rsFolders['surname'];
	  $usertype = ($row_rsFolders['accessID']==0) ?  "Everyone" : $row_rsFolders['usertype'];
	  echo ($row_rsFolders['accessID'] == 99) ? "&nbsp;Access: Just ".$name : "&nbsp;Access: ".$usertype; ?>
                  <?php echo isset($row_rsFolders['groupname']) ? ", ".$row_rsFolders['groupname'] : ""; ?></span>
                 
                  
                  
                </li>
                <?php } // end while ?>
            </ul>
            <?php  } // not empty ?>
            
            <!-- 
            
            ****************SHORT CUTS*******************
            
            -->
            
            <?php if((is_object($rsShortcuts) || is_resource($rsShortcuts)) && $search=="") { ?>
            <ul class="docs-list sortable" id="documentshortcut">
              <?php while ($row_rsShortcuts = mysql_fetch_assoc($rsShortcuts)) { ?>
              <?php if($row_rsShortcuts['shortcuttype']==1) {
			  	$link =  "/documents/index.php?categoryID=".$row_rsShortcuts['shortcuttoID'];
			  	$shortcutname= "Short cut to ".$row_rsShortcuts['categoryname'];
				$target = "_top";
			  } else {
				$link =  $row_rsShortcuts['shortcutURL'];
			  	$shortcutname= $row_rsShortcuts['shortcutname'];
				$target = "_blank\" rel=\"noopener\"";
			  } ?>
                <li class="docsItem draggable shortcut<?php echo $row_rsShortcuts['ID']; ?> shortcuttype<?php echo $row_rsShortcuts['shortcuttype']; ?>" id="listItem_2.<?php echo $row_rsShortcuts['ID']; ?>" <?php echo "style=\"left: ".$row_rsShortcuts['left']."px; top: ".$row_rsShortcuts['top']."px;\"";  ?>><span><a href="<?php echo $link; ?>"  title="<?php echo $shortcutname; ?>" class="shortcut " <?php echo $clickevent; ?>='document.location=\"<?php echo $link; ?>\"; return false;' target="<?php echo $target; ?>">  <?php echo htmlentities($shortcutname, ENT_COMPAT, "UTF-8"); ?></a></span>
                  <?php if (($row_rsLoggedIn['ID'] == $row_rsShortcuts['createdbyID'] || $row_rsLoggedIn['usertypeID'] >=9)) { // not My Documents and authorised to edit ?>
                  <span class="showonhover"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?categoryID=<?php echo isset($_GET['categoryID']) ? $_GET['categoryID'] : 0; ?>&amp;deleteShortcutID=<?php echo $row_rsShortcuts['ID']; ?>" onclick="return confirm('Are you sure you want to delete the shortcut:\n&quot;<?php echo htmlentities($shortcutname, ENT_COMPAT, "UTF-8");  ?>&quot;?')" class="btn btn-default btn-secondary " title="Delete this shortcut"><i class="glyphicon glyphicon-trash"></i> Delete</a></span>
                  <?php } //end authorised to edit ?>
                  <span class="icon_text_useraccess showonhover">
                  <?php $name = ($row_rsShortcuts['shortcuttoID'] ==-1) ? "you" :$row_rsShortcuts['firstname']." ".$row_rsShortcuts['surname'];
	  $usertype = ($row_rsShortcuts['accessID']==0) ?  "Everyone" : $row_rsShortcuts['usertype'];
	  echo ($row_rsShortcuts['accessID'] == 99) ? "&nbsp;Access: Just ".$name : "&nbsp;Access: ".$usertype; ?>
              <?php echo isset($row_rsShortcuts['groupname']) ? ", ".$row_rsShortcuts['groupname'] : ""; ?></span></li>
                <?php }  ?>
            </ul>
            <?php } // not empty ?>
            
            <!-- 
            
            ****************FLIP BOOKS*******************
            
            -->
              <?php if((is_object($rsFlipbooks) || is_resource($rsFlipbooks))  && $search=="") { ?>

            <ul class="docs-list sortable" id="flipbook">
              <?php while ($row_rsFlipbooks = mysql_fetch_assoc($rsFlipbooks)) { ?>
                <li class="docsItem draggable flipbook<?php echo $row_rsFlipbooks['ID']; ?>" id="listItem_3.<?php echo $row_rsFlipbooks['ID']; ?>" <?php echo "style=\"left: ".$row_rsFlipbooks['left']."px; top: ".$row_rsFlipbooks['top']."px;\"";  ?>><span><a href="/documents/flipbook/flipbook.php?flipbookID=<?php echo $row_rsFlipbooks['ID']; ?>"  title = "View flipbook: <?php echo $row_rsFlipbooks['flipbookname']; ?>" target = "_blank" class=" document flipbook" rel="noopener" <?php echo $clickevent; ?>="/documents/flipbook/flipbook.php?flipbookID=<?php echo $row_rsFlipbooks['ID']; ?>'; return false;"><?php echo htmlentities($row_rsFlipbooks['flipbookname'], ENT_COMPAT, "UTF-8"); ?></a></span>
                <span class="showonhover">&nbsp;</span><span class="showonhover"><a href="javascript:void(0);" class="btn btn-default btn-secondary " title="Link to this flipbook" onClick="prompt('You can copy the link for this flipbook to your clipboard below:','<?php echo $host."/documents/flipbook/flipbook.php?flipbookID=". $row_rsFlipbooks['ID'];  ?>')"><i class="glyphicon glyphicon-link"></i> Link</a></span>
                  <?php if(isset($_SESSION['MM_Username'])) { // if logged in
	  
if ($row_rsLoggedIn['ID'] == $row_rsFlipbooks['userID'] || $row_rsLoggedIn['usertypeID'] >=9) { // authorised to edit ?>
	<span class="showonhover status<?php echo $row_rsFlipbooks['active']; ?>">&nbsp;</span>
                  <span class="showonhover"><a href="/documents/admin/flipbooks/update_flipbook.php?flipbookID=<?php echo $row_rsFlipbooks['ID']; ?>" title="Edit this flipbook" target="_blank"class="btn btn-default btn-secondary " rel="noopener"><i class="glyphicon glyphicon-pencil"></i> Update</a></span><span class="showonhover"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?categoryID=<?php echo isset($_GET['categoryID']) ? $_GET['categoryID'] : 0; ?>&amp;deleteFlipbookID=<?php echo $row_rsFlipbooks['ID']; ?>" onclick="return confirm('Are you sure you want to delete the flipbook:\n&quot;<?php echo htmlentities($row_rsFlipbooks['flipbookname'], ENT_COMPAT, "UTF-8"); ?>&quot;?')" class="btn btn-default btn-secondary " title="Delete this document"><i class="glyphicon glyphicon-trash"></i> Delete</a></span>
                  <?php } // auth to edit 
						   } // logged in ?>
                  </li>
                <!-- end docs item -->
                
                <?php }  ?>
            </ul>
            <?php } //  not empty ?>
            
            <!-- 
            
            ****************DOCUMENTS*******************
            
            -->
           
            <?php  if(is_object($rsDocuments) || is_resource($rsDocuments)) { ?>
            <ul class="docs-list sortable" id="documents">
              <?php  while($row_rsDocuments = mysql_fetch_assoc($rsDocuments)) { ?>
                <li class="docsItem draggable document<?php echo $row_rsDocuments['ID']; ?>" data-ID="<?php echo $row_rsDocuments['ID']; ?>" id="listItem_4.<?php echo $row_rsDocuments['ID']; ?>" <?php echo "style=\"left: ".$row_rsDocuments['left']."px; top: ".$row_rsDocuments['top']."px;\"";  ?>><span><a  href="/documents/view.php?documentID=<?php echo $row_rsDocuments['ID']; ?>&amp;categoryID=<?php echo intval($_GET['categoryID']); ?>" <?php echo $clickevent; ?>="<?php if($row_rsDocumentPrefs['opennewwindow']==1) { ?>MM_openBrWindow('/documents/view.php?documentID=<?php echo $row_rsDocuments['ID']; ?>&amp;categoryID=<?php echo intval($_GET['categoryID']); ?>','viewDocument','scrollbars=yes,toolbar=yes,width=800,height=500,resizable=yes');<?php } else { ?>window.location.href='/documents/view.php?documentID=<?php echo $row_rsDocuments['ID']; ?>&amp;categoryID=<?php echo intval($_GET['categoryID']); ?>';<?php } ?> return false;" title = "View document: <?php echo $row_rsDocuments['documentname']; ?>" <?php echo ($row_rsDocumentPrefs['opennewwindow']==1) ? " target = \"_blank\" rel=\"noopener\"" : ""; ?> class="document <?php echo substr(strrchr($row_rsDocuments['filename'],'.'),1,3); ?> "><?php echo htmlentities($row_rsDocuments['documentname'], ENT_COMPAT, "UTF-8"); ?></a></span> 
               
               <span class="showonhover">
                 <a href="javascript:;" class="btn btn-default btn-secondary" title="Link to this document" onClick="prompt('You can copy the link for this document to your clipboard below:','<?php echo $host."/documents/view.php?documentID=". $row_rsDocuments['ID'];  ?>')"><i class="glyphicon glyphicon-link"></i> Link</a></span>
                  <?php if(isset($_SESSION['MM_Username'])) { // if logged in
	  
if ($row_rsDocuments['lock'] != 1 || $row_rsLoggedIn['ID'] == $row_rsDocuments['userID'] || $row_rsLoggedIn['usertypeID'] >=9) { // authorised to edit ?>
	
                  
               
                  <span class="showonhover"><a href="/documents/members/modify_document.php?documentID=<?php echo $row_rsDocuments['ID']; ?>" class="btn btn-default btn-secondary" title="Update this document"><i class="glyphicon glyphicon-pencil"></i> Update</a></span><span class="showonhover"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?categoryID=<?php echo isset($_GET['categoryID']) ? $_GET['categoryID'] : 0; ?>&amp;deleteDocumentID=<?php echo $row_rsDocuments['ID']; ?>" onclick="return confirm('NOTE: If you are adding a newer verion of this document then you should update this document rather than deleting it to keep all existing links active.\n\nAre you sure you want to delete the document:\n&quot;<?php echo htmlentities($row_rsDocuments['documentname'], ENT_COMPAT, "UTF-8"); ?>&quot;?')" class="btn btn-default btn-secondary " title="Delete this document"><i class="glyphicon glyphicon-trash"></i> Delete</a></span><span class="showonhover status<?php echo $row_rsDocuments['active']; ?>">&nbsp;</span>
                  <?php } // auth to edit  
				  else {?>
                   <span>&nbsp;</span>  <span>&nbsp;</span>
				 <?php  }
				 
				 if ($row_rsDocuments['lock'] == 1) {  // doc locked
		 if($row_rsDocuments['userID'] != $row_rsLoggedIn['ID']) { // is same user ?>
                  <span class="showonhover"><i class="glyphicon glyphicon-lock" title="This document has been locked by the creator" data-toggle="toolip"></i></span>
                  <?php } else { // locked but not same user ?>
                  <span class="showonhover"><i class="glyphicon glyphicon-lock" title="This document has been locked by me" data-toggle="toolip"></i></span>
                  <?php } // end locked, not same user
			 } else { // not locked ?>
             <span>&nbsp;</span>
                  <?php
			 } // end not locked
					  
						   } // logged in ?>
                  <span class="docLink" >
                  <label>Link:
                    <input name="documentlink" type="text"   value="<?php echo getProtocol()."://". $_SERVER['HTTP_HOST']."/"."documents/view.php?documentID=".$row_rsDocuments['ID']; ?>" size="80" maxlength="255" />
                  </label>
                  </span> </li>
                <!-- end docs item -->
                
                <?php } // end while ?>
            </ul>
            <?php 	  } //  not empty ?>
            <?php if (!is_resource($rsFolders) && !is_resource($rsDocuments) && !is_resource($rsFlipbooks) && !is_resource($rsShortcuts)  && !is_object($rsFolders) && !is_object($rsDocuments) && !is_object($rsFlipbooks) && !is_object($rsShortcuts)) { // folder empty ?>
            <p>This folder is currently empty.</p>
            <?php }  ?><p id="info"></p>
            </div><!-- end docs wrapper -->
          
            
            <?php } // end is authorised 
						   else { ?>
            <p class="alert alert-danger" role="alert">You do not have access to these documents. You may need to <a href="/login/index.php?accesscheck=<?php echo $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']; ?>">log in</a>.</p>
            <?php } ?></div>
            