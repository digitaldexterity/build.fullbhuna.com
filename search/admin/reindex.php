<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}




$isearch_path = '..';
define('IN_ISEARCH', true);

$fromCommandLine = False;
if (isset($_SERVER['argv']))
{
    if ((isset($_SERVER['argv'][0])) && (preg_match('/reindex\\.php$/i', $_SERVER['argv'][0])))
    {
        $fromCommandLine = True;
    }
    else
    {
        foreach ($_SERVER['argv'] as $arg)
        {
            if ($arg == 'cmd')
            {
                $fromCommandLine = True;
            }
        }
    }
}

if (((!isset($_SERVER['PHP_SELF'])) && (!isset($_SERVER['PHP_SELF']))) || isset($_REQUEST['cmd']))
{
    $fromCommandLine = True;
}

if ($fromCommandLine)
{
    chdir(preg_replace('/reindex\\.php$/i', '', __FILE__));
}

require_once("$isearch_path/inc/core.inc.php");
isearch_open();
require_once("$isearch_path/inc/spider.inc.php");

if ($fromCommandLine)
{
    $isearch_maxSpiderTime = 9999999;

    $reset = 'true';
    $pause = 'false';

    ini_set('max_execution_time', 0);
    isearch_reset();
}
else
{
    //require_once("$isearch_path/inc/admin_auth.inc.php");

    $isearch_maxSpiderTime = 10;

    $reset = isset($_REQUEST['reset']) ? $_REQUEST['reset'] : 'true';
    $pause = isset($_REQUEST['pause']) ? $_REQUEST['pause'] : 'false';

    if ($reset == 'false')
    {
        $getFocus = '';
    }
    else
    {
        $getFocus = 'self.focus();';
    }

   ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
<title>Reindex Site</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="EN-GB">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Fri, 01 Jan 1999 00:00:01 GMT">
<META NAME="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="admin.css" >
</head>

<body bgcolor="#ffffc0" text="#000000" onload="<?php echo $getFocus; ?>">

<script>
<!--
menuFrame = parent.isearch_reindex_menu;
if (menuFrame != null)
{
    menuDoc = menuFrame.document;
    if (menuDoc.form1.finished != null)
    {
        menuDoc.form1.finished.value = 'false';
    }
}
// -->
</script>

<?php 

    if ($pause == 'true')
    {
        $found = isearch_getUrlCount(True);
        $new = isearch_getUrlCount(True, 'new');
        $indexed = $found - $new;

	 ?>
<script>
<!--
menuFrame = parent.isearch_reindex_menu;
if (menuFrame != null)
{
    menuDoc = menuFrame.document;
    menuDoc.form1.state.value = 'paused [found: <?php echo $found; ?>, indexed: <?php echo $indexed; ?>]';
}
// -->
</script>

<h3>Spidering Paused</h3>
<A href="<?php echo $_SERVER['PHP_SELF']; ?>?reset=false">Resume</A>


<?php echo "</body></html>";
        isearch_close();
        exit;
    }
    else if ($reset == 'false')
    {
        echo "<h3>Spidering Continuing...</h3>\n";
    }
    else
    {
        echo "<h3>Spidering Starting Now...</h3>\n";
        isearch_reset();
    }

    echo str_pad(' ', 256);
    flush();
}

$startTime = time();
$pageCount = 0;
do
{
    $moreToDo = isearch_indexAFile(True);

    if (!$fromCommandLine)
    {
        echo str_pad(' ', 256);
        flush();
    }

    if ($isearch_config['spider_delay'] > 0)
    {
        sleep($isearch_config['spider_delay']);
    }

    $currentTime = time();
    $pageCount ++;
} while (($moreToDo) && (($currentTime - $startTime) < $isearch_maxSpiderTime));

if ($fromCommandLine)
{
    echo "Total time " . ($currentTime - $startTime) . " secs for $pageCount pages\n";
    if (($pageCount > 0) && (($currentTime - $startTime) > 0))
    {
        echo ($pageCount / ($currentTime - $startTime)) . " pages per second\n";
    }
}
else
{
    $found = isearch_getUrlCount($moreToDo);
    $new = isearch_getUrlCount($moreToDo, 'new');
    $indexed = $found - $new;

    if ($moreToDo)
    {
       ?>
<script>
<!--
newLocation='<?php echo $_SERVER['PHP_SELF']; ?>?reset=false';
menuFrame = parent.isearch_reindex_menu;
if (menuFrame != null)
{
    menuDoc = menuFrame.document;
    if (menuDoc.form1.pause.value == 'true')
    {
        newLocation += '&pause=true';
        menuDoc.form1.pause.value = 'false';
    }
    else
    {
        menuDoc.form1.state.value = 'spidering [found: <?php echo $found; ?>, indexed: <?php echo $indexed; ?>]';
    }
}
setTimeout(function() {
	window.location=newLocation;
},1000);
// -->
</script>

<?php echo "</body><noscript>
<head>
    <meta http-equiv=\"refresh\" content=\"1;URL=".$_SERVER['PHP_SELF']."?reset=false\">
</head>
</noscript></html>";
    }
    else
    {
        $count = isearch_getUrlCount();

	?>
<h3>...Spidering Completed.</h3>
<p>Found a total of <?php echo $count; ?> pages.
<p><A href="log.php" TARGET="isearch_spider_log">View Spider Log</A>

<script>
<!--
menuFrame = parent.isearch_reindex_menu;
if (menuFrame != null)
{
    menuDoc = menuFrame.document;
    menuDoc.form1.state.value = 'completed [found: <?php echo $found; ?>, indexed: <?php echo $indexed; ?>]';
    menuDoc.form1.finished.value = 'true';
}
// -->
</script>

</body>
</html>

<?php  }
}

isearch_close();

?>
