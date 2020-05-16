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

if (isset($isearch_path))
{
    /* Path from http root directory to isearch index.php, no trailing slash */
    $isearch_base = $isearch_path;
}
else if (!isset($isearch_base))
{
    /* Path from http root directory to isearch index.php, no trailing slash */
    $isearch_base = '/client/search/';
}

if (!isset($isearch_resultFrame))
{
    /* Set the default frame in which results are displayed.
     *
     * If your site uses frames, set this to the name of the frame that you
     * want the search results displayed in.
     *
     * If your site does not use frames, set this to "_self" to display
     * search results in the same browser window as the search form.
     *
     * Set this to "_blank" to open the search results in a new browser
     * window.
     */
    $isearch_resultFrame = '_self';
}

if (!isset($isearch_searchFormWidth))
{
    /* The default width of the search string entry field. */
    $isearch_searchFormWidth = 12;
}

if (!isset($isearch_allowSearchInternet))
{
    /* If this is not defined, or set to True, the "Search Internet" button will
     * be shown on the form next to the "Search Site" button.
     *
     * If this is set to False, only the "Search Site" button will be displayed.
     */
    $isearch_allowSearchInternet = True;
}

/* Use language default if available, else use the word "Help" */
if (isset($isearch_lang['helpbutton']))
{
    $isearch_helpbutton = $isearch_lang['helpbutton'];
}
else
{
    $isearch_helpbutton = 'Help';
}

/* Use language default if available, else use the words "Search Site" */
if (isset($isearch_lang['searchbutton']))
{
    $isearch_searchbutton = $isearch_lang['searchbutton'];
}
else
{
    $isearch_searchbutton = "Search Site";
}

/* Use language default if available, else use the words "Search Internet" */
if (isset($isearch_lang['searchinternetbutton']))
{
    $isearch_searchinternetbutton = $isearch_lang['searchinternetbutton'];
}
else
{
    $isearch_searchinternetbutton = "Search Internet";
}

if (!isset($s))
{
    $s = '';
}


if ((isset($isearch_config['char_set_8_bit'])) && ($isearch_config['char_set_8_bit']))
{
    $htmlSearchString = htmlentities($s, ENT_COMPAT, "UTF-8");

}
else
{
    $trans = get_html_translation_table(HTML_SPECIALCHARS);
	$htmlSearchString = strtr($s, $trans);

}



echo <<<EOF
<FORM method="post" action="$isearch_base/index.php" target="$isearch_resultFrame">
<CENTER>
<TABLE border="0" cellpadding="3" cellspacing="1">

EOF;

if ($isearch_searchFormWidth <= 12)
{
    /* Display button below search entry box */
    echo <<<EOF
  <TR>
    <TD><INPUT class="standard" maxLength="255" name="s" size="$isearch_searchFormWidth" value='$htmlSearchString'> </TD>
  </TR>
  <TR align="center">
    <TD>
      <INPUT type="submit" value="$isearch_searchbutton">

EOF;

    if ($isearch_allowSearchInternet)
    {
        echo <<<EOF
      <INPUT type="submit" name="internet" value="$isearch_searchinternetbutton">

EOF;
    }

    echo <<<EOF
    </TD>
  </TR>

EOF;
}
else
{
    /* Display button to the right of search entry box */
    echo <<<EOF
  <TR>
    <TD>
EOF;

    echo <<<EOF

      <INPUT maxLength="255" name="s" size="$isearch_searchFormWidth" value='$htmlSearchString'>
      <INPUT type="submit" value="$isearch_searchbutton">

EOF;

    if ($isearch_allowSearchInternet)
    {
        echo <<<EOF
      <INPUT type="submit" name="internet" value="$isearch_searchinternetbutton">

EOF;
    }

    echo <<<EOF
      <A TARGET="_blank" href="$isearch_base/help/help.php">$isearch_helpbutton</A>
    </TD>
  </TR>

EOF;
}

echo <<<EOF
</TABLE>
</CENTER>
<INPUT type="hidden" name="action" value="search">
</FORM>

EOF;

?>
