<?php if(isset($row_rsPreferences['googletagmanager']) && substr($row_rsPreferences['googletagmanager'],0,3)=="GTM") { ?>
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $row_rsPreferences['googletagmanager']; ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>dataLayer=[];(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $row_rsPreferences['googletagmanager']; ?>');</script>
<!-- End Google Tag Manager -->
<?php $tagmanager = true; } // remainder dealt with in googleAnalyrtics function  in footer?>