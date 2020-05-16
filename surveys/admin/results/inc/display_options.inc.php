<?php

$varSurveyID_rsMainSections = "-1";
if (isset($_GET['surveyID'])) {
  $varSurveyID_rsMainSections = $_GET['surveyID'];
}
mysql_select_db($database_aquiescedb, $aquiescedb);
$query_rsMainSections = sprintf("SELECT survey_section.`description`, survey_section.ID FROM survey_section WHERE survey_section.statusID = 1 AND survey_section.surveyID = %s", GetSQLValueString($varSurveyID_rsMainSections, "int"));
$rsMainSections = mysql_query($query_rsMainSections, $aquiescedb) or die(mysql_error());
$row_rsMainSections = mysql_fetch_assoc($rsMainSections);
$totalRows_rsMainSections = mysql_num_rows($rsMainSections);
?><style>
li.checked { list-style-type:disc; }
li.unchecked { list-style-type:circle; }

</style>
<form action="user_summary.php" method="get" name="form1" id="form1">
  <fieldset id="displaysettings">
  <h3>Show:</h3>
  
 <label>
<select name="username"  id="username">
  <option value="allusers" <?php if (!(strcmp("allusers", $_GET['username']))) {echo "selected=\"selected\"";} ?>>All responders</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsResponders['username']?>"<?php if (!(strcmp($row_rsResponders['username'], $_GET['username']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsResponders['jobtitle']?></option>
  <?php
} while ($row_rsResponders = mysql_fetch_assoc($rsResponders));
  $rows = mysql_num_rows($rsResponders);
  if($rows > 0) {
      mysql_data_seek($rsResponders, 0);
	  $row_rsResponders = mysql_fetch_assoc($rsResponders);
  }
?>
</select>
</label>
 <input name="surveyID" type="hidden" id="surveyID" value="<?php echo intval($_GET['surveyID']); ?>" />
  <label>
  <input name="usertypeID" type="hidden" id="usertypeID" value="<?php echo intval($_GET['usertypeID']); ?>" />
  <br />
  </label>
  <label>
  <input <?php if (!(strcmp($_GET['showsections'],1))) {echo "checked=\"checked\"";} ?> name="showsections" type="checkbox" id="showsections" value="1" />
Section Titles
<input <?php if (!(strcmp($_GET['showquestionnumbers'],1))) {echo "checked=\"checked\"";} ?> name="showquestionnumbers" type="checkbox" id="showquestionnumbers" value="1"/>
  Questions</label> 
  Numbers
  <label>
  <input <?php if (!(strcmp($_GET['showquestions'],1))) {echo "checked=\"checked\"";} ?> name="showquestions" type="checkbox" id="showquestions" value="1"/>
  Questions</label>
  <label>
  <input <?php if (!(strcmp($_GET['showcomments'],1))) {echo "checked=\"checked\"";} ?> name="showcomments" type="checkbox" id="showcomments" value="1" />
  Text Answers </label>
  <label>
  <input <?php if (!(strcmp($_GET['showmultichoice'],1))) {echo "checked=\"checked\"";} ?> name="showmultichoice" type="checkbox" id="showmultichoice" value="1"/>
  Multi-choice answers</label>
  <label for="showunchecked">
  <input <?php if (!(strcmp($_GET['showunchecked'],1))) {echo "checked=\"checked\"";} ?> name="showunchecked" type="checkbox" id="showunchecked" value="1"/>
  Unchecked answers</label>
  <label><br />
  <input <?php if (!(strcmp($_GET['showscores'],1))) {echo "checked=\"checked\"";} ?> name="showscores" type="checkbox" id="showscores" value="1"  />
  User Scores</label>
  <label>
  <input <?php if (!(strcmp($_GET['showfinalscores'],1))) {echo "checked=\"checked\"";} ?> name="showfinalscores" type="checkbox" id="showfinalscores" value="1"  />
  Final Scores</label> 
  <label> - order multi choice answers by score:
  
  <input name="orderby" type="radio" id="orderby" value="ASC" checked="checked" />
Ascending
<input type="radio" name="orderby" id="orderby" value="DESC"/>
Descending <br>
  <input <?php if (!(strcmp($_GET['showcharts'],1))) {echo "checked=\"checked\"";} ?> name="showcharts" type="checkbox" id="showcharts" value="1" />
Display Charts:
<input <?php if (!(strcmp($_GET['showsubtopicsummary'],1))) {echo "checked=\"checked\"";} ?> name="showsubtopicsummary" type="checkbox" id="showsubtopicsummary" value="1" />
  Sub Topic Summary</label>
  <label>
  <input <?php if (!(strcmp($_GET['showtopicsummary'],1))) {echo "checked=\"checked\"";} ?> name="showtopicsummary" type="checkbox" id="showtopicsummary" value="1"  />
  Topic Summary</label>
  <label>
  <input <?php if (!(strcmp($_GET['showsectionsummary'],1))) {echo "checked=\"checked\"";} ?> name="showsectionsummary" type="checkbox" id="showsectionsummary" value="1"/>
  Section Summary</label> 
   <label></label><label>
  for:
<select name="showsectionID"  id="showsectionID">
  <option value="0" <?php if (!(strcmp(0, $_GET['showsectionID']))) {echo "selected=\"selected\"";} ?>>All</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsMainSections['ID']?>"<?php if (!(strcmp($row_rsMainSections['ID'], $_GET['showsectionID']))) {echo "selected=\"selected\"";} ?>><?php echo $row_rsMainSections['description']?></option>
  <?php
} while ($row_rsMainSections = mysql_fetch_assoc($rsMainSections));
  $rows = mysql_num_rows($rsMainSections);
  if($rows > 0) {
      mysql_data_seek($rsMainSections, 0);
	  $row_rsMainSections = mysql_fetch_assoc($rsMainSections);
  }
?>
</select>
  </label>
   <label>
   <input <?php if (!(strcmp($_GET['showsurveyscores'],1))) {echo "checked=\"checked\"";} ?> name="showsurveyscores" type="checkbox" id="showsurveyscores" value="1"/>
Full Survey Summary</label>
   <br />
   <label>
   <input <?php if (!(strcmp($_GET['show2008'],1))) {echo "checked=\"checked\"";} ?>  type="checkbox" name="show2008" id="show2008" value="1" />
   2008 scores</label>
   <label>
   <input <?php if (!(strcmp($_GET['show2005'],1))) {echo "checked=\"checked\"";} ?>  type="checkbox" name="show2005" id="show2005"  value="1"/>
   2005 scores</label>
   <label>
   <input <?php if (!(strcmp($_GET['showscotlandaverage'],1))) {echo "checked=\"checked\"";} ?>  type="checkbox" name="showscotlandaverage" id="showscotlandaverage" value="1" />
   Show Scotland average</label>
    <label>
   <input <?php if (!(strcmp($_GET['showscotlandaverage2005'],1))) {echo "checked=\"checked\"";} ?>  type="checkbox" name="showscotlandaverage2005" id="showscotlandaverage2005" value="1" />
   include 2005 average</label><label>
   <input <?php if (!(strcmp($_GET['showsection9'],1))) {echo "checked=\"checked\"";} ?>  type="checkbox" name="showsection9" id="showsection9" value="1" />
   Section 9</label>
   <br />
   <input type="submit" class="button" onClick="document.getElementById('loading').style.display = 'inline';" value="Refresh"/>
  <span id="loading" style="display:none;">&nbsp;<img src="../../../../core/images/loading_16x16.gif" alt="Loading - please wait" width="16" height="16" style="vertical-align:
middle;" />&nbsp;Please wait...</span></p>
  <a href="../index.php">Back to surveys</a>
  </fieldset>
</form><p>&nbsp;</p>
<script>if (document.getElementById('showmultichoice').checked) { document.getElementById('showunchecked').disabled = false; } else {document.getElementById('showunchecked').disabled = true; document.getElementById('showunchecked').checked = false;}</script>
<?php
mysql_free_result($rsMainSections);
?>
