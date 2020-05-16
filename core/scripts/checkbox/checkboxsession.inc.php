<?php
if (!isset($_SESSION)) {
  session_start();
}
if(!isset($_SESSION['checkboxpage']) || $_SESSION['checkboxpage'] != $_SERVER['PHP_SELF']) {
	// previous session from another page or no session
	unset($_SESSION['checkbox']);
	$_SESSION['checkboxpage'] = $_SERVER['PHP_SELF'];
}
?>
<script>
<?php if(isset($_SESSION['checkbox']) && count($_SESSION['checkbox'])>0) { 
$array = "";
foreach($_SESSION['checkbox'] as $key => $value) {
	$array .= "\"".$key."\",";
}?>
var preChecked = new Array(<?php echo trim($array,","); ?>);
<?php } ?>
var useCheckboxSession = true;

</script>