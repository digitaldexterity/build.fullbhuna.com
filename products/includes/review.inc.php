<!-- reviews-->
<?php  
global $rsReviews, $row_rsReviews,$totalRows_rsReviews,$row_rsProductPrefs, $row_rsLoggedIn;



if (isset($row_rsProductPrefs['allowcomments']) && $row_rsProductPrefs['allowcomments']==1 ) { ?>
<!-- ANCHOR ID reviews-->
<?php if($row_rsPreferences['captcha_type']==3  && trim($row_rsPreferences['recaptcha_site_key'])!="") { ?><script src='https://www.google.com/recaptcha/api.js' async defer></script>
<script>
// function below only used for invisbile reCaptcha [3]
       function onReviewSubmit(token) {
         document.getElementById("reviewform").submit();
       }
     </script>
<?php } ?>
<div class="reviews" id="productreviews">
  <h2>Customer ratings &amp; reviews</h2>
  
  <?php   if($totalRows_rsReviews>0) { 
 
   do { 
  
  if(isset($row_rsReviews['message']) && trim($row_rsReviews['message'])!="") { ?>
   <div class="review" itemscope itemtype="https://schema.org/Review">
    <?php $rating = isset($row_rsReviews['rating']) ? $row_rsReviews['rating'] : 0;  ?>
    <span itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating" class="rating starrating rating<?php echo $rating; ?>">Rating: <span itemprop="ratingValue"><?php echo $rating; ?></span>
   <meta itemprop="best" content="10" /></span>
    
    <strong class="postername" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name"><?php echo htmlentities(anonymiser($row_rsReviews['posterfirstname'],$row_rsReviews['postersurname']), ENT_COMPAT, "UTF-8"); ?></span> says: </strong><span itemprop="review"><?php echo htmlentities($row_rsReviews['message'], ENT_COMPAT, "UTF-8"); ?></span></div>
  <?php } // END IS MESSAGE 
  } while ($row_rsReviews = mysql_fetch_assoc($rsReviews));  ?>
 
 <?php  }  if(!isset($_POST['addreview']) && $totalRows_rsReviews ==0) { ?>
 <p>There are currently no reviews for this product. Be the first to add a review of this product below.</p>
  <?php }// END IS no REVIEWS
  ?>
  <form action="<?php echo $editFormAction; ?>" method="post"  name="reviewform" id="reviewform" class="reviewform" >
   <h3>Add your review</h3><?php if($row_rsProductPrefs['commentsmemberonly']==0 || isset($_SESSION['MM_Username'])) { ?> <table class="form-table">
      <tr class="rating">
        <td class="text-nowrap text-right">Overall rating:</td>
        <td><div class="stars">
            <label>
              <input id="rating-1" name="rating" type="radio" value="1" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==1) echo "checked"; ?>/>
              1</label>
            <label>
              <input id="rating-2" name="rating" type="radio" value="2" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==2) echo "checked"; ?>/>
              2</label>
            <label>
              <input id="rating-3" name="rating" type="radio" value="3" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==3) echo "checked"; ?>/>
              3</label>
            <label>
              <input id="rating-4" name="rating" type="radio" value="4" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==4) echo "checked"; ?>/>
              4</label>
            <label>
              <input id="rating-5" name="rating" type="radio" value="5" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==5) echo "checked"; ?>/>
              5</label>
            <label>
              <input id="rating-6" name="rating" type="radio" value="6" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==6) echo "checked"; ?>/>
              6</label>
            <label>
              <input id="rating-7" name="rating" type="radio" value="7" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==7) echo "checked"; ?>/>
              7</label>
            <label>
              <input id="rating-8" name="rating" type="radio" value="8" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==8) echo "checked"; ?>/>
              8</label>
            <label>
              <input id="rating-9" name="rating" type="radio" value="9" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==9) echo "checked"; ?>/>
              9</label>
            <label>
              <input id="rating-10" name="rating" type="radio" value="10" <?php if(isset($_REQUEST['rating']) && $_REQUEST['rating']==10) echo "checked"; ?>/>
              10</label>
          </div></td>
      </tr> <tr>
        <td class="text-nowrap text-right top"><label for="fullname">Your name:</label></td>
        <td>
        <input name="fullname" type="text" size="50" maxlength="255"  value="<?php echo isset($_POST['fullname']) ? htmlentities($_POST['fullname'], ENT_COMPAT, "UTF-8" ) : (isset($row_rsLoggedIn['surname']) ? $row_rsLoggedIn['firstname']." ". $row_rsLoggedIn['surname']: ""); ?>"></td>
      </tr>      
      <?php if(isset($row_rsProductPrefs['commentsemail']) && $row_rsProductPrefs['commentsemail']==1) { // show location ?> <tr>
        <td class="text-nowrap text-right top">Your email:</td>
        <td><input name="email" type="email" size="50" maxlength="255"  value="<?php echo isset($_POST['email']) ? htmlentities($_POST['email'], ENT_COMPAT, "UTF-8" ): (isset($row_rsLoggedIn['email']) ? $row_rsLoggedIn['email']: ""); ?>" /></td>
      </tr>
      <?php } ?> 
      
      
     <?php if(isset($row_rsProductPrefs['commentslocation']) && $row_rsProductPrefs['commentslocation']==1) { // show location ?> <tr>
        <td class="text-nowrap text-right top">Your town/city:</td>
        <td><input name="locationname" type="text" size="50" maxlength="255"  value="<?php isset($_POST['locationname']) ? htmlentities($_POST['locationname'], ENT_COMPAT, "UTF-8" ): ""; ?>" /></td>
      </tr>
      <?php } ?> <tr>
        <td class="text-nowrap text-right top">Comment:</td>
        <td><textarea name="message" cols="50" rows="5"><?php echo isset($_POST['message']) ? htmlentities($_POST['message'], ENT_COMPAT, "UTF-8" ): ""; ?></textarea></td>
      </tr><?php if(isset($row_rsProductPrefs['commentscaptcha']) && $row_rsProductPrefs['commentscaptcha']==1) { // show captcha ?> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td>
                          
                          
                          <img src="/core/includes/random_image.php" alt="Security image" />
                        
        </td>
      </tr> <tr>
        <td class="text-nowrap text-right"><label for="captcha_answer">Security letters:</label></td>
        <td><input name="captcha_answer" type="text"  id="captcha_answer" value="Type the letters shown above" size="30" maxlength="30"  style="color:#999999" onfocus="this.value='';this.style.color= '#000000';" />
                           </td>
      </tr><?php } // end show captcha ?> <tr>
        <td class="text-nowrap text-right">&nbsp;</td>
        <td><button type="submit" class="btn btn-primary<?php if($row_rsPreferences['captcha_type']==3) {  ?> g-recaptcha" data-sitekey="<?php echo $row_rsPreferences['recaptcha_site_key']; ?>" data-callback="onReviewSubmit<?php } ?>">Submit review</button></td>
      </tr>
    </table>
    <input name="IPaddress" type="hidden"  value="<?php echo getClientIP(); ?>" />
    <input name="addreview" type="hidden"  value="true" />
    <input type="hidden" name="productID" value="<?php echo $row_rsProduct['ID']; ?>">
    <input type="hidden" name="userID" value="<?php $userID = isset($row_rsLoggedIn['ID']) ? $row_rsLoggedIn['ID'] : 0; echo $userID; ?>">
        <input type="hidden" name="productname" value="<?php echo $row_rsProduct['title']; ?>">
		<input type="hidden" name="token" value="<?php echo md5(PRIVATE_KEY.$row_rsProduct['ID'].$userID.getClientIP()); ?>"><?php } else { ?>
        <p>Please <a href="/login/index.php?accesscheck=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">log in</a> or <a href="/login/signup.php?returnURL=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">sign up</a> to post reviews</p>
        <?php } ?>
  
  </form>
</div>
<?php } ?>
<!-- end reviews --> 