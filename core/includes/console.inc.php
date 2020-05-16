<?php if(is_readable(SITE_ROOT."local/includes/console.inc.php")) {
	include(SITE_ROOT."local/includes/console.inc.php");
} else {
	?><div class="console container debug"><?php if(isset($_SESSION['debug'])) {
	echo "<div class=\"container\"><pre>";
	 echo (isset($console) && trim($console)!="") ? "CONSOLE:<br>".nl2br($console) : "";
	 echo "<br><br>DEBUG INFO:"; 
echo "<br>\$_SESSION:<br>"; ksort($_SESSION); print_r($_SESSION);
echo "<br>\$_COOKIE:<br>"; ksort($_COOKIE); print_r($_COOKIE);
echo "<br>\$_POST:<br>"; ksort($_POST); print_r($_POST);
echo "<br>\$_GET:<br>"; ksort($_GET); print_r($_GET); 
echo "<br>\$_SERVER:<br>"; ksort($_SERVER); print_r($_SERVER);
echo isset($sql) ? "<br>SQL:<br>".$sql : "";  
	echo "</pre></div>"; 
}	 ?></div>
<?php } ?>