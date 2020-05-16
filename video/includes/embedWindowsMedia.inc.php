<object id='mediaPlayer' width="<?php echo VIDEO_DEFAULT_WIDTH; ?>" height="<?php echo VIDEO_DEFAULT_HEIGHT; ?>" 
      classid='CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6'
      codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701'
      standby='Loading Microsoft Windows Media Player components...' type='application/x-oleobject'>
  <param name='URL' value="<?php echo $videoURL; ?>" />
  <param name='animationatStart' value='true' />
  <param name='transparentatStart' value='true' />
  <param name='autoStart' value="false" />
  <param name='showControls' value="true" />
  <param name='loop' value="false" />
  <embed type='application/x-mplayer2'
        pluginspage='http://microsoft.com/windows/mediaplayer/en/download/'
        id='mediaPlayer' name='mediaPlayer' displaysize='4' autosize='-1' 
        bgcolor='darkblue' showcontrols="true" showtracker='-1' 
        showdisplay='0' showstatusbar='-1' videoborder3d='-1' width="<?php echo VIDEO_DEFAULT_WIDTH; ?>" height="<?php echo VIDEO_DEFAULT_HEIGHT; ?>"
        src="<?php echo $videoURL; ?>" autostart="False" designtimesp='5311' loop="False"> </embed>
</object>
<!-- for earlier versions of media playe prior to 7 , use  classid='CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95'
fileName instead of URL

7 and later classid='CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6'  -->
