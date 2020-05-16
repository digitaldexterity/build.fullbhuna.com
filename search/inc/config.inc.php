<?php

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

// PHPLOCKITOPT NOENCODE

if ( !defined('IN_ISEARCH') )
{
    die('Hacking attempt');
}
$regionID = isset($regionID) ? $regionID: 1;
error_reporting(E_ERROR);
@ini_set("display_errors", 1); 

/******************************************************************************
 *                    START OF BASIC CONFIGURATION OPTIONS                    *
 *                                                                            *
 * You must set these options for iSearch to work.                            *
 ******************************************************************************/

/* Fill in the following as applicable for your MySQL configuration. If you
 * do not know what to put here, please contact your hosting service provider.
 *
 * This MySQL user MUST have SELECT INSERT UPDATE DELETE INDEX ALTER CREATE
 * and DROP MySQL privileges on the database. Optionally they may also have
 * global LOCK_TABLES privilege (MySQL 4.02+ only).
 */
 
$isearch_sql_server   = $hostname_aquiescedb;
$isearch_sql_username = $username_aquiescedb;
$isearch_sql_password = $password_aquiescedb;
$isearch_sql_database = $database_aquiescedb;


/* Administrator password. Set this to an empty string to disable to internal
 * administrator password protection. Otherwise set it to a password that you
 * will use for accessing the iSearch administration page.
 */
$isearch_admin_password = 'hsaljfhasjhfalkjhrqwjhjzxcalkjfherjhqwuehrwueqhtqcx';   /* Change This ! */


/******************************************************************************
 *                   START OF ADVANCED CONFIGURATION OPTIONS                  *
 *                                                                            *
 * These options allow you to fine tune some aspects of iSearch for your      *
 * site. It is not normally necessary to change these.                        *
 ******************************************************************************/

/* For extra security you can setup a MySQL user with read-only privileges. This
 * user is used when searching. Note that search logging requires write access,
 * so the default MySQL user (defined above) will be used.
 *
 * This MySQL user MUST have SELECT privilege on the database.
 *
 * If empty, the default MySQL user will be used (defined above).
 */
$isearch_sql_ro_username = '';
$isearch_sql_ro_password = '';

/* You can change the table names that iSearch uses. This could be useful if
 * you want to include seperate indexes for different parts of your site.
 *
 * Recommended values are 'isearch_info', 'isearch_urls', 'isearch_words',
 * 'isearch_words_new', 'isearch_log' and 'isearch_spider_log'.
 */
$isearch_table_prefix       = 'isearch_';

$isearch_table_info         = $isearch_table_prefix . 'info';
$isearch_table_urls         = $isearch_table_prefix . 'urls';
$isearch_table_urls_new     = $isearch_table_prefix . 'new';
$isearch_table_words        = $isearch_table_prefix . 'words';
$isearch_table_words_new    = $isearch_table_prefix . 'words_new';
$isearch_table_admin_log    = $isearch_table_prefix . 'admin_log';
$isearch_table_search_log   = $isearch_table_prefix . 'search_log';
$isearch_table_spider_log   = $isearch_table_prefix . 'spider_log';
$isearch_table_links        = $isearch_table_prefix . 'links';
$isearch_table_links_words  = $isearch_table_prefix . 'links_words';
$isearch_table_alts         = $isearch_table_prefix . 'alts';

/* Delay between automatic respidering (in hours). This delay is used by the
 * auto_spider.inc.php script.
 *
 * Recommended value is 24 (spider each day) to 168 (spider each week)
 */
$isearch_spider_hours = 168;


/******************************************************************************
 *                        END OF CONFIGURATION OPTIONS                        *
 ******************************************************************************/
?>
