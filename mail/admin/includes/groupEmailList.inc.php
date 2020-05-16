<?php if(isset($_GET['refresh'])) { // using ajax
	require_once('../../../Connections/aquiescedb.php'); ?>
<?php
} // if used as an ajax call we need to include connection file
$editFormAction = "index.php" ; ?>
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

$currentPage = "/mail/admin/groupemail/index.php";
$regionID = isset($regionID) ? $regionID : 1;

$maxRows_rsGroupEmails = 20;
$pageNum_rsGroupEmails = 0;
if (isset($_GET['pageNum_rsGroupEmails'])) {
  $pageNum_rsGroupEmails = $_GET['pageNum_rsGroupEmails'];
}
$startRow_rsGroupEmails = $pageNum_rsGroupEmails * $maxRows_rsGroupEmails;

$varRegionID_rsGroupEmails = "1";
if (isset($regionID)) {
  $varRegionID_rsGroupEmails = $regionID;
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsGroupEmails = sprintf("SELECT groupemail.*, usertype.name, usergroup.groupname FROM groupemail  LEFT JOIN usertype ON (groupemail.usertypeID = usertype.ID)  LEFT JOIN usergroupmember ON (usergroupmember.groupID= groupemail.usergroupID ) LEFT JOIN usergroup ON (groupemail.usergroupID = usergroup.ID) WHERE (groupemail.regionID = 0 OR groupemail.regionID =%s) GROUP BY groupemail.ID ORDER BY startdatetime DESC", GetSQLValueString($varRegionID_rsGroupEmails, "int"));
$query_limit_rsGroupEmails = sprintf("%s LIMIT %d, %d", $query_rsGroupEmails, $startRow_rsGroupEmails, $maxRows_rsGroupEmails);
$rsGroupEmails = mysql_query($query_limit_rsGroupEmails, $aquiescedb) or die(mysql_error());
$row_rsGroupEmails = mysql_fetch_assoc($rsGroupEmails);

if (isset($_GET['totalRows_rsGroupEmails'])) {
  $totalRows_rsGroupEmails = $_GET['totalRows_rsGroupEmails'];
} else {
  $all_rsGroupEmails = mysql_query($query_rsGroupEmails);
  $totalRows_rsGroupEmails = mysql_num_rows($all_rsGroupEmails);
}
$totalPages_rsGroupEmails = ceil($totalRows_rsGroupEmails/$maxRows_rsGroupEmails)-1;

$queryString_rsGroupEmails ="";


?>
    <?php if ($totalRows_rsGroupEmails == 0) { // Show if recordset empty ?>
     <p>There are currently no group emails.<?php echo $_SESSION['MM_Username']; ?></p>
     <?php } // Show if recordset empty ?>
 <?php if ($totalRows_rsGroupEmails > 0) { // Show if recordset not empty ?> <p class="text-muted">Group emails <?php echo ($startRow_rsGroupEmails + 1) ?> to <?php echo min($startRow_rsGroupEmails + $maxRows_rsGroupEmails, $totalRows_rsGroupEmails) ?> of <?php echo $totalRows_rsGroupEmails ?> (Start date descending)</p>
     <form action="<?php echo $editFormAction; ?>" method="POST" name="form2" id="form2"><table class="table table-hover">
     <thead>
        <tr>
          <th>&nbsp;</th>
          <th colspan="2">Start</th>
          <th>Subject</th>
          <th>Recipients</th>
            <th>Total</th>  
          <th>Sent</th>          
          <th>Views</th><th>Clicks</th>
          <th>Actions</th>
        </tr></thead><tbody>
        <?php do { ?>
          <tr>
            <td class="middle"><?php if ($row_rsGroupEmails['active']==0 && !isset($row_rsGroupEmails['enddatetime'])) { 
			 ?><i class="glyphicon glyphicon-pencil text-warning"></i>
			<?php } else if($row_rsGroupEmails['active']==1 && !isset($row_rsGroupEmails['enddatetime']) && $row_rsGroupEmails['startdatetime'] <= date('Y-m-d H:i:s')) {  ?><img src="/core/images/loading_16x16.gif" alt="Currently sending group email" width="16" height="16" style="vertical-align:
middle;" />
            <?php } else if ($row_rsGroupEmails['active']==1 && !isset($row_rsGroupEmails['enddatetime']) && $row_rsGroupEmails['startdatetime'] > date('Y-m-d H:i:s')) {  ?><i class="glyphicon glyphicon-time"></i>
            <?php } else {  ?><i class="glyphicon glyphicon-ok text-success"></i>            <?php } ?></td>
            <td class="middle text-nowrap"><?php echo date('j M y',strtotime($row_rsGroupEmails['startdatetime'])); ?></td>
             <td class="middle text-nowrap"><?php echo date('H:i',strtotime($row_rsGroupEmails['startdatetime'])); ?></td>
            <td class="middle">
              <a href="/mail/admin/groupemail/preview.php?emailID=<?php echo $row_rsGroupEmails['ID']; ?>" title="Click to preview"><?php echo $row_rsGroupEmails['subject']; ?></a></td>
            <td  class="middle text-nowrap "><a href="recipients.php?groupemailID=<?php echo $row_rsGroupEmails['ID']; ?>"><?php $recipients =  isset($row_rsGroupEmails['groupname']) ? $row_rsGroupEmails['groupname'] : "";  $recipients  .= ($row_rsGroupEmails['usertypeID']>0) ?  " who are rank ".$row_rsGroupEmails['name']."+" : ""; 
			
			$sentselect = "SELECT groupemaillist.sent, COUNT(groupemaillist.sent) AS emailsent FROM groupemaillist WHERE groupemailID = ".$row_rsGroupEmails['ID']." GROUP BY groupemaillist.sent";
			$rsSent = mysql_query($sentselect, $aquiescedb) or die(mysql_error().": ".$sentselect);
			$row_rsSent = mysql_fetch_assoc($rsSent);  // row 1
			$unsent = isset($row_rsSent['emailsent']) ? $row_rsSent['emailsent'] : 0; 
					
			$row_rsSent = mysql_fetch_assoc($rsSent);  // row 2
			if(isset($row_rsSent['emailsent'])) {
				$sent = $row_rsSent['emailsent'];
				$total_recips = $unsent + $sent;
			
			} else {
				
				$total_recips = $unsent;
				$sent = isset($row_rsGroupEmails['enddatetime']) ? $total_recips  : 0;
				
			}echo $recipients=="" ? "Everyone" : $recipients ;	?></a> <td class="middle"><?php echo $total_recips>0  ?  $total_recips : ""; ?></td>
			
			
            <td class="middle"><?php 
		
			

echo $sent>0 ?  $sent : "-";   ?>&nbsp;<?php if ($row_rsGroupEmails['active'] == 1 && !isset($row_rsGroupEmails['enddatetime'])) { ?><a href="/mail/admin/groupemail/index.php?pauseemailID=<?php echo $row_rsGroupEmails['ID']; ?>" onclick="return confirm('Are you sure you want to pause this email? You can resume later only if you do not edit settings.'); "><i class="glyphicon glyphicon-remove-circle" data-toggle="tooltip" title="Stop sending this email"></i></a><?php } else if ($row_rsGroupEmails['active'] == 0 && !isset($row_rsGroupEmails['enddatetime'])) { ?><!--- RESUME ---><?php } ?></td>


<td class="middle"><a href="/mail/admin/groupemail/clicktrack.php?groupemailID=<?php echo $row_rsGroupEmails['ID']; ?>"><?php if($row_rsGroupEmails['trackclicks'] ==1 && $row_rsGroupEmails['html'] !="") { echo $row_rsGroupEmails['readcount']; } else { echo "N/A*"; } ?></a></td>
<td class="middle"><a href="/mail/admin/groupemail/clicktrack.php?groupemailID=<?php echo $row_rsGroupEmails['ID']; ?>&clicksonly=1"><?php if($row_rsGroupEmails['trackclicks'] ==1) { echo $row_rsGroupEmails['clickcount']; } else { echo "N/A*"; } ?></a></td>
            <td  class="text-nowrap"><div class="btn-group"><a href="preview.php?emailID=<?php echo $row_rsGroupEmails['ID']; ?>" class="btn btn-sm btn-default btn-secondary" ><i class="glyphicon glyphicon-search"></i> View/Edit</a><a href="/mail/admin/groupemail/index.php?duplicateemailID=<?php echo $row_rsGroupEmails['ID']; ?>" onclick="return confirm('Are you sure you want to create a duplicate this email?');" class="btn btn-sm btn-default btn-secondary"> <i class="glyphicon glyphicon-plus-sign"></i> Add Copy</a><a href="/mail/admin/groupemail/index.php?deleteemailID=<?php echo $row_rsGroupEmails['ID']; ?>" onclick="return confirm('Are you sure you want to delete this email?');" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-trash"></i> Delete</a></div></td>
        </tr>
          <?php if(!isset($_GET['refresh'])) { // re calibrate click counts
		 
		
		  
		  $update = "UPDATE groupemail SET readcount = (SELECT COUNT(DISTINCT(userID)) FROM groupemailclick WHERE groupemailID = ".$row_rsGroupEmails['ID']." GROUP BY groupemailID) WHERE ID= ".$row_rsGroupEmails['ID'];
		  mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		  
		   $update = "UPDATE groupemail SET clickcount = (SELECT COUNT(DISTINCT(userID)) FROM groupemailclick WHERE groupemailID = ".$row_rsGroupEmails['ID']." AND url IS NOT NULL GROUP BY groupemailID) WHERE ID= ".$row_rsGroupEmails['ID'];
		  mysql_query($update, $aquiescedb) or die(mysql_error().": ".$update);
		  
		  
		  } ?>
          <?php } while ($row_rsGroupEmails = mysql_fetch_assoc($rsGroupEmails)); ?></tbody>
        </table>
    
</form>
     
     <?php } // Show if recordset not empty ?>
     <table class="form-table">
     <tr>
       <td><?php if ($pageNum_rsGroupEmails > 0) { // Show if not first page ?>
             <a href="<?php printf("%s?pageNum_rsGroupEmails=%d%s", $currentPage, 0, $queryString_rsGroupEmails); ?>">First</a>
             <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsGroupEmails > 0) { // Show if not first page ?>
             <a href="<?php printf("%s?pageNum_rsGroupEmails=%d%s", $currentPage, max(0, $pageNum_rsGroupEmails - 1), $queryString_rsGroupEmails); ?>" rel="prev">Previous</a>
             <?php } // Show if not first page ?>       </td>
       <td><?php if ($pageNum_rsGroupEmails < $totalPages_rsGroupEmails) { // Show if not last page ?>
             <a href="<?php printf("%s?pageNum_rsGroupEmails=%d%s", $currentPage, min($totalPages_rsGroupEmails, $pageNum_rsGroupEmails + 1), $queryString_rsGroupEmails); ?>" rel="next">Next</a>
             <?php } // Show if not last page ?>       </td>
       <td><?php if ($pageNum_rsGroupEmails < $totalPages_rsGroupEmails) { // Show if not last page ?>
             <a href="<?php printf("%s?pageNum_rsGroupEmails=%d%s", $currentPage, $totalPages_rsGroupEmails, $queryString_rsGroupEmails); ?>">Last</a>
             <?php } // Show if not last page ?>       </td>
     </tr>
   </table>
   
<?php
mysql_free_result($rsGroupEmails);


?>
