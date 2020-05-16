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
    die("Hacking attempt");
}

/* List of the languages available, to allow selection from the admin control
 * panel. To create a new language:
 *
 *  1. Determine the english name for your language. The steps below assume we
 *     are using the language named "german"
 *  2. Copy english.inc.php to german.inc.php
 *  3. Edit german.inc.php to translate all of the phrases into German.
 *  4. Add the string "german" to the list of languages below
 *  5. From the iSearch Admin page, select the "german" language and click
 *     "Save".
 */

$isearch_lang_config = array(
        "english",
       
    );

?>
