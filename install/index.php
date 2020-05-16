<?php 
if (is_readable("../Connections/aquiescedb.php")) { 
// already installed - for security not overridable
	header("location: /install/update/"); exit;
} ?>
<?php require("includes/install.inc.php"); ?>
<?php 
if(phpversion()>="5.3.3"  || isset($_POST['ignore_warnings'])) {
error_reporting(0);
@ini_set("display_errors", 0);

if (!isset($_SESSION)) {
  session_start();
}
if (function_exists("gd_info") || isset($_POST['ignore_warnings'])) { // GD 2 or above
	if (isset($_POST['next'])) { // posted
		if($_POST['password'] != $_POST['password2'] || strlen($_POST['password'])<7) {
			$submit_error = "Please make sure both passwords are the same and there are a minimum of 7 characters";
		} else { // no error		
			$site_name = isset($_POST['site_name']) ? $_POST['site_name'] : "Full Bhuna";
			
			$site_root = site_root(); // remove "/install from directory path 
			
			if(createDirectories($site_root)) {
			
				$filename = $site_root."Connections".DIRECTORY_SEPARATOR."preferences.php";
				$handle = fopen($filename,"w");
				$max_upload = ini_get('post_max_size') > ini_get('upload_max_filesize') ? ini_get('upload_max_filesize') : ini_get('post_max_size');
				$max_upload = str_replace("M","000000",$max_upload); // make milions instread of M
				if (!($gdv = gdVersion())) {
					$gdv = 0;
				}
				$contents = 
"<?php 
/****************************************************************/
/* Preferences File 							   				*/
/* Change any of the values to suit your installation           */
/* NOTE: Depending on your permissions you may have to delete   */
/* and reupload new file                                        */
/****************************************************************/
//define(\"SAFE_MODE\", true); // prevent database updates
//ini_set(\"send_mail\",\"\"); //seems to be required for IIS
";
/*$contents .= function_exists("date_default_timezone_set") ? "date_default_timezone_set(\"Europe/London\"); " : "";

TJIS NNEDS TO BE MOVED TO aquiesce db as it doesn't seem to work on incldue within inclde */
$contents .= "
ini_set('date.timezone', 'Europe/London');
if(isset(\$aquiescedb)) {
	//mysql_query(\"SET time_zone='Europe/London'\"); // if required
	mysql_query(\"SET time_zone = '+\".(date('Z')/3600).\":00'\"); // works with MySQL without timezone file
}
//ini_set(\"default_charset\", \"UTF-8\"); // if required
//define(\"USE_SSL\",true); // uncomment to use https for login
//mysql_query(\"SET sql_mode = ''\"); // if required to turn off ONLY_FULL_GROUP_BY 
// used for CLI scripts ONLY!
define(\"HTTP_HOST\",\"http://".$_SERVER['HTTP_HOST']."/\"); // used  exclusively for backup system
\$site_name = \"".$site_name."\";
\$admin_name = \"Control Panel\";
//define(\"DEVELOPER_NAME\",\"Digital Dexterity Glasgow\"); // add name of devloper and URL to appear in special web badge code
//define(\"DEVELOPER_URL\",\"http:\/\/www.digitaldexterity.net/\");
//define(\"DEBUG_EMAIL\",\"\"); // email debugs to this address

// GENERAL
define(\"MAX_UPLOAD\",".$max_upload.");
// MEMBERS
define(\"DEFAULT_LOGIN_HOME\", \"/members/index.php\");
//WYSIWYG defaults
define(\"WYSIWYG_DEFAULT_WIDTH\", \"100%\");

// Gallery
\$keep_original = true; // keep or discard original of images - default true but make false to save server space
if(!defined(\"GD_VERSION\")) { define(\"GD_VERSION\",  ".$gdv."); }
/* resizetype can be:
		 crop  [scale and crop to fit new aspect ratio]
		 fit [scale and squeeze to fit new aspect ratio] 
		 contain [scale to fit whole image into new size, keep aspect old ratio]
		 scalewidth [scale to fit width, default if only width given] 
		 scaleheight [scale to fit height, default if only height given]
		 
		 restriction:
		 
		 regionID - only scale for region/site
		 */
\$image_sizes = array(\"thumb\"=>array(\"width\"=>200,\"prefix\"=>\"t_\"),
									\"medium\"=>array(\"width\"=>600,\"prefix\"=>\"m_\"),
									\"large\"=>array(\"width\"=>1200,\"prefix\"=>\"l_\"));

// shop
define(\"DEFAULT_PRODUCTS_COUNT\",8);
define(\"SHOP_FRIENDLY_URL\", \"shop\");



//Email
define(\"GROUP_EMAIL_SEND_COUNT\",5);
\$logemail = true; // delete if necessary - log all correspondence
//Tracker
\$tracker = true; // make false if not using tracker - can add to individual pages also
define(\"TRACKER_EXCLUDE_IP\",\"0.0.0.0\"); // IP to be ignired by tracker - e.g. site owner
//define(\"TRACKER_INCLUDE_BOTS\",true); // include search engines etc in visitor stats
define(\"TRACKER_PERIOD\",\"2 MONTH\"); // Length of time for stats to be collected
//define(\"GOOGLE_ANALYTICS_ID\",\"\"); // uncomment and add Google Analytics ID to track this 


//lightwindow
\$use_lightwindow = true;
\$lw_width = 600;
\$lw_height = 500;

define(\"UPLOAD_ROOT\",SITE_ROOT.\"Uploads\".DIRECTORY_SEPARATOR);
//define(\"SAVE_MENU\", true);
//define(\"LOGIN_TOKEN\", true); // log in token required (default true)
//define(\"ALLOW_UPLOAD_ANY\",true); // allow any file to be uploaded
//define(\"SUPRESS_MAIL\",true); // stop sending email if defined - useful for testing



// Extra fields  can be added to users for specific sites an dcontained within an extra tab
// Uncomment example below
//define(\"EXTRA_USER_TAB\",\"Another Tab\");
//\$extraUserFields = array(0=>array(\"name\"=>\"areacovered\",\"type\"=>\"text\"),1=>array(\"name\"=>\"availability\",\"type\"=>\"text\"));

//define(\"WEB_DEV_LOGIN_STRAP\",\"<p class='text-right'>Web development by <a href='https://www.digitaldexterity.co.uk/' target='_blank' title='Digital Dexterity website designers'>Digital Dexterity</a> Glasgow</p>\");



define(\"PRIVATE_KEY\",\"enter_secret_string_here\"); // Remember to use key change script if updated
?>";
			
			if (fwrite($handle, $contents)) {
				fclose($handle);
				$filename = $site_root."Connections".DIRECTORY_SEPARATOR."index.php";
				$handle = fopen($filename,"w");
				fwrite($handle, " ");
				fclose($handle);
				chmod($filename, 0664);
				
				$filename = $site_root."Uploads".DIRECTORY_SEPARATOR."index.php";
				$handle = fopen($filename,"w");
				if (fwrite($handle, " ")) { // write to uploads OK
					fclose($handle);
					chmod($filename, 0664);
					$filename = $site_root."Uploads".DIRECTORY_SEPARATOR.".htaccess";
					$handle = fopen($filename,"w");
					fwrite($handle, "AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi\nOptions -ExecCGI");
					fclose($handle);
					chmod($filename, 0664);
					$handle = fopen($filename,"r");
					$_SESSION['password'] = $_POST['password'];
					$_SESSION['email'] = $_POST['email'];
					$_SESSION['site_name'] = $_POST['site_name'];
					header("location: index2.php");
					exit;		
				} // end write to uploads OK
				else {
				$submit_error = "There was a problem testing your folder permissions - the server needs to be able to write to these folders. Please make sure the Uploads and Connections directories and all directories within them are  WRITEABLE. On UNIX servers you can do this manually using FTP CHMOD 775. On Windows check that the server security settings for the folders allow your IIS server to Write to them. If in doubt, please speak to your system administrator.\n\n".$site_root."Uploads".DIRECTORY_SEPARATOR."index.php";
				} // end uploads error
			} // end connections write OK
			else
			{ $submit_error = "There was a problem testing your folder permissions - the server needs to be able to write to these folders. Please make sure the Uploads and Connections directories and all directories within them are  WRITEABLE. On UNIX servers you can do this manually using FTP CHMOD 775. On Windows check that the server security settings for the folders allow your IIS server to Write to them. If in doubt, please speak to your system administrator.\n\n".$site_root."Connections".DIRECTORY_SEPARATOR."index.php";
			} // connections write not OK
			} // end folder exists 
			else {$submit_error = "Writable folders do not exist and cannot create them.\n\r\n".$site_root."Connections".DIRECTORY_SEPARATOR."\n\r\n".$site_root."Uploads".DIRECTORY_SEPARATOR;
			
			} // folder doesn't exist
		} // no error
	} // end posted
} // end GD 2 or above
else { // end not GD
	$submit_error = "In order to use the full functionality of Full Bhuna, you must have GD LIbrary 2 installed on your server (see your PHP documentation). It is highly recommended that you install this first.<br /><br /> You can override this warning by checking \"ignore warnings\" below.";
} // end not GD
} // php version no good
else {
	
	$submit_error = "PHP version 5.3.3 is recommended for this version of Full Bhuna. Some features may not work with previous versions of PHP.<br /><br /> You can override this warning by checking \"ignore warnings\" below.";
	
} 


// server specific stuff

function gdVersion($user_ver = 0)
{
    if (! extension_loaded('gd')) { return; }
    static $gd_ver = 0;
    // Just accept the specified setting if it's 1.
    if ($user_ver == 1) { $gd_ver = 1; return 1; }
    // Use the static variable if function was called previously.
    if ($user_ver !=2 && $gd_ver > 0 ) { return $gd_ver; }
    // Use the gd_info() function if possible.
    if (function_exists('gd_info')) {
        $ver_info = gd_info();
        preg_match('/\d/', $ver_info['GD Version'], $match);
        $gd_ver = $match[0];
        return $match[0];
    }
    // If phpinfo() is disabled use a specified / fail-safe choice...
    if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
        if ($user_ver == 2) {
            $gd_ver = 2;
            return 2;
        } else {
            $gd_ver = 1;
            return 1;
        }
    }
    // ...otherwise use phpinfo().
    ob_start();
    phpinfo(8);
    $info = ob_get_contents();
    ob_end_clean();
    $info = stristr($info, 'gd version');
    preg_match('/\d/', $info, $match);
    $gd_ver = $match[0];
    return $match[0];
} // End gdVersion()

// Usage:




?><!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna - Step 1 - Upload Files</title>
<!-- InstanceEndEditable -->
<?php require_once('includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!-- 
.siteadmin {
	display:none;
}
--></style>
<!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
      <p>&nbsp;</p>
  <h1>Installing Full Bhuna</h1>
      <p>Thank you for choosing Full Bhuna... you've made a great choice! To install, follow the following steps CAREFULLY!</p>
  <h1>Step 1 - Uploading the files</h1>
  <?php if (isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
  <?php } ?>
  <p>Well you've already done most of Step 1, you've uploaded all the files to your server.  </p>
  <form action="index.php" method="post" id="form1"><table class="form-table">
  <tr>
    <th scope="row"  class="form-group">  <label for="site_name">Now, choose a name for your site:</label></th>
    <td><p>
  
      <input name="site_name" type="text"  id="site_name" value="<?php echo @$_POST['site_name']; ?>" size="50" maxlength="50"  class="form-control" />
    
  </p>
</td>
  </tr><tr>
    <th scope="row">Your  username is:</th>
    <td>wadmin</td>
  </tr>
  <tr class="form-group">
    <th scope="row">Your email:</th>
    <td><input name="email" type="email"  id="email" value="<?php echo @$_POST['email']; ?>" size="50" maxlength="150" class="form-control" /></td>
  </tr>
  
  <tr class="form-group">
    <th scope="row"><label for="password">Choose a password:</label></th>
    <td>
      <input name="password" type="password" id="password" size="50" maxlength="50" class="form-control" /></td>
  </tr>
  <tr class="form-group">
    <th scope="row"><label for="password2">Repeat password:</label></th>
    <td><input name="password2" type="password" id="password2" size="50" maxlength="50"  class="form-control"/></td>
  </tr>
  </table>
  
     
        <p>
          <button name="button" type="submit" class="btn btn-primary" id="button"  onclick="if(document.getElementById('password').value != document.getElementById('password2').value) { alert('The two entered passwords do not match'); return false; }">Proceed to Step 2</button>
          <input name="next" type="hidden" id="next" value="true" />
    </p>
        <p>
          <input type="checkbox" name="ignore_warnings" id="ignore_warnings" value="1" />
          Ignore warnings
        </p>
</form>
 
<!-- InstanceEndEditable --></div>
</main>
<?php require_once('includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>