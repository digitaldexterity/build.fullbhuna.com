<?php require_once('../../../Connections/aquiescedb.php'); ?><?php require_once('../includes/logtransaction.inc.php'); ?>
<?php 
$message = var_export($_GET, true);
$amount = 0;
//mail("paul@digdex.co.uk","PAYTRAIL",$message);
if(isset($_GET['ORDER_NUMBER'])) {
	if(isset($_GET['PAID'])) { // success
		logtransaction($_GET['ORDER_NUMBER'],"","SUCCESS");
		//mail("paul@digdex.co.uk","PAYTRAIL","SUCCESS");
	} else { // fail
		logtransaction($_GET['ORDER_NUMBER'],"","FAILED");
		//mail("paul@digdex.co.uk","PAYTRAIL","FAILED");
	}
}

?>