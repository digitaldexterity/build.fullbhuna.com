<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once(SITE_ROOT."/members/includes/userfunctions.inc.php"); ?>
<?php 
if(emailTaken($_GET['email'])>0) { ?>
<div id="alreadyRegistered">
<img src="/core/images/warning.gif" alt="Alert!"  style="vertical-align:middle;width:16px; height:16px;">
  <span style="color:#990000">You appear to be already on our system with email: <?php echo htmlentities($_GET['email']); ?>.</span><br />
<div id="link_forward">&raquo;&nbsp;If you know your password, please <a href="/login/index.php?<?php echo addslashes($_SERVER['QUERY_STRING']); ?>">log in to continue...</a></div>
<div id="link_forward">&raquo;&nbsp;If you don't have a password or have forgotten it, <a href="/login/forgot_password.php?<?php echo addslashes($_SERVER['QUERY_STRING']); ?>">get your password...</a></div><br /><br />

</div>
<?php
} else {  }
exit;
?>

