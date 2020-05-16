<?php 

//define("DEBUG_EMAIL", ""); // send errors to theis email
//define("DEBUG", true); // turn on all debugging
if(defined("DEBUG")  || isset($_SESSION["debug"])) {
	$_SESSION["log"] = isset($_SESSION["log"]) ? $_SESSION["log"] : ""; // start session log if required
	error_reporting(32767); // 0 = display no errors, 32767 display all
	@ini_set("display_errors", 1); // 0 = don't display none, 1 = display/
} else {
	error_reporting(0); 
	@ini_set("display_errors", 0); 
}


if (is_readable("../Connections/aquiescedb.php")) {
	die("Full Bhuna database appears to be already installed, you may wish to \"upgrade\" instead. Alternatively, please delete the contents of Connections folder on the server and install again.");
} ?>
<?php require("includes/install.inc.php"); ?>
<?php

$site_root = site_root();


if (!isset($_SESSION)) {
  session_start();
}

if (isset($_POST['next'])) {
	
	$hostname_aquiescedb = (isset($_POST['host']) && $_POST['host']!="") ? $_POST['host'] : "localhost";
	$database_aquiescedb = (isset($_POST['database']) && $_POST['database']!="") ? $_POST['database'] : "aquiescedb";
	$username_aquiescedb = (isset($_POST['username']) && $_POST['username']!="") ? $_POST['username'] : "aquiescedbuser";
	$password_aquiescedb = (isset($_POST['password']) && $_POST['password']!="") ? $_POST['password'] : "password";
	
	// test connection
	
	if($aquiescedb = mysql_pconnect($hostname_aquiescedb, $username_aquiescedb, $password_aquiescedb)) { // connection OK to server
		if(mysql_select_db($database_aquiescedb, $aquiescedb)) { // connect OK to database
			$mysql_get_server_info = explode("-",mysql_get_server_info());
			if(floatval($mysql_get_server_info[0]) >=4 || isset($_POST['ignore_warnings'])) { // correct version
			
				$prefs_path = str_replace("\\","\\\\","Connections".DIRECTORY_SEPARATOR."preferences.php");
				
				
				
				$contents = 
"<?php 
// SECURITY POLICIES update or remove as required
// Prevents javascript XSS attacks aimed to steal the session ID MUST go before session start
ini_set('session.cookie_httponly', 1);
// Prevent Session ID from being passed through  URLs
ini_set('session.use_only_cookies', 1);
// Uses a secure connection (HTTPS) 
//ini_set('session.cookie_secure', 1); 
header('X-Frame-Options: SAMEORIGIN'); // REMOVE if THIS site is to go in 3rd party iframe

header('X-XSS-Protection: 0');
header('Referrer-Policy: origin-when-cross-origin');
header(\"Content-Security-Policy: default-src 'none'; child-src 'self' www.google.com; script-src 'self' 'unsafe-inline' *.google.com www.google-analytics.com ajax.googleapis.com *.gstatic.com  maps.googleapis.com malsup.github.io *.tinymce.com *.fontawesome.com *.mylivechat.com secure.skypeassets.com; connect-src 'self' www.google-analytics.com stats.g.doubleclick.net; img-src 'self' *.tinymce.com *.google.com *.gstatic.com *.googleapis.com www.google-analytics.com *.mylivechat.com data:; style-src 'self' 'unsafe-inline' *.googleapis.com *.tinymce.com *.mylivechat.com; font-src 'self' data: *.gstatic.com *.tinymce.com; media-src 'self'; frame-src 'self' *.vimeo.com *.google.com\");
//header(\"Content-Security-Policy-Report-Only: default-src 'none';\");
header('X-Content-Type-Options: nosniff');
 

if (!isset(\$_SESSION)) {
  session_start();
}

define(\"SITE_ROOT\", \"".$site_root."\");

if(!function_exists(\"mysql_connect\")) {
	require_once(SITE_ROOT.\"core/includes/mysqli.inc.php\"); 
}
/*

\$host = \"build.fullbhuna.com\";					
if(!isset(\$is_cron) && isset(\$_SERVER[\"HTTP_HOST\"]) && \$_SERVER[\"HTTP_HOST\"]!=\$host) {
	\$page = (\$_SERVER[\"REQUEST_URI\"] == \"/index.php\") ? \"/\" : \$_SERVER[\"REQUEST_URI\"];
	\$protocol = (!isset(\$_SERVER[\"HTTPS\"]) || strtolower(\$_SERVER[\"HTTPS\"]) != \"on\") ? \"http://\" : \"https://\" ;
	\$url = \$protocol.\$host.\$page;
	
	if(isset(\$_SERVER[\"HTTP_REFERER\"]) && \$_SERVER[\"HTTP_REFERER\"]!=\"\") {
		 \$_SESSION[\"referer\"] = \$_SERVER[\"HTTP_REFERER\"];
	 }
	header( \"HTTP/1.1 301 Moved Permanently\" ); 
	header( \"Status: 301 Moved Permanently\" );
	header( \"Location: \".\$url); exit;
}  */




\$html_class =  isset(\$_SESSION[\"MM_UserGroup\"]) ? \" rank\".intval(\$_SESSION[\"MM_UserGroup\"]) : \"\";


//define(\"DEBUG_EMAIL\", \"\"); // send errors to theis email
define(\"DEBUG\", true); // turn on all debugging
if(defined(\"DEBUG\")  || isset(\$_SESSION[\"debug\"])) {
	\$_SESSION[\"log\"] = isset(\$_SESSION[\"log\"]) ? \$_SESSION[\"log\"] : \"\"; // start session log if required
	error_reporting(32767); // 0 = display no errors, 32767 display all
	@ini_set(\"display_errors\", 1); // 0 = don't display none, 1 = display/
} else {
	error_reporting(0); 
	@ini_set(\"display_errors\", 0); 
}

set_error_handler(\"fb_error_handler\");
function fb_error_handler (\$errno,\$errstr,\$errfile, \$errline) {
	if(defined(\"DEBUG_EMAIL\") && intval(\$errno)<8) { // only send real errors
		\$error= \"Error [\".intval(\$errno).\"] \".\$errstr.\" in file \".\$errfile.\" line \".\$errline. \"(IP: \".\$_SERVER[\"REMOTE_ADDR\"].\")\";
		mail(DEBUG_EMAIL, \"Error reported from \".\$_SERVER[\"HTTP_HOST\"], \$error);
	}
}




# FileName=\"Connection_php_mysql.htm\"
# Type=\"MYSQL\"
# HTTP=\"true\"

\$hostname_aquiescedb = \"$hostname_aquiescedb\"; // between the quotes, put the host of your database - usually localhost
\$database_aquiescedb = \"$database_aquiescedb\"; // between the quotes, put the name of your database
\$username_aquiescedb = \"$username_aquiescedb\"; // between the quotes put the username for yourdatabase
\$password_aquiescedb = \"$password_aquiescedb\"; // between the quotes put the password for your database
\$aquiescedb = mysql_pconnect(\$hostname_aquiescedb, \$username_aquiescedb, \$password_aquiescedb) or trigger_error(mysql_error(),E_USER_ERROR); // leave
?><?php

\$regionID=1;
mysql_select_db(\$database_aquiescedb, \$aquiescedb); // more often than not!
//restrict to coming soon page(except Google)  uncomment below
//if(!isset(\$_SESSION['MM_Username']) && !preg_match(\"/offline|rss/i\", \$_SERVER['PHP_SELF'])  && !isset(\$override_offline) && !(strpos(\$_SERVER['HTTP_USER_AGENT'],\"google\"))) { header(\"location: /offline/index.php?status=construction&login=true&accesscheck=\".urlencode(\$_SERVER['REQUEST_URI'])); exit; }
require_once(SITE_ROOT.\"$prefs_path\");

// override SQL mode if required
//$sql= \"SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));\";
//mysql_query($sql, $aquiescedb) or die(mysql_error());

// extra session security if required
// also include sessions.inc.php if needed
/* if (isset(\$_SESSION[\"HTTP_USER_AGENT\"]))
{
	if (\$_SESSION[\"HTTP_USER_AGENT\"] != md5(\$_SERVER[\"HTTP_USER_AGENT\"].PRIVATE_KEY))
	{
	   unset(\$_SESSION);  exit;
	}
}
else
{
	\$_SESSION[\"HTTP_USER_AGENT\"] = md5(\$_SERVER[\"HTTP_USER_AGENT\"].PRIVATE_KEY);
} */
?>";

// check to see if tables already exist

				mysql_select_db($database_aquiescedb, $aquiescedb);
				$q = "SHOW TABLES FROM `".$database_aquiescedb."`";
				$rs = mysql_query($q, $aquiescedb) or die(mysql_error().": ".$q);
				if(!isset($_POST['ignore_warnings']) && mysql_num_rows($rs)>0) {
					$submit_error = "Database tables already exists. Do you want to continue to add the new tables? ";
				} else { // add tables
				
				
					$file = "fullbhuna.sql";
					if (load_db_dump($file,$hostname_aquiescedb,$username_aquiescedb,$password_aquiescedb,$database_aquiescedb)) { // data entered
					
						$aquiescedb = mysql_pconnect($hostname_aquiescedb, $username_aquiescedb, $password_aquiescedb);
						mysql_select_db($database_aquiescedb, $aquiescedb);
						
						
						$insert = "INSERT INTO `users` (`ID`, `usertypeID`, `firstname`, `surname`, `email`, `username`, `password`, `changepassword`, `termsagree`, `emailoptin`, `dateadded`,  `addedbyID`, `regionID`) VALUES (1, 10, 'Web', 'Administrator',  '".$_SESSION['email']."', 'wadmin', '".md5($_SESSION['password'])."',0,1,1,NOW(),1,0)";
						mysql_query($insert, $aquiescedb) or die(mysql_error());
				
						
						// LOGGED IN
						$_SESSION['MM_Username'] = "wadmin";
						$_SESSION['MM_UserGroup'] = "10";
						unset($_SESSION['password']);
						unset($_SESSION['email']);
						
						// insert search defaults
						$update = "UPDATE isearch_info SET start_urls = 'http://".$_SERVER['HTTP_HOST']."/', allowed_urls_beginning ='http://".$_SERVER['HTTP_HOST']."/', exclude_urls_beginning='http://".$_SERVER['HTTP_HOST']."/admin/'";
						mysql_query($update, $aquiescedb) or die(mysql_error());
						
						
						// set up no reply address
						$update = "UPDATE mailprefs SET noreplyemail = 'no-reply@".str_replace("www.","",$_SERVER['HTTP_HOST'])."'";
						mysql_query($update, $aquiescedb) or die(mysql_error());
						
						// set up organisation name in prefs
						$update = "UPDATE preferences SET orgname=".GetSQLValueString($_SESSION['site_name'], "text").", installdatetime= '".date('Y-m-d H:i:s')."', createdbyID = 1, createddatetime='".date('Y-m-d H:i:s')."'";
						mysql_query($update, $aquiescedb) or die(mysql_error());
						
						// set up organisation name in site prefs
						$update = "UPDATE region SET title=".GetSQLValueString($_SESSION['site_name'], "text")." WHERE ID = 1";
						mysql_query($update, $aquiescedb) or die(mysql_error());
						
						
						
						
						
						// everything OK so write database connection string and go to activate
						$filename = "../Connections/aquiescedb.php";
						$handle = fopen($filename,"w");
						fwrite($handle, $contents);
						fclose($handle);
						chmod($filename, 0664);
						
						header("location: tools/activate.php?firstrun=true");exit;
					} else { // data not entered
					$submit_error = "There was a problem: Could not input the required data into your database. This may be because the details you entered below are incorrect. Please check.";
					} // end data not entered
				} // OK to add
			} // end OK version
			else { $submit_error = "Full Bhuna requires MySQL version 4.0.2 or later. Please upgrade your version of MySQL before installation. (Your version: ".mysql_get_server_info().")";}
		} // end connection OK to database
		else { $submit_error = "Connected to server but could not connect to database. Please check the database name and that the named user has full access. Error: ".mysql_error();}
	} // end connection OK to server
	else { $submit_error = "Could not connect to database server. Please check details entered. Error: ".mysql_error(); }
} // end post



?><!DOCTYPE html>
<!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<html lang="en" class="full_bhuna install <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="../Templates/Install.dwt.php" codeOutsideHTMLIsLocked="false" --><!-- Copyright Paul Egan. Any unauthorised copying, reproduction or alteration is strictly prohibited -->
<head>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Install Full Bhuna - Step 2 - Create Database</title>
<!-- InstanceEndEditable -->
<?php require_once('includes/head.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><style><!-- 
.siteadmin {
	display:none;
}
--></style><!-- InstanceEndEditable -->
</head>
<body>
<?php require_once('includes/header.inc.php'); ?>
<main>
<div class="container"><!-- InstanceBeginEditable name="Body" -->
      <p>&nbsp;</p>
      <h1>Step 2 - Create Your Database</h1>
      <?php if (isset($submit_error)) { ?><p class="alert alert-danger" role="alert"><?php echo $submit_error; ?></p>
      <?php } ?>
      <ul>
        <li>Create a MySQL database on your server to hold all the site data</li>
      </ul>
  <p>Again, the install program cannot do this, so if you don't know how to set up a database, ask your server administrator.</p>
  <ul>
    <li>Enter the details of the database you've just created below:</li>
  </ul>
  <form action="index2.php" method="post" id="form1" onsubmit="document.getElementById('activate').disabled = true;document.getElementById('wait').style.display = 'inline';">
    <table border="0" cellpadding="5" cellspacing="0" class="form-table">
      <tr  class="form-group">
        <td align="right">Database host:</td>
        <td><input name="host" type="text"  id="host" value="<?php echo isset($_POST['host']) ? $_POST['host'] : "localhost" ; ?>" class="form-control" />
          <em>The server the database is on - usually the same one as your site, i.e. 'localhost'</em></td>
      </tr>
      <tr class="form-group">
        <td align="right">Database name:</td>
        <td><input name="database" type="text"  id="database" value="<?php echo isset($_POST['database']) ? $_POST['database'] : ""; ?>"  class="form-control"/>
          <em>The name of your database on the server</em></td>
      </tr>
      <tr class="form-group">
        <td align="right">Access username:</td>
        <td><input name="username" type="text"  id="username" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" value="<?php echo isset($_POST['username']) ? $_POST['username'] : ""; ?>"  class="form-control"/>
          <em>The username to access it</em></td>
      </tr>
      <tr class="form-group">
        <td align="right">Access password:</td>
        <td><input name="password" type="text"  id="password" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" value="<?php echo isset($_POST['password']) ? $_POST['password'] : ""; ?>"  class="form-control"/>
          <em>The password to access it</em></td>
      </tr>
    </table>
    <p>That's it! Now you can activate your copy of Full Bhuna...</p>
    <p>
      <button name="activate" type="submit" class="btn btn-primary" id="activate" >Activate Full Bhuna</button>
      <span id="wait" style="display:none"><img src="/images/processing.gif" alt="Please wait" width="16" height="16" style="vertical-align:
middle;" />
Please wait...</span>     
<input name="next" type="hidden" id="next" value="true" />
    </p>
    <p>
  <input name="ignore_warnings" type="checkbox" id="ignore_warnings" value="1" <?php if(isset($_POST['ignore_warnings'])) echo "checked"; ?> />
    Ignore warnings</p>
  </form>
  
  <!-- InstanceEndEditable --></div>
</main>
<?php require_once('includes/footer.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>