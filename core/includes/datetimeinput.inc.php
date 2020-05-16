<?php // Copyright 2009 Paul Egan ?><?php 
// optional variables to input
// $inputname - the name of the field (default:datetime)
// $setvalue - value from either database or entered (default:null)
// $startyear - where year menu must start (default this year minus 5 or encompassing the setvalue)
// $endyear - where year menu must finish (default this year + 5 or encompassing setvalue)
// $time - if "true" then will append time if "shift" will append am/pm/nightshift
// $starthour - where set hours will start at this
// $endhour - where set hours will end at this
// $showdate - if "false" this misses out date
// $showday - if "false" this misses out day and sets to 1
// $showmonth - if "false" this misses out month and sets to 1
// $showyear - if "false" this misses out year and sets to 1970
// $tabindex - sets tab indexs from this integer onwards
// $shortmonth - use short month name
// $minInc - minutes increment - default 5
// $submitonchange - true = auto submit form on any change - default false
// $callback - null = callback function
// $disabled = "disabled" - default null make picker disabled
// $bootstrap = true - use bootstarp styling
// ADD class='highlight-days-67 split-date format-y-m-d divider-dash' for pop up calendar
 
$inputname = isset($inputname) ? $inputname : "datetime"; 
$disabled = isset($disabled) ? $disabled : "";
$bootstrap = isset($bootstrap)  ? $bootstrap : true;
$bootstrapclass = ($bootstrap)  ? " form-control " : "";


$simpleinputname = preg_replace("/[^a-zA-Z0-9_\-]/","",$inputname); // gets rid of array identifiers
$minInc = isset($minInc) ? $minInc : 5; // increments of minutes
// echo "<input type='hidden' name='".$inputname."' value='".$setvalue."' />";
$submitonchange = (isset($submitonchange) && $submitonchange) ? "true" : "false";

?>

<link href="/core/scripts/date-picker/css/datepicker.css" rel="stylesheet"  />
<span  id="datepicker_<?php echo $inputname; ?>" class="fb_datepicker<?php echo ($bootstrap) ? "   form-inline" : "";?>">

<?php 


if (!function_exists("tabindex")) {
	function tabindex($increment) {
		global $tabindex;
	return  (isset($tabindex)) ? " tabindex=\"".($tabindex+$increment)."\"" : "";
	}
}

if (!(isset($showdate) && $showdate == false)) { 
$monthName = (isset($shortmonth)) ? array(1=>"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct","Nov", "Dec") : array(1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October","November", "December");  
$dateVals = (isset($setvalue) && $setvalue !="") ? preg_split("/[-: ]/", $setvalue) : array('','','');// get three values from date   


//check to see if start/end year are set - if so also check if the database value fits within range
$startyear = (isset($startyear) && $startyear > $dateVals[0] && $dateVals[0] >0) ? $dateVals[0] : isset($startyear) ? $startyear : date("Y")-10;
$endyear = (isset($endyear) && $endyear < $dateVals[0] && $dateVals[0] >0) ?  $dateVals[0] : isset($endyear) ? $endyear : date('Y')+10;


if(!isset($showday) || $showday==true) {
	echo "<select class=\"dom ".$bootstrapclass."\" name=\"dd-".$inputname."\" id=\"dd-".$inputname."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(1)." ".$disabled.">\n";  
	echo "<option value=\"--\">--";                    
	for($CurrentDay=1; $CurrentDay <= 31; $CurrentDay++) {

		$CurrentDay = str_pad($CurrentDay, 2, "0", STR_PAD_LEFT);
		echo "<option value=\"$CurrentDay\"";

		if(intval($dateVals[2])== intval($CurrentDay)) {  
			echo " selected=\"selected\"";  
		} 
		echo ">$CurrentDay</option>\n";  
	}  
	echo "</select>";  

} else {
	echo "<input type = \"hidden\" value = \"01\" name=\"dd-".$inputname."\" id=\"dd-".$inputname."\" >";
}

if(!isset($showmonth) || $showmonth==true) {
echo "<select class=\"month ".$bootstrapclass."\" name=\"mm-".$inputname."\" id=\"mm-".$inputname."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(0)." ".$disabled.">\n";  
echo "<option value=\"--\">--";              
for($CurrentMonth = 1; $CurrentMonth <= 12; $CurrentMonth++)  {  
	echo "<option value=\"";
	if  (intval($CurrentMonth)<10) {
		echo "0";
		echo intval($CurrentMonth); 
	} else {
		echo intval($CurrentMonth); 
	}
	echo "\"";  
	if(intval($dateVals[1])== intval($CurrentMonth)) {  
		echo " selected=\"selected\"";  
	}
	echo ">".$monthName[$CurrentMonth]."</option>\n";  
}  
echo "</select>";  
} else {
	echo "<input type = \"hidden\" value = \"01\" name=\"mm-".$inputname."\" id=\"mm-".$inputname."\" >";
}


if(!isset($showyear) || $showyear==true) {                 
echo "<select class=\"year ".$bootstrapclass."\" name=\"yy-".$inputname."\" id=\"yy-".$inputname."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(2)." ".$disabled.">\n";  

echo "<option value=\"--\">--"; 
 
for($CurrentYear = $startyear; $CurrentYear <= $endyear;$CurrentYear++) {  
echo "<option value=\"$CurrentYear\"";  
if(intval($dateVals[0])== intval($CurrentYear)) {  
echo " selected=\"selected\"";  
}
echo ">$CurrentYear</option>\n";  
}  
echo "</select>";
} else {
	echo "<input type = \"hidden\" value = \"1970\" name=\"yy-".$inputname."\" id=\"yy-".$inputname."\" >";
}

}// end show date

$display = (!isset($time) ||  $time == FALSE) ? " style=\"display:none;\" " : "";
$hour = (isset($setvalue) && $setvalue !="") ? date("H",strtotime($setvalue)) : "";
$minutes = (isset($setvalue) && $setvalue !="") ? date("i",strtotime($setvalue)) : "";
if(isset($time) && $time=="true") {
	echo "<span".$display." class = \"time\">";

	echo (!isset($showdate) || $showdate===true) ? "<span class=\"timeat\">&nbsp;at&nbsp;</span>" : ""; 
	echo "<select name=\"hh-".$inputname."\" id=\"hh-".$inputname."\" class=\"".$bootstrapclass."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(3)." ".$disabled.">\n";  
	echo "<option value=\"--\">--";              
	$starthour = isset($starthour) ? $starthour : 0;      
	$endhour = isset($endhour) ? $endhour : 23; 
	for($CurrentHour = $starthour; $CurrentHour <= $endhour; $CurrentHour++)  {    
		$CurrentHour = str_pad($CurrentHour, 2, "0", STR_PAD_LEFT);
		echo "<option value=\"".$CurrentHour."\"";  
		if($hour== $CurrentHour) {  
			echo " selected=\"selected\"";  
		}
		echo ">".$CurrentHour."</option>\n";  
	}  
	echo "</select>:"; 

	echo "<select name=\"mi-".$inputname."\"  id=\"mi-".$inputname."\"  class=\"".$bootstrapclass."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(4)." ".$disabled.">\n";  
	echo "<option value=\"--\">--";   
	$minutes = ($minutes == "") ? "" : (($minutes>0) ? floor($minutes/$minInc) * $minInc: 0);// round to nearest increment
	for($CurrentMinutes = 0; $CurrentMinutes <= 59; $CurrentMinutes += $minInc)  {  
		$CurrentMinutes = str_pad($CurrentMinutes, 2, "0", STR_PAD_LEFT);
		echo "<option value=\"".$CurrentMinutes."\"";  
		if($minutes== $CurrentMinutes) {  
			echo " selected=\"selected\"";  
		}
		echo ">".$CurrentMinutes."</option>\n";  
	}  
	echo "</select>";echo "</span></span>";
} else if(isset($time) && $time=="shift")  { 
	echo "<select name=\"hh-".$inputname."\"  id=\"hh-".$inputname."\" class=\"".$bootstrapclass."\" onChange=\"javascript:updateDate_".$simpleinputname."();\" ".tabindex(4).">\n"; 
	echo "<option value = \"04\"";
	echo ($hour>=4 && $hour < 12) ? "selected = \"selected\"" : "";
	echo ">am</option>";
	echo "<option value = \"12\"";
	echo ($hour>=12 && $hour < 20) ? "selected = \"selected\"" : "";
	echo ">pm</option>"; 
	echo "<option value = \"20\"";
	echo ($hour>=20 && $hour < 24) ? "selected = \"selected\"" : "";
	echo ">nightshift</option>";
	echo "</select><input type=\"hidden\" id=\"mi-".$inputname."\" name=\"mi-".$inputname."\" value = \"00\">";
}







?>

</span><!-- end form group-->

<script>
// if javascript on, then add date picker
document.getElementById('datepicker_<?php echo $inputname; ?>').style.display = "inline-block";

if(typeof(dateFields) === "undefined") {
	var dateFields = new Array('<?php echo $inputname; ?>');
} else {
	dateFields.push("<?php echo $inputname; ?>");
}

window.onload= function() {
	for(i=0; i<dateFields.length; i++) {
		document.getElementById(dateFields[i]).type = "hidden";
	}
}


function updateDate_<?php echo $simpleinputname; ?>() {
	document.getElementById('<?php echo $inputname; ?>').value = 
	<?php if (!(isset($showdate) && $showdate == false)) { ?>
	document.getElementById('yy-<?php echo $inputname; ?>').value+"-"+document.getElementById('mm-<?php echo $inputname; ?>').value+"-"+document.getElementById('dd-<?php echo $inputname; ?>').value<?php   if (isset($time) && $time == true) { ?>+" "+<?php }} ?>
	<?php if (isset($time) && $time == true) { ?>document.getElementById('hh-<?php echo $inputname; ?>').value+":"+document.getElementById('mi-<?php echo $inputname; ?>').value+":00"<?php } ?>;
	if (document.getElementById('<?php echo $inputname; ?>').value.indexOf("--")>=0) document.getElementById('<?php echo $inputname; ?>').value = "";
	if(<?php echo $submitonchange; ?>) document.getElementById('<?php echo $inputname; ?>').form.submit();
	<?php if(isset($callback)) {
	 echo $callback; 
	} ?>
}

// set the select values from input (function to be called if needed)
function setDate_<?php echo $simpleinputname; ?>() {
	 var dateParts = document.getElementById("<?php echo $simpleinputname; ?>").value.split("-");
      document.getElementById("yy-<?php echo $simpleinputname; ?>").value=dateParts[0];
	  document.getElementById("mm-<?php echo $simpleinputname; ?>").value=dateParts[1];
	  document.getElementById("dd-<?php echo $simpleinputname; ?>").value=dateParts[2];
}
	
</script>
<?php
unset($time,$startyear,$imputname,$showdate,$minInc,$tabindex,$setvalue,$submitonchange,$starthour,$endhour, $disabled);
?>