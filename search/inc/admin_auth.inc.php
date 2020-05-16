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
    global $isearch_table_admin_log, $regionID;
    global $isearch_db;

    mysql_query("DELETE FROM $isearch_table_admin_log WHERE regionID = ".$regionID, $isearch_db);
}


/* Return the contents of the admin log */
function isearch_getAdminLog()
{
    global $isearch_table_admin_log, $regionID;
    global $isearch_db;

    $log = '';

    $result = mysql_query("SELECT * FROM $isearch_table_admin_log WHERE regionID = ".$regionID."  ORDER BY id", $isearch_db);
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
    mysql_query("INSERT INTO $isearch_table_admin_log (msg, time) VALUES ('" . mysql_escape_string2($string) . "', '$now', ".$regionID.")", $isearch_db);

    if ($level <= 5)
    {
        echo $string . "<BR>\n";
    }
}


?>