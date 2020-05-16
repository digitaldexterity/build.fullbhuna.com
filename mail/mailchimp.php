<?php require_once('../Connections/aquiescedb.php'); ?>
<?php // page will automatically add to mailchimp list (from API detais in mail prefs) and return user to page they came from

$regionID = (isset($regionID) && $regionID>0) ? intval($regionID) : 1;
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMailPrefs = "SELECT * FROM mailprefs WHERE ID = ".intval($regionID);
$rsMailPrefs = mysql_query($query_rsMailPrefs, $aquiescedb) or die(mysql_error());
$row_rsMailPrefs = mysql_fetch_assoc($rsMailPrefs);
$totalRows_rsMailPrefs = mysql_num_rows($rsMailPrefs);



$email = isset($_GET['email']) ? $_GET['email'] : "";
$firstname = isset($_GET['firstname']) ? $_GET['firstname'] : "";
$surname = isset($_GET['surname']) ? $_GET['surname'] : "";
$data = array('email' => $email,'status' => 'subscribed','firstname' => $firstname,'lastname'  => $surname);

if($email!="" && $row_rsMailPrefs['mailchimpapi']!="" && $row_rsMailPrefs['mailchimplistid']!="") {

syncMailchimp($data);

if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
	$url = $_SERVER['HTTP_REFERER'];
	$url .= strpos($url,"?")==false ? "?" : "&";
	$url .= "success=".urlencode("Thank you, ".$email." has been added to our subscriber list.");
	header("location: ".$url); exit;
} else {

die( "Done");
}
echo "Not all data provided";
}


function syncMailchimp($data) {
	global $row_rsMailPrefs;
    $apiKey = $row_rsMailPrefs['mailchimpapi'];
    $listId = $row_rsMailPrefs['mailchimplistid'];

    $memberId = md5(strtolower($data['email']));
    $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

    $json = json_encode(array(
        'email_address' => $data['email'],
        'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
        'merge_fields'  => array(
            'FNAME'     => $data['firstname'],
            'LNAME'     => $data['lastname']
        )
    ));

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                                                                 

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode;
}

?>