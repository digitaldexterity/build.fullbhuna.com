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

/******************************************************************************
 *                   START OF LANGUAGE CONFIGURATION OPTIONS                  *
 *                                                                            *
 * Language       : English                                                   *
 * Contributed By : Ian Willis                                                *
 ******************************************************************************/

/* The language strings contained in this file are subjected to the following
 * translations before being displayed to the user:
 *  %s  The string that the user is searching for
 *  %f  The number of the first result to be displayed
 *  %l  The number of the last result to be displayed
 *  %t  The total number of results found
 *  %e  Elapsed search time (in seconds)
 */

/* The 2 letter language code used to identify this language. Please see
 *      http://www.w3.org/WAI/ER/IG/ert/iso639.htm
 * for a list of the 2 letter codes. This is used in the Content-Language
 * meta tag for the search results pages.
 */
$isearch_languageCode = 'EN';


/* English :    "."
 * Usage :      Used as decimal point when displaying numbers.
 */
$isearch_lang['decimal_point'] = '.';

/* English :    "Search This Site"
 * Usage :      Search result page title and heading when accessed without
 *              search form results (i.e. when just displaying the search form.
 */
$isearch_lang['search_title'] = 'Search This Site';

/* English :    "Site Search Results"
 * Usage :      Search result page title and top level heading
 */
$isearch_lang['results_title'] = 'Site Search Results';

/* English :    "Cached Page"
 * Usage :      Title shown when displaying a page from cache
 */
$isearch_lang['viewcache_title'] = 'Cached Page';

/* English :    "Search results for '%s'"
 * Usage :      Search result page second level heading
 */
$isearch_lang['results_head2'] = "Search results for '%s'";

/* English :    "Showing results %f to %l of %t"
 * Usage :      Used in results page, e.g.:
 *                  Showing results 1 to 10 of 25 (0.100 seconds)
 */
$isearch_lang['results_head3'] = 'Showing results %f to %l of %t';

/* English :    "(%e seconds)"
 * Usage :      Used in results page, e.g.:
 *                  Showing results 1 to 10 of 25 (0.100 seconds)
 */
$isearch_lang['results_head4'] = '(%e seconds)';

/* English :    "No matches found."
 * Usage :      Displayed on the results page when no search matches are found.
 */
$isearch_lang['nomatch'] = 'Sorry, no matches found';

/* English :    "Cached"
 * Usage :      Displayed on the results page to indicate that a cached copy of
 *              the page has been kept.
 */
$isearch_lang['cached'] = 'Cached';

/* English :    "&lt;&lt;&nbsp;Previous&nbsp;Page"
 * Usage :      Displays "<< Previous Page" at the bottom of the search results.
 */
$isearch_lang['previous'] = '&lt;&lt;&nbsp;Previous&nbsp;Page';

/* English :    "Next&nbsp;Page&nbsp;&gt;&gt;"
 * Usage :      Displays "Next Page >>" at the bottom of the search results.
 */
$isearch_lang['next'] = 'Next&nbsp;Page&nbsp;&gt;&gt;';

/* English :    "This is the contents of the iSearch cache for the URL %u"
 * Usage :      Displayed when a page is being displayed from the iSearch cache.
 */
$isearch_lang['cachedpage'] = 'This is the contents of the iSearch cache for the URL %u';

/* English :    "Search Site"
 * Usage :      Displayed on the search form button. When this button is clicked
 *              the search of the site is performed.
 */
$isearch_lang['searchbutton'] = 'Search Site';

/* English :    "Search Internet"
 * Usage :      Displayed on the search form button. When this button is clicked
 *              a search of the internet is performed.
 */
$isearch_lang['searchinternetbutton'] = 'Search Internet';

/* English :    "Help"
 * Usage :      Displayed on the search form button. When this button is clicked
 *              the help page is displayed.
 */
$isearch_lang['helpbutton'] = 'Help';

/* English :    "All Words"
 * Usage :      Displayed on the search form to select that all of the search
 *              words must be matched
 */
$isearch_lang['allwords'] = 'All Words';

/* English :    "Any Words"
 * Usage :      Displayed on the search form to select that any of the search
 *              words can be matched
 */
$isearch_lang['anywords'] = 'Any Words';


/* English :    "Statistics"
 * Usage :      Statistics page title and heading.
 */
$isearch_lang['stats_title'] = 'Statistics';

/* English :    "Top %d Searches"
 * Usage :      On the search statistics page.
 *              "%d" will be replaced with the number of top searches displayed.
 */
$isearch_lang['topsearches'] = 'Top %d Searches';

/* English :    "Last %d Searches"
 * Usage :      On the search statistics page.
 *              "%d" will be replaced with the number of last searches displayed.
 */
$isearch_lang['lastsearches'] = 'Last %d Searches';

/* English :    "Search Term"
 * Usage :      On the search statistics page
 */
$isearch_lang['searchterm'] = 'Search Term';

/* English :    "Matches"
 * Usage :      On the search statistics page
 */
$isearch_lang['matches'] = 'Matches';


/* English :    "Partial"
 * Usage :      Displayed on the search form next to a checkbox.
 */
$isearch_lang['partial'] = 'Partial';

/* English :    "Advanced Search"
 * Usage :      Displayed on the search form to show advanced search form
 */
$isearch_lang['advanced'] = 'Advanced&nbsp;Search';

/* English :    "Simple Search"
 * Usage :      Displayed on the advanced search form to show simple search form
 */
$isearch_lang['simple'] = 'Simple&nbsp;Search';

/* English :    "With ALL the words:"
 * Usage :      Displayed on the advanced search form
 */
$isearch_lang['with_all'] = 'With ALL the words:';

/* English :    "With ANY of the words:"
 * Usage :      Displayed on the advanced search form
 */
$isearch_lang['with_any'] = 'With ANY of the words:';

/* English :    "With the EXACT PHRASE:"
 * Usage :      Displayed on the advanced search form
 */
$isearch_lang['with_exact'] = 'With the EXACT PHRASE:';

/* English :    "WITHOUT the words:"
 * Usage :      Displayed on the advanced search form
 */
$isearch_lang['without'] = 'WITHOUT the words:';


/* English :    "Did you mean to search for:"
 * Usage :      Displayed on the search results where alternative are available
 */
$isearch_lang['suggest_title'] = 'Did you mean to search for:';

/* English : "Please enter a term to search for."
 * Usage : Displayed if the user does not enter any terms to search for.
 */
$isearch_lang['please_enter'] = 'Please enter a term to search for.';

/******************************************************************************
 *                    END OF LANGUAGE CONFIGURATION OPTIONS                   *
 ******************************************************************************/

?>
