<?php if(!isset($aquiescedb)) {
	require_once('../../../Connections/aquiescedb.php');
	require_once('../../../core/includes/adminAccess.inc.php');
} ?><?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
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


if(isset($_GET['archiveID']) && strlen($_GET['archiveID'])>0) {
	$update = "UPDATE productorders SET archive = 1 WHERE VendorTxCode = ".GetSQLValueString($_GET['archiveID'], "text");
	mysql_query($update, $aquiescedb) or die(mysql_error());
}


$supplieronly  = "";
$where = "";
$groupby = isset($groupby) ? $groupby : (isset($_GET['groupby']) ? $_GET['groupby'] : 1 );	
$onlysales = isset($onlysales) ? $onlysales : (isset($_GET['onlysales']) ? $_GET['onlysales'] : 0 );	
$supplierID = isset($supplierID) ? $supplierID : (isset($_GET['supplierID']) ? $_GET['supplierID'] : 0);
$startdate = isset($startdate) ? $startdate : (isset($_GET['startdate']) ? $_GET['startdate']." 00:00:00" : "1970-01-01 00:00:00" );
$enddate = isset($enddate) ? $enddate : (isset($_GET['enddate']) ? $_GET['enddate']." 23:59:59" : "2090-01-01 00:00:00" );
$csv= isset($csv) ? $csv : (isset($_GET['csv']) ? $_GET['csv'] : 0);



$maxrows = ($csv == 1 || isset($_GET['startdate']) || isset($_GET['productID'])) ? 5000 : 50;



if ($groupby == 0 ){ // product/supplier
	$groupbysql = " GROUP BY productorderproducts.ID ";
	$customeronly = "|hide";
	$select = ", directory.name AS supplier, product.sku, product.title,  productoptions.optionname, product.price AS price, NULL AS costprice, productorderproducts.Quantity AS totalOrdered  ";	
	
} else { // 1 customer order (default)
	$groupbysql = " GROUP BY productorders.VendorTxCode ";
	$supplieronly = "|hide";
	$select = ", NULL AS supplier, product.sku, product.title,  productoptions.optionname, product.price, NULL AS costprice, count((productorderproducts.productID)) AS totalOrdered  ";
}

if(isset($_GET['productID']) && intval($_GET['productID'])>0) {
	$where .= " AND productorderproducts.productID = ".intval($_GET['productID'])." ";
}

if($onlysales==1) {
	$where .= " AND (productorders.Status LIKE 'ACCEPTED%%' OR productorders.Status LIKE 'COMPLETED%%' OR productorders.Status LIKE 'PROCESSED%%'  OR productorders.Status LIKE 'SUCCESS%%' OR productorders.Status LIKE 'AUTHORISED%%') ";
}

if($supplierID>0) {
	$supplieronly = "|hide";
}

$orderby = (isset($_REQUEST['orderby']) && $_REQUEST['orderby']==2) ?   " ORDER BY LastUpdated DESC" : " ORDER BY createddatetime DESC";

$maxRows_rsOrders = $maxrows;
$pageNum_rsOrders = 0;
if (isset($_GET['pageNum_rsOrders'])) {
  $pageNum_rsOrders = $_GET['pageNum_rsOrders'];
}
$startRow_rsOrders = $pageNum_rsOrders * $maxRows_rsOrders;

$varSearch_rsOrders = "%";
if (isset($_GET['search'])) {
  $varSearch_rsOrders = $_GET['search'];
}
$varSupplierID_rsOrders = "0";
if (isset($supplierID)) {
  $varSupplierID_rsOrders = $supplierID;
}
$varPromotionID_rsOrders = "0";
if (isset($_GET['promotionID'])) {
  $varPromotionID_rsOrders = $_GET['promotionID'];
}
$varRegionID_rsOrders = "1";
if (isset($regionID)) {
  $varRegionID_rsOrders = $regionID;
}
$varStartDate_rsOrders = "1970-01-01";
if ($startdate) {
  $varStartDate_rsOrders = $startdate;
}
$varEndDate_rsOrders = "2900-01-01";
if (isset($enddate)) {
  $varEndDate_rsOrders = $enddate;
}
$varShowArchived_rsOrders = "0";
if (isset($_GET['showarchived'])) {
  $varShowArchived_rsOrders = $_GET['showarchived'];
}

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsOrders = sprintf("SELECT productorders.createddatetime, productorders.LastUpdated,productorders.ID,productorders.VendorTxCode,productorders.userID ".$select.",    productorders.TxType, productorders.`Currency`,productorders.Amount, productorders.shipping,  productorders.AmountPaid,productorders.AmountTax,  productorders.DeliveryFirstnames, productorders.DeliverySurname, productorders.DeliveryCompany, productorders.DeliveryAddress1, productorders.DeliveryAddress2, productorders.DeliveryCity, productorders.DeliveryCountry, productorders.DeliveryPostCode,productorders.BillingPhone,productorders.CustomerEMail, sum(productorderproducts.dispatched) AS totalDispatched , productorders.Status, productorders.archive, (SELECT  COUNT(productorderpromos.ID) AS pc FROM productorderpromos WHERE productorderpromos.VendorTxCode = productorders.VendorTxCode) AS promos, productorders.approvalrequired, productorders.approvedbyID, productorders.approveddatetime, track_session.adwords   FROM productorders LEFT JOIN productorderproducts ON (productorders.VendorTxCode = productorderproducts.VendorTxCode) LEFT JOIN product ON (productorderproducts.productID = product.ID) LEFT JOIN productorderpromos ON (productorderpromos.VendorTxCode = productorders.VendorTxCode) LEFT JOIN directory ON (product.supplierdirectoryID = directory.ID) LEFT JOIN track_session ON (productorders.sessionID = track_session.ID) LEFT JOIN productoptions ON (productoptions.ID = productorderproducts.optionID) LEFT JOIN productfinish ON  (productoptions.finishID = productfinish.ID)  WHERE (archive = 0 OR %s = 1)  AND  (DeliverySurname LIKE %s OR productorders.ID = %s	OR productorders.VendorTxCode LIKE %s) AND (%s IS NULL OR (productorders.createddatetime) >= %s) AND (%s IS NULL OR  (productorders.createddatetime) <= %s) AND (productorders.regionID = %s OR (%s = 1 AND productorders.regionID IS NULL)) AND (%s = 0 OR %s = directory.ID) AND  (%s = 0 OR productorderpromos.promotionID = %s) ".$where.$groupbysql.$orderby."", GetSQLValueString($varShowArchived_rsOrders, "int"),GetSQLValueString($varSearch_rsOrders . "%", "text"),GetSQLValueString($varSearch_rsOrders . "%", "text"),GetSQLValueString($varSearch_rsOrders . "%", "text"),GetSQLValueString($varStartDate_rsOrders, "date"),GetSQLValueString($varStartDate_rsOrders, "date"),GetSQLValueString($varEndDate_rsOrders, "date"),GetSQLValueString($varEndDate_rsOrders, "date"),GetSQLValueString($varRegionID_rsOrders, "int"),GetSQLValueString($varRegionID_rsOrders, "int"),GetSQLValueString($varSupplierID_rsOrders, "int"),GetSQLValueString($varSupplierID_rsOrders, "int"),GetSQLValueString($varPromotionID_rsOrders, "int"),GetSQLValueString($varPromotionID_rsOrders, "int"));
$query_limit_rsOrders = sprintf("%s LIMIT %d, %d", $query_rsOrders, $startRow_rsOrders, $maxRows_rsOrders);
$rsOrders = mysql_query($query_limit_rsOrders, $aquiescedb) or die(mysql_error().": ".$query_limit_rsOrders);
$row_rsOrders = mysql_fetch_assoc($rsOrders);

if (isset($_GET['totalRows_rsOrders'])) {
  $totalRows_rsOrders = $_GET['totalRows_rsOrders'];
} else {
  $all_rsOrders = mysql_query($query_rsOrders);
  $totalRows_rsOrders = mysql_num_rows($all_rsOrders);
}
$totalPages_rsOrders = ceil($totalRows_rsOrders/$maxRows_rsOrders)-1;

$queryString_rsOrders = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsOrders") == false && 
        stristr($param, "totalRows_rsOrders") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsOrders = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsOrders = sprintf("&totalRows_rsOrders=%d%s", $totalRows_rsOrders, $queryString_rsOrders);

if($csv == 0) {
?>
<?php if ($totalRows_rsOrders == 0) { // Show if recordset empty ?>
            <p>There are no orders that match the current search criteria. </p>
            <?php if($_SESSION['MM_UserGroup']==10) echo $query_rsOrders; ?>
            <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsOrders > 0) { // Show if recordset not empty ?>
            <p class="text-muted">Items <?php echo ($startRow_rsOrders + 1) ?> to <?php echo min($startRow_rsOrders + $maxRows_rsOrders, $totalRows_rsOrders) ?> of <?php echo $totalRows_rsOrders ?></p>
            <table class=" table table-hover table-orders">
            <thead>
              <tr>
               <th class="txcode">No.</th>
               
               <th class="lastupdated hidden-xs hidden-sm">Last updated</th>
                <th class="purchased">Purchased</th>
                <th class="customer">Customer</th><th class="supplier">Supplier</th>
                <th class="product">SKU</th>
               <th class="product">Product</th>
                <th class="txtype">Tx Type</th>
                <th class="text-right text-nowrap net">ex VAT</th>
                <th class="text-right text-nowrap gross">inc VAT</th>
                <th class="status">Payment</th>
                <th class="tools" colspan="2">Dispatched</th>
              </tr></thead><tbody>
              <?php $total = 0;  do { ?>
                <tr class="approvalrequired<?php echo $row_rsOrders["approvalrequired"]; ?> <?php echo isset($row_rsOrders["approvedbyID"]) ? "approved" : ""; ?>">
                
                <td class="top txcode"><a href="/products/admin/orders/orderDetails.php?VendorTxCode=<?php echo $row_rsOrders["VendorTxCode"]."&returnURL=".urlencode($_SERVER['REQUEST_URI']); ?>" data-toggle="tooltip" title="<?php echo $row_rsOrders["VendorTxCode"]; ?>"><?php echo $row_rsOrders["ID"]; ?></a></td>
                
               
                  <td class="top text-nowrap lastupdated hidden-xs hidden-sm"><?php echo date('D d M y H:i:s',strtotime($row_rsOrders["LastUpdated"])); ?></td>
                  <td class="top purchased text-nowrap"><?php echo isset($row_rsOrders["createddatetime"]) ? date('D d M y H:i:s',strtotime($row_rsOrders["createddatetime"])) : date('D d M y H:i:s',strtotime($row_rsOrders["LastUpdated"])); ?></td>
                  <td class="top  customer"><a href="/members/admin/modify_user.php?userID=<?php echo $row_rsOrders["userID"]; ?>"><?php echo  $row_rsOrders["DeliveryFirstnames"]." ".$row_rsOrders["DeliverySurname"]; ?></a></td>
                  <td class="top text-nowrap supplier"><?php echo $row_rsOrders["supplier"]; ?></td>
                   <td class="top  product"><?php echo $row_rsOrders["sku"]; ?></td>
                  <td class="top  product"><?php echo $row_rsOrders["title"]; ?></td>
                  <td class="top txtype"><?php echo $row_rsOrders["TxType"]; ?></td>
                  <?php
				  
				  if ($groupby == 0 ) { // product /supplier
					  $amountPaid =$row_rsOrders["price"]*$row_rsOrders["totalOrdered"] ;
					  
				  } else {
				  
				   if(isset($row_rsOrders["AmountPaid"]) && $row_rsOrders["AmountPaid"]!=$row_rsOrders["Amount"]) {
					  $amountPaid =$row_rsOrders["AmountPaid"] ;
					  $class= "red";
					  
				  } else {
					  $amountPaid =$row_rsOrders["Amount"] ;
					  $class= "";
				  } } ?>
                  <td class="top text-right text-nowrap net <?php echo $class; ?>"><?php  echo isset($row_rsOrders["AmountTax"]) ?  number_format(($amountPaid-$row_rsOrders["AmountTax"]),2) : ""; ?></td>
                  <td class="top text-right text-nowrap gross <?php echo $class; ?>"><?php  echo ($row_rsOrders['adwords']==1) ?   "&nbsp;<img src=\"/core/images/icons/google_favicon.png\" width=\"16\" height=\"16\" alt=\"Google Adwords\"  style=\"vertical-align:
middle;\" title=\"This sale orginated from Google Adwords\" data-toggle = \"tooltip\" >" : ""; ?><?php if($row_rsOrders["promos"]>0) { ?>
                    <img src="/core/images/icons/star.png" alt="Promo" width="16" height="16" style="vertical-align:
middle;" title="This sale was part of a promotion" data-toggle = "tooltip"  />&nbsp;
                    <?php } ?>
                    <?php  
					
					echo  number_format($amountPaid,2) ; $total += preg_match("/(COMPLETED|AUTHORISED)/",$row_rsOrders["Status"]) ? $amountPaid : 0; ?>
                    
                    </td>
                  <td class="top status status-<?php echo  $row_rsOrders["Status"]; ?>"><?php echo  $row_rsOrders["Status"]; ?></td>
                  <td nowrap class="top tools"><?php echo intval($row_rsOrders["totalDispatched"])." of ".$row_rsOrders["totalOrdered"]; ?></td>
                  <td nowrap class="tools">
                  
                  <div class="btn-group" role="group" >
                  
                  <a href="/products/admin/orders/orderDetails.php?VendorTxCode=<?php echo $row_rsOrders["VendorTxCode"]."&returnURL=".urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-sm btn-default btn-secondary"><i class="glyphicon glyphicon-pencil"></i></a>
                  
                  
                  
                  
                  <!-- Single button -->
<div class="btn-group">
  <button type="button" class="btn btn-sm btn-default btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="glyphicon glyphicon-cog"></i> Actions <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
 <?php  echo (isset($row_rsOrders['CustomerEMail'])) ? "<li><a href=\"/mail/admin/email/send.php?recipient=". $row_rsOrders['CustomerEMail']."&returnURL=". urlencode($_SERVER['REQUEST_URI'])."\" ><i class=\"glyphicon glyphicon-envelope\"></i> Email</a></li>" : ""; ?>
   <?php if($row_rsOrders["archive"]==0) { ?>
                    <li><a href="index.php?archiveID=<?php echo $row_rsOrders["VendorTxCode"]; ?>"  onClick="return confirm('Are you sure you want to archive this order?');"><i class="glyphicon glyphicon-save"></i> Archive</a></li>
                    <?php } ?>
  </ul>
</div><!-- end button group-->


                  
                  
                  </div><!-- end btn-group--></td>
                     
                </tr>
                
                <?php } while ($row_rsOrders = mysql_fetch_assoc($rsOrders)); ?><tr class="total">
                 <td class="lastupdated">&nbsp;</td>
                   <td class="purchased">&nbsp;</td>
                     <td class="customer">&nbsp;</td>
                    
                 
                 
               
                
                  <td class="supplier">&nbsp;</td>
                   <td class="product">&nbsp;</td>
                <td class="txcode">&nbsp;</td>
                <td class="txtype">&nbsp;</td>
                   <td class="text-right net">&nbsp;</td>
                  <td class="text-right gross"><strong> <?php  echo  number_format($total,2) ;  ?></strong></td>
                   <td class="status">&nbsp;</td>
                  <td colspan="4" class="tools">&nbsp;</td>
                 
                </tr></tbody>
            </table>
            <?php } // Show if recordset not empty ?>
          <?php echo createPagination($pageNum_rsOrders,$totalPages_rsOrders,"rsOrders");
		 
} // not CSV

else { // IS CSV 

$headers = array("Date".$supplieronly,"Date|hide","Order No.","Order No.".$customeronly,"UserID|hide","Supplier".$supplieronly,"Product Code","Product Name", "Option","Price|hide", "Cost Price|hide", "Quantity", "Type|hide","Currency".$customeronly, "Amount".$supplieronly,   "Shipping".$customeronly,"Paid".$customeronly, "Tax".$customeronly, "Firstname", "Surname",   "Company","Address1","Address2","Address3","Country","Postcode","Telephone","email", "Dispatched".$customeronly,  "Status".$customeronly,"Archived|hide","Promos|hide",  "Approval Required|hide", "ApprovedBy|hide", "Approved|hide", "Adwords|hide");
$filename=isset($filename) ? $filename : "Orders-YY-MM-DD";	

	exportCSV($headers, $rsOrders, $filename);
	
	?>
<?php } ?>