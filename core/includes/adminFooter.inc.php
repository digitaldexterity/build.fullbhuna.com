<?php if(is_readable(SITE_ROOT.'local/includes/adminFooter.inc.php')) { ?>
      <?php require_once(SITE_ROOT.'local/includes/adminFooter.inc.php'); ?>
      <?php } else { ?>
       <footer class="hidden-print">
       <?php if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']==10) { ?>
       <div class="wadmin-tools">
       <form class="container" method="post">
        Debugging mode: 
        <label>
      <input type="radio" name="debugging" value="0"  onClick="this.form.submit()" <?php if(!isset($_SESSION['debug'])) echo "checked=\"checked\""; ?>> Off</label> &nbsp;&nbsp;&nbsp;
    <label>
      <input type="radio" name="debugging" value="1"onClick="this.form.submit()" <?php if(isset($_SESSION['debug'])) echo "checked=\"checked\""; ?>> On</label>
  &nbsp;&nbsp;&nbsp;
    Bootstrap: <label><input name="setbootstrap" type="radio" value="3" onClick="this.form.submit()" <?php if(!isset($_COOKIE['setbootstrap']) || $_COOKIE['setbootstrap']==3) echo "checked"; ?>> <a href="https://getbootstrap.com/docs/3.3/css/" target="_blank" rel="noopener">3.3.7</a></label> &nbsp;&nbsp;&nbsp;<label><input type="radio" value="4" onClick="this.form.submit()" name="setbootstrap" <?php if(isset($_COOKIE['setbootstrap']) && $_COOKIE['setbootstrap']==4) echo "checked"; ?>> <a href="https://getbootstrap.com/docs/4.0/getting-started/introduction/">4.0.0</a></label></form>
       </form>
       </div>
       <?Php } ?>
   
    <div class="footer container">
    <p class="helpLinks">
 
      <?php if (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup'] >= 9) { // if  admin ?><a href="/core/requests/members/add_request.php" onClick="javascript:MM_openBrWindow('/requests/members/add_request.php','Help','scrollbars=yes,width=400,height=400'); return false;">Change Request</a>  | 
      <?php } //end if  admin ?>
    <a href="javascript:void(0);" onClick="addToFavourites(window.location.href,document.title); return false;" class="link_favourites">Add to favourites</a><span id="favouritescallback"></span> </p>
<p><em>Full Bhuna</em> Content Management System developed, built and lovingly maintained by <a href="https://www.digitaldexterity.co.uk" target="_blank" rel="noopener">Digital Dexterity</a>. &copy; Copyright 2002-<?php echo date('Y'); ?>. All rights reserved.</p>
<?php trackPage(@$pageTitle);  ?>
<script>
getData("/mail/ajax/groupemail.ajax.php");
</script>
<?php require_once(SITE_ROOT.'mail/includes/reminders.inc.php'); ?>
    </div></footer>
	  <?php } ?><?php require_once(SITE_ROOT.'core/includes/console.inc.php'); ?>