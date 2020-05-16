<!-- uses http://1000hz.github.io/bootstrap-validator/ -->
<script src="/core/scripts/validator.min.js"></script>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<?php if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
global $regionID;
$regionID = (isset($regionID) && intval($regionID)>0)  ? intval($regionID): 1;

if(!isset($row_rsForm['ID'])) { // not on form page so get data 


$formID = isset($formID) ? $formID: -1; 


if (isset($_REQUEST['formID'])) {
  $formID = $_REQUEST['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsForm = sprintf("SELECT * FROM `form` WHERE ID = %s", GetSQLValueString($formID, "int"));
$rsForm = mysql_query($query_rsForm, $aquiescedb) or die(mysql_error());
$row_rsForm = mysql_fetch_assoc($rsForm);
$totalRows_rsForm = mysql_num_rows($rsForm);

$colname_rsFormItems = "-1";
if (isset($_REQUEST['formID'])) {
  $formID = $_REQUEST['formID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsFormItems = sprintf("SELECT * FROM formfield WHERE formID = %s ORDER BY ordernum ASC", GetSQLValueString($formID, "int"));
$rsFormItems = mysql_query($query_rsFormItems, $aquiescedb) or die(mysql_error());
$row_rsFormItems = mysql_fetch_assoc($rsFormItems);
$totalRows_rsFormItems = mysql_num_rows($rsFormItems);

$colname_rsLoggedIn = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLoggedIn = $_SESSION['MM_Username'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsLoggedIn = sprintf("SELECT ID, users.firstname, users.surname FROM users WHERE username = %s", GetSQLValueString($colname_rsLoggedIn, "text"));
$rsLoggedIn = mysql_query($query_rsLoggedIn, $aquiescedb) or die(mysql_error());
$row_rsLoggedIn = mysql_fetch_assoc($rsLoggedIn);
$totalRows_rsLoggedIn = mysql_num_rows($rsLoggedIn);

mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsPreferences = "SELECT * FROM preferences WHERE ID = ".$regionID."";
$rsPreferences = mysql_query($query_rsPreferences, $aquiescedb) or die(mysql_error());
$row_rsPreferences = mysql_fetch_assoc($rsPreferences);
$totalRows_rsPreferences = mysql_num_rows($rsPreferences);
}  // end not on form page

$header = isset($header) ? $header : $row_rsForm['header'];
if(!isset($form_token_generated)) { // genreate only one token
	$_SESSION['token'] = md5(uniqid(rand(), true));
	$form_token_generated = true;
}
?>

<style>
<!--
.formitems {
	
	margin: 15px -15px;
	
}

.formitems * {
	box-sizing:border-box;
}

.formitems {
	display:flex;
	flex-wrap: wrap;	
}

.formitems > div {
	flex-grow: 12;
}

/* safari fix for last column wrapping in flex */
.formitems:after, .formitems:before {
  display: none;
}

.formitems .row {
	width:100%;
	margin:  0 0 15px 0;
}


.formitems .cell {
	display:block;
	width:100%;
	padding: 0 15px;
	position: relative; vertical-align:top;
}




@media (min-width: 768px) {
	
	.formitems .row.halfwidth {
	width:50%;
	float:left;
}

	.formitems .shows1 .cell {
		width:50%;
	}

.formitems .row .cell.fullwidth {
	width:100%;
}
.formitems .showlabels1 .cell.fieldlabel {
	/*text-align: right;*/
}
}

.formitems .cell.fieldlabel.description {
	text-align: left;
}
.formitems input[type='text'], .formitems input[type='email'], .formitems textarea {	
	width: 100%;
	position: relative; z-index: 1; /* fix non click-focus issue*/
}
.formitems textarea {
	height: 100px;
}
.formitems .wide {
	height: 100px;
}
-->
</style>
<?php if($row_rsForm['statusID']==1 || (isset($_SESSION['MM_UserGroup']) && $_SESSION['MM_UserGroup']>=7 )) { // is active or admin ?>
<div class="formbuilder formbuilder<?php echo $row_rsForm['ID']; ?>">
<?php if (isset($_REQUEST['formID'])) { // only on main form page ?>
  <h1><?php echo $row_rsForm['formname']; ?></h1>
  <?php } ?>
  <?php echo trim($header); ?>
  <form method="post" enctype="multipart/form-data" action="/forms/form.php?formID=<?php echo $row_rsForm['ID']; ?>"  id="formBuilder<?php echo $row_rsForm['ID']; ?>">
    <div class="formitems">
      <?php $item = 0; $required = 0; if($totalRows_rsFormItems>0) { do { $item++; ?>
      <div class="row form-group showlabels<?php echo $row_rsForm['showlabels']; ?> item<?php echo $row_rsFormItems['ID']; ?> <?php echo ($row_rsFormItems['halfwidth']==1) ? " halfwidth ": " clearfix "; ?>" ><div class='cell fieldlabel <?php echo ($row_rsFormItems['formfieldtype']==0) ? "description fullwidth " : ""; echo ($row_rsFormItems['formfieldtype']==0) ? " fullwidth " : ""; ?>' <?php if(trim($row_rsFormItems['formfieldname'])=="") { echo " style='display:none;'";} ?> >
          <label <?php if($row_rsFormItems['formfieldtype']==1 || $row_rsFormItems['formfieldtype']==2 || $row_rsFormItems['formfieldtype']==6) echo "for=\"formfieldresponse_".$item."\""; ?>><?php echo $row_rsFormItems['formfieldname']; if($row_rsFormItems['required']==1) {  $required ++;?><span class="required">*</span><?php } ?></label></div><?php if($row_rsFormItems['formfieldtype']>0) { ?><div class='cell  formitem'>
		  <input type='hidden' name='required[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['required']; ?>'>
          <input type='hidden' name='addverifyfield[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['addverifyfield']; ?>'>
          <input type='hidden' name='encryptfield[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['encryptfield']; ?>'>
          <input type='hidden' name='formfieldspecialtype[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['formfieldspecialtype']; ?>'>
          <input type='hidden' name='formitemID[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['ID']; ?>'>
          <input type='hidden' name='formfieldtype[<?php echo $item; ?>]' value='<?php echo $row_rsFormItems['formfieldtype']; ?>'>
           <input type='hidden' name='formfieldspecialtype[<?php echo $item; ?>]' value='<?php echo $row_rsFormItems['formfieldspecialtype']; ?>'>
          <input type='hidden' name='formfieldlabel[<?php echo $item; ?>]' value='<?php echo (trim($row_rsFormItems['formfieldname'])!="") ? htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8") : htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8"); ?>'><?php if($row_rsFormItems['formfieldtype']>=3 && $row_rsFormItems['formfieldtype']<=5) {
				$select = "SELECT * FROM  formfieldchoice WHERE formfieldID = ".$row_rsFormItems['ID']." ORDER BY ordernum";
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$choices = mysql_num_rows($result);
			}
			if($row_rsFormItems['formfieldtype']==1) { 

switch($row_rsFormItems['formfieldspecialtype']) {
	case 1 : $type = "email"; break;
	case 2 : $type = "text"; break;
	case 3 : $type = "password"; break;
	default : $type = "text"; 
} ?>


            
             <!-- TEXTBOX -->
            <?php if($type=="password") { ?><div class="input-group"><?php } ?>
          <input type="<?php echo $type; ?>" name="formfieldresponse[<?php echo $item; ?>]" id="formfieldresponse_<?php echo $item; ?>" value="<?php echo isset($_REQUEST['formfieldresponse'][$item]) ? htmlentities($_POST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : ""; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?> placeholder="<?php echo ($row_rsForm['showplaceholders']==1 && !isset($row_rsFormItems['formfieldplaceholder'])) ?  htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8") :htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8") ; ?>" data-required-error="<?php echo $row_rsForm['text_enter_value']; ?>" class="form-control"><?php if($type=="password") { ?>
          <span class="input-group-btn">
        <button class="btn btn-default btn-secondary  toggle-password" type="button" toggle="#formfieldresponse_<?php echo $item; ?>"><i class="glyphicon glyphicon-eye-open  "></i></button>
      </span>
          </div><?php } ?>
          
          
          <?php if($row_rsFormItems['addverifyfield']==1) { // add verify field ?>
          </div><!-- end cell -->
          </div><!-- end form-group-->
          <div class="row form-group showlabels<?php echo $row_rsForm['showlabels']; ?> item<?php echo $row_rsFormItems['ID']; ?> <?php echo ($row_rsFormItems['halfwidth']==1) ? " halfwidth ": " clearfix "; ?>" >
          <div class="cell field-label">
          <label>Verify <?php echo $row_rsFormItems['formfieldname']; ?>:</label></div><div class="cell formitem">
          <input type="<?php echo $type; ?>" name="formfieldverify[<?php echo $item; ?>]" id="formfieldverify_<?php echo $item; ?>" value=""  class="form-control">          
          <?php } // end add verify field ?>
          
      
      
          <?php } else if($row_rsFormItems['formfieldtype']==2) {?>
          
          
          
          
          
           <!-- TEXT AREA -->
           
           
           
          <textarea name="formfieldresponse[<?php echo $item; ?>]" id="formfieldresponse_<?php echo $item; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>  placeholder="<?php echo ($row_rsForm['showplaceholders']==1 && !isset($row_rsFormItems['formfieldplaceholder'])) ?  htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8") :htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8") ; ?>" data-required-error="<?php echo $row_rsForm['text_enter_value']; ?>" class="form-control"><?php echo isset($_REQUEST['formfieldresponse'][$item]) ? htmlentities($_REQUEST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : ""; ?></textarea>
          <?php } else if($row_rsFormItems['formfieldtype']==3) { ?>
          
          
            <!-- CHECKBOX -->
            
            
            
			<?php 		if($choices>0) { $i = 0;
						while($choice = mysql_fetch_assoc($result)) { $i++; ?><div class="form-check form-check-inline"><input type='hidden' name='choiceID[<?php echo $item; ?>][<?php echo $i; ?>]' value ='<?php echo $choice['ID']; ?>'><input type="checkbox" class="form-check-input" name="formfieldresponse[<?php echo $item; ?>][<?php echo $i; ?>]" id="formfieldresponse_<?php echo $item; ?>_<?php echo $i; ?>" value="<?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?>" <?php if(isset($_REQUEST['formfieldresponse'][$item][$i])) echo " checked"; ?> <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?> data-error="<?php echo $row_rsForm['text_check_value']; ?>">&nbsp;<label class="form-check-label" for="formfieldresponse_<?php echo $item; ?>_<?php echo $i; ?>"><?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></label></div>
          <?php } ?>
          <?php }?>
          <?php 	} else if($row_rsFormItems['formfieldtype']==4) { ?>
          
          
          
          
          <!-- RADIO / SELECT -->
          
<?php if($choices>0) { 
	$i = 0;
	if($choices>10) { ?>
    <select class="form-control" name="formfieldresponse[<?php echo $item; ?>]"  id="formfieldresponse_<?php echo $item; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required " : ""; ?> data-error="<?php echo $row_rsForm['text_select_value']; ?>">
	<?php while($choice = mysql_fetch_assoc($result)) { $i++; ?>
    <option <?php if($_REQUEST['formfieldresponse'][$item]==htmlentities($choice['ID'], ENT_COMPAT, "UTF-8")) echo "selected"; ?> value = "<?php echo htmlentities($choice['ID'], ENT_COMPAT, "UTF-8"); ?>"><?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></option>
	<?php } // end while ?>
    </select>
	<?php } else {
	while($choice = mysql_fetch_assoc($result)) { $i++; ?>
          <div class="form-check form-check-inline"><input type="radio" class="form-check-input" name="formfieldresponse[<?php echo $item; ?>]"  id="formfieldresponse_<?php echo $item; ?>" value = "<?php echo htmlentities($choice['ID'], ENT_COMPAT, "UTF-8"); ?>" <?php if($_REQUEST['formfieldresponse'][$item]==htmlentities($choice['ID'], ENT_COMPAT, "UTF-8")) echo "checked"; ?> <?php echo ($row_rsFormItems['required']==1) ? " required " : ""; ?> data-error="<?php echo $row_rsForm['text_select_value']; ?>">&nbsp;<label class="form-check-label" for="formfieldresponse_<?php echo $item; ?>"><?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></label></div>
          <?php } // end while?>
          <?php } // end is choices < 10 ?>
          <?php } // end is choices?>
          
          
          
          
          <?php 		} else if($row_rsFormItems['formfieldtype']==5) { ?>
          
          
          
          <!-- SELECT -->
          
          
          
          <select name='formfieldresponse[<?php echo $item; ?>]' <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?> data-error="<?php echo $row_rsForm['text_select_value']; ?>" class="form-control">
          <option value="">Choose...</option>
            <?php	while($choice = mysql_fetch_assoc($result)) { $i++; ?>
            <option value="<?php echo $choice['ID']; ?>" <?php if($_REQUEST['formfieldresponse'][$item]==htmlentities($choice['ID'], ENT_COMPAT, "UTF-8")) echo "selected"; ?>><?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></option>
            <?php } ?>
             <!-- hack to prevent option text on mobile devices being truncated -->
       <optgroup label=""></optgroup>
          </select>        
          <?php 	} else if($row_rsFormItems['formfieldtype']==6) { ?>
          
           <!-- File chooser -->
           
           
          <input type="file" name="formfieldresponse[<?php echo $item; ?>]" id="formfieldresponse_<?php echo $item; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>>
          <?php } else if($row_rsFormItems['formfieldtype']==7) { ?>
          
          <!-- Date picker -->
          
          
          <input type="hidden" name='formfieldresponse[<?php echo $item; ?>]' id='formfieldresponse_<?php echo $item; ?>' value="<?php $setvalue =  isset($_REQUEST['formfieldresponse'][$item]) ? htmlentities($_REQUEST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : "";  echo $setvalue; $inputname = "formfieldresponse_".$item;  $startyear = date('Y')-100; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>  data-required-error="<?php echo $row_rsForm['text_enter_value']; ?>" class="form-control highlight-days-67 split-date format-y-m-d divider-dash"><?php require(SITE_ROOT.'core/includes/datetimeinput.inc.php'); ?>
          <?php } // end form field 7 ?>
          
          
         
          
          
          
          
          <div class="help-block with-errors"></div></div><!-- end cell--><?php } ?></div><!-- end form-group--><?php } while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems));  ?>
          
          
       <?php if($row_rsForm['adduser']==1) { ?>   
       <div class="row form-group">
       
        <div class="cell form-check form-check-inline">
          <?php echo $row_rsPreferences['emailoptintext']; ?><span class="required">*</span>: &nbsp;
    <label><input type="radio" name="emailoptin" id="emailoptin_1" value="1" <?php if(isset($_REQUEST['emailoptin']) && $_REQUEST['emailoptin']==1) echo "checked" ?> class="form-check-input" required> Yes</label> &nbsp; 
  <label><input type="radio" name="emailoptin" value="0" <?php if(isset($_REQUEST['emailoptin']) && $_REQUEST['emailoptin']==0) echo "checked" ?> class="form-check-input" required> No</label>
  <div class="help-block with-errors"></div></div></div>
  <?php } ?>
  
  
  
      <?php if($row_rsForm['captcha']==1) { ?>
      <div class="row">
        <div class="cell text-right"><label for="captcha_answer">Security:</label></div>
        <div class="cell"><div><img src="/core/includes/random_image.php" alt="Security image" style="vertical-align:text-bottom; width:150px !important; height:50px !important;" /> Please type the letters shown below: </div>
          <input name="captcha_answer" type="text"  id="captcha_answer"  size="30" maxlength="30"   /></div>
      </div>
      <?php } else if(($row_rsForm['captcha']==2 || $row_rsForm['captcha']==3) && trim($row_rsPreferences['recaptcha_site_key'])!="") {
		if($row_rsForm['captcha']==2) {  ?>
       <div class="row"><div class="cell"><div class="g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>"></div></div></div><?php }//  reCaptcha 2
	  } // reCaptcha ?>
      <div id='recaptcha<?php echo $row_rsForm['ID']; ?>' ></div>
      <div class="row ">
       
        <div class="cell"><button  type="submit" class="btn btn-primary<?php if($row_rsForm['captcha']==33) {  ?> g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" data-size="invisible" data-callback="onForm<?php echo $row_rsForm['ID']; ?>Submit<?php } ?>" ><?php echo $row_rsForm['text_submit']; ?></button> <?php echo ($required>0) ? " <span class=\"required\"> *</span> ".$row_rsForm['text_required'] : ""; ?>
          <input name="formID" type="hidden" id="formID" value="<?php echo $row_rsForm['ID']; ?>">
          <input name="form_token" type="hidden" id="form_token" value="<?php echo  htmlentities($_SESSION['form_token']); ?>">
          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : 0; ?>">
          <input name="fullname" type="hidden" value="<?php echo isset($row_rsLoggedIn['surname']) ?  htmlentities($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'], ENT_COMPAT, "UTF-8") : ""; ?>"></div>
      </div>
    </div>
    <?php echo $row_rsForm['footer']; ?>
  </form>
  </div>
 <script>
$(".toggle-password").click(function() {

  $(this).find("i").toggleClass("glyphicon-eye-open glyphicon-eye-close");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});
</script><?php if(($row_rsForm['captcha']==2 || $row_rsForm['captcha']==3)  && trim($row_rsPreferences['recaptcha_site_key'])!="") { ?>

     <script>
	 var recaptchaCall<?php echo $row_rsForm['ID']; ?> = 0;
    $(document).ready(function(){
		
        $('#formBuilder<?php echo $row_rsForm['ID']; ?>').validator().on('submit', function (e) {
			
          if (e.isDefaultPrevented()) {
			 
            // handle the invalid form...
            console.log("validation failed");
          } else {
            // everything looks good!
			recaptchaCall<?php echo $row_rsForm['ID']; ?>++;
			
            e.preventDefault();
            console.log("validation success");	
            
			if(recaptchaCall<?php echo $row_rsForm['ID']; ?>==1)
            {
                widgetId<?php echo $row_rsForm['ID']; ?> = grecaptcha.render('recaptcha<?php echo $row_rsForm['ID']; ?>', {
                'sitekey' : '<?php echo $row_rsPreferences['recaptcha_site_key']; ?>',
                'callback' : onForm<?php echo $row_rsForm['ID']; ?>Submit,
                'size' : "invisible"
                });
			
            }

            grecaptcha.reset(widgetId<?php echo $row_rsForm['ID']; ?>);
            grecaptcha.execute(widgetId<?php echo $row_rsForm['ID']; ?>); 
			
		
          }
        });
    }); 

    // function below only used for invisbile reCaptcha [3]
       function onForm<?php echo $row_rsForm['ID']; ?>Submit(token) {
         document.getElementById("formBuilder<?php echo $row_rsForm['ID']; ?>").submit();
       }

    </script>
   
<script src='https://www.google.com/recaptcha/api.js' async defer>
 </script>
 <?php } // end captcha ?>
<?php }  // end is items?>
<?php } // end  is active 
else { ?>
<p>Sorry, this form is currently unavailable.</p>
<?php }
mysql_free_result($rsPreferences);
unset($row_rsForm);
mysql_free_result($rsForm);
mysql_free_result($rsFormItems);

?>