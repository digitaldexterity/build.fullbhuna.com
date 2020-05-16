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

/* List of the styles available, to allow selection from the admin control
 * panel. To create a new style:
 *
 *  1. Determine a name for your style. The steps below assume we are using a
 *     style named "mystyle"
 *  2. Copy default.inc.php to mystyle.inc.php
 *  3. Add the string "mystyle" to the list of styles below
 *  4. From the iSearch Admin page, select the "mystyle" style and click
 *     "Save".
 */

$isearch_style_config = array(
        "default",
        "green",
        "red",
        "classic",
    );

?>
