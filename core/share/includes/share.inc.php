<?php global $canonicalURL;  if(is_readable(SITE_ROOT.'local/includes/share.inc.php')) {  require_once(SITE_ROOT.'local/includes/share.inc.php'); 
} else  if(isset($canonicalURL) && strlen($canonicalURL)>0) {  ?>
<div class="fb_share">
<span class="share_text"><?php echo isset($region['text_share']) ? htmlentities($region['text_share'], ENT_COMPAT , "UTF-8") : "Share:" ?></span>
<?php $metadescription = isset($metadescription) ? $metadescription : "";
$shareimageURL = (isset($shareimageURL) && strlen($shareimageURL)>0) ? $shareimageURL : "";
?>

<ul class="fb_share_tools">
  
  <li class="share_tool_facebook"> <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonicalURL); ?>"  target="_blank" rel="noopener" >Facebook</a> </li>
  <li class="share_tool_twitter"> <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($pageTitle); ?>&url=<?php echo urlencode($canonicalURL); ?>" target="_blank" rel="noopener"> Twitter </a> </li>
  <li class="share_tool_pinterest"> <a href="https://uk.pinterest.com/pin/create/bookmarklet/?url=<?php echo urlencode($canonicalURL); ?>&description=<?php echo urlencode($metadescription); ?>&title=<?php echo urlencode($pageTitle); ?>&media=<?php echo urlencode($shareimageURL); ?>" target="_blank" rel="noopener"  >Pinterest</a> </li>
  <li class="share_tool_linkedin"> <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($canonicalURL); ?>&title=<?php echo urlencode($pageTitle); ?>&summary=<?php echo urlencode($pageTitle); ?>"  target="_blank" rel="noopener" > Linkedin </a> </li>
  <li class="share_tool_whatsapp"> <a href="https://wa.me/?text=<?php echo urlencode($pageTitle." - ".$canonicalURL); ?>"   rel="noopener" > Whatsapp </a> </li>
  
  
  
  
  
  
  <li class="share_tool_email"><a href="mailto:?subject=<?php echo urlencode($pageTitle." shared from ".$site_name); ?>&body=<?php echo urlencode($canonicalURL); ?>" >Email</a> </li>
  <li class="share_tool_print"> <a href="javascript:void(0);" onClick="window.print();" > Print </a> </li>
  <li class="share_tool_bookmark"> <a href="javascript:void(0);"  > Bookmark </a> </li>
</ul></div>
<script>
$('.share_tool_bookmark').click(function(e) {
		 
    var bookmarkURL = window.location.href;
    var bookmarkTitle = document.title;
	$.get("/seo/ajax/trackpage.ajax.php?pageTitle=Bookmark");

    if ('addToHomescreen' in window && addToHomescreen.isCompatible) {
      // Mobile browsers
      addToHomescreen({ autostart: false, startDelay: 0 }).show(true);
    } else if (window.sidebar && window.sidebar.addPanel) {
      // Firefox version < 23
      window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
    } else if ((window.sidebar && /Firefox/i.test(navigator.userAgent)) || (window.opera && window.print)) {
      // Firefox 23+ and Opera version < 15
      $(this).attr({
        href: bookmarkURL,
        title: bookmarkTitle,
        rel: 'sidebar'
      }).off(e);
      return true;
    } else if (window.external && ('AddFavorite' in window.external)) {
      // IE Favorites
      window.external.AddFavorite(bookmarkURL, bookmarkTitle);
    } else {
      // Other browsers (mainly WebKit & Blink - Safari, Chrome, Opera 15+)
      alert('Press ' + (/Mac/i.test(navigator.userAgent) ? 'Cmd' : 'Ctrl') + '+D to bookmark this page.');
    }

    return false;
  });
  </script>
<?php } ?>