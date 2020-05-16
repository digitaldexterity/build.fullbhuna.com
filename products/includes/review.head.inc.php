<?php require_once(SITE_ROOT.'core/includes/framework.inc.php'); ?>
<?php 
// must go after previous selects

$regionID = isset($regionID) ? intval($regionID) : 1;

if(isset($_POST['addreview'])) {
	if(md5(PRIVATE_KEY.$_POST['productID'].$_POST['userID'].getClientIP()) !=$_POST['token']) {
		$error = "Sorry, there was an error posting your submission. Please try again ";
	} else if(isset($row_rsProductPrefs['commentscaptcha']) && $row_rsProductPrefs['commentscaptcha']==1 && md5(strtolower($_POST['captcha_answer'])) != $_SESSION['captcha'])	{ // security image incorrect
		$error = "Sorry, you have typed the security letters incorrectly. Please try again. ";
	} else if (strpos($_POST['message'],"http")!==false) {
		$error = "Sorry, submissions cannot contain web addresses. ";
	} else if (trim($_POST['message'])=="") {
		$error = "Please enter a review. ";
	} else if (trim($_POST['fullname'])=="") {
		$error = "Please enter your name. ";
	} else { // no errors
		require_once(SITE_ROOT.'members/includes/userfunctions.inc.php');
		require_once(SITE_ROOT.'location/includes/locationfunctions.inc.php');
		require_once(SITE_ROOT.'mail/includes/sendmail.inc.php');		
		if(!isset($_POST['userID']) && intval($_POST['userID'])>0) {
			$userID =intval($_POST['userID']);
		} else {
			$names = explode(" ",$_POST['fullname'],2);
			$firstname = isset($names[0]) ? $names[0] : "Anonymous";
			$surname = isset($names[1]) ? $names[1] : "";
			$userID = createNewUser($firstname,$surname);
		} 
		if($userID>0) {
		  	if(isset($_POST['locationname'])) {
			  	$locationID = createLocation(1,0,$_POST['locationname']);
			  	addUserToLocation($userID, $locationID);	  
		  	}	
			$select = "SELECT ID FROM forumtopic WHERE productID = ".intval($_POST['productID']);
			mysql_select_db($database_aquiescedb, $aquiescedb);
			$result = mysql_query($select, $aquiescedb) or die(mysql_error());	
			if(mysql_num_rows($result)==0) {
				$insert = "INSERT INTO forumtopic (topic, productID, regionID) VALUES (".GetSQLValueString($_POST['productname'], "text").", ".GetSQLValueString($_POST['productID'], "int").",".$regionID.")";
				mysql_query($insert, $aquiescedb) or die(mysql_error());
				$topicID = mysql_insert_id();
			} else {
				$row = mysql_fetch_assoc($result);
				$topicID = 	$row['ID'];				
			}
			$insert = "INSERT INTO forumcomment (topicID,  postedbyID, posteddatetime, statusID, message, IPaddress, emailme, rating) VALUES (".$topicID.",".$userID.",'".date('Y-m-d H:i:s')."', 0, ".GetSQLValueString($_POST['message'], "text").",".GetSQLValueString($_POST['IPaddress'], "text").", 0, ".GetSQLValueString($_POST['rating'], "int").")";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
			$to = $row_rsPreferences['contactemail'];
			$subject = "Product review submitted for ".$_POST['productname'];
			$message = "The following product review has been submitted for ".$_POST['productname']."\n\n";
			$message  .= $_POST['fullname']." says:\n\n";
			$message  .= $_POST['message']."\n\n";
			$message .= "All reviews require approval before going live. Click on the link below to approve:\n\n";
			$message .= getProtocol()."://".$_SERVER['HTTP_HOST']."/forum/admin/edit_topic.php?topicID=".$topicID;			
			sendMail($to, $subject, $message);
			$msg = "Thank you. Your comment has been received.";
			if(isset($row_rsProductPrefs['reviewemailtemplateID']) && $row_rsProductPrefs['reviewemailtemplateID']>0 && isset($_POST['email'])) {
				$merge= array("productname"=>$_POST['productname']);
				sendMail($_POST['email'],"","","","","","",true,"","","",$row_rsProductPrefs['reviewemailtemplateID'],false,$merge);
			}
		}
	}	
}

$maxRows_rsReviews = 50;
$pageNum_rsReviews = 0;
if (isset($_GET['pageNum_rsReviews'])) {
  $pageNum_rsReviews = $_GET['pageNum_rsReviews'];
}
$startRow_rsReviews = $pageNum_rsReviews * $maxRows_rsReviews;

$varProductID_rsReviews = "-1";
if (isset($row_rsProduct['ID'])) {
  $varProductID_rsReviews = $row_rsProduct['ID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsReviews = sprintf("SELECT forumtopic.ID,   poster.firstname AS posterfirstname,  poster.surname AS postersurname, forumcomment.message, forumcomment.rating, forumtopic.statusID, forumcomment.statusID AS commentstatusID, location.locationname FROM forumtopic  LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID AND forumcomment.statusID = 1) LEFT JOIN users AS poster ON (forumcomment.postedbyID = poster.ID) LEFT JOIN location ON (poster.defaultaddressID = location.ID) WHERE forumtopic.productID  = %s AND forumtopic.statusID = 1 AND forumcomment.statusID = 1 ORDER BY forumcomment.posteddatetime", GetSQLValueString($varProductID_rsReviews, "int"));
$query_limit_rsReviews = sprintf("%s LIMIT %d, %d", $query_rsReviews, $startRow_rsReviews, $maxRows_rsReviews);
$rsReviews = mysql_query($query_limit_rsReviews, $aquiescedb) or die(mysql_error());
$row_rsReviews = mysql_fetch_assoc($rsReviews);

if (isset($_GET['totalRows_rsReviews'])) {
  $totalRows_rsReviews = $_GET['totalRows_rsReviews'];
} else {
  $all_rsReviews = mysql_query($query_rsReviews);
  $totalRows_rsReviews = mysql_num_rows($all_rsReviews);
}
$totalPages_rsReviews = ceil($totalRows_rsReviews/$maxRows_rsReviews)-1;

$select = "SELECT COUNT(forumcomment.rating) AS ratingCount, AVG(forumcomment.rating) AS avgrating FROM product  LEFT JOIN forumtopic ON (forumtopic.productID = product.ID) LEFT JOIN forumcomment ON (forumcomment.topicID = forumtopic.ID  AND forumcomment.statusID=1) WHERE  product.ID = ".$varProductID_rsReviews." GROUP BY product.ID";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$avgrating = mysql_fetch_assoc($result);

// **** REMOVED GROUP BY HERE ***/
 ?>