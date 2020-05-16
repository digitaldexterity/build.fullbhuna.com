iSearch2
--------

iSearch homepage: http://www.isearchthenet.com/isearch



COPYRIGHT/LICENSE NOTICE
------------------------

Copyright 2002-2005 Ian Willis. All rights reserved.

This script is free to use on any website, commercial or individual. A small
donation would be much appreciated and encourage me to put more effort into
developing iSearch2 further.

For more information on making a donation, please visit the following web page:

    http://www.isearchthenet.com/isearch/donate.php

By using this script you agree to take full responsibility for it. Ian Willis is
in no way accountable for any damage caused.

Reselling or distributing this code without prior written consent is expressly
forbidden.

If you have any questions about this copyright or license, please contact
isearch@ianwillis.co.uk


INTRODUCTION
------------

iSearch2 is a tool for allowing visitors to a website to perform a search on the
contents of the site. Unlike other such tools the spidering engine is written in
PHP, so it does not require binaries to be run on the server to generate the
search index.

iSearch2 takes note of the following data from the HTML <head> section of each
page:

<title>page title</title>
<META name="keywords" content="word1,word2,word3...">
<META name="description" content="a description of the page">
<META name="robots" content="nofollow,noindex">

In addition all words from the body are put into the search index.

iSearch2 performs simple page match scoring. Keywords score highly, and some
<body> words (those in <H1> to <H5> headings) are given higher relevance in
search scoring.


REQUIREMENTS
------------

iSearch2 has the following requirements:

1. A server that supports PHP4. This must include file operations on URLs (i.e.
   the allow_url_fopen option must be enabled.)
2. A server that supports MySQL


UPGRADE
-------

To upgrade from a previous version you must reset the URL tables. Once
installed, click on the "Reset URL Index" button on the configuration page, then
the "Spider" button.


INSTALLATION AND SUPPORT
------------------------

Please visit http://www.isearchthenet.com/isearch



REVISION HISTORY
----------------

iSearch2 is based on iSearch version 1.9j.

2.0     -   14th October, 2004
        -   First release of iSearch2


2.1     -   22nd October, 2004
        -   Minor bugs fixed.
        -   Fix for HTML accented characters in search descriptions.
        -   Added "Hide" and "Space-Replace" regular expressions.

2.2     -   1st November, 2004
        -   Fix for Parse error: on inc/core.inc.php line 72
        -   Fix for Hide and Space-Replace when highlighting.
        -   Improved highlighting

2.3     -   15th November, 2004
        -   First iSearch pro release


2.4     -   15th December, 2004
        -   Fixes for online conversion (pro version).
        -   Support for searching groups (pro version).
        -   Added "Stop Words Length" for stopping short words from being indexed.
        -   Added configuration of search box display.
        -   Added ability to supress descriptions in search results.
        -   Added ability to supress URLs in search results.
        -   Added ability to supress titles in search results.
        -   Added ability to supress page sizes in search results.
        -   Added error level configuration.
        -   Fixed bug when "+" and "-" were not surrounded by whitespace.
        -   Fixed "max page" limit bug.
        -   Fixed bug with ignoring default pages.
        -   Fixed character set conversion bug.
        -   Added support for JavaScript window.open links.

2.5     -   21st December, 2004
        -   Fixed warning when spidering
        -   Added parsing of JavaScript
        -   Fixed inline frames

2.6     -   4th Feb, 2005
        -   Fixed header problems when viewing cache.
        -   Added MySQL index on url field (speeds up spidering)
        -   Removed some auto_spider messages.
        -   Fixed group search next/previous page links
        -   Fixed multiple group selections.
        -   Fixed ALT image tags
        -   Fixed quote characters in anchor tags
        -   Added menu to spider allowing pause/reset/copy

2.7     -   3rd Mar 2005
        -   Fixed bug with command line detection in reindex.php
        -   Added scoring on title words
        -   Added partial matching
        -   Added "Must Match All" configuration
        -   Added advanced search form
        -   Added automatic creation of .htaccess and .htpasswd files
        -   Added backup and restore of settings
        -   Fixed MySQL connection bugs
        -   Fixed bug with + and quoted search strings
        -   Added highlighting of found words in page titles
        -   Removed some PHP notice and warning messages
        -   Added support for documents without a <body> tag
        -   Fixed anchor link searching with attributes before the href
        -   Added support for href and single quotes (requires aggressive link
            search to be enabled)

2.7a    -   4th Mar 2005
        -   Fixed notice message in viewcache.php
        -   Added admin password authentication to reindex.php and log file
            viewers
        -   Added "Allow Dashes" configuration
        -   Fixed bug which caused results titles to be lowercased and stripped
            of punctuation

2.8     -   18th Apr 2005
        -   Added read only MySQL access for searching
        -   Added groups to sitemap (pro version)
        -   Added extra links to display in results (pro version)
        -   Added timeout to update checking
        -   Added javascript checking for empty search strings
        -   Fixed "partial match" not returning results bug
        -   Prevented highlighting of stopped words
        -   Fixed "basedir" bug when parsing robots.txt
        -   Added support for &#x escaped chars
        -   Fixed bug deleting not found URLs
        -   Removed error messages if tables could not be locked

2.9     -   3rd June 2005
        -   Fixed ALT tags in images
        -   Fixed "Must Match All" setting
        -   Fixed redirect base locations when using fopen on URLs
        -   Fixed pagination of browing search index
        -   Fixed bug evaluating &#x escaped chars
        -   Added suggestions (pro version)
        -   Added "Smart Log" feature to allow you to enter suggestions and links (pro version)
        -   Added max title length
        -   Added max description length
        -   Added matching (and scoring) of words within URLs
        -   Added automatic addition of pdf and doc to file extensions when enabled
        -   Added ability to change style of extra links (pro version)
        -   Added loggin of admin events (login and config changes)
        -   Added automatic fixing of URLs with spaces

2.10    -   5th July 2005
        -   Fixed bug which caused multiple indexes on tables.
        -   Fixed index browsing multiple pages bug.
        -   Fixed MySQL read-only configuration.
        -   Fixed PHP warning caused by automatic fixing of URLs with spaces.
        -   Added ability to ignore image alt tags.
        -   Added ability to select reading mechanism.
        -   Added configuration of previous and next links.
        -   Added German help file.

2.11    -   6th July 2005
        -   Fixed problem regarding readonly MySQL configuration in config file
        -   Fixed bug with displaying results when Optionally showing previous/next links.

2.12    -   14th July 2005
        -   Fixed search logging and statistics
        -   Fixed iconv warnings
        -   Fixed base URL parsing when reading via fopen
        -   Fixed location of robots.txt when using port numbers in URL
        -   Fixed default previous/next link type to show below results
        -   Added localisation of decimal point
        -   Added ability to hide display of search time
        -   Added configuration of the number of results pages to show in previous/next bar
        -   Added test mode to allow link following only
        -   Added ability for URL lists to contain files which list the URLs
        -   Reformatted admin page
        -   Added Portuguese help file
        -   Updated Portuguese language file

2.13    -   9th August 2005
        -   Added capability for "Sounds Like" matching
        -   Added ability to perform "Sounds Like" and/or "Partial" searches if no results are found.
        -   Added Google Sitemap generation (experimental)
        -   Added support for HTTP proxy
        -   Added "Allow Colons" to allow colons in search words
        -   Added ability to add/respider/remove individual pages without needing to respider the whole site.
        -   Added Dutch, German and Spanish help files.
        -   Added total number of searches to stats page.
        -   Updated Dutch and Spanish language files.

2.14    -   20th September, 2005
        -   Added auto_spider_img.php for better autospidering.
        -   Added SPAM hack detection to form submission.
        -   Added tooltips to admin interface
        -   Changed look and feel of admin interface
        -   Added chdir for cron jobs with php5
        -   Added ability to disable javascript checking for empty searches
        -   Added limiting of max sections from MySQL for efficiency
        -   Added character set specific html entity conversion
        -   Added limiting max results display to the maximum that can be displayed.
        -   Added logging of date that spidering is started
        -   Fixed (harmless) divide by zero error message.
        -   Fixed bug finding suggestions for multiple words and quoted strings
        -   Fixed bug with not closing tags when highlighting words in results.
        -   Fixed bug with sockets reading mechanism

2.15    -   23rd September, 2005
        -   Removed advert from admin page in professional version.
        -   Added "Remember My Password" option to admin login.
        -   Show advanced search form in results if user used it for the search.
        -   Fixed selection of group on search form.
        -   Fixed version compare bug with 16 bit character sets.
        -   Fixed problem with multiple group selections.
