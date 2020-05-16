<?php // REQUIRES adminAccess.inc.php in header
if(isset($totalRegions) && $totalRegions>0 && isset($adminUser['usertypeID']) && ($adminUser['usertypeID']>=9 ||  $adminUser['regionID'] ==0)) { ?>
<form method="post" class="region form-inline" id="regionForm" ><label>Site:
<select onchange="this.form.submit();" name="setregionID" class="form-control">
<?php while($adminRegion = mysql_fetch_assoc($rsAdminRegions)) { ?>
<option value="<?php echo $adminRegion['ID']; ?>" <?php if($adminRegion['ID'] == $regionID) { echo "selected = \"selected\""; } ?>><?php echo $adminRegion['title']; ?></option>
<?php } ?>
</select></label>
</form>
<?php } 
?>
