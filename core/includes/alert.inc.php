<?php $dismiss = isset($_GET['dismiss']) ? "alert-dismissible" : "";
$msg = (isset($msg) && $msg!="") ? $msg : (isset($_GET['msg']) ? htmlentities($_GET['msg'], ENT_COMPAT,"UTF-8") : "");
if(trim($msg)!="") { ?>
<div class="alert alert-info <?php echo $dismiss; ?>" role="alert" id="fb-alert-info"><?php echo nl2br($msg); ?></div>	
<?php } ?>
<?php $success = (isset($success) && $success!="") ? $success : (isset($_GET['success']) ? htmlentities($_GET['success'], ENT_COMPAT,"UTF-8") : "");
if(trim($success)!="") { ?>
<div class="alert alert-success <?php echo $dismiss; ?>" role="alert" id="fb-alert-success"><?php echo nl2br($success); ?></div>	
<?php } ?>
<?php $warning = (isset($warning) && $warning!="") ? $warning : (isset($_GET['warning']) ? htmlentities($_GET['warning'], ENT_COMPAT,"UTF-8") : "");
if(trim($warning)!="") { ?>
<div class="alert alert-warning <?php echo $dismiss; ?>" id="fb-alert-warning"><?php echo nl2br(htmlentities($warning, ENT_COMPAT,"UTF-8")); ?></div>	
<?php } ?>
<?php $error = (isset($error) && $error!="") ? $error : (isset($_GET['error']) ? htmlentities($_GET['error'], ENT_COMPAT,"UTF-8") : "");
if(trim($error)!="") { ?>
<div class="alert alert-danger  <?php echo $dismiss; ?>" id="fb-alert-danger"><?php echo nl2br($error); ?></div>	
<?php }  ?>
<?php if(isset($errors) && is_array($errors) && !empty($errors)) { ?>
<div class="alert alert-danger <?php echo $dismiss; ?>" id="fb-alert-danger">You have the following error<?php echo (count($errors)>1) ? "s" : ""; ?>:
  <ol>
<?php foreach($errors as $key => $value) { echo "<li>".htmlentities($value, ENT_COMPAT,"UTF-8")."</li>"; } ?>
</ol>
	</div>
<?php }?>

