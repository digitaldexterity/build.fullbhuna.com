<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../members/includes/userfunctions.inc.php'); ?>
<?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7) {
	// security - only access if admin
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

if(isset($_GET['finduser'])) {
	$_GET['firstname'] = isset($_GET['firstname']) ? $_GET['firstname'] : "";
	$_GET['middlename'] = isset($_GET['middlename']) ? $_GET['middlename'] : "";
	$_GET['surname'] = isset($_GET['surname']) ? $_GET['surname'] : "";
		$_GET['email'] = isset($_GET['email']) ? $_GET['email'] : "";
	$_GET['dob'] = isset($_GET['dob']) ? $_GET['dob'] : "";
	$_GET['fuzzy'] = isset($_GET['fuzzy']) ? $_GET['fuzzy'] : false;
	$result =	findSimilarUsers($_GET['firstname'], $_GET['middlename'], $_GET['surname'], $_GET['email'], $_GET['dob'], $_GET['fuzzy']);
	
	
	
	
	if (mysql_num_rows($result) > 0) { // Show if recordset not empty 
	; ?>
	<div>Found the following  users. Please select  user:</div>
	<ul>
	<?php while($row = mysql_fetch_assoc($result)) { $dob = isset($row['dob']) ? date('d/m/Y', strtotime($row['dob'])) : ""; ?><li><a href="javascript:void(0);" class="selectuser warning<?php echo $row['warning']; ?>" userID="<?php echo $row['ID']; ?>" email="<?php echo $row['email']; ?>" mobile="<?php echo $row['mobile']; ?>" dob="<?php echo $dob ?>" fullname="<?php echo $row['firstname']."&nbsp;".$row['surname'];?>"><?php echo $row['ID'].": ".$row['firstname']." ".$row['middlename']." ".$row['surname']; echo (isset($row['email']) || $dob!=="") ?  "&nbsp;(".$row['email']." ".$dob.")" :""; ?></a> <a class="link_view" href="/members/admin/modify_user.php?userID=<?php echo $row['ID']; ?>">View</a></li><?php }  ?></ul>
	<span>OR </span>
	<?php } else {?>
	<span>
	No similar users found. </span>
	<?php }?>
	<span><a href="/members/admin/add_user.php?firstname=<?php echo urlencode($_GET['firstname']); ?>&surname=<?php echo urlencode($_GET['surname']); echo (isset($_GET['callerURL'])) ? "&returnURL=".urlencode($_GET['callerURL']) : ""; ?>">Add new user</a>.</span>
	<?php 
	mysql_free_result($result);
}

if(isset($_GET['newuser'])) {
	$createdbyID = isset($_GET['createdbyID']) ? intval($_GET['createdbyID']) : 0;
	$userID = createNewUser($_GET['firstname'],$_GET['surname'],"",0,0,0,$createdbyID,"",true,"","","","","", "", "",0,"",0,1,"","",0,"","","", "", 0,  0, "",$_GET['middlename']);
	echo $userID;
}

} // end can access ?>

