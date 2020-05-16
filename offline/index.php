<?php if(is_readable('../Connections/aquiescedb.php')) { ?>
<?php require_once('../Connections/aquiescedb.php'); ?>
<?php } if(is_readable("../login/includes/login.inc.php")) { ?>
<?php require_once("../login/includes/login.inc.php"); ?>
<?php } 
if (!isset($_SESSION)) {
  session_start();
} ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo isset($site_name) ? $site_name : "This site is"; ?> temporarily offline</title>
<link href="/3rdparty/bootstrap/css/bootstrap.min.css" rel="stylesheet" >
<link href='https://fonts.googleapis.com/css?family=Shadows+Into+Light+Two' rel='stylesheet' type='text/css'>
<style >
<!--
body, html {

text-align:center;
}
body {
	font-family: Arial, Helvetica, sans-serif;
	background-color: #ffffff;color: #000000;
	margin:20px;
}
a:link, a:visited {
	color: #000000;
	text-decoration: underline;
}
a:hover, a:focus {
	color: #000000;
	text-decoration: underline;
}
.postit {
	background:url(/core/images/post_it_blank.png) no-repeat center center;
	width:446px;
	height: 452px;
	padding:100px;
	margin: 50px auto;
	font-family: 'Shadows Into Light Two', cursive;
	color:rgb(0,0,153);
}
	 
-->
</style>
<link href="/SpryAssets/SpryValidationTextField.css" rel="stylesheet" >
</head>

<body>
<div class="container">
<img src="../local/images/qco-overload-maintenance-image.png" width="430" height="600">
<div id="main-text">
    <h1>Hang in there</h1>
                    <p>We are updating the site at the moment. Sorry for the inconvenience, service should be restored shortly</p>
  </div>

  <?php if(isset($_GET['login'])) { ?>
<form id="form1" name="form1" method="post" action="<?php echo isset($loginFormAction) ? $loginFormAction : ""; ?>"><fieldset><legend>Members</legend>
  
  
    <input name="username" type="text"  id="username" size="16" maxlength="50" onFocus="if(this.value=='Username') { this.value = ''; }" value="Username">
 


  <input name="password" type="text"  id="password" size="16" maxlength="50" value="Password" onFocus="if(this.value=='Password') { this.value = ''; this.type='password'; }">
  <input type="hidden" name="token" id="token" value="<?php $token = md5(uniqid(rand(), true));
$_SESSION['token'] = $token; echo $token; ?>">


<input name="login" type="submit" class="button" id="login" value="Log in">
            
              <input type="hidden" name="stayloggedin" value="1"> <input type="hidden" name="token" id="token" value="<?php $token = md5(uniqid(rand(), true));
$_SESSION['token'] = $token; echo $token; ?>"></fieldset>
</form><?php } ?></div>
</body>
</html>
