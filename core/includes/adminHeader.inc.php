<header>
  <div class="header container clearfix">
    <div class="row">
      <div class="col-sm-4 col-sm-push-8"><a href="/core/admin/" class="logo"><?php echo $site_name; ?></a></div>
      <div class="col-sm-8 col-sm-pull-4 title"><a href="/core/admin/"><?php echo isset($admin_name) ? $admin_name : "Control Panel"; ?></a></div>
    </div>
    <div class="hamburger hidden-print"><span class="glyphicon glyphicon-menu-hamburger"></span></div>
    <nav class="admin-main-menu hidden-print" data-spy="affix" data-offset-top="110">
      <?php if(is_readable(SITE_ROOT."Connections/adminmenu.inc.php")) { ?>
      <?php require_once(SITE_ROOT."Connections/adminmenu.inc.php"); ?>
      <?php } // done like this to help dreamweaver update with template ?>
    </nav>
  </div>
</header>
