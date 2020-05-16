<div>
  <?php  $nav_instance ++ ; $nextpage = sprintf("%s?pageNum_rsQuestions=%d%s&next=true", $currentPage, min($totalPages_rsQuestions, $pageNum_rsQuestions + 1), $queryString_rsQuestions); 
  $prevpage = sprintf("%s?pageNum_rsQuestions=%d%s&previous=true", $currentPage, max(0, $pageNum_rsQuestions - 1), $queryString_rsQuestions);  
  $exitURL = (isset($row_rsSurvey['summaryend']) && $row_rsSurvey['summaryend']==1) ? "summary.php" : "finish.php"; $exitURL .="?surveyID=".intval($_GET['surveyID']); 
  $finishURL = $exitURL."&finish=true"; ?>
  
  <!-- not used yet - but will be for multi question pages-->
  <input type="hidden" name="nextpage" value="<?php echo $nextpage; ?>" />
  <input type="hidden" name="prevpage" value="<?php echo $prevpage; ?>" />
  <input type="hidden" name="exitURL" value="<?php echo $exitURL; ?>" />
 
  <?php if ($pageNum_rsQuestions > 0) { // Show if not first page ?>
  <button name="previous" class="previous btn btn-default btn-secondary" id="previous_<?php echo $nav_instance; ?>" type="button"   onclick="checkSubmit('<?php echo $prevpage; ?>', false);"  >&lt; Previous</button>
  <?php } // Show if not first page ?>
  
  <?php if ($row_rsThisSurvey['showsummary'] == 1) { ?>
  <button name="summary" id="summary_<?php echo $nav_instance; ?>" type="submit" class="btn btn-default btn-secondary" onclick="checkSubmit('summary.php?surveyID=<?php echo intval($_GET['surveyID']); ?>', true);" >Index</button>
  <?php } ?>
  
  
  <?php if(isset($_SESSION['MM_Username'])) { // a user session so they can come back later ?><button name="exit" id="exit_<?php echo $nav_instance; ?>"  class="btn btn-default btn-secondary" type="submit"  onClick = "if(confirm('Are you sure you want to exit?\n\nYour answers will be saved but NOT submitted.\n\n(To submit instead, press Cancel then click Finish on final question.)')) { checkSubmit('<?php  echo $exitURL; ?>', true); }  return false;" >Save for later</button><?php } ?>
  
  
  <?php if ($pageNum_rsQuestions < $totalPages_rsQuestions) { // Show if not last page ?>  
  <button name="next" class="next btn btn-primary" id="next_<?php echo $nav_instance; ?>" type="submit"  onclick="checkSubmit('<?php echo $nextpage; ?>', true); return false;" >Next &gt;</button>
  <?php } else { ?>
   
    
    
    <button class="btn btn-primary" name="complete" type="submit" id="complete_<?php echo $nav_instance; ?>"  onClick = "if(confirm('CONFIRMATION: Please confirm you wish to submit all your final answers.\n\nPLEASE WAIT for answers to be submitted and confirmation page to appear.')) { checkSubmit('<?php echo $finishURL; ?>');  } return false;" >Finish</button>
  <?php } ?>
  <img  src="/core/images/loading_16x16.gif" alt="Loading - please wait" width="16" height="16" class="questionLoading" style="vertical-align:
middle;" />
  </div>
