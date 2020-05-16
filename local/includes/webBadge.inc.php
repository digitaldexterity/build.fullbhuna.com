<?php if(defined('DEVELOPER_NAME')) { 
				 $version = isset($pageTitle) ? strlen($pageTitle)%10 : 0;
switch($version) {
	case 0 : $text = "Web by ".DEVELOPER_NAME.""; break;
	case 1 : $text = "".DEVELOPER_NAME.""; break;
	case 2 : $text = "Web site developer"; break;
	case 3 : $text = "Web designer"; break;
	case 4 : $text = "Site by ".DEVELOPER_NAME.""; break;
	case 5 : $text = "Design by ".DEVELOPER_NAME.""; break;
	case 6 : $text = "Programmed by ".DEVELOPER_NAME.""; break;
	case 7 : $text = "Made with care by ".DEVELOPER_NAME.""; break;
	case 8 : $text = "Built at ".DEVELOPER_NAME.""; break;
	default : $text = "Contact Web Site Technical Support";
}?>
<div id="webBadge" ><a href="<?php echo DEVELOPER_URL; ?>" title="This web site was built by <?php echo DEVELOPER_NAME; ?> Web Design Glasgow" target="_blank" rel="noopener" rev="vote-for" style="display:block; width:100px; height:27px; background-repeat:no-repeat; background-image:url('<?php echo DEVELOPER_BADGE; ?>'); text-indent:-3000px;"><?php echo $text; ?></a></div>
<?php } ?>
