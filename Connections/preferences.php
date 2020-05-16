<?php 
/****************************************************************/
/* Preferences File 							   				*/
/* Change any of the values to suit your installation           */
/* NOTE: Depending on your permissions you may have to delete   */
/* and reupload new file                                        */
/****************************************************************/
//define("SAFE_MODE", true); // prevent database updates
//ini_set("send_mail",""); //seems to be required for IIS

ini_set('date.timezone', 'Europe/London');
if(isset($aquiescedb)) mysql_query("SET time_zone='Europe/London'"); // if required
//mysql_query("SET time_zone = '+".(date('Z')/3600).":00'"); // works with MySQL without timezone file
//ini_set("default_charset", "UTF-8"); // if required
//define("USE_SSL",true); // uncomment to use https for login
//mysql_query("SET sql_mode = ''"); // if required to turn off ONLY_FULL_GROUP_BY 
// used for CLI scripts ONLY!
define("HTTP_HOST","http://build.fullbhuna.com/"); // used  exclusively for backup system
$site_name = "Full Bhuna";
$admin_name = "Control Panel";
//define("DEVELOPER_NAME","Digital Dexterity Glasgow"); // add name of devloper and URL to appear in special web badge code
//define("DEVELOPER_URL","http:\/\/www.digitaldexterity.net/");
//define("DEBUG_EMAIL",""); // email debugs to this address

// GENERAL
define("MAX_UPLOAD",40000000);
// MEMBERS
define("DEFAULT_LOGIN_HOME", "/members/index.php");
//WYSIWYG defaults
define("WYSIWYG_DEFAULT_WIDTH", "100%");

// Gallery
$keep_original = true; // keep or discard original of images - default true but make false to save server space
if(!defined("GD_VERSION")) { define("GD_VERSION",  2); }
/* resizetype can be:
		 crop  [scale and crop to fit new aspect ratio]
		 fit [scale and squeeze to fit new aspect ratio] 
		 contain [scale to fit whole image into new size, keep aspect old ratio]
		 scalewidth [scale to fit width, default if only width given] 
		 scaleheight [scale to fit height, default if only height given]
		 
		 restriction:
		 
		 regionID - only scale for region/site
		 */
$image_sizes = array("thumb"=>array("width"=>200,"prefix"=>"t_"),
									"medium"=>array("width"=>600,"prefix"=>"m_"),
									"large"=>array("width"=>1200,"prefix"=>"l_"));

// shop
define("DEFAULT_PRODUCTS_COUNT",8);
define("SHOP_FRIENDLY_URL", "shop");



//Email
define("GROUP_EMAIL_SEND_COUNT",5);
$logemail = true; // delete if necessary - log all correspondence
//Tracker
$tracker = true; // make false if not using tracker - can add to individual pages also
define("TRACKER_EXCLUDE_IP","0.0.0.0"); // IP to be ignired by tracker - e.g. site owner
//define("TRACKER_INCLUDE_BOTS",true); // include search engines etc in visitor stats
define("TRACKER_PERIOD","2 MONTH"); // Length of time for stats to be collected
//define("GOOGLE_ANALYTICS_ID",""); // uncomment and add Google Analytics ID to track this 


//lightwindow
$use_lightwindow = true;
$lw_width = 600;
$lw_height = 500;

define("UPLOAD_ROOT",SITE_ROOT."Uploads".DIRECTORY_SEPARATOR);
//define("SAVE_MENU", true);
//define("LOGIN_TOKEN", true); // log in token required (default true)
//define("ALLOW_UPLOAD_ANY",true); // allow any file to be uploaded
//define("SUPRESS_MAIL",true); // stop sending email if defined - useful for testing



// Extra fields  can be added to users for specific sites an dcontained within an extra tab
// Uncomment example below
//define("EXTRA_USER_TAB","Another Tab");
//$extraUserFields = array(0=>array("name"=>"areacovered","type"=>"text"),1=>array("name"=>"availability","type"=>"text"));

//define("WEB_DEV_LOGIN_STRAP","<p class='text-right'>Web development by <a href='https://www.digitaldexterity.co.uk/' target='_blank' title='Digital Dexterity website designers'>Digital Dexterity</a> Glasgow</p>");


define("MYSQL_SALT","askdUYdsuad");


define("PRIVATE_KEY","enter_secret_string_here"); // Remember to use key change script if updated
?>