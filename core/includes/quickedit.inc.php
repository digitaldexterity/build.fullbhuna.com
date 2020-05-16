<?php if(is_readable(SITE_ROOT."local/includes/quickedit.inc.php")) {
	require_once(SITE_ROOT."local/includes/quickedit.inc.php");
} else { if(isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=8) { ?><style><!--
/* QUICK EDIT */

.quickeditbar {
	display:none;
}

@media (min-width: 768px) {



.quickeditbar {
	display:block;
	box-sizing:border-box;
	position:absolute;
	position:fixed;
	top:0;
	left:0;
	width:100%;
	z-index:99999;
	padding: 0 15px;
	height:30px;
	line-height:30px;
	background:#000;
	border-bottom:1px solid #cccccc;
	color: #cccccc;
	-webkit-box-shadow: 0px 4px 22px 0px rgba(0,0,0,0.75);
-moz-box-shadow: 0px 4px 22px 0px rgba(0,0,0,0.75);
box-shadow: 0px 4px 22px 0px rgba(0,0,0,0.75);
font-family:Arial, Helvetica, sans-serif !important;
font-size:16px !important;
}

.quickeditbar a, .quickeditbar a:link, .quickeditbar a:visited,.quickeditbar a:active, .quickeditbar a:hover {
	color: #cccccc;
}

.quickeditbar a:hover {
	color: #ffffff;
	text-decoration:none;
}

.quickeditbar img {
	width:20px;
	height:20px;
	display:inline-block;
	vertical-align:middle;
	position:relative;
	top:-2px;
}



.quickedit_close {
	float:right;
}


.quickeditcontainer { 
	position:relative;
	margin: 0 auto;
	
}
.quickeditcontainer.active { 
	z-index:65500; /* only bring forward when actve so as not to cover drop down menus this is just below 65536 of tinyme drop down menus */

}

.quickeditcontainer .mce-tinymce {
	position:absolute;
	top:-76px; /* move up toolbar amount */
	left:0;
	z-index:500;
	-webkit-box-shadow: 0px 0px 22px 0px rgba(0,0,0,0.75);
	-moz-box-shadow: 0px 0px 22px 0px rgba(0,0,0,0.75);
	box-shadow: 0px 0px 22px 0px rgba(0,0,0,0.75);
}


}
--></style>
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<div class="quickeditbar">
<span class="quickedituser"><img src="/core/images/full-bhuna-inverse.png" width="20" height="20" alt="Full Bhuna" /> You are logged in as <a href="/members/"><?php echo $_SESSION['MM_Username']; ?></a>. &nbsp;&nbsp; <a href="/login/logout.php"> <span class="glyphicon glyphicon-log-out"></span> Log Out</a></span><span class="quickeditadmin"><?php if(isset($articleID)) { ?><a  href="/articles/admin/update_article.php?articleID=<?php echo $articleID; ?>" target="_blank" id="edit_link" rel="noopener"> <span class="glyphicon glyphicon-pencil"></span> Editor</a><?php } if(isset($quickedit)) { ?> &nbsp;&nbsp;<a href="#" id="quickedit_link"> <span class="glyphicon glyphicon-pencil"></span> Quick Edit</a><?php } ?>   &nbsp;&nbsp;<a href="/core/admin/" target="_blank" rel="noopener"><span class="glyphicon glyphicon-cog"></span> Control Panel</a></span> &nbsp;&nbsp;<a href="javascript:void(0)" target="_blank" class="quickedit_close" rel="noopener" onClick="closeQuickEditBar()"><span class="glyphicon glyphicon-remove"></span> Close</a></span></span>
</div>
<script>
$(document).ready(function(e) {
	$("body").addClass("quickedit");
	<?php if(isset($articleID) && isset($mergebody)) { ?>
	
	$("#quickedit_link").hide();
	
	if ($(".quickeditcontainer").length > 0) {		
			
			//find the container DIV
	
			if ($(".quickeditcontainer").closest().hasClass('container')) {
				// has parent container so make this the container
				var containerDIV = $(".quickeditcontainer");
				
			} else {
				var containerDIV = $(".quickeditcontainer").find(".container");	
			
				/*$(".quickeditcontainer").css("width", containerDIV.outerWidth());	- CANT do this as content may be full width even with cntainers within*/
					
			}
			
			if(containerDIV) {	
				$("#quickedit_link").show();			
				var mce_width =  $(".quickeditcontainer").innerWidth();
				var mce_height =  $(".quickeditcontainer").height()+(4*36);
				
				/** do we need decodeSafeEmails ?**/
				
				$(".quickeditcontainer").append("<form method='post' action='<?php echo $_SERVER['REQUEST_URI']; ?>' class='quickeditform'><textarea class='quickedittextarea' name='quickedithtml'>"+atob('<?php echo  base64_encode($body); ?>')+"</textarea><input type='submit' value='Save'><input type='hidden' name='updatearticleID' value='<?php echo $articleID; ?>'><input type='hidden' name='token' value='<?php echo md5($articleID.PRIVATE_KEY); ?>'></form>");
				tinymce.init({
					selector: '.quickedittextarea',
					plugins: "save",
					menubar: 'edit view save',
					toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | save',
					content_css: '/local/css/styles.css',
					width: mce_width,
					height: mce_height,
					body_class: 'quickedit',
					convert_urls: true,
					relative_urls :  false,
					remove_script_host : true
			  });  
			
			  $(".quickeditform").hide();
			  $("#quickedit_link").click(function() {
				  $(".quickeditcontainer").toggleClass("active");
				  $(".quickeditform").fadeToggle();
			  });
		}
	}
		<?php } ?>
	   
});

function closeQuickEditBar() {
	$(".quickeditbar").fadeOut();
}
  
  </script>
<?php } }?>