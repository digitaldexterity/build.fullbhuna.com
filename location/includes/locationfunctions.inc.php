<?php 
function createLocation($public=0,$categoryID=0,$locationname="",$description="",$address1="",$address2="",$address3="",$address4="",$address5="",$postcode="",$telephone1="", $telephone2="", $telephone3="", $fax="", $imageURL="", $mapURL="", $locationURL="",$latitude="", $longitude="",$createdbyID=0,$locationemail = "",$userID="",$countryID="") {
	global $database_aquiescedb, $aquiescedb;
		$public = ($public ==1) ? 1 : 0; // prevent from being null
	mysql_select_db($database_aquiescedb, $aquiescedb);
	 $insert = "INSERT INTO location (`public`, categoryID, userID, locationname, `description`, address1, address2, address3, address4, address5, postcode, telephone1, telephone2, telephone3, fax, imageURL, mapURL, locationURL, locationemail, latitude, longitude, countryID, createdbyID, createddatetime) VALUES (".
                       GetSQLValueString($public, "int").",".
                       GetSQLValueString($categoryID, "int").",".
					    GetSQLValueString($userID, "int").",".
                       GetSQLValueString($locationname, "text").",".
                       GetSQLValueString($description, "text").",".
                       GetSQLValueString($address1, "text").",".
                       GetSQLValueString($address2, "text").",".
                       GetSQLValueString($address3, "text").",".
                       GetSQLValueString($address4, "text").",".
                       GetSQLValueString($address5, "text").",".
                       GetSQLValueString($postcode, "text").",".
                       GetSQLValueString($telephone1, "text").",".
                       GetSQLValueString($telephone2, "text").",".
                       GetSQLValueString($telephone3, "text").",".
                       GetSQLValueString($fax, "text").",".
                       GetSQLValueString($imageURL, "text").",".
                       GetSQLValueString($mapURL, "text").",".
                       GetSQLValueString($locationURL, "text").",".
					   GetSQLValueString($locationemail, "text").",".
                       GetSQLValueString($latitude, "double").",".
                       GetSQLValueString($longitude, "double").",".
					   GetSQLValueString($countryID, "int").",".
                       GetSQLValueString($createdbyID, "int").",NOW())";
					   $errorsql = (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) ? ":<br><br>".$insert : ""; // only have full select statement if webadmin
					   $result=mysql_query($insert, $aquiescedb) or die(mysql_error().$errorsql);
					   return mysql_insert_id();
}

?>