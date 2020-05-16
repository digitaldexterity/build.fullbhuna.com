<?php  

require_once('../../../Connections/aquiescedb.php'); ?><?php if(isset($_POST['authcode'])) {
	require_once('../includes/logtransaction.inc.php'); ?>
<?php /* on secure trading in order for notificatiosn to work - 

set up in ST admin a nofictaion to sen date least authcode */

$status = ($_POST['authcode']!=="DECLINED") ? "COMPLETED" : "DECLINED";
$amount = (isset($_POST['mainamount']) && $status=="COMPLETED") ? floatval($_POST['mainamount']) : 0;
logtransaction($_POST['orderreference'],"",$status, 0, "", "", 0, $amount);

} else {
	echo "No post";
}
?>