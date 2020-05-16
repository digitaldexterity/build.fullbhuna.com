<?php require_once('../../Connections/preferences.php'); ?>
<?php require_once('../includes/framework.inc.php'); ?>
<?php 
if (defined("FACEBOOK_ACCESS_TOKEN")) {
	$data  = get_web_page("https://graph.facebook.com/".FACEBOOK_PAGE_ID."/posts?access_token=".FACEBOOK_ACCESS_TOKEN);
	$data = json_decode($data, true);
	$posts = isset($_GET['posts']) ? intval($_GET['posts']) : 50;
	//print_r( $data); die();
	if(is_array($data) && isset($data["data"])) {
	foreach($data["data"] as $key => $value) {
		if(trim($value["message"])!="") {
		echo "<div class=\"facebook_post\">";
		echo "<abbr class=\"facebook_created_time timeago\" title=\"".date('d M Y H:i:s', strtotime($value["created_time"]))."\">".date('d M Y H:i:s', strtotime($value["created_time"]))."</abbr>";
		
		echo isset($value["story"]) ? "<div class=\"facebook_story\">".$value["story"]."</div>" : "";
		echo "<div class=\"facebook_message\">".$value["message"]."</div>";
		$id = explode("_",$value["id"]);
		echo "<a class=\"facebook_link\" href=\"https://www.facebook.com/".$id[0]."/posts/".$id[1]."\" target=\"_blank\">Read More</a>";
		echo "</div>";
		$posts --;
		}
		if($posts==0) exit;
	}
	}
} else {
	echo "Facebook access token required";
}

/*

1.- Create an App.

2.- Go to: https://developers.facebook.com/tools/explorer/

Select your new created app on the right top.
Select "Get App Token"
3.- Copy this "{ACCESS-TOKEN}" (is in the form: number|hash)

IMPORTANT: (This are not app_id|app_secret!!!)

4.- Query the URL with CURL:

https://graph.facebook.com/{PAGE-ID}}/posts?access_token={ACCESS-TOKEN}
(5).- Equivalent URL:

https://graph.facebook.com/{PAGE-ID}}/feed?access_token={ACCESS-TOKEN}
I´ve put all of this together in a very simple gist:

https://gist.github.com/biojazzard/740551af0455c528f8a9

Hope it helps.
*/

/* If you want to find the Facebook Page ID of some other page, then do try the “View Source” method for them. Search for page_id= tag in the code.

*/

/* CSS

.facebook_post {
	padding: 0 0 0 40px;
	background:url(/core/images/facebook-logo.png) no-repeat left top;
	margin: 0 0 10px 0;
}

*/

?>