<footer>
  <div class="container"><hr>
<p class="siteadmin"><a href="/install/tools/" class="btn btn-default btn-secondary" target="_blank">Tools</a> <a href="/"  class="btn btn-default btn-secondary" target="_blank">Main Site</a> <a href="/core/admin/" class="btn btn-default btn-secondary" target="_blank">Site Admin</a> <a href="/core/admin/?deleteinstall=true" class="btn btn-danger" target="_blank" onClick="return confirm('Do you want to delete the install files? It is receommended you do this once you have finished using them.')">Delete Install Files</a> <a href="/install/login.php?logout=true" class="btn btn-warning">Log Out</a></p>
<p><em>Full Bhuna</em> Content Management System developed and built by <a href="https://www.digitaldexterity.co.uk/" title="Web Developers Glasgow">Digital Dexterity</a>.  &copy; Copyright <?php echo date('Y'); ?>. All rights reserved. <em>Is anyone still using WordPress?</em></p><?php if(defined("DEBUG")  || isset($_SESSION["debug"])) {

echo "<pre>";
print_r($_SESSION);
} 
?></div></footer>

