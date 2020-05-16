<?php require_once('../../Connections/aquiescedb.php'); ?>
<?php require_once('../../core/includes/adminAccess.inc.php'); ?><?php require_once('../../core/includes/framework.inc.php'); ?>
<?php require_once('../../core/includes/upload.inc.php');
$regionID = isset($regionID) ? $regionID : 1;
// Got rid of single size below as didn't work with lightbox scripts!
//$size = (isset($_POST['size']) && $_POST['size'] !="") ? array($_POST['size']=>$image_sizes[$_POST['size']]) : false;
//$uploaded = getUploads(UPLOAD_ROOT,$size);
$uploaded = getUploads(); ?>
<html>
<head>
<title>Upload Image</title>

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<style type="text/css">
<!--
body {
	margin: 0;
	padding: 0;
}

#uploadform table td {
	padding :0;
}
-->
</style>
<script src="//code.jquery.com/jquery-1.8.2.js"></script>
<script>!window.jQuery && document.write('<script src="/3rdparty/jquery/jquery-1.12.1.min.js"><\/script>')</script>
</head>
<body>
<script>
<?php if (isset($uploaded) && is_array($uploaded)) { $prefix = isset($image_sizes[$_POST['size']]['prefix']) ? $image_sizes[$_POST['size']]['prefix'] : ""; ?>
var parser = document.createElement('a');
parser.href = window.href;
parser.protocol+"//"+parser.hostname;
imageURL = parser.protocol+"//"+parser.hostname+"<?php echo getImageURL($uploaded["filename"][0]["newname"], $_POST['size']); ?>";
window.parent.document.getElementById('src').value = imageURL;
window.parent.document.getElementById('prev').innerHTML = "<img src='"+imageURL+"'>";
window.parent.document.getElementById('alt').focus();
window.parent.document.getElementById('width').value = "<?php echo @$uploaded["filename"][0][$prefix.'width']; ?>";
window.parent.document.getElementById('height').value = "<?php echo @$uploaded["filename"][0][$prefix.'height']; ?>";
<?php  } ?></script>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="uploadform"  onsubmit="document.getElementById('upload').disabled = true; document.getElementById('uploading').style.display='inline';" style="margin:0">
  <table border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td><span style="font-size:10px">
        <input type="file"  name="filename" id="filename" class="fileinput " accept=".jpg,.jpeg,.gif,.png" >
        <input type="hidden" name="imageURL" id="imageURL">
        
        </span></td>
    </tr>
    <tr>
      <td><span style="font-size:10px"><select name="size"  id="size">
          <option value="<?php echo (@$keep_original == true) ? "" : "large"; ?>" selected>Reduce size...</option>
          <?php if (@$keep_original == true) { ?>
          <option value="">No re-sizing</option>
          <?php } ?>
          <?php foreach($image_sizes as $size=>$values) {
					if(!isset($values['regionID']) || $values['regionID'] == $regionID) {
					$values['width'] = isset($values['width']) ? $values['width'] : "any" ;
					$values['height'] = isset($values['height']) ? $values['height'] : "any" ;?>
          <option value="<?php echo $size; ?>" ><?php echo ucwords(str_replace("_", " ",$size))." "; echo trim("(".$values['width']." x ".$values['height'].") ".@$values['resizetype'],"x "); ?></option>
          <?php }} ?>
        </select>
        <input name="upload" type="submit" class="button" id="upload" value="Upload" >
        <span id="uploading" style="display:none;"> <img src="/core/images/processing.gif" alt="Loading" width="16" height="16" align="absmiddle"> Uploading, please wait...</span></span></td>
    </tr>
  </table>
</form>
</body>
</html>
