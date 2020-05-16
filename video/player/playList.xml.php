<?php
header('Content-Type: text/xml');
header('Content-Disposition: inline; filename=playList.xml');
echo '<?xml version="1.0"  encoding="UTF-8"?>';
?>

<player>
  <video>
    <videoList>
      <source><?php echo htmlentities($_GET['src']); ?></source>
    </videoList>
    <videoAdsList>
      <source></source>
    </videoAdsList>
    <videoAdsLinkList>
      <source></source>
    </videoAdsLinkList>
    <thumbnailList>
      <source><?php echo htmlentities($_GET['preview']); ?></source>
    </thumbnailList>
    <smalldescrPlayList>
      <description>
        <?php echo "<![CDATA[new element]]>"; ?>
      </description>
    </smalldescrPlayList>
    <smallthumbPlayList>
      <source></source>
    </smallthumbPlayList>
    <videoSubtitleList>
      <source></source>
    </videoSubtitleList>
    <videoLinkList>
      <source></source>
    </videoLinkList>
    <videoLinkTargetList>
      <source></source>
    </videoLinkTargetList>
    <videoAdsLinkTargetList>
      <source></source>
    </videoAdsLinkTargetList>
  </video>
</player>
