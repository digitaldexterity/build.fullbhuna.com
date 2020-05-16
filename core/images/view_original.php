<?php require_once('../../Connections/aquiescedb.php'); ?>
<!doctype html>
<html class="" lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width">
<title><?php $pageTitle = "View original image"; echo $pageTitle." | ".$site_name; $pageTitle .= ": ".htmlentities($_GET['imageURL'], ENT_COMPAT, "UTF-8"); ?></title>
<?php require_once('../seo/includes/seo.inc.php'); trackPage(@$pageTitle); ?>
<link href="/local/css/styles.css" rel="stylesheet"  />
<link href="/core/css/global.css" rel="stylesheet"  />
</head>
<body>
<?php if(is_readable(SITE_ROOT."Uploads/".htmlentities($_GET['imageURL']))) { ?>
<img src="/Uploads/<?php echo htmlentities($_GET['imageURL']); ?>" alt="Original Image" />
<?php } else { ?>
<p>The full size version of this image is not available.</p>
<?php } ?>
<p class="link_back"><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">Back</a></p>
</body>
</html>
