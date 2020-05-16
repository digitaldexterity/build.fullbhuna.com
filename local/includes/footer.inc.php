<footer class="hidden-print">
   
        <div class="footer container">
      <div class="row">
      <div class="col-md-4"><h3>Opening Hours:</h3>
      <?php echo nl2br($sitePrefs['openinghours']); ?>
      
      </div>
       <div class="col-md-4"><h3>Quick Links:</h3>
       <?php echo buildMenu(0,0); ?></div>
        <div class="col-md-4"><h3>Get in touch:</h3>
        <address><?php echo $sitePrefs['orgname']; ?><br><?php echo nl2br($sitePrefs['orgaddress']); ?>
<br><br>T : <?php echo $sitePrefs['orgphone']; ?>
<?php if(isset($sitePrefs['contactemail'])) {
	$emails = explode("@", $sitePrefs['contactemail']); 
 ?>
<br><script>writeEmail("<?php echo $emails[0]; ?>","<?php echo $emails[1]; ?>","E : ")</script>
<?php } ?></address></div>
      </div><hr>
     <div class="text-center">&copy; <?php echo date('Y'); ?> <?php echo $sitePrefs['orgname']; ?>. Web by <a href="https://www.digitaldexterity.co.uk" title="Web Designers in Glasgow">Digital Dexterity</a>. <a href="/legal/Privacy-Policy/">Privacy Policy</a> | <a href="/legal/Disclaimer/">Terms &amp; Conditions</a> | <a href="/articles/">Site Map</a></div></div>
        </div>
        
</footer>
<?php trackPage(@$pageTitle); echo googleAnalytics(); ?>
<script>
getData("/mail/ajax/groupemail.ajax.php");
</script>
<?php require_once(SITE_ROOT.'core/includes/console.inc.php'); ?>
<?php //require_once(SITE_ROOT.'core/includes/webBadge.inc.php'); ?>

	