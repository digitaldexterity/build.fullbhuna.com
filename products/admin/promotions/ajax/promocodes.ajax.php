<?php require_once('../../../../Connections/aquiescedb.php'); ?><?php require_once('../../../../core/includes/adminAccess.inc.php'); ?>
<?php if (!function_exists("GetSQLValueString")) {
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


$colname_rsCodes = "-1";
if (isset($_GET['promoID'])) {
  $colname_rsCodes = $_GET['promoID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCodes = sprintf("SELECT * FROM productpromocode WHERE promoID = %s", GetSQLValueString($colname_rsCodes, "int"));
$rsCodes = mysql_query($query_rsCodes, $aquiescedb) or die(mysql_error());
$row_rsCodes = mysql_fetch_assoc($rsCodes);
$totalRows_rsCodes = mysql_num_rows($rsCodes);
?>
<?php if ($totalRows_rsCodes > 0) { // Show if recordset not empty ?>
  <table  class="table table-hover">
  <thead>
    <tr>
      <th>&nbsp;</th>   <th>Code</th>   <th>Created</th>  <th>Valid from</th>
      <th>Valid to</th> <th>Used</th>
      <th>Set</th>
      
      
      
      
      
      
      
      
    </tr></thead><tbody>
    <?php do { ?>
      <tr>
        <td class="status<?php echo $row_rsCodes['statusID']; ?>">&nbsp;</td>  <td><?php echo $row_rsCodes['promocode']; ?></td> <td><?php echo  date('d M Y', strtotime($row_rsCodes['createddatetime'])); ?></td> 
        <td><?php echo isset($row_rsCodes['validfrom']) ?   date('d M Y', strtotime($row_rsCodes['validfrom'])) : "-"; ?></td>
        <td><?php echo isset($row_rsCodes['validuntil']) ?   date('d M Y', strtotime($row_rsCodes['validuntil'])) : "-"; ?></td>
        <td><?php echo isset($row_rsCodes['modifieddatetime']) ?   date('d M Y', strtotime($row_rsCodes['modifieddatetime'])) : "-"; ?></td>
        <td><?php if($row_rsCodes['statusID']==1) { ?>
          <a href="update_promotion.php?promoID=<?php echo $_GET['promoID']; ?>&defaultTab=2&cancel=true&promocodeID=<?php echo $row_rsCodes['ID']; ?>">Cancel</a>
          <?php } else { ?><a href="update_promotion.php?promoID=<?php echo $_GET['promoID']; ?>&defaultTab=2&reset=true&promocodeID=<?php echo $row_rsCodes['ID']; ?>">Reset</a><?php } ?></td>
        
        
        
        
        
        
        
      </tr>
      <?php } while ($row_rsCodes = mysql_fetch_assoc($rsCodes)); ?>
  </tbody></table>
  <?php }  else { ?>
  <p>No codes added so far</p>
  <?php } mysql_free_result($rsCodes); ?>