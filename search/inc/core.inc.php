<?php


if ( !defined('IN_ISEARCH') )
{
    die('Hacking attempt');
}



/* Disable notice/warning errors */
error_reporting(E_ALL);

/* Include configuration options */
require_once("$isearch_path/inc/config.inc.php");

/* Import the list of supported languages */
require_once("$isearch_path/lang/config.inc.php");

$isearch_version = '2.15';

/* Die function - called on a fatal error */
function isearch_die( $error = 'unknown' )
{
    echo "<H1>iSearch Error: $error.</H1>";
    exit;
}


/* Open the iSearch component */
function isearch_open($readOnly = False)
{
    global $isearch_path ,$regionID;
    global $isearch_sql_server, $isearch_sql_username, $isearch_sql_password, $isearch_sql_database;
    global $isearch_sql_ro_username, $isearch_sql_ro_password;
    global $isearch_db, $isearch_ro_db;
    global $isearch_table_info, $isearch_table_urls;

    global $isearch_config;

    /* From language include file */
    global $isearch_lang;
    global $isearch_languageCode;
    global $isearch_lang_config;


    if ($readOnly)
    {
        if ($isearch_sql_ro_username == '')
        {
            $isearch_ro_db = mysql_connect($isearch_sql_server, $isearch_sql_username, $isearch_sql_password);
            $isearch_db = $isearch_ro_db;
        }
        else
        {
            $isearch_ro_db = mysql_connect($isearch_sql_server, $isearch_sql_ro_username, $isearch_sql_ro_password);
        }
    }
    else
    {
        $isearch_db = mysql_connect($isearch_sql_server, $isearch_sql_username, $isearch_sql_password);

        if ($isearch_sql_ro_username == '')
        {
            $isearch_ro_db = $isearch_db;
        }
        else
        {
            $isearch_ro_db = mysql_connect($isearch_sql_server, $isearch_sql_ro_username, $isearch_sql_ro_password);
        }

        if (!$isearch_db)
        {
            isearch_die('Unable to connect to MySQL. Please check your iSearch MySQL server, username and password configuration options in <I>isearch2/inc/config.inc.php</I>.');
        }
        mysql_select_db($isearch_sql_database, $isearch_db) or isearch_die('Unable to select the database. Please check your iSearch MySQL database configuration option in <I>isearch2/inc/config.inc.php</I>.');
    }

    if (!$isearch_ro_db)
    {
        isearch_die('Unable to connect to MySQL (readonly). Please check your iSearch MySQL server, username and password configuration options in <I>isearch2/inc/config.inc.php</I>.');
    }
    mysql_select_db($isearch_sql_database, $isearch_ro_db) or isearch_die('Unable to select the database (readonly). Please check your iSearch MySQL database configuration option in <I>isearch2/inc/config.inc.php</I>.');

    $resultInfo = mysql_query("SELECT * FROM $isearch_table_info WHERE id=".intval($regionID)."", $isearch_ro_db);
    if (($resultInfo) && ($isearch_config = mysql_fetch_array($resultInfo, MYSQL_ASSOC)))
    {
        $explodeVars = array(
            'allowed_ext',
            'allowed_urls',
            'allowed_urls_beginning',
            'exclude_urls',
            'exclude_urls_beginning',
            'groups',
            'remove_get_vars',
            'start_urls',
            'stop_words',
            'strip_defaults',

            'robots_domains',
            'robots_excludes',
            );

        foreach ($explodeVars as $varname)
        {
            if ($isearch_config[$varname] == '')
            {
                $isearch_config[$varname] = array();
            }
            else
            {
                $isearch_config[$varname] = explode(' ', $isearch_config[$varname]);
            }
        }

        /* Check that language is valid */
        if (!in_array($isearch_config['lang_name'], $isearch_lang_config))
        {
            $isearch_config['lang_name'] = 'english';
        }

        include($isearch_path . '/lang/' . $isearch_config['lang_name'] . '.inc.php');

        /* Set maximum execution time */
        @ini_set('max_execution_time', $isearch_config['max_execution_time']);
        switch ($isearch_config['error_reporting'])
        {
            case 2:
                // Disable notices and warnings
                error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
                break;
            case 3:
                // Show all errors
                error_reporting(E_ALL);
                break;
            case 4:
                // Turn off all error reporting
                error_reporting(0);
                break;
            default:
                // Disable notices
                error_reporting(E_ALL ^ (E_NOTICE));
                break;
        }

        

        return True;
    } else { // added by me to add region tables
	
	
	$insert = "INSERT INTO `".$isearch_table_info."` VALUES (".intval($regionID).", '', 0, 0, 0, 1, 2, NULL, 'utf-8', 1, 1, 2, 0, 1, 1, 1, 1, 1, 1, 1, 3, 1, '', 1, 0, 0, 0, 10, 'english', 3, 3, 1, 0, 0, 0, 40, 300, 65536, 20, NULL, 0, 0, NULL, NULL, 0, 2, 10, 0, '', '', 8080, '', 2, '', '_self', 10, 0, 40, 0, 1, 1, 0, 1, 0, 0, 1, 4, 0, 2, 'http://www.fullbhuna.com/home/', 2, 'default', 1, '/tmp', 'isearch', 0, 10, 0, NULL, NULL, 1, 1, 1162919313, 17798400, '2.15', 'php php3 php4 html htm shtml dhtml asp pl cgi', NULL, 'http://www.fullbhuna.com/', NULL, 'http://www.fullbhuna.com/admin/', NULL, 'PHPSESSID', '', '', 20, 1, 20, 'www.fullbhuna.com', '', 0)";

	mysql_query($insert) or die(mysql_error().": ".$insert);
	return True;
	}

    return False;
}


/* Close the iSearch component */
function isearch_close()
{
    global $isearch_db, $isearch_ro_db;

    mysql_close($isearch_ro_db);
    if (isset($isearch_db))
    {
        mysql_close($isearch_db);
    }
}


function isearch_getPostVar($var, $default='')
{
    if (isset($_REQUEST[$var]))
    {
        return $_REQUEST[$var];
    }

    return $default;
}





?>
