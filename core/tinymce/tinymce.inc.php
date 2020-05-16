<?php // Copyright 2017 Paul Egan ?>
<?php if(strpos($_SERVER['HTTP_HOST'],"local")===false) { 
if(defined("TINY_MCE_API_KEY")) { 
//y5p57fhglh9vi4o1kp7klurufd6h9coesoennrkv6dps71gg ?>
<script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=<?php echo  TINY_MCE_API_KEY ; ?>"></script><?php } else if(is_readable(SITE_ROOT."local/tinymce/js/tinymce/tinymce.min.js")) { //local copy
} else { // fall back to old CDN ?>
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<?php } } else { // for localhost ?>
<script src="/3rdparty/tinymce/tinymce.min.js"></script><?php } ?>
<script>
// requires download and set up of the compat 3x plug in from TinyMCE
// class="mceNonEditable" for non-editable regions **
 tinymce.init({
	 selector: ".tinymce",
	forced_root_block : 'p', /* prevents new div on return within div */
	 verify_html : false,
	 allow_script_urls: true, /* remove for security if on public site */
	 branding: false,
	 plugins:"compat3x imagetools link code table media textcolor colorpicker hr noneditable anchor paste lists <?php echo defined("TINY_MCE_PLUGINS") ? TINY_MCE_PLUGINS : ""; ?>",
	 external_plugins: {
    'advimage': '/core/tinymce/plugins/advimage/plugin.min.js',
	'preventdelete': '/core/tinymce/plugins/preventdelete/plugin.min.js',
	"responsivefilemanager" : "/core/tinymce/plugins/responsivefilemanager/plugin.min.js",
	"filemanager" : "/core/tinymce/filemanager/plugin.min.js"
  },
  	extended_valid_elements : 'iframe[src|class|width|height|name|align|style|allowTransparency|allowfullscreen|frameborder|scrolling],script[async|language|type|src|class],style[*],ins[*]',
	valid_children : "+body[link],+body[style]",
	menubar: 'edit  format table tools',
	toolbar: 'insertfile undo redo | styleselect | bold italic forecolor | alignleft aligncenter  | bullist numlist outdent indent |  hr  anchor link responsivefilemanager  image  media code <?php echo defined("TINY_MCE_TOOLBAR") ? TINY_MCE_TOOLBAR : ""; ?>',
	images_upload_url: '/core/tinymce/uploadHandler.php',
	images_upload_base_path: "/",
    images_upload_credentials: true,
	style_formats_merge: true,
	style_formats: [{ title:'Site styles', items: [
    //{ title: 'Red text', inline: 'span', styles: { color: '#ff0000' } },
    
   // { title: 'Badge', inline: 'span', styles: { display: 'inline-block', border: '1px solid #2276d2', 'border-radius': '5px', padding: '2px 5px', margin: '0 2px', color: '#2276d2' } },
    { title: 'Float right' ,selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',  classes: 'fltrt' },
	{ title: 'Button Styled Link' ,selector: 'a',  classes: 'button btn btn-primary' },
	{ title: 'Lead Paragraph' ,selector: 'p',  classes: 'lead' },
	{ title: 'Responsive Table' ,selector: 'table',  classes: 'responsive' },
	{ title: 'Responsive Table (with gutter)' ,selector: 'table',  classes: 'responsive gutter' }
	<?php echo defined("TINYMCE_EXTRA_STYLES") ? TINYMCE_EXTRA_STYLES : ""; ?>
  ]}],
	link_list: "<?php echo defined("TINY_MCE_LINK_LIST") ? TINY_MCE_LINK_LIST : "/core/tinymce/lists/link_list.js.php" ?>",
	image_list: "/core/tinymce/lists/image_list.js.php",
	external_filemanager_path:"/core/tinymce/filemanager/",
   	filemanager_title:"Full Bhuna Filemanager" ,
	height: 400,
	content_css : <?php echo defined("TINYMCE_CONTENT_CSS") ? "\"".TINYMCE_CONTENT_CSS."?v=".rand()."\"" : "\"/local/css/styles.css?v=".rand()."\""; ?> ,
	body_class : "container pageBody <?php echo"region"; echo isset($regionID) ? $regionID : 0;  echo isset($row_rsArticle['sectionID']) ? " section".$row_rsArticle['sectionID']." article".intval($_GET['articleID']) : ""; echo isset($row_rsArticle['class']) ? " ".$row_rsArticle['class'] : ""; echo isset($body_class) ? " ".$body_class : "";  ?>",
	convert_urls:<?php echo isset($convert_urls) ? $convert_urls : "true"; ?>,
	relative_urls : <?php echo isset($relative_urls) ? $relative_urls : "false"; ?>,
	remove_script_host : <?php echo isset($remove_script_host) ? $remove_script_host : "true"; ?> //only used if relative is false
	});
 </script>