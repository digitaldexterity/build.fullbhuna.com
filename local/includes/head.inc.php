<?php $dev = true; // adds randowm strings to files

$regionID = (isset($regionID) && intval($regionID)) ?  intval($regionID) : 0;
mysql_select_db($database_aquiescedb, $aquiescedb);
$select ="SELECT * FROM preferences WHERE ID = ".$regionID." LIMIT 1";
$result = mysql_query($select, $aquiescedb) or die(mysql_error());
$sitePrefs = mysql_fetch_assoc($result);


if(strpos($_SERVER['HTTP_HOST'],"build")!==false || strpos($_SERVER['HTTP_HOST'],"websystem")!==false || strpos($_SERVER['HTTP_HOST'],"digidex")!==false || strpos($_SERVER['HTTP_HOST'],"easing")!==false) { 
// demo site ?>
<meta name="robots" content="noindex,nofollow" />
<?php } ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="/local/scripts/app.js?rand=<?php echo isset($dev) ? rand() : "0";  ?>"></script>
<script src="/core/scripts/common.js?v=2"></script>
<!-- add integrity with https://www.srihash.org -->
<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js" integrity="sha384-SlE991lGASHoBfWbelyBPLsUlwY1GwNDJo3jSJO04KZ33K2bwfV9YBauFfnzvynJ" crossorigin="anonymous"></script>
<link rel="index" href="<?php echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://" ; echo $_SERVER['HTTP_HOST']."/articles/" ?>" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://" ; echo $_SERVER['HTTP_HOST']; ?>/news/news.rss.php" />
<!-- manually use href="tel:" links -->
<meta name="format-detection" content="telephone=no">
<link href="/local/css/layout.css?rand=<?php echo isset($dev) ? rand() : "0";  ?>" rel="stylesheet"  media="screen"  />
<link href="/local/css/styles.css?rand=<?php echo isset($dev) ? rand() : "0";  ?>" rel="stylesheet"  media="screen"  />
<?php echo isset($region['faviconURL']) ? "<link rel=\"shortcut icon\" href=\"".$region['faviconURL']."\" />": ""; ?>
<!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <![endif]-->
<?php $body_class = isset($body_class) ? $body_class : "";
$body_class .=  isset($regionID) ? " region".$regionID : " region0";
$body_class .=  isset($row_rsArticle['sectionID']) ? " parentsection".$row_rsArticle['parentsectionID']." section".$row_rsArticle['sectionID']." article".$row_rsArticle['ID']." ".$row_rsArticle['class']." ".$row_rsArticle['sectionclass'] : ""; 
if(isset($row_rsLoggedIn['ID'])) { 
	// css users and groups for display convenience but not security
	$body_class .= " user".intval($row_rsLoggedIn['ID']);
 	$body_class .= isset($row_rsLoggedIn['usertypeID']) ? " rank".intval($row_rsLoggedIn['usertypeID']) : "";
	$body_class .= isset($row_rsLoggedIn['groupID']) ? " group".$row_rsLoggedIn['groupID'] : "";
	
	if(mysql_num_rows($rsLoggedIn)>1) { // more groups included	
		while($row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn)) {
			$body_class .= " group".$row_rsLoggedIn['groupID'];
		}
		mysql_data_seek($rsLoggedIn,0);
		$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
	}
}
// $mergebody = isset($mergebody) ? str_replace("<img src=","<img class=\"lazy\" data-src=",$mergebody) : ""; // for lazy load ?>
<meta name="generator" content="Powered by Full Bhuna. http://www.fullbhuna.com">
<script>

$(document).ready(function() {
 // executes when HTML-Document is loaded and DOM is ready
 
 	$(".cycle").cycle();
	var $masonry =  $(".masonry").masonry();
	// images loaded plugin to fix layout as images load	
	$masonry.imagesLoaded().progress( function() {
  		$masonry.masonry('layout');
	});
	
 	$('.hamburger').click(function () { // Capture responsive menu button click
    	// Show/hide menu
    	$('.main-menu').fadeToggle();
		$(this).toggleClass("open");
    });	
	$( '.main-menu li:has(ul)' ).doubleTapToGo(); // fixes drop down issues on TOUCH devices (resolution independent) - function below	
	// screen size cookie
	if(!getCookie("fb_screensize") && screen.width>0) {
		setCookie("fb_screensize", screen.width+"x"+screen.height);
		getData("/core/seo/includes/screensize.ajax.php?width="+screen.width+"&height="+screen.height);		
	}
	
	$(window).scroll(function() {		
		if($(window).scrollTop()>30) {
			$("header").addClass("minimised scrolled");			
		} else {
			$("header").removeClass("minimised");
		}		
	});
	
	$('.js-scroll-to').click(function(e) {		
		target = $($(this).attr('href'));	
		if (target.offset()) {
			$('html, body').animate({scrollTop: target.offset().top + 'px'}, 600);
		}		
		e.preventDefault();
	});
	
	/*$('.lazy').lazy({ // optional params 
          effect: "fadeIn",
          effectTime: 2000,
          threshold: 0
        }); // lazy load images (requires data-src set)
	*/
	/* SIMPLE ACCORDION */

var allPanels = $('.accordion + *').hide();
    
  $('.accordion').click(function() {
    allPanels.slideUp();
    $(this).next().slideDown();
    return false;
  });
});


$(window).on('load', function () {
 // executes when complete page is fully loaded, including all frames, objects and images
});

$(window).resize(function(){
 // call any time window is resized
});

new WOW().init();

/*
	doubleTapToGo addition for touch menus
*/

;(function( $, window, document, undefined )
{
	$.fn.doubleTapToGo = function( params )
	{
		if( !( 'ontouchstart' in window ) &&
			!navigator.msMaxTouchPoints &&
			!navigator.userAgent.toLowerCase().match( /windows phone os 7/i ) ) return false;

		this.each( function()
		{
			var curItem = false;

			$( this ).on( 'click', function( e )
			{
				var item = $( this );
				if( item[ 0 ] != curItem[ 0 ] )
				{
					e.preventDefault();
					curItem = item;
				}
			});

			$( document ).on( 'click touchstart MSPointerDown', function( e )
			{
				var resetItem = true,
					parents	  = $( e.target ).parents();

				for( var i = 0; i < parents.length; i++ )
					if( parents[ i ] == curItem[ 0 ] )
						resetItem = false;

				if( resetItem )
					curItem = false;
			});
		});
		return this;
	};
})( jQuery, window, document );
</script>
<style><!--
.wow.fadeIn , .wow.fadeInLeft, .wow.fadeInRight,  .wow.fadeInUp{
visibility:hidden; /*helper to mitigate flashing */
}

--></style>
<noscript><style><!--
.wow.fadeIn , .wow.fadeInLeft, .wow.fadeInRight, .wow.fadeInUp{

visibility:visible;
}


--></style></noscript>
<!-- CMS HEAD additions -->
<?php echo $sitePrefs['head']; ?>