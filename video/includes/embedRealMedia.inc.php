<!-- begin embedded RealMedia file... -->

<table border='0' align="left" cellpadding='0' class="form-table">
  <!-- begin video window... -->
  <tr>
    <td><object id='rvocx' classid='clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA'
        width="320" height="240">
        <param name='src' value="http://servername/path/to/media.file" />
        <param name='autostart' value="true" />
        <param name='controls' value='imagewindow' />
        <param name='console' value='video' />
        <param name='loop' value="true" />
        <embed src="//servername/path/to/media.file" width="320" height="240" 
        loop="True" type='audio/x-pn-realaudio-plugin' controls='imagewindow' console='video' autostart="True"> </embed>
    </object></td>
  </tr>
  <!-- ...end video window -->
  <!-- begin control panel... -->
  <tr>
    <td><object id='rvocx' classid='clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA'
          width="320" height='30'>
        <param name='src' value="http://servername/path/to/media.file" />
        <param name='autostart' value="true" />
        <param name='controls' value='ControlPanel' />
        <param name='console' value='video' />
        <embed src="//servername/path/to/media.file" width="320" height='30' 
          controls='ControlPanel' type='audio/x-pn-realaudio-plugin' console='video' autostart="True"> </embed>
    </object></td>
  </tr>
  <!-- ...end control panel -->
  <!-- ...end embedded RealMedia file -->
  <!-- begin link to launch external media player... -->
  <tr>
    <td align='center'><a href="http://servername/path/to/media.file" target='_blank' style='font-size: 85%;' rel="noopener">Launch in external player</a>
      <!-- ...end link to launch external media player... --></td>
  </tr>
</table>
