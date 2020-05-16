<?php

if ( !defined('IN_ISEARCH') )
{
    die('Hacking attempt');
}

/* Set the following variable to True to debug the automatic spidering */
$isearch_verbose = true;

if ($isearch_verbose)
{
    echo "<p>iSearch autospider running\n";
}

if (!isset($isearch_path))
{
    if ($isearch_verbose)
    {
        echo "<p>iSearch - configuration error - \$isearch_path is not set\n";
    }
}
else
{
    /* Include configuration options */
    require_once("$isearch_path/inc/config.inc.php");

    if ($isearch_db = mysql_connect($isearch_sql_server, $isearch_sql_username, $isearch_sql_password))
    {
        if (mysql_select_db($isearch_sql_database, $isearch_db))
        {
            if ($result = mysql_query ("SELECT last_update FROM $isearch_table_info WHERE id = ".intval($regionID)."", $isearch_db))
            {
                if ($item = mysql_fetch_object($result))
                {
					
					echo "***".$item->last_update;
                    if ($item->last_update == 0)
                    {
                        require_once("$isearch_path/inc/core.inc.php");
                        require_once("$isearch_path/inc/spider.inc.php");

                        /* Open the search component */
                        if (isearch_open())
                        {
                            if ($isearch_verbose)
                            {
                                echo "<p>iSearch - Indexing the next file\n";
                            }

                            /* Index a single file */
                            isearch_indexAFile($isearch_verbose);

                            /* Close the search component */
                            isearch_close();
                        }
                        else if ($isearch_verbose)
                        {
                            echo "<p>iSearch - Unable to open iSearch component\n";
                        }
                    }
                    else if ($item->last_update + ($isearch_spider_hours * 3600) <= time())
                    {
                        require_once("$isearch_path/inc/core.inc.php");
                        require_once("$isearch_path/inc/spider.inc.php");

                        /* Open the search component */
                        if (isearch_open())
                        {
                            if ($isearch_verbose)
                            {
                                echo "<p>iSearch - Resetting spider engine\n";
                            }

                            /* Reset the spider engine to start spidering */
                            isearch_reset();

                            /* Close the search component */
                            isearch_close();
                        }
                        else if ($isearch_verbose)
                        {
                            echo "<p>iSearch - Unable to open iSearch component\n";
                        }
                    }
                }
                else if ($isearch_verbose)
                {
                    echo "<p>iSearch - unable to read configuration information\n";
                }
            }
            else if ($isearch_verbose)
            {
                echo "<p>iSearch - unable to query configuration table\n";
            }
        }
        else
        {
            if ($isearch_verbose)
            {
                echo "<p>iSearch - unable to select database\n";
            }
            mysql_close($isearch_db);
        }
    }
    else if ($isearch_verbose)
    {
        echo "<p>iSearch - unable to connect to database\n";
    }
}

if ($isearch_verbose)
{
    echo "<p>iSearch autospider finished\n";
}
