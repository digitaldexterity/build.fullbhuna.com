<?php 
if (!isset($_SESSION)) {
  session_start();
}

if(isset($_GET['value'])) { // data
	
		if($_GET['value'] == "on" || intval($_GET['value']) >0) { // checked
			$_SESSION['checkbox'][$_GET['checkboxID']] = $_GET['value'];
		} else { // unchecked
			if($_GET['checkboxID']!="0") {
				if(isset($_SESSION['checkbox'][$_GET['checkboxID']])) {
					unset($_SESSION['checkbox'][$_GET['checkboxID']]);
				}
			} else { 
				unset($_SESSION['checkbox']); // uncheck all on all pages
			}
		}
} // end data
?>