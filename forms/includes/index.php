<!-- uses http://1000hz.github.io/bootstrap-validator/ -->
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
$_SESSION['form_token'] = md5(uniqid(rand(), true));
?>
<?php if($row_rsForm['captcha']==2) { ?><script src='https://www.google.com/recaptcha/api.js'></script>
<?php } ?><script src="/core/scripts/validator.min.js"></script>
<script src="/core/scripts/date-picker/js/datepicker.js"></script>
<style>
<!--
.formitems {
	width: 100%;
	margin: 15px -15px;
	
}

.formitems * {
	box-sizing:border-box;
}


.formitems .row {
	margin:  0 0 15px 0;
}


.formitems .cell {
	display:inline-block;
	width:100%;
	padding: 0 15px;
	position: relative; vertical-align:top;
}

@media (min-width: 768px) {
	
	.formitems .row.halfwidth1 {
	width:50%;
	float:left;
}

	.formitems .showlabels1 .cell {
		width:50%;
	}

.formitems .row .cell.fullwidth {
	width:100%;
}
.formitems .showlabels1 .cell.fieldlabel {
	text-align: right;
}
}

.formitems .cell.fieldlabel.description {
	text-align: left;
}
.formitems input[type='text'], .formitems input[type='email'], .formitems textarea {	
	width: 100%;
}
.formitems textarea {
	height: 100px;
}
.formitems .wide {
	height: 100px;
}
-->
</style>
<div class="formbuilder formbuilder<?php echo $row_rsForm['ID']; ?>">
<?php if (isset($_REQUEST['formID'])) { // only on main form page ?>
  <h1><?php echo $row_rsForm['formname']; ?></h1><?php } ?>
  <?php echo trim($header); ?>
  <form method="post" enctype="multipart/form-data" action="/forms/form.php?formID=<?php echo $row_rsForm['ID']; ?>" data-toggle="validator">
    <div class="formitems">
      <?php $item = 0; $required = 0; if($totalRows_rsFormItems>0) { do { $item++; ?>
      <div class="row form-group showlabels<?php echo $row_rsForm['showlabels']; ?> item<?php echo $row_rsFormItems['ID']; ?> halfwidth<?php echo $row_rsFormItems['halfwidth']; ?>" ><div class='cell fieldlabel <?php echo ($row_rsFormItems['formfieldtype']==0) ? "description fullwidth " : ""; echo ($row_rsFormItems['formfieldtype']==0) ? " fullwidth " : ""; ?>' <?php if(trim($row_rsFormItems['formfieldname'])=="") { echo " style='display:none;'";} ?> >
          <label <?php if($row_rsFormItems['formfieldtype']==1 || $row_rsFormItems['formfieldtype']==2 || $row_rsFormItems['formfieldtype']==6) echo "for=\"formfieldresponse_".$item."\""; ?>><?php echo $row_rsFormItems['formfieldname']; if($row_rsFormItems['required']==1) {  $required ++;?><span class="required">*</span><?php } ?></label></div><?php if($row_rsFormItems['formfieldtype']>0) { ?><div class='cell  formitem'>
		  <input type='hidden' name='required[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['required']; ?>'>
          <input type='hidden' name='formitemID[<?php echo $item; ?>]' value ='<?php echo $row_rsFormItems['ID']; ?>'>
          <input type='hidden' name='formfieldtype[<?php echo $item; ?>]' value='<?php echo $row_rsFormItems['formfieldtype']; ?>'>
          <input type='hidden' name='formfieldlabel[<?php echo $item; ?>]' value='<?php echo htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8"); ?>'><?php if($row_rsFormItems['formfieldtype']>=3 && $row_rsFormItems['formfieldtype']<=5) {
				$select = "SELECT * FROM  formfieldchoice WHERE formfieldID = ".$row_rsFormItems['ID']." ORDER BY ordernum";
				$result = mysql_query($select, $aquiescedb) or die(mysql_error());
				$choices = mysql_num_rows($result);
			}
			if($row_rsFormItems['formfieldtype']==1) { $type = stripos($row_rsFormItems['formfieldname'],"email")!==false ? "email" : "text"; ?>
          <input type='<?php echo $type; ?>' name='formfieldresponse[<?php echo $item; ?>]' id='formfieldresponse_<?php echo $item; ?>' value="<?php echo isset($_POST['formfieldresponse'][$item]) ? htmlentities($_POST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : ""; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?> placeholder="<?php echo ($row_rsForm['showplaceholders']==1 && !isset($row_rsFormItems['formfieldplaceholder'])) ?  htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8") :htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8") ; ?>" data-required-error="A value is required in this form field" class="form-control">
          <?php } else if($row_rsFormItems['formfieldtype']==2) {?>
          <textarea name='formfieldresponse[<?php echo $item; ?>]' <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>  placeholder="<?php echo ($row_rsForm['showplaceholders']==1 && !isset($row_rsFormItems['formfieldplaceholder'])) ?  htmlentities($row_rsFormItems['formfieldname'], ENT_COMPAT, "UTF-8") :htmlentities($row_rsFormItems['formfieldplaceholder'], ENT_COMPAT, "UTF-8") ; ?>" data-required-error="A value is required in this form field" class="form-control"><?php echo isset($_POST['formfieldresponse'][$item]) ? htmlentities($_POST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : ""; ?></textarea>
          <?php } else if($row_rsFormItems['formfieldtype']==3) {
					if($choices>0) { $i = 0;
						while($choice = mysql_fetch_assoc($result)) { $i++; ?>
          <label class='text-nowrap'><input type='hidden' name='choiceID[<?php echo $item; ?>][<?php echo $i; ?>]' value ='<?php echo $choice['ID']; ?>'><input type='checkbox' name='formfieldresponse[<?php echo $item; ?>][<?php echo $i; ?>]' value="<?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?>" <?php if(isset($_POST['formfieldresponse'][$item][$i])) echo " checked"; ?> <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?> data-error="Please check box">
           <?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></label>  &nbsp;&nbsp;&nbsp;
          <?php } ?>
          <?php }?>
          <?php 	} else if($row_rsFormItems['formfieldtype']==4) {
					if($choices>0) { $i = 0;
						while($choice = mysql_fetch_assoc($result)) { $i++; ?>
          <label class='text-nowrap radio-inline'><input type='radio' name='formfieldresponse[<?php echo $item; ?>]' value = "<?php echo htmlentities($choice['ID'], ENT_COMPAT, "UTF-8"); ?>" <?php if($_POST['formfieldresponse'][$item]==htmlentities($choice['ID'], ENT_COMPAT, "UTF-8")) echo "checked"; ?> <?php echo ($row_rsFormItems['required']==1) ? " required " : ""; ?> data-error="Please select a value"> <?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></label> &nbsp;&nbsp;&nbsp;
          <?php } ?>
          <?php }?>
          <?php 		} else if($row_rsFormItems['formfieldtype']==5) { ?>
          <select name='formfieldresponse[<?php echo $item; ?>]' <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>>
          <option value="">Choose...</option>
            <?php	while($choice = mysql_fetch_assoc($result)) { $i++; ?>
            <option value="<?php echo $choice['ID']; ?>" <?php if($_POST['formfieldresponse'][$item]==htmlentities($choice['ID'], ENT_COMPAT, "UTF-8")) echo "selected"; ?>><?php echo htmlentities($choice['formfieldchoicename'], ENT_COMPAT, "UTF-8"); ?></option>
            <?php } ?>
          </select>
          <?php 	} else if($row_rsFormItems['formfieldtype']==6) { ?>
          <input type="file" name="formfieldresponse[<?php echo $item; ?>]" id="formfieldresponse_<?php echo $item; ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>>
          <?php } else if($row_rsFormItems['formfieldtype']==7) { ?>
          <input type="hidden" name='formfieldresponse[<?php echo $item; ?>]' id='formfieldresponse_<?php echo $item; ?>' value="<?php $setvalue =  isset($_POST['formfieldresponse'][$item]) ? htmlentities($_POST['formfieldresponse'][$item], ENT_COMPAT, "UTF-8") : "";  echo $setvalue; $inputname = "formfieldresponse_".$item;  ?>" <?php echo ($row_rsFormItems['required']==1) ? " required  " : ""; ?>  data-required-error="A value is required in this form field" class="form-control highlight-days-67 split-date format-y-m-d divider-dash"><?php require(SITE_ROOT.'core/includes/datetimeinput.inc.php'); ?>
          <?php } // end form field 7 ?><div class="help-block with-errors"></div></div><!-- end cell--><?php } ?></div><!-- end form-group--><?php } while ($row_rsFormItems = mysql_fetch_assoc($rsFormItems)); } ?>
      <?php if($row_rsForm['captcha']==1) { ?>
      <div class="row">
        <div class="cell text-right"><label for="captcha_answer">Security:</label></div>
        <div class="cell"><div><img src="/core/includes/random_image.php" alt="Security image" style="vertical-align:text-bottom; width:150px !important; height:50px !important;" /> Please type the letters shown below: </div>
          <input name="captcha_answer" type="text"  id="captcha_answer"  size="30" maxlength="30"   /></div>
      </div>
      <?php } ?>
       <div class="row"><div class="cell"><div class="g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>"></div></div></div>
      <div class="row">
        <div class="cell text-right"><?php echo ($required>0) ? "<span class=\"required\">*</span> required item" : "&nbsp;"; ?></div>
        <div class="cell"><input  type="submit" value="<?php echo $row_rsForm['text_submit']; ?>">
          <input name="formID" type="hidden" id="formID" value="<?php echo $row_rsForm['ID']; ?>">
          <input name="form_token" type="hidden" id="form_token" value="<?php echo htmlentities($_SESSION['form_token']); ?>">
          <input name="createdbyID" type="hidden" id="createdbyID" value="<?php echo isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : 0; ?>">
          <input name="fullname" type="hidden" value="<?php echo isset($row_rsLoggedIn['surname']) ?  htmlentities($row_rsLoggedIn['firstname']." ".$row_rsLoggedIn['surname'], ENT_COMPAT, "UTF-8") : ""; ?>"></div>
      </div>
    </div>
  </form>
  </div>
<?php
mysql_free_result($rsPreferences);
unset($row_rsForm);
mysql_free_result($rsForm);

?>
