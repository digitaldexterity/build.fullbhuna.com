<?php require_once(SITE_ROOT.'core/includes/quickedit.inc.php'); ?>
 <?php require_once(SITE_ROOT.'articles/includes/functions.inc.php'); ?>
<header>
      
  
     <nav><div class="container"><a href="/" class="logo ">Logo</a><div class="hamburger"><span class="glyphicon glyphicon-menu-hamburger"></span> Menu</div>
        <!-- <div class="animated hamburger"><div class="menu-line menu-line-1"></div><div class="menu-line menu-line-2"></div><div class="menu-line menu-line-3"></div></div>-->
             
             
              <?php  echo buildMenu(0,4,"main-menu"); ?>
              <?php //require_once(SITE_ROOT.'products/includes/productFunctions.inc.php'); ?>
              <?php // echo productMenu(0,4,"sf-menu", true); ?>
             
           </div>
        </nav>
      <div class="visible-xs-block">XS</div>
  <div class="visible-sm-block">SM  768</div>
  <div class="visible-md-block">MD 992</div>
  <div class="visible-lg-block">LG 1200</div>
  <div class="visible-xl-block">XL 1200</div>
    </header>