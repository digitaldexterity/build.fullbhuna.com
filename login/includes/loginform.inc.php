<?php if($row_rsPreferences['captcha_login']==1 && ($row_rsPreferences['captcha_type']==2 || $row_rsPreferences['captcha_type']==3)  && trim($row_rsPreferences['recaptcha_site_key'])!="") { ?><script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script>
// function below only used for invisbile reCaptcha [3]
       function onLoginSubmit(token) {
         document.getElementById("login").submit();
       }
    </script>
<?php } ?><form action="<?php echo isset($loginFormAction) ?  $loginFormAction : ""; ?>" method="post" name="login" class="form-horizontal" onSubmit="$('.login-submit').attr('disabled', true);">

  <div class="form-group">
    <label for="username" class="control-label col-sm-4"><?php echo (isset($row_rsPreferences['text_username'])) ? htmlentities($row_rsPreferences['text_username'], ENT_COMPAT, "UTF-8") : "Username" ?>:</label><div class="col-sm-8">
      <input name="username" type="text"  id="username"  maxlength="50" value="<?php echo isset($_REQUEST['username']) ? htmlentities($_REQUEST['username'], ENT_COMPAT, "UTF-8") : ""; ?>" class="form-control" /></div>
    </div>
    <div class="form-group">
    <label for="password"  class="control-label col-sm-4"><?php echo (isset($row_rsPreferences['text_password'])) ? htmlentities($row_rsPreferences['text_password'], ENT_COMPAT, "UTF-8") : "Password" ?>:</label><div class="col-sm-8">
    <div class="input-group">
      <input name="password" type="password"  id="fb-password-field" maxlength="20" class="form-control" autocomplete="off" /><span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#fb-password-field"><i class="glyphicon glyphicon-eye-open  "></i></button>
      </span> </div>
   </div>
    </div>

<?php if ($row_rsPreferences['captcha_login']==1) { 
		if($row_rsPreferences['captcha_type']==1) {?>
        <div>
          <img src="/core/includes/random_image.php" alt="Security CAPTCHA image" style="width:150px !important; height:50px !important;" />
        </div>
        <div class="form-group">
          <input name="captcha_answer" type="text"  id="captcha_answer" maxlength="40" placeholder="Please enter letters above"  class="form-control" />
           
        </div>
       <?php } else if(($row_rsPreferences['captcha_type']==2 || $row_rsPreferences['captcha_type']==3) && trim($row_rsPreferences['recaptcha_site_key'])!="") {
		if($row_rsPreferences['captcha_type']==2) {  ?>
       <div class="g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>"></div><?php }//  reCaptcha 2
	  } // reCaptcha
	   } // use captcha ?>
          
          
  
       
<div class="form-group">
<div class="col-sm-offset-4 col-sm-8">
 <input type="hidden" name="login_token" id="login_token" value="<?php 
 echo $login_token; ?>" />
 <button type="submit"  class="login-submit btn btn-primary <?php if($row_rsPreferences['captcha_type']==3) {  ?>g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" data-callback="onLoginSubmit<?php } ?>" ><?php echo (isset($row_rsPreferences['logintext'])) ? htmlentities($row_rsPreferences['logintext'], ENT_COMPAT, "UTF-8") : "Log in" ?></button> <a href="/login/forgot_password.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" id="link_forgottenpassword" class="text-nowrap"><?php echo (isset($row_rsPreferences['text_forgotpass'])) ? htmlentities($row_rsPreferences['text_forgotpass'], ENT_COMPAT, "UTF-8") : "Forgotten&nbsp;password?" ?></a></div></div>
   
   <div class="form-group">
   <div class="col-sm-offset-4 col-sm-8">
   
   <?php if($row_rsPreferences['stayloggedin']>=0) { ?>
    <label class="stayloggedin" ><input <?php if ($row_rsPreferences['stayloggedin']==1 || !(strcmp($_GET['stayloggedin'],1))) {echo "checked=\"checked\"";} ?> type="checkbox" name="stayloggedin" value="1"  onclick="if(this.checked) { return confirm('You will now stay logged in on this device until you log out or don\'t access for 30 days.\n\nNote: This feature is added for your convenience by storing a cookie on your device, but for security we do not recommend using this functionality on a shared computer.'); }"  /> <span data-toggle="tooltip" title="For your security do not check this on a shared computer. Checking this box will keep you logged in on this device until you log out or don\'t access for 30 days."><?php echo (isset($row_rsPreferences['text_stayloggedin'])) ? htmlentities($row_rsPreferences['text_stayloggedin'], ENT_COMPAT, "UTF-8") : "Keep me logged in on this device" ?></span></label>
    <?php } ?>
    
    
    
    </div></div>
    
    
 
</form>
<script>
$(".toggle-password").click(function() {

  $(this).find("i").toggleClass("glyphicon-eye-open glyphicon-eye-close");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});
</script>
