<script src="/video/Scripts/AC_RunActiveContent.js"></script>
<script src="/video/Scripts/swfobject_modified.js"></script>
<?php $streamName = explode(".",$videoURL); ?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="<?php echo VIDEO_DEFAULT_WIDTH; ?>" height="<?php echo VIDEO_DEFAULT_HEIGHT; ?>" id="FLVPlayer">
          <param name="movie" value="FLVPlayer_Progressive.swf" />
          <param name="quality" value="high" />
          <param name="wmode" value="opaque" />
          <param name="scale" value="noscale" />
          <param name="salign" value="lt" />
          <param name="FlashVars" value="&amp;MM_ComponentVersion=1&amp;skinName=Corona_Skin_3&amp;streamName=<?php echo $streamName[0]; ?>&amp;autoPlay=false&amp;autoRewind=false" />
          <param name="swfversion" value="8,0,0,0" />
          <!-- This param tag prompts users with Flash Player 6.0 r65 and higher to download the latest version of Flash Player. Delete it if you don’t want users to see the prompt. -->
          <param name="expressinstall" value="../Scripts/expressInstall.swf" />
          <!-- Next object tag is for non-IE browsers. So hide it from IE using IECC. -->
          <!--[if !IE]>-->
          <object type="application/x-shockwave-flash" data="FLVPlayer_Progressive.swf" width="<?php echo VIDEO_DEFAULT_WIDTH; ?>" height="<?php echo VIDEO_DEFAULT_HEIGHT; ?>">
            <!--<![endif]-->
            <param name="quality" value="high" />
            <param name="wmode" value="opaque" />
            <param name="scale" value="noscale" />
            <param name="salign" value="lt" />
            <param name="FlashVars" value="&amp;MM_ComponentVersion=1&amp;skinName=Corona_Skin_3&amp;streamName=<?php echo $streamName[0]; ?>&amp;autoPlay=false&amp;autoRewind=false" />
            <param name="swfversion" value="8,0,0,0" />
            <param name="expressinstall" value="../Scripts/expressInstall.swf" />
            <!-- The browser displays the following alternative content for users with Flash Player 6.0 and older. -->
            <div>
              <h4>Content on this page requires a newer version of Adobe Flash Player.</h4>
              <p><a href="http://www.adobe.com/go/getflashplayer"><img src="//www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
            </div>
            <!--[if !IE]>-->
          </object>
          <!--<![endif]-->
        </object>
         <script>
<!--
swfobject.registerObject("FLVPlayer");
//-->
    </script>