<?php

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

if ( !defined('IN_ISEARCH') )
{
    die('Hacking attempt');
}

if (isset($_SERVER['PHP_SELF']))
{
    $_SERVER['PHP_SELF'] = $_SERVER['PHP_SELF'];
}

/* Clear the admin log file */
function isearch_clearAdminLog()
{
    global $regionID, $isearch_table_admin_log;
    global $isearch_db;

    mysql_query("DELETE FROM $isearch_table_admin_log WHERE regionID =".$regionID, $isearch_db);
}


/* Return the contents of the admin log */
function isearch_getAdminLog()
{
    global $regionID, $isearch_table_admin_log;
    global $isearch_db;

    $log = '';

    $result = mysql_query("SELECT * FROM $isearch_table_admin_log WHERE regionID = ".$regionID." ORDER BY id", $isearch_db);
    if ($result)
    {
        while ($item = mysql_fetch_object($result))
        {
            $log .= date('M d, Y, H:i:s - ', $item->time) . $item->msg . "\n";
        }
    }

    return $log;
}


/* Save the string in the admin log file */
function isearch_adminLog($string, $level=1)
{
    global $isearch_table_admin_log, $regionID;
    global $isearch_db;

    $now = time();
    mysql_query("INSERT INTO $isearch_table_admin_log (msg, time, regionID) VALUES ('" . mysql_escape_string($string) . "', '$now', ".$regionID.")", $isearch_db);

    if ($level <= 5)
    {
        echo $string . "<BR>\n";
    }
}


$isearch_admin = '';
if (isset($_REQUEST['isearch_password']))
{
    /* Remember the password in a session cookie */
    if ($_REQUEST['isearch_password'] != '')
    {
        $isearch_admin = md5($_REQUEST['isearch_password']);
    }
    setcookie('isearch_admin', $isearch_admin, isset($_REQUEST['isearch_remember_me']) ? 2147483647 : 0);
    if (md5($isearch_admin_password) == $isearch_admin)
    {
        isearch_adminLog('Admin login in from IP address : ' . $_SERVER['REMOTE_ADDR'], 5);
    }
    else if ($_REQUEST['isearch_password'] == '')
    {
        isearch_adminLog('Admin logged out from IP address : ' . $_SERVER['REMOTE_ADDR'], 5);
    }
    else
    {
        isearch_adminLog('!!! Admin login FAILED from IP address : ' . $_SERVER['REMOTE_ADDR'] . ' !!!', 5);
    }
}
else if (isset($_COOKIE['isearch_admin']))
{
    $isearch_admin = $_COOKIE['isearch_admin'];
}

if ($isearch_admin_password != '')
{
    if (md5($isearch_admin_password) != $isearch_admin)
    {
        if ($isearch_admin != '')
        {
            sleep(3);    /* Delay to help prevent password cracking */
            echo "<p>Incorrect password\n";
        }

        /* Prompt for admin password */
        echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="en">
<head>
<title>iSearch Configuration</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="EN-GB">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Fri, 01 Jan 1999 00:00:01 GMT">
<meta name="author" content="Ian Willis">
<meta name="copyright" content="Copyright Ian Willis. All rights reserved.">
<meta name="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="admin.css" >
</head>

<body BGCOLOR="#ffffc0" TEXT="#000000">

<CENTER><H1>iSearch $isearch_version Configuration</H1></CENTER>

<CENTER>
<p>Please enter your iSearch administrator password:
<p>
<TABLE BGCOLOR="#c0ffff" CELLPADDING="5">
<FORM method="post" action="$_SERVER['PHP_SELF']">
  <TR>
    <TD><B>iSearch Administrator Password:</B></TD>
    <TD><INPUT MAXLENGTH="20" TYPE="password" NAME="isearch_password" SIZE="20"></TD>
  </TR>
  <TR>
    <TD><B>Remember My Password On This Computer:</B></TD>
    <TD><INPUT TYPE="checkbox" NAME="isearch_remember_me"></TD>
  </TR>

  <TR>
    <CENTER>
    <TD COLSPAN=2 ALIGN="CENTER"><INPUT TYPE="submit" VALUE="Login"></TD>
    </CENTER>
  </TR>
</FORM>
</TABLE>
</CENTER>
</body>
</html>
EOF;
        exit;
    }
}

?>