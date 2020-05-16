<!--
________  .__       .__  __         .__                      
\______ \ |__| ____ |__|/  |______  |  |                     
 |    |  \|  |/ ___\|  \   __\__  \ |  |                     
 |    `   \  / /_/  >  ||  |  / __ \|  |__                   
/_______  /__\___  /|__||__| (____  /____/                   
        \/  /_____/               \/                         
________                   __               .__  __          
\______ \   ____ ___  ____/  |_  ___________|__|/  |_ ___.__.
 |    |  \_/ __ \\  \/  /\   __\/ __ \_  __ \  \   __<   |  |
 |    `   \  ___/ >    <  |  | \  ___/|  | \/  ||  |  \___  |
/_______  /\___  >__/\_ \ |__|  \___  >__|  |__||__|  / ____|
        \/     \/      \/           \/                \/     
 
Full Bhuna CMS by Paul Egan

-->
<meta name="robots" content="noindex,nofollow" />
<meta name="viewport" content="width=device-width, initial-scale=1"><script src="/core/scripts/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<!-- fall back -->
<script>!jQuery.ui && document.write('<link rel="stylesheet" href="/3rdparty/jquery/jquery-ui-1.12.1/jquery-ui.min.css"><script src="/3rdparty/jquery/jquery-ui-1.12.1/jquery-ui.min.js"><\/script>')</script>
<script src="/core/scripts/common.js"></script>
<script src="/core/scripts/popper.min.js"></script>
<script src="/3rdparty/hopscotch/js/hopscotch.min.js"></script>
<link href="/3rdparty/hopscotch/css/hopscotch.min.css" rel="stylesheet" >
<?php if(isset($_COOKIE['setbootstrap']) && $_COOKIE['setbootstrap']==4) { ?>
<script src="/core/bootstrap/js/bootstrap.min.js"></script>
<link href="/core/bootstrap/css/bootstrap.min.css" rel="stylesheet"  />
<link href="/core/fonts/glyphicons/glyphicons.css" rel="stylesheet"  /><link href="/core/css/bootstrap-fb-theme.css" rel="stylesheet" type="text/css"><?php } else { ?>
<script src="/3rdparty/bootstrap/js/bootstrap.min.js"></script>
<link href="/3rdparty/bootstrap/css/bootstrap.min.css" rel="stylesheet"  />
<?php } ?>
<link href="/3rdparty/fonts/font-awesome-4.4.0/css/font-awesome.min.css" rel="stylesheet" >
<link href="/core/css/global.css" rel="stylesheet"  />
<link href="/core/css/adminLayout.css?v=9<?php echo (isset($_SESSION['debug'])) ? rand() : ""; ?>" rel="stylesheet"  media="screen, projection" /><script src="/core/scripts/fancybox/jquery.fancybox.min.js"></script>
<link href="/core/scripts/fancybox/jquery.fancybox.min.css" rel="stylesheet"  />
<?php if(is_readable(SITE_ROOT."local/css/admin.css")) { ?>
<link href="/local/css/admin.css" rel="stylesheet"  media="screen, projection" />
<?php } ?><script>
$(document).ready(function() {
 // executes when HTML-Document is loaded and DOM is ready
 	$('.hamburger').click(function () { // Capture responsive menu button click
    	// Show/hide menu
    	$('header nav').toggleClass("open");
		
    });
	
	// screen size cookie
	if(!getCookie("screensize") && screen.width>0) {
		setCookie("screensize", screen.width+"x"+screen.height, 365, "/");
		getData("/seo/includes/screensize.ajax.php?width="+screen.width+"&height="+screen.height);		
	}
	
	$( '.main-menu li:has(ul)' ).doubleTapToGo(); // fixes drop down issues on TOUCH devices (resolution independent) - function below

		console.log("Page loaded");
		
		
		
		/*
		document.onkeypress = function (e) {
    		e = e || window.event;
    		// use e.keyCode
			alert(e.keyCode);
		};
var fb_lastKeyPress = 0;

		$(document).on('keydown', function() {
			alert();
			 if (new Date() - fb_lastKeyPress < 500) {
				
			  fb_lastKeyPress = new Date();
			 }
			
		};
		*/
		
		<?php $expiryperiod = (ini_get("session.gc_maxlifetime")*1000) - 120000; 
		
		// session time minus 2 mintues 
		//$expiryperiod = 10000; // 10secs ?>
		 if(typeof(fb_keepAlive) == 'undefined' && getCookie("cookiestayloggedin") == null) { 
		 // only if not stay logged in and fb_keepAlive var is set
			console.log("Session will expire. Will warn after "+parseInt(<?php echo intval($expiryperiod); ?>/60000)+" minutes");
			$.sessionTimeout({
  				warnAfter: <?php echo intval($expiryperiod); ?>, // 
  				redirAfter: 60000 // 1 minute
			});
		 } else {
			 console.log("Session will not expire.");
		 }
			 // always keep alive anyway
			 window.setInterval(function() {
				 $.get("/login/ajax/keep_session_alive.ajax.php", function(data, status){
       
				 console.log("Keep alive data: " + data + "\nStatus: " + status);
			 		});
			 }, 60000);
			 
		
		 
		
        
		
		/* Bootstrap tooltips */
		//$('[data-toggle="tooltip"]').tooltip(); 
		/* fix below for table-cell bug  */
		$('[data-toggle="tooltip"]').tooltip({
    		container : 'body'
  		});
		
		$('body').removeClass('nojQuery'); /* disable default :hover */
		/* SEO textarea length helper */
		var minSEOtextbox = 50;
		var maxSEOtextbox = 60;
		var minSEOtextarea = 130;
		var maxSEOtextarea = 160;
		
		
		$('.seo-length').each(function(){
			if($(this).is("textarea")){
				var SEOlengthstring = " chars of "+minSEOtextarea+"-"+maxSEOtextarea;
			} else {
				var SEOlengthstring = " chars of "+minSEOtextbox+"-"+maxSEOtextbox;
			}
			$(this).after("<div class='seo-counter'><span>"+$(this).val().length+"</span>"+SEOlengthstring+"</div>");
			
		});
		$('.seo-length').keyup(function() {
			
  			var length = $(this).val().length;
  			$(this).nextAll('.seo-counter').children('span').text(length);
			if($(this).is("textarea")) {
				minLength = minSEOtextarea;
				maxLength = maxSEOtextarea;
			} else {
				minLength = minSEOtextbox;
				maxLength = maxSEOtextbox;
			}
			if(length<minLength) {
				$(this).css("background-color", "#fcf8e3");
			} else if(length>maxLength) {
				$(this).css("background-color", "#f2dede");
			} else {
				$(this).css("background-color", "#dff0d8");
			}
		});
		
		var fb_editor_domain = '<?php echo isset($thisRegion['hostdomain']) ? $thisRegion['hostdomain'] : $_SERVER['HTTP_HOST']; ?>';
		
});
	
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




    </script><?php $body_class = isset($body_class) ? $body_class : "";
$body_class .=  isset($regionID) ? " region".$regionID : " region0";
$body_class .= isset($_SESSION['MM_UserGroup']) ? " rank".intval($_SESSION['MM_UserGroup']) : " rank0";
?><style><!--
<?php  if(isset($totalRegions)) { // we have admin regions
	if($totalRegions<=1) { // we have one or less
		echo ".region { display: none !important; } ";
	} else if(is_array($rsAdminRegions)) { 
		while($adminRegion = mysql_fetch_assoc($rsAdminRegions)) {
		//hide areas not applicable to this region
			if($adminRegion['ID'] != $regionID) echo ".region".$adminRegion['ID']." { display: none  !important; }\n";	
		}  
		mysql_data_seek($rsAdminRegions,0);  
	}
}
if(isset($thisRegion['adminheadercolor'])) {
echo ".adminBody header, .adminBody .btn.btn-primary { background-color: ".$thisRegion['adminheadercolor'].";\n
border-color: ".$thisRegion['adminheadercolor'].";\n 
}\n\nh1 i, h2 i, h3 i { color: ".$thisRegion['adminheadercolor']."; }\n";
}
if(isset($thisRegion['adminheaderimageURL'])) {
echo ".adminBody header .logo { background-image: url(/Uploads/".addslashes($thisRegion['adminheaderimageURL'])."); }\n";
}
if(!isset($_SESSION['debug'])) {
	echo ".debug  { display: none !important; }\n";
}
?>
--></style>
<meta name="author" content="Created with ❤ by Paul Egan">
<?php if(is_readable(SITE_ROOT."local/includes/adminHead.inc.php")) {
	include(SITE_ROOT."local/includes/adminHead.inc.php");
} ?>