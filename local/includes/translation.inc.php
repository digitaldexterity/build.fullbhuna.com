<?php // Copyright 2009 Paul Egan ?>
<?php if(is_readable(SITE_ROOT."/core/region/translate.php")) {
	$transurl = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on") ? "https://" : "http://";
	$transurl = urlencode($transurl.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>

<div class="translation">
  <form action="/core/region/translate.php" method="get" role="form">
    <select name="translate" id="translate" onchange="this.form.submit();">
      <option value="">Translate this page to... (choose)</option>
      <option  value="Afrikaans|<?php echo $transurl; ?>|af|">Afrikaans</option>
      <option  value="Albanian|<?php echo $transurl; ?>|sq|">Albanian</option>
      <option value="Arabic|<?php echo $transurl; ?>|ar|">Arabic</option>
      <option  value="Belarusian|<?php echo $transurl; ?>|be|">Belarusian</option>
      <option  value="Bulgarian|<?php echo $transurl; ?>|bg|">Bulgarian</option>
      <option  value="Catalan|<?php echo $transurl; ?>|ca|">Catalan</option>
      <option value="Chinese|<?php echo $transurl; ?>|zh-CN|">Chinese</option>
      <option  value="Croatian|<?php echo $transurl; ?>|hr|">Croatian</option>
      <option value="Czech|<?php echo $transurl; ?>|cs|">Czech</option>
      <option  value="Danish|<?php echo $transurl; ?>|da|">Danish</option>
      <option  value="Dutch|<?php echo $transurl; ?>|nl|">Dutch</option>
      <!--<option  value="English|<?php echo $transurl; ?>|en|">English</option>-->
      <option  value="Estonian|<?php echo $transurl; ?>|et|">Estonian</option>
      <option  value="Filipino|<?php echo $transurl; ?>|tl|">Filipino</option>
      <option  value="Finnish|<?php echo $transurl; ?>|fi|">Finnish</option>
      <option value="French|<?php echo $transurl; ?>|fr|">French</option>
      <option  value="Galician|<?php echo $transurl; ?>|gl|">Galician</option>
      <option value="German|<?php echo $transurl; ?>|det|">German</option>
      <option value="Greek|<?php echo $transurl; ?>|el|">Greek</option>
      <option  value="Hebrew|<?php echo $transurl; ?>|iw|">Hebrew</option>
      <option value="Hindi|<?php echo $transurl; ?>|hi|">Hindi</option>
      <option  value="Hungarian|<?php echo $transurl; ?>|hu|">Hungarian</option>
      <option  value="Icelandic|<?php echo $transurl; ?>|is|">Icelandic</option>
      <option  value="Indonesian|<?php echo $transurl; ?>|id|">Indonesian</option>
      <option  value="Irish|<?php echo $transurl; ?>|ga|">Irish Gaelic</option>
      <option value="Italian|<?php echo $transurl; ?>|it|">Italian</option>
      <option value="Japanese|<?php echo $transurl; ?>|ja|">Japanese</option>
      <option  value="Korean|<?php echo $transurl; ?>|ko|">Korean</option>
      <option  value="Latvian|<?php echo $transurl; ?>|lv|">Latvian</option>
      <option value="Lithuanian|<?php echo $transurl; ?>|lt|">Lithuanian</option>
      <option  value="Macedonian|<?php echo $transurl; ?>|mk|">Macedonian</option>
      <option  value="Malay|<?php echo $transurl; ?>|ms|">Malay</option>
      <option  value="Maltese|<?php echo $transurl; ?>|mt|">Maltese</option>
      <option  value="Norwegian|<?php echo $transurl; ?>|no|">Norwegian</option>
      <option value="Polish|<?php echo $transurl; ?>|pl|">Polish</option>
      <option value="Portuguese|<?php echo $transurl; ?>|pt|">Portuguese</option>
      <option value="Romanian|<?php echo $transurl; ?>|ro|">Romanian</option>
      <option value="Russian|<?php echo $transurl; ?>|ru|">Russian</option>
      <option value="Serbian|<?php echo $transurl; ?>|sr|">Serbian </option>
      <option value="Slovak|<?php echo $transurl; ?>|sk|">Slovak</option>
      <option  value="Slovenian|<?php echo $transurl; ?>|sl|">Slovenian</option>
      <option value="Spanish|<?php echo $transurl; ?>|es|">Spanish</option>
      <option  value="Swahili|<?php echo $transurl; ?>|sw|">Swahili</option>
      <option  value="Swedish|<?php echo $transurl; ?>|sv|">Swedish</option>
      <option  value="Thai|<?php echo $transurl; ?>|th|">Thai</option>
      <option  value="Turkish|<?php echo $transurl; ?>|tr|">Turkish</option>
      <option  value="Ukrainian|<?php echo $transurl; ?>|uk|">Ukrainian</option>
      <option  value="Urdu|<?php echo $transurl; ?>|ur|">Urdu</option>
      <option  value="Vietnamese|<?php echo $transurl; ?>|vi|">Vietnamese</option>
      <option  value="Welsh|<?php echo $transurl; ?>|cy|">Welsh</option>
      <option  value="Yiddish|<?php echo $transurl; ?>|yi|">Yiddish</option>
      <option value="">More languages soon</option>
    </select>
    <input name="go" type="submit" class="button" id="go" value="Go" />
   
  </form>
</div>
<?php } ?>