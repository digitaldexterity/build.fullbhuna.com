<?php 
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsCategories = "SELECT ID, title FROM eventcategory WHERE active = 1 ORDER BY title ASC";
$rsCategories = mysql_query($query_rsCategories, $aquiescedb) or die(mysql_error());
$row_rsCategories = mysql_fetch_assoc($rsCategories);
$totalRows_rsCategories = mysql_num_rows($rsCategories);
?><script src="../../../core/scripts/date-picker/js/datepicker.js"></script>
<script src="/SpryAssets/SpryValidationTextField.js"></script><script src="/core/scripts/formUpload.js"></script>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet"  />
<style><!--
<?php if($totalRows_rsCategories==0) {
	echo ".category { display: none; }";
} ?>
<?php if(trim($row_rsEventPrefs['customfield1'])=="") {
	echo ".custom1 { display: none; }";
}

if(trim($row_rsEventPrefs['customfield2'])=="") {
	echo ".custom2 { display: none; }";
}

?>
--></style><form action="add_event.php" method="post" enctype="multipart/form-data"  role="form">
		  <table class="form-table">
		    <tr class="category form-group">
		      <th class="top text-right" scope="row"><label for="categoryID">Category:</label></th>
		      <td>
		        <select name="categoryID" id="categoryID" class="form-control">
		          <option value=""><?php echo isset($region['text_choose']) ? htmlentities($region['text_choose'], ENT_COMPAT, "UTF-8") : "Choose..." ?></option>
		          <?php
do {  
?>
		          <option value="<?php echo $row_rsCategories['ID']?>"><?php echo $row_rsCategories['title']?></option>
		          <?php
} while ($row_rsCategories = mysql_fetch_assoc($rsCategories));
  $rows = mysql_num_rows($rsCategories);
  if($rows > 0) {
      mysql_data_seek($rsCategories, 0);
	  $row_rsCategories = mysql_fetch_assoc($rsCategories);
  }
?>
                </select></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="eventtitle">Event Name:</label></th>
		      <td><span id="sprytextfield1">
		        <input name="eventtitle" type="text" id="eventtitle" size="50" maxlength="50"  class="form-control"/>
	          <span class="textfieldRequiredMsg">A value is required.</span></span></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="startdatetime">Starts:</label></th>
		      <td><input type="hidden" name="startdatetime" id="startdatetime" value="<?php $startdatetime = isset($_GET['startdatetime']) ? htmlentities($_GET['startdatetime'], ENT_COMPAT, "UTF-8"): date('Y-m-d H:i:s'); $setvalue= $startdatetime;  echo $setvalue;  $inputname = "startdatetime"; $time = true;?>"  class="highlight-days-67 split-date format-y-m-d divider-dash"/>
	          <?php require(SITE_ROOT.'core/includes/datetimeinput.inc.php'); ?></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="enddatetime">Ends:</label></th>
		      <td><input type="hidden" name="enddatetime" id="enddatetime" value="<?php $setvalue = date('Y-m-d H:i:s', strtotime($startdatetime." + 1 HOUR"));  $inputname = "enddatetime"; $time = true; ?>" class='highlight-days-67 split-date format-y-m-d divider-dash' /><?php require(SITE_ROOT.'core/includes/datetimeinput.inc.php'); ?></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="locationname">Where?</label></th>
		      <td>
		        <span id="sprytextfield2">
		        <input name="locationname" type="text" id="locationname" size="50" maxlength="50"  class="form-control" />
</span></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row">&nbsp;</th>
		      <td><span id="sprytextfield3">
		        <input name="address1" type="text" id="address1" size="50" maxlength="50"  class="form-control" />
</span></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row">&nbsp;</th>
		      <td><span id="sprytextfield4">
		        <input name="postcode" type="text" id="postcode" size="50" maxlength="50"  class="form-control" />
</span></td>
	        </tr>
		    <tr class="custom1 form-group">
		      <th class="top text-right" scope="row"><label for="customvalue1"><?php echo $row_rsEventPrefs['customfield1']; ?>:</label></th>
		      <td>
		        <input name="customvalue1" type="text" id="customvalue1" size="50" maxlength="100"  class="form-control" /></td>
	        </tr>
		    <tr class="custom2 form-group">
		      <th class="top text-right" scope="row"><label for="customvalue2"><?php echo $row_rsEventPrefs['customfield2']; ?>:</label></th>
		      <td><input name="customvalue2" type="text" id="customvalue2" size="50" maxlength="100"  class="form-control" /></td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="eventdetails">Details:</label></th>
		      <td>
		        
		        <textarea name="eventdetails" id="eventdetails" cols="45" rows="5"  class="form-control"></textarea>
	          </td>
	        </tr>
		    <tr class="form-group">
		      <th class="top text-right" scope="row"><label for="filename">Image (optional):</label></th>
		      <td>
		        <input type="file" name="filename" id="filename" class="form-control"></td>
		      </tr>
		    <tr>
		      <th scope="row">&nbsp;</th>
		      <td>
	          <button type="submit" >Add event</button>
	          <input name="createddatetime" type="hidden" id="createddatetime" value="<?php echo date('Y-m-d H:i:s'); ?>" />
	          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo $row_rsLoggedIn['ID']; ?>" />
	          <input type="hidden" name="directoryID" id="directoryID" value="<?php echo isset($_GET['directoryID']) ? htmlentities($_GET['directoryID']) : ""; ?>" />
	          <input type="hidden" name="eventgroupID" id="eventgroupID" />
	          <input name="statusID" type="hidden" id="statusID" value="1" />
	          <input type="hidden" name="imageURL" id="imageURL"></td>
	        </tr>
	      </table>
		  <input type="hidden" name="MM_insert" value="form1" />
		  </form>
		  <script>
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {hint:"Place name", isRequired:false});
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {hint:"Address", isRequired:false});
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {hint:"Postcode", isRequired:false});
//-->
          </script>
          <?php 
mysql_free_result($rsCategories);
?>