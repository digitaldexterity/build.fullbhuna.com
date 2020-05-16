<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../includes/documentfunctions.inc.php'); ?>
<?php  if(isset($_POST['search'])) {
	echo "<div>";
	$docs = getDocuments(0, $_POST['search'], 20);
	if(is_object($docs) || is_resource($docs)) {
		while($doc = mysql_fetch_assoc($docs)) {
			echo "<a href=\"/documents/view.php?documentID=".$doc['ID']."&categoryID=".$doc['categoryID']."\" target=\"_blank\" rel=\"noopener\">".$doc['documentname']."</a>";
		}
	
	} else {
		echo "<div>No results</div>";
	}
	echo "</div>";
} ?>