
<?php $tablename = isset($_GET['tablename']) ? $_GET['tablename'] : "";
if(isset($_GET['columnnames']) && strlen($_GET['columnnames'])>4) {
	$column = explode(",",$_GET['columnnames']);
	foreach($column as $key => $value) {
		echo "<input type=\"hidden\" name=\"column[".$key."]\" value = \"".$value."\">\n";
	}
} else { // no set colums so add drop-downs
}
?><label>
  <input type="checkbox" name="ignorerow1" id="ignorerow1">
  Ignore first row (if column headers)</label>