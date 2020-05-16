<?php global $row_rsProductPrefs, $row_rsProduct; ?>
<?php $varProductID_rsTabs = "-1";
if (isset($row_rsProduct['ID'])) {
  $varProductID_rsTabs = $row_rsProduct['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsTabs = sprintf("SELECT productdetails.* FROM productdetails WHERE productdetails.productID = %s AND productdetails.statusID = 1 ORDER BY ordernum, ID", GetSQLValueString($varProductID_rsTabs, "int"));
$rsTabs = mysql_query($query_rsTabs, $aquiescedb) or die(mysql_error());
$row_rsTabs = mysql_fetch_assoc($rsTabs);
$totalRows_rsTabs = mysql_num_rows($rsTabs);



$description = trim(str_replace("<p></p>","",$row_rsProduct['description'])); ?>
<!-- tabs will only show if more than one (CSS) -->
<?php  $useTabs = ($totalRows_rsTabs+$row_rsProductPrefs['commondetails']+$row_rsProductPrefs['reviewstab'])>0 ? true : false;
if($useTabs) { // only use tabs if more than one details - css below ?>
<script src="/SpryAssets/SpryTabbedPanels.js"></script>
<link href="/SpryAssets/SpryTabbedPanels.css" rel="stylesheet"  media="screen, projection"/>
<?php } else { echo "<style> ul.TabbedPanelsTabGroup { display:none; } </style>"; } ?>

<div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <?php if( $description!="") { ?>
    <li class="TabbedPanelsTab description" tabindex="0"><?php echo htmlentities($row_rsProductPrefs['maintabtext'], ENT_COMPAT, "UTF-8"); ?></li>
    <?php } ?>
    <?php if($totalRows_rsTabs>0) { 
		$defaultTab = 0; $tab = 0; do { $tab++; echo
        "<li class=\"TabbedPanelsTab\" tabindex=\"".$tab."\">".htmlentities($row_rsTabs['tabtitle'], ENT_COMPAT, "UTF-8")."</li>";
		if($row_rsTabs['defaulttab']==1) { $defaultTab = $tab; } 
         } while ($row_rsTabs = mysql_fetch_assoc($rsTabs)); } ?>
    <li class="TabbedPanelsTab productData"  tabindex="<?php echo $tab+1; ?>">Product Data</li>
    <?php if($row_rsProductPrefs['commondetails']==1) { ?>
    <li class="TabbedPanelsTab commondetails"><?php echo htmlentities($row_rsProductPrefs['commondetailstitle'], ENT_COMPAT, "UTF-8"); ?></li>
    <?php } ?>
    <?php if($row_rsProductPrefs['reviewstab']==1) { ?>
    <li class="TabbedPanelsTab reviews">Reviews</li>
    <?php } ?>
   
  </ul>
  <div class="TabbedPanelsContentGroup">
    <?php if( $description!="") { ?>
    <div class="TabbedPanelsContent description">
      <div itemprop="description" ><?php echo $description; ?></div>
    </div><!-- end description tab -->
    <?php } ?>
    <?php if($totalRows_rsTabs>0) { 
				  mysql_data_seek($rsTabs,0);$row_rsTabs = mysql_fetch_assoc($rsTabs);
				  do { ?>
    <div class="TabbedPanelsContent"><?php echo $row_rsTabs['tabtext']; ?></div><!-- end multi tab -->
    <?php } while ($row_rsTabs = mysql_fetch_assoc($rsTabs)); } ?>
    <div class="TabbedPanelsContent productData">
    <?php require_once('productdata.inc.php'); ?>
  </div><!-- end data tab -->
  <?php if($row_rsProductPrefs['commondetails']==1) { ?>
  <div class="TabbedPanelsContent commondetails"><?php echo $row_rsProductPrefs['commondetailstext']; ?></div><!-- end common tab -->
  <?php } ?>
  <?php if($row_rsProductPrefs['reviewstab']==1) { ?>
  <div class="TabbedPanelsContent reviews">
    <?php  require('review.inc.php'); ?>
  </div><!-- end reviews tab -->
  <?php } ?>
  
</div><!-- end  tab group -->
</div><!-- end tabs -->
<?php  mysql_free_result($rsTabs); if($useTabs) {  ?><script>
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1", { defaultTab:<?php echo ($totalRows_rsTabs>0) ? $defaultTab : "0"; ?> });//-->
    </script><?php } ?>
