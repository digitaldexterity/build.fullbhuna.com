<?php require_once('../../Connections/aquiescedb.php'); ?><?php require_once('../../core/includes/adminAccess.inc.php'); ?>
<?php $regionID = isset($regionID) ? $regionID: 1;
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "9,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../../login/index.php?notloggedin=true";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}


?><?php
if(!function_exists("mysql_escape_string2")) {
function mysql_escape_string2($theValue) {
	$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
	return $theValue;
}
}

$isearch_path= SITE_ROOT.'search';
define('IN_ISEARCH', true);


require_once($isearch_path."/inc/core.inc.php");
isearch_open();
isearch_createTables();
require_once("$isearch_path/inc/admin_auth.inc.php");
require_once("$isearch_path/inc/search.inc.php");
require_once("$isearch_path/inc/spider.inc.php");
require_once("$isearch_path/lang/config.inc.php");
require_once("$isearch_path/style/config.inc.php");

$isearch_action = isset($_REQUEST['isearch_action']) ? $_REQUEST['isearch_action'] : '';
$isearch_tab = isset($_REQUEST['isearch_tab']) ? $_REQUEST['isearch_tab'] : '0';


$isearch_pro = true;




$only_available = $isearch_pro ? '' : '<DIV style="color: red;">Only available in the professional version.</DIV><BR>';

/* Array of controls on various tabs */
$tabData =
    array(
        array(
            'name' => 'Basic',
            'description' => 'Basic configuration options',
            'controls' =>
            array(
                array(
                    'name' => 'Site Administrator Email Address',
                    'description' => 'Used for emailing search log and statistics and automatic update notification. Your email address is never shared or sent from your own server.',
                    'varname' => 'admin_email',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Start URL(s)',
                    'description' => 'A list of URLs that spidering should start from. Multiple start sites can be specified on seperate lines.<p>This is normally your home page (e.g. "http://www.yoursite.com/"). Start&nbsp;URL(s) are always included in the search index regardless of the Allowed&nbsp;URL(s)/Exclude&nbsp;URL(s) lists. If entries in this list begin with an "@" symbol they are treated as a file containing entries.',
                    'varname' => 'start_urls',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Allow URL(s) Beginning',
                    'description' => 'A URL must match an entry in this list or "Allow URL(s) Regexp" to be included in the search index. Multiple entries are allowed on seperate lines.<p>This should normally allow URLS from your website to be included (e.g. "http://www.yoursite.com"). If entries in this list begin with an "@" symbol they are treated as a file containing entries.',
                    'varname' => 'allowed_urls_beginning',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Exclude URL(s) Beginning',
                    'description' => 'A URL will not be added to the search index if it begins with one of the entries in this list. Multiple entries are allowed on seperate lines.<p>This allows you to exclude certain URLs from the search index. URLs that match both the allowed list and disallowed list are not included in the search index. (e.g. "http://www.yoursite.com/private/"). This does not affect URLs already in the search index (use "Reset URL Index" then "Spider" after changing this setting). If entries in this list begin with an "@" symbol they are treated as a file containing entries.',
                    'varname' => 'exclude_urls_beginning',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Test Mode',
                    'description' => '<UL><LI>Disabled - Test mode is disabled.<LI>Follow Links Only - The spider will follow links, but will not create a search index.</UL>',
                    'varname' => 'test_mode',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Disabled'),
                        array('value' => '1', 'description' => 'Follow Links Only'),
                    ),
                ),
                array(
                    'name' => 'Update Notification',
                    'description' => 'Periodically check for updates to iSearch and send you an email if a newer version is available.',
                    'varname' => 'notify_updates',
                    'type' => 'checkbox',
                ),
            ),
        ),
        array(
            'name' => 'Spider Basic',
            'description' => 'Basic settings for the spider engine',
            'controls' =>
            array(
                array(
                    'name' => 'Allowed File Extension(s)',
                    'description' => 'Only files with the file extensions specified will be spidered. Multiple entries are seperated by spaces. (e.g. "php&nbsp;htm&nbsp;html"). In addition, URLs without file extensions will be treated as directories (see the "Allow Directories" configuration option).',
                    'varname' => 'allowed_ext',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Directory Handling',
                    'description' => 'Specify how directories (URLs without a "." in the filename) are handled. Normally you should allow them and add a trailing slash.',
                    'varname' => 'directory_handling',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Directories are Excluded'),
                        array('value' => '1', 'description' => 'Allow Directories'),
                        array('value' => '2', 'description' => 'Allow and Add Trailing Slash'),
                        array('value' => '3', 'description' => 'Allow and Strip Trailing Slash'),
                    ),
                ),
                array(
                    'name' => 'Strip Default Filenames',
                    'description' => 'A list of default filenames that will be removed from URLs. If a URL ends in one of these filenames, the filename will be stripped from the URL.',
                    'varname' => 'strip_defaults',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Keep Cached Copies',
                    'description' => 'Keeps a cached copy of the page contents when it was spidered. This can be useful for dynamic pages that update frequently.',
                    'varname' => 'keep_cache',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'www subdomain handling',
                    'description' => 'Select whether you want the www subdomain to be left as it is, stripped off or always added when spidering pages. If you select "Leave as is" you may end up with multiple copies of the same page in the search index, e.g. <I>http://www.yourdomain.com/index.php</I> and <I>http://yourdomain.com/index.php</I>. If you do not use subdomains of your main domain, it is suggested to use the "Add www subdomain" option.',
                    'varname' => 'www_option',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '1', 'description' => 'Leave as is'),
                        array('value' => '2', 'description' => 'Strip www subdomains'),
                        array('value' => '3', 'description' => 'Add www subdomain'),
                    ),
                ),
                array(
                    'name' => 'Max File Size',
                    'description' => 'Maximum number of bytes to read from each file that we index. If a file is larger than this, only the first part of it will be read.' . $isearch_pro ? ' If you index PDF or MS Word documents, you should probably increase this setting to 1048576 (1MB). Note that the limit for online conversion is 1048576 bytes.' : '',
                    'varname' => 'max_file_size',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '4096',
                    'max' => '9999999',
                ),
                array(
                    'name' => 'Stop Word Length',
                    'description' => 'Words of this number of characters or less will be ignored during spidering. Suggested value is 2 or 3. Set to 0 to disable this feature.',
                    'varname' => 'stop_words_length',
                    'type' => 'text',
                    'size' => '2',
                    'maxlength' => '2',
                    'min' => '0',
                    'max' => '10',
                ),
                array(
                    'name' => 'Stop Words',
                    'description' => 'Define a list of words to ignore during searching. Searching for these words will not match anything.',
                    'varname' => 'stop_words',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Spider Echo Level',
                    'description' => 'Level of information displayed on screen when spidering. 0 is silent, 1 logs only errors, 10 is very verbose. Suggested value is 3.',
                    'varname' => 'log_echo_level',
                    'type' => 'text',
                    'size' => '2',
                    'maxlength' => '2',
                    'min' => '0',
                    'max' => '10',
                ),
            ),
        ),
        array(
            'name' => 'Spider Advanced',
            'description' => 'Advanced settings for the spider engine',
            'notes' => '<UL><LI>For more information about using regular expressions (regexps), <A href="http://www.phpbuilder.com/columns/dario19990616.php3" TARGET="_blank">click here</A>.</LI></UL>',
            'controls' =>
            array(
                array(
                    'name' => 'Allow URL(s) Regexp',
                    'description' => 'A list regular expressions for URLs that are allowed in the search index. Multiple entries are allowed on seperate lines. These normally start with your website (e.g. "^http://(www\.)?yoursite\.com"). The "^" character matches a start of string, "$" matches an end of string, "." matches a single character, "\\." matches a dot, "(www\.)?" matches an optional "www." subdomain, ".*" matches any string. If entries in this list begin with an "@" symbol they are treated as a file containing entries.',
                    'varname' => 'allowed_urls',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Exclude URL(s) Regexp',
                    'description' => 'A list regular expressions for URLs that will not be added to the search index. This allows more powerful pattern matching for URL exclusion. (e.g. "^http://(www\.)?yoursite\.com/private/"). This does not affect URLs already in the search index (use "Reset URL Index" then "Spider" after changing this setting). If entries in this list begin with an "@" symbol they are treated as a file containing entries.',
                    'varname' => 'exclude_urls',
                    'type' => 'textarea',
                ),
                array(
                    'name' => 'Follow Frames',
                    'description' => 'Causes the spider engine to index any sub-frames that it finds. Note that many major search engines (including Google) do not do this. It is much better to author your site to support legacy browsers using the &lt;NOFRAMES&gt; tag.',
                    'varname' => 'follow_frames',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Aggressive Link Search',
                    'description' => 'Aggressive link search attempts to find links to other pages that are not HTML anchors. Any complete URLs will be found anywhere on your page (they must include the "http://" part), including within comments and HTML &lt;head&gt; section.',
                    'varname' => 'aggressive_link_search',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Javascript Link Search',
                    'description' => 'Attempts to find links to pages that within JavaScript. It searches for JavaScript window.open() calls in indexed pages. If you do not use JavaScript menus, then select "None". If you use embedded JavaScipt menus (i.e. the JavaScript is in the HTML file) then select "Embedded*quot;. If your JavaScript menus are in seperate (.js) files, then select "Embedded and External".',
                    'varname' => 'javascript_link_search',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'None'),
                        array('value' => '1', 'description' => 'Embedded'),
                        array('value' => '2', 'description' => 'Embedded and External'),
                    ),
                ),
                array(
                    'name' => 'Remove GET variables',
                    'description' => 'A list of variable names that will be removed from the query part (after a ?) of URLs. This can be used to strip variables using for storing session information. e.g. "PHPSESSID". If your site does not have dynamic content, you could set this to "*" to remove all GET variables (everything after the "?" will be stripped.',
                    'varname' => 'remove_get_vars',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Additional Spider Delay',
                    'description' => 'Inserts an additional delay (in seconds) between spidering of each page. This reduces the load on your server, but increases the times it takes to spider your site. Set to 0 to disable.',
                    'varname' => 'spider_delay',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '9999',
                ),
                array(
                    'name' => 'Url Search',
                    'description' => 'This option allows parts of a URL to be replaced using a regexp before it is opened. If your site is hosted by Lycos, you may be unable to use url file opens. Set <I>Url Search</I> to "^http://members\\.lycos\\.co\\.uk/username/" and <I>Url Replace</I> to "/data/members/free/tripod/uk/u/s/e/username/htdocs/" (ensure that path names are correct for your site). Leave empty to disable this option.',
                    'varname' => 'url_search',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Url Replace',
                    'description' => 'See <I>URL Search</I> above.',
                    'varname' => 'url_replace',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Basic Authorization',
                    'description' => 'If your site uses HTTP Basic Authorization (.htaccess files) for user login, enter a username and password (seperated by a colon) for iSearch, so that password protected pages on your site can be read, e.g. "username:password". <B>WARNING: THIS IS SENT ON ALL URL REQUESTS THAT ISEARCH MAKES</B>. Check your "Allowed URL(s)" carefull to make sure that you do not give you password to other sites.',
                    'varname' => 'basic_authorization',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'File Redirect Handling',
                    'description' => 'Determine the handling of File redirects (i.e. HTTP "Location" headers)',
                    'varname' => 'file_redirect_handling',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Do Not Follow File Redirects'),
                        array('value' => '1', 'description' => 'Follow All File Redirects'),
                        array('value' => '2', 'description' => 'Follow Redirects within Current Domain'),
                        array('value' => '3', 'description' => 'Follow Redirects within Allowed URLs'),
                    ),
                ),
                array(
                    'name' => 'Directory Redirect Handling',
                    'description' => 'Determine the handling of directory redirects (i.e. HTTP "Location" headers)',
                    'varname' => 'dir_redirect_handling',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Do Not Follow Directory Redirects'),
                        array('value' => '1', 'description' => 'Follow All Directory Redirects'),
                        array('value' => '2', 'description' => 'Follow Redirects within Current Domain'),
                        array('value' => '3', 'description' => 'Follow Redirects within Allowed URLs'),
                    ),
                ),
                array(
                    'name' => 'Ignore Image Alt Tags',
                    'description' => 'Causes iSearch to ignore the &lt;alt&gt; tag taxt of images in the spider index.',
                    'varname' => 'ignore_image_alt_tags',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Reading Mechanism',
                    'description' => 'Determine how iSearch reads files that it is spidering.<UL>' .
'<LI>"Auto Detect" will automatically detect a mechnism that should work.<BR>'.
'<LI>"fopen" uses PHP fopen wrappers ('.($isearch_url_fopen_detected ? '' : 'NOT ').'OK).<BR>'.
'<LI>"sockets" uses native sockets (http only) ('.($isearch_sockets_detected ? '' : 'NOT ').'OK).<BR>'.
'<LI>"curl" uses libcurl, which must be compiled into your PHP executable ('.($isearch_curl_detected ? '' : 'NOT ').'OK).</UL>',
                    'varname' => 'reading_mechanism',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Auto Detect'),
                        array('value' => '1', 'description' => 'fopen'),
                        array('value' => '2', 'description' => 'sockets'),
                        array('value' => '3', 'description' => 'curl'),
                    ),
                ),
            ),
        ),
        array(
            'name' => 'Proxy',
            'description' => 'Enable spidering of sites using a proxy server.',
            'controls' =>
            array(
                array(
                    'name' => 'Proxy Enable',
                    'description' => 'Enable use of a proxy server for spidering pages.',
                    'varname' => 'proxy_enable',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Proxy Host',
                    'description' => 'Host name of the proxy server. This can be a name or an IP address.',
                    'varname' => 'proxy_host',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '128',
                ),
                array(
                    'name' => 'Proxy Port',
                    'description' => 'Port number of the proxy server.',
                    'varname' => 'proxy_port',
                    'type' => 'text',
                    'size' => '5',
                    'maxlength' => '5',
                    'min' => '0',
                    'max' => '65535',
                ),
                array(
                    'name' => 'Proxy Username',
                    'description' => 'Username to use for proxy authentication. Leave blank to disable proxy authentication.',
                    'varname' => 'proxy_user',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '40',
                ),
                array(
                    'name' => 'Proxy Pasword',
                    'description' => 'Password to use for proxy authentication. Note this is stored in plain text format in the MySQL database.',
                    'varname' => 'proxy_pass',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '40',
                ),
            ),
        ),
        array(
            'name' => 'Character Set',
            'description' => 'Character set settings for your site',
            'controls' =>
            array(
                array(
                    'name' => 'Character Set',
                    'description' => 'Select the character set that will be used in the content-type meta tag of the results page. This should be set to the character set used throughout the rest of your site. Examples settings are "iso-8559-1" for ISO 8559-1 Western European, "shift-jis" for Japanese language, "utf-8" for international languages.',
                    'varname' => 'char_set',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '255',
                ),
                array(
                    'name' => '8 Bit',
                    'description' => 'Set to true if your character set can be represented as 8 bits. This includes ISO-8859 charsets (containing accented characters). If you use a multi-byte character set (such as for Chinese or Japanese languages), change this to False. This causes less manipulation of character data stored and searched for.',
                    'varname' => 'char_set_8_bit',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Language',
                    'description' => 'Select the language that you want to use to display search results.',
                    'varname' => 'lang_name',
                    'type' => 'combo',
                    'itemvar' => 'isearch_lang_config', /* read items from $isearch_lang_config */
                ),
            ),
        ),
        array(
            'name' => 'Search',
            'description' => 'Change how searches are performed and what options are available to visitors.',
            'controls' =>
            array(
                array(
                    'name' => 'Partial Matches',
                    'description' => 'The default setting for partial matches. If this is not enabled, then exact word matches will be performed. With this enabled partial word matches will be performed. The user can override this on the "Advanced Search" form.',
                    'varname' => 'search_partial',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Always Use Exact Matching'),
                        array('value' => '1', 'description' => 'Always Use Partial Matching'),
                        array('value' => '2', 'description' => 'Use Partial if Exact Match Fails'),
                    ),
                ),
                array(
                    'name' => 'Must Match All',
                    'description' => 'Determines the default behaviour when the user enters multiple words as a search term. If disabled, then pages matching ANY of the entered words will be displayed. If enabled, only pages that match ALL of the entered words will be displayed.',
                    'varname' => 'search_all',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Allow Dashes',
                    'description' => 'When enabled allows the user to search for hyphenated words in the search box. When disabled, a dash within a word is replaced by a space character. To enable search words exclusion, the dash must be surrounded by spaces. You should respider your site after changing this option.',
                    'varname' => 'allow_dashes',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Allow Colons',
                    'description' => 'When enabled allows the user to search for words containing a colon in the search box. You should respider your site after changing this option.',
                    'varname' => 'allow_colons',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Body Word Rank',
                    'description' => 'Words found in the main body add a score of <I>Body Word Rank</I> to the page rank. Takes effect next time the site is spidered.',
                    'varname' => 'word_rank',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'Body Heading Word Rank',
                    'description' => 'If a word with the body is in an HTML heading the page score is increased by <I>Heading Word Rank</I>. Takes effect next time the site is spidered.',
                    'varname' => 'heading_rank',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'Keyword Rank',
                    'description' => 'Keywords found in the keywords meta tag add a score of <I>Keyword Rank</I> to the page rank. Set this to 0 to ignore keywords. Takes effect next time the site is spidered.',
                    'varname' => 'keyword_rank',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'Title Rank',
                    'description' => 'Words within the page title add a score of <I>Title Rank</I> to the page rank. Set this to 0 to ignore page titles. Takes effect next time the site is spidered.',
                    'varname' => 'title_rank',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'URL Rank',
                    'description' => 'Words within the page URL add a score of <I>URL Rank</I> to the page rank. Set this to 0 to ignore URLs when scoring. Takes effect next time the site is spidered.',
                    'varname' => 'url_rank',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'Search Box Width',
                    'description' => 'Width in characters of the search box on the results page.',
                    'varname' => 'search_box_width',
                    'type' => 'text',
                    'size' => '3',
                    'maxlength' => '3',
                    'min' => '0',
                    'max' => '999',
                ),
                array(
                    'name' => 'Search Internet',
                    'description' => 'Include a "Search Internet" button on the simple search form.',
                    'varname' => 'search_internet',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Help Link',
                    'description' => 'Include a "Help" link on the simple search form.',
                    'varname' => 'search_help_link',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Partial Matches',
                    'description' => 'Include a "Partial" checkbox on the simple search form. This allows partial words to be searched for (e.g. "cat" will find pages containing "va<B>cat</B>ion").',
                    'varname' => 'form_show_partial',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Sounds Like',
                    'description' => 'Matching can be performed by the phonetic sound of words, rather than their exact spelling. This often allows misspelled words to be matched.',
                    'varname' => 'soundex',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Never Use Sounds Like'),
                        array('value' => '1', 'description' => 'Always Use Sounds Like'),
                        array('value' => '2', 'description' => 'Use Sounds Like if Exact Match Fails'),
                    ),
                ),
                array(
                    'name' => 'Advanced Link',
                    'description' => 'Include an "Advanced" link on the simple search form. This allows access to advanced search options.',
                    'varname' => 'form_show_advanced',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Check For Empty Search Term',
                    'description' => 'Includes javascript checking for empty search term and displays an error message to the user.',
                    'varname' => 'check_empty_search',
                    'type' => 'checkbox',
                ),
            ),
        ),
        array(
            'name' => 'Display',
            'description' => 'Displaying Results',
            'notes' => '<UL><LI>For more information about using regular expressions (regexps), <A href="http://www.phpbuilder.com/columns/dario19990616.php3" TARGET="_blank">click here</A>.</LI></UL>',
            'controls' =>
            array(
                array(
                    'name' => 'Results Per Page',
                    'description' => 'Maximum number of search results to be displayed on each results page. If there are more than this many matches, they will be displayed on multiple pages.',
                    'varname' => 'results_per_page',
                    'type' => 'text',
                    'size' => '3',
                    'maxlength' => '3',
                    'min' => '1',
                    'max' => '100',
                ),
                array(
                    'name' => 'Max Pages',
                    'description' => 'Maximum number of result pages to be displayed.',
                    'varname' => 'max_pages',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '1',
                    'max' => '1000',
                ),
                array(
                    'name' => 'Description Style',
                    'description' => 'URL description shown below each URL in the search results. This can be the description meta tag, Google style extract from the body text of matched pages or both. Google style results are automatically used when a matched page does not have a description meta tag.',
                    'varname' => 'description_style',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No Description is Shown'),
                        array('value' => '1', 'description' => 'Description Meta Tags'),
                        array('value' => '2', 'description' => 'Google Style Extraction'),
                        array('value' => '3', 'description' => 'Meta Tag then Google Style'),
                        array('value' => '4', 'description' => 'Google Style then Meta Tag'),
                    ),
                ),
                array(
                    'name' => 'Match Score Style',
                    'description' => 'Search results can optionally show a score next to them. This option defines which mechanism is used to display that score.',
                    'varname' => 'match_score',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No Score Is Displayed'),
                        array('value' => '1', 'description' => 'Percentage Score Displayed'),
                        array('value' => '2', 'description' => 'Out Of Ten Score Displayed'),
                    ),
                ),
                array(
                    'name' => 'Highlight Searched Words',
                    'description' => 'Highlight words that were searched for in descriptions shown on the results page.',
                    'varname' => 'highlight_results',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Hide Powered By Message',
                    'description' => 'Hides the "Powered By iSearch2" messages.',
                    'enabled' => $isearch_pro,
                    'varname' => 'hide_powered_by',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Page Opening Target Frame',
                    'description' => 'Sets the target frame when the user clicks on one of the search results or entries in the site map. If your site uses frames, set this to the name of the frame to open page into. If you do not use frames, set this to "_self". Set it to "_blank" to open the page in a new browser window.',
                    'varname' => 'target_frame',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Strip Query',
                    'description' => 'Set this to prevent the display of the query part of a URL (the part after the "?").',
                    'varname' => 'display_strip_query',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Max Title Length',
                    'description' => 'Maximum length (in characters) of titles that will be displayed. If a title longer than this is displayed, it will be truncated. If 0 any length title will be displayed.',
                    'varname' => 'max_displayed_title_length',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '9999',
                ),
                array(
                    'name' => 'Max Description Length',
                    'description' => 'Maximum length (in characters) of description that will be displayed. If a description longer than this is displayed, it will be truncated. If 0 any length description will be displayed.',
                    'varname' => 'max_displayed_description_length',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '9999',
                ),
                array(
                    'name' => 'Max Url Length',
                    'description' => 'Maximum length of URLs that will be displayed. If a URL longer than this is displayed, it will be shortened so that the beginning and end are displayed. If this is 0 the URL will not be displayed.',
                    'varname' => 'max_displayed_url_length',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '9999',
                ),
                array(
                    'name' => 'Style',
                    'description' => 'Select the style that you want to use to display search results.',
                    'varname' => 'style_name',
                    'type' => 'combo',
                    'itemvar' => 'isearch_style_config', /* read items from $isearch_style_config */
                ),
                array(
                    'name' => 'Sitemap Format',
                    'description' => 'Set the default display used to generate the sitemap page. Grouped sitemaps are only available in the professional version.',
                    'varname' => 'sitemap_type',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Link Hierarchy'),
                        array('value' => '1', 'description' => 'Flat Unordered'),
                        array('value' => '2', 'description' => 'Flat Sorted By URL'),
                        array('value' => '3', 'description' => 'Flat Sorted By Title'),
                        array('value' => '4', 'description' => 'Directory Hierarchy'),

                        array('value' => '5', 'description' => 'Grouped Unordered'),
                        array('value' => '6', 'description' => 'Grouped Sorted By URL'),
                        array('value' => '7', 'description' => 'Grouped Sorted By Title'),

                    ),
                ),
                array(
                    'name' => 'Hide Regexp',
                    'description' => 'Hide characters matching this regular expression from being displayed in the results descriptions. Normally this should be left blank.',
                    'varname' => 'hide_regexp',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Space-Replace Regexp',
                    'description' => 'Replace matches for this regular expression with spaces when they are displayed in the results descriptions. Normally this should be left blank.',
                    'varname' => 'replace_regexp',
                    'type' => 'text',
                    'size' => '20',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Show Time',
                    'description' => 'Show the time (number of seconds) that the search took to execute.',
                    'varname' => 'show_time',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Show Title',
                    'description' => 'Show page titles in search results.',
                    'varname' => 'show_title',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Show Size',
                    'description' => 'Show the size of pages in the search results.',
                    'varname' => 'show_size',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Previous and Next',
                    'description' => 'Determine where previous and next page links are displayed. If you set this to "Nowhere" you will only be able see 1 page of results. If you select an "Optional" setting the previous and next bars will only be shown when there are previous and next links to display. If you select "Always" they will always be shown, and empty if there is only 1 page of results.',
                    'varname' => 'prevnext_type',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Nowhere'),
                        array('value' => '1', 'description' => 'Above Results   - Optional'),
                        array('value' => '2', 'description' => 'Below Results   - Optional'),
                        array('value' => '3', 'description' => 'Above and Below - Optional'),
                        array('value' => '4', 'description' => 'Above Results   - Always'),
                        array('value' => '5', 'description' => 'Below Results   - Always'),
                        array('value' => '6', 'description' => 'Above and Below - Always'),
                    ),
                ),
                array(
                    'name' => 'Prev/Next Num Pages',
                    'description' => 'Determine how many pages can be directly jumped to from previous and next links. This affects the number of digits displayed on the previous and next link line. Set to 0 to prevent any being displayed.',
                    'varname' => 'prevnext_num',
                    'type' => 'text',
                    'size' => '2',
                    'maxlength' => '2',
                    'min' => '0',
                    'max' => '99',
                ),
            ),
        ),
        array(
            'name' => 'Advanced',
            'description' => 'Advanced options that do not normally need modification',
            'controls' =>
            array(
                array(
                    'name' => 'Maximum Execution Time',
                    'description' => 'PHP maximum execution time when running iSearch. If you get the error message "Fatal error: Maximum execution time of xx seconds exceeded", then increase this value.',
                    'varname' => 'max_execution_time',
                    'type' => 'text',
                    'size' => '7',
                    'maxlength' => '7',
                    'min' => '0',
                    'max' => '1000000',
                ),
                array(
                    'name' => 'PHP Error Reporting',
                    'description' => 'Sets the level of error reporting provided by PHP. The recommended setting is "Disable notices". See the PHP error_reporting function documentation for more details of the different levels.',
                    'varname' => 'error_reporting',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '1', 'description' => 'Disable notices'),
                        array('value' => '2', 'description' => 'Disable notices and warnings'),
                        array('value' => '3', 'description' => 'Show all errors'),
                        array('value' => '4', 'description' => 'Turn off all error reporting'),
                    ),
                ),
                array(
                    'name' => 'PDF Support',
                    'description' => 'Determine whether PDF files are handled by iSearch. For "Executable PDF Support" the Xpdf pdftotext executable must be run on your server. Your PHP installation must be configured to allow this.</A>',
                    'enabled' => $isearch_pro,
                    'varname' => 'pdf_support',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No PDF Support'),
                        array('value' => '1', 'description' => 'Executable PDF Support'),
                        array('value' => '2', 'description' => 'Online PDF Support'),
                    ),
                ),
                array(
                    'name' => 'PDF Executable Path',
                    'description' => 'If the "PDF Support" setting is set to "Executable PDF Support", then this setting defines the full path to the xpdf pdftotext executable. You can download Xpdf from www.foolabs.com/xpdf',
                    'enabled' => $isearch_pro,
                    'varname' => 'pdf_exec',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Microsoft Word Support',
                    'description' => 'Determine whether MS Word documents are handled by iSearch. For "Executable Word Support" the antiword executable must be run on your server. Your PHP installation must be configured to allow this.',
                    'enabled' => $isearch_pro,
                    'varname' => 'msword_support',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No Word Support'),
                        array('value' => '1', 'description' => 'Executable Word Support'),
                        array('value' => '2', 'description' => 'Online Word Support'),
                    ),
                ),
                array(
                    'name' => 'Word Executable Path',
                    'description' => 'If the "Word Support" setting is set to "Executable Word Support", then this setting defines the full path to the antiword executable. You can download antiword from http://www.winfield.demon.nl/',
                    'enabled' => $isearch_pro,
                    'varname' => 'msword_exec',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Temporary Directory',
                    'description' => 'If the "PDF Support" or "Word Support" settings are set to "Executable PDF/Word Support", then you must enter a temporary directory name that is writable by PHP scripts.',
                    'enabled' => $isearch_pro,
                    'varname' => 'tmpdir',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Online Conversion ID',
                    'description' => 'If the "PDF Support" or "Word Support" settings are set to "Online PDF/Word Support", then you must enter your ID from the iSearch website.',
                    'enabled' => $isearch_pro,
                    'varname' => 'online_id',
                    'type' => 'text',
                    'size' => '40',
                    'maxlength' => '255',
                ),
                array(
                    'name' => 'Show Admin Tooltips',
                    'description' => 'Show tooltips on the iSearch admin pages.',
                    'varname' => 'show_admin_tooltips',
                    'type' => 'checkbox',
                ),
            ),
        ),
        array(
            'name' => 'Groups',
            'description' => 'Allows you to group related pages together for ease of searching. A visitor to your site can search in a single group, or multiple groups. Using groups you can make it easier for visitors to find what they want.',
            'enabled' => $isearch_pro,
            'controls' =>
            array(
                array(
                    'name' => 'Show On Search Form',
                    'description' => 'How groups are shown on the search form.',
                    'varname' => 'form_show_groups',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No Groups Shown'),
                        array('value' => '1', 'description' => 'Single Combo Box'),
                        array('value' => '2', 'description' => 'Multi-Select'),
                    ),
                ),
                /* Other controls are dynamically generated */
            ),
        ),
        array(
            'name' => 'Links',
            'description' => 'Allows you to add extra links related to specific keywords. Using Links you can add extra links to be displayed above or at the top of the links your spider found.',
            'enabled' => $isearch_pro,
            'controls' =>
            array(
                array(
                    'name' => 'Extra Link Display',
                    'description' => 'Determines how extra links are displayed. "Show Above" causes link results to be shown seperately from indexed results, "Show At Top" causes linked results to be shown first in the results, indistinguishable from indexed results.',
                    'varname' => 'extra_link_display',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'No Extra Links Shown'),
                        array('value' => '1', 'description' => 'Shown Above Results'),
                        array('value' => '2', 'description' => 'Shown At Top Of Results'),
                    ),
                ),
                /* Other controls are dynamically generated */
            ),
        ),
        array(
            'name' => 'Suggestions',
            'description' => 'Allows you to specify alternative words that will be used for a given search, or suggested to the user when they search for a specific term.',
            'enabled' => $isearch_pro,
            'controls' =>
            array(
                array(
                    'name' => 'Enable Suggestions',
                    'description' => 'Determines how suggestions are handled.',
                    'varname' => 'suggestions',
                    'type' => 'combo',
                    'items' =>
                    array(
                        array('value' => '0', 'description' => 'Disable Suggestions'),
                        array('value' => '1', 'description' => 'Enable Suggestions'),
                        array('value' => '2', 'description' => 'Always Suggest'),
                        array('value' => '3', 'description' => 'Always Redirect'),
                    ),
                ),
                /* Other controls are dynamically generated */
            ),
        ),
        array(
            'name' => 'Smart Log',
            'description' => 'Allows you to analyse log contents intelligently and take action to help your visitors find what they want, such as suggest alternatives, provide alternative results, or provide a redirect.',
            'enabled' => $isearch_pro,
            'controls' =>
            array(
                // Other controls are dynamically generated
            ),
        ),
        array(
            'name' => 'Logging',
            'description' => 'Logging and statistics configuration and display.',
            'controls' =>
            array(
                array(
                    'name' => 'Spider Log Level',
                    'description' => 'Log level of information saved in the spider log. 0 is silent, 1 logs only errors, 10 is very verbose. Suggested value is 5.',
                    'varname' => 'log_level',
                    'type' => 'text',
                    'size' => '2',
                    'maxlength' => '2',
                    'min' => '0',
                    'max' => '10',
                ),
                array(
                    'name' => 'Keep Search Log',
                    'description' => 'Logs each search that is performed. The log is used to generate statistics and can also be emailed to the site administrator.',
                    'varname' => 'log_searches',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Email Log Period (days)',
                    'description' => 'Sends an email report of searches performed on your web site. Each search is logged, and the list of search made emailed to you. Set this to 0 to disable this feature (no emails will be sent).',
                    'varname' => 'search_log_email_days',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '999',
                ),
                array(
                    'name' => 'Total Searches',
                    'description' => 'Display the total number of searches on the statistics page.',
                    'varname' => 'total_searches',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'Top Searches',
                    'description' => 'The number of top searches that are displayed on the statistics page. Set to 0 to disable.',
                    'varname' => 'top_searches',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '999',
                ),
                array(
                    'name' => 'Last Searches',
                    'description' => 'The number of last searches that are displayed on the statistics page. Set to 0 to disable.',
                    'varname' => 'last_searches',
                    'type' => 'text',
                    'size' => '4',
                    'maxlength' => '4',
                    'min' => '0',
                    'max' => '999',
                ),
                // Other controls are dynamically generated
            ),
        ),
        array(
            'name' => 'Backup Settings',
            'description' => 'Backup or restore the current iSearch settings.',
            'url' => $_SERVER['PHP_SELF'].'?isearch_action=backup_restore',
            'target' => '_self',
        ),
        array(
            'name' => 'Browse Index',
            'description' => 'Browse information about the pages that are stored in the search index.',
            'url' => $_SERVER['PHP_SELF'].'?isearch_action=browse',
            'target' => '_self',
        ),
        array(
            'name' => 'Add/Remove/Respider Pages',
            'description' => 'Add new pages to the search index, or remove/respider existing pages.',
            'url' => $_SERVER['PHP_SELF'].'?isearch_action=add_remove_respider',
            'target' => '_self',
        ),
        array(
            'name' => 'PHP Info',
            'description' => 'Show PHP configuration information.',
            'url' => 'phpinfo.php',
            'target' => '_blank',
        ),
        array(
            'name' => 'Support',
            'description' => 'Get help and support with iSearch from the iSearch web site.',
            'url' => 'http://www.iSearchTheNet.com/isearch/support.php',
            'target' => '_blank',
        ),
    );

$tabCount = count($tabData);

$free_pro = $isearch_pro ? 'Professional' : 'Free';

?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>
<?php $pageTitle = "Search Configuration"; echo $site_name." ".$admin_name." - ".$pageTitle; ?>
</title>
<!-- InstanceEndEditable -->
<?php require_once('../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><meta name="robots" content="noindex,nofollow">
<LINK REL=StyleSheet href="admin.css" ><script  SRC="tooltip.js"></script>
<style><!--
--></style>
<!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" --><layer name="nstooltip" BGCOLOR="#cccccc" WIDTH=0 HEIGHT=0 STYLE="border-width:thin;z-index:1"></layer>
<DIV ID="tooltip"></DIV>


<?php require_once('../../core/region/includes/chooseregion.inc.php'); ?>
<h1>Search Spider Configuration</h1>

<?php 





function showTooltip($title, $tooltip)
{
    global $isearch_config;

    if ($isearch_config['show_admin_tooltips'])
    {
        $tooltip = '<B>'.$title.'</B><BR>' . str_replace("'", '&#39;', str_replace('"', '&quot;', $tooltip));
        return ' onMouseOver="showTooltip(\''.$tooltip.'\')" onMouseOut="hideTooltip()" ';
    }
    return '';
}

function showTooltipIcon($title, $tooltip)
{
    global $isearch_config;

    if ($isearch_config['show_admin_tooltips'])
    {
        $tooltip = '<B>'.$title.'</B><BR>' . str_replace("'", '&#39;', str_replace('"', '&quot;', $tooltip));
        return '<A href="#" onMouseover="window.status=\' \';showTooltip(\''.$tooltip.'\'); return true;" onMouseout="hideTooltip()"><img border="0" src="images/i_13x13.png" width="13" height="13"></A>
';
    }
    return '';
}

function isearch_createIndex($table, $col)
{
    global $isearch_db;

    $needToCreate = True;
    $result = mysql_query("SHOW INDEX FROM $table", $isearch_db);
    while ($item = mysql_fetch_array($result))
    {
        if ($item['Key_name'] == $col)
        {
            $needToCreate = False;
        }
        else if ($item['Key_name'] != "PRIMARY")
        {
            mysql_query("ALTER TABLE $table DROP INDEX " . $item['Key_name'], $isearch_db);
        }
    }

    if ($needToCreate)
    {
        if (! mysql_query("ALTER TABLE $table ADD INDEX $col ($col)", $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
}

function isearch_createTables()
{
    global $isearch_table_info, $isearch_table_urls, $isearch_table_urls_new;
    global $isearch_table_words, $isearch_table_words_new;
    global $isearch_table_search_log, $isearch_table_spider_log, $isearch_table_admin_log;
    global $isearch_table_links, $isearch_table_alts, $isearch_table_links_words;
    global $isearch_db;
    global $isearch_version;
    global $isearch_config;

    $changed = False;

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_info", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_info (" .
                 "id INT NOT NULL primary key, " .
                 "admin_email VARCHAR(255), " .
                 "aggressive_link_search BOOL DEFAULT 0, " .
                 "allow_colons BOOL DEFAULT 0, " .
                 "allow_dashes BOOL DEFAULT 0, " .
                 "dir_redirect_handling INT DEFAULT 1, " .
                 "directory_handling INT DEFAULT 2, " .
                 "basic_authorization VARCHAR(255), " .
                 "char_set VARCHAR(255), " .
                 "char_set_8_bit BOOL DEFAULT 1, " .
                 "check_empty_search TINYINT DEFAULT 1, " .
                 "description_style TINYINT DEFAULT 1, " .
                 "display_strip_query BOOL DEFAULT 0, " .
                 "error_reporting INT DEFAULT 1, " .
                 "extra_link_display TINYINT DEFAULT 1, " .
                 "follow_frames BOOL DEFAULT 1, " .
                 "form_show_advanced TINYINT DEFAULT 1, " .
                 "form_show_groups TINYINT DEFAULT 1, " .
                 "form_show_partial TINYINT DEFAULT 1, " .
                 "file_redirect_handling INT DEFAULT 1, " .
                 "heading_rank INT DEFAULT 3, " .
                 "hide_powered_by BOOL DEFAULT 0, " .
                 "hide_regexp VARCHAR(255) DEFAULT '' NOT NULL, " .
                 "highlight_results BOOL DEFAULT 1, " .
                 "javascript_link_search TINYINT DEFAULT 0, " .
                 "ignore_image_alt_tags TINYINT DEFAULT 0, " .
                 "keep_cache BOOL DEFAULT 0, " .
                 "keyword_rank INT DEFAULT 10, " .
                 "lang_name VARCHAR(255), " .
                 "log_echo_level INT DEFAULT 3, " .
                 "log_level INT DEFAULT 3, " .
                 "log_searches BOOL DEFAULT 1, " .
                 "match_score INT DEFAULT 2, " .
                 "max_displayed_description_length INT DEFAULT 0, " .
                 "max_displayed_title_length INT DEFAULT 0, " .
                 "max_displayed_url_length INT DEFAULT 40, " .
                 "max_execution_time INT DEFAULT 300, " .
                 "max_file_size INT DEFAULT 65536, " .
                 "max_pages INT DEFAULT 20, " .
                 "msword_exec VARCHAR(255), " .
                 "msword_support INT DEFAULT 0, " .
                 "notify_updates BOOL DEFAULT 0, " .
                 "online_id VARCHAR(255), " .
                 "pdf_exec VARCHAR(255), " .
                 "pdf_support INT DEFAULT 0, " .
                 "prevnext_type INT DEFAULT 2, " .
                 "prevnext_num INT DEFAULT 10, " .
                 "proxy_enable TINYINT DEFAULT 0, " .
                 "proxy_host VARCHAR(255) DEFAULT '' NOT NULL, " .
                 "proxy_pass VARCHAR(255) DEFAULT '' NOT NULL, " .
                 "proxy_port INT DEFAULT 8080, " .
                 "proxy_user VARCHAR(255) DEFAULT '' NOT NULL, " .
                 "reading_mechanism TINYINT DEFAULT 0, " .
                 "replace_regexp VARCHAR(255) DEFAULT '' NOT NULL, " .
                 "results_frame VARCHAR(255), " .
                 "results_per_page INT DEFAULT 10, " .
                 "search_all TINYINT DEFAULT 0, " .
                 "search_box_width INT DEFAULT 40, " .
                 "search_help_link TINYINT DEFAULT 1, " .
                 "search_internet TINYINT DEFAULT 1, " .
                 "search_log_email_days INT DEFAULT 0, " .
                 "search_partial TINYINT DEFAULT 0, " .
                 "show_admin_tooltips TINYINT DEFAULT 1, " .
                 "show_size BOOL DEFAULT 1, " .
                 "show_time BOOL DEFAULT 1, " .
                 "show_title BOOL DEFAULT 1, " .
                 "sitemap_type TINYINT DEFAULT 4, " .
                 "spider_delay INT DEFAULT 0, " .
                 "soundex TINYINT DEFAULT 2, " .
                 "start_urls LONGTEXT, " .
                 "stop_words_length TINYINT DEFAULT 2, " .
                 "style_name VARCHAR(255), " .
                 "suggestions TINYINT DEFAULT 1, " .
                 "tmpdir VARCHAR(255), " .
                 "target_frame VARCHAR(255), " .
                 "test_mode TINYINT DEFAULT 0, " .
                 "title_rank INT DEFAULT 10, " .
                 "url_rank INT DEFAULT 0, " .
                 "url_replace VARCHAR(255), " .
                 "url_search VARCHAR(255), " .
                 "word_rank INT DEFAULT 1, " .
                 "www_option TINYINT DEFAULT 1, " .

                 "search_log_last_emailed INT DEFAULT 0, " .
                 "update_last_checked INT DEFAULT 0, " .
                 "update_last_version VARCHAR(255), " .

                 "allowed_ext TEXT, " .
                 "allowed_urls LONGTEXT, " .
                 "allowed_urls_beginning LONGTEXT, " .
                 "exclude_urls LONGTEXT, " .
                 "exclude_urls_beginning LONGTEXT, " .
                 "groups LONGTEXT, " .
                 "remove_get_vars LONGTEXT, " .
                 "stop_words LONGTEXT, " .
                 "strip_defaults TEXT, " .

                 "top_searches INT DEFAULT 20," .
                 "total_searches TINYINT DEFAULT 1," .
                 "last_searches INT DEFAULT 20," .

                 "robots_domains LONGTEXT, " .
                 "robots_excludes LONGTEXT, " .

                 "last_update INT DEFAULT 0" .
                 ")";

        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
        $siteName = strtolower($_SERVER['SERVER_NAME']);
        $siteDomain = str_replace('www.', '', $siteName);
        $startWith = "http://$siteName";
        if ($siteName != $siteDomain)
        {
            $startWith .= " http://$siteDomain";
        }

        if (!mysql_query ("INSERT INTO $isearch_table_info (id, admin_email, start_urls, allowed_urls_beginning, exclude_urls_beginning, allowed_ext, remove_get_vars, lang_name, style_name, target_frame, results_frame, tmpdir, update_last_version, char_set) VALUES (".$regionID.", 'webmaster@$siteDomain', 'http://$siteName/', '$startWith', 'http://$siteName/private/', 'php php3 php4 html htm shtml dhtml asp pl cgi', 'PHPSESSID', 'english', 'default', 'isearch', '_self', '/tmp', '$isearch_version', 'utf-8')", $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }

        $changed = True;
    }
    else
    {
        /* Check whether fields introduced with version 2.1 are present in the table and add them if not. */
        if (!isset($isearch_config['hide_regexp']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD hide_regexp VARCHAR(255) DEFAULT '' NOT NULL, " .
            "ADD replace_regexp VARCHAR(255) DEFAULT '' NOT NULL";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.4 are present in the table and add them if not. */
        if (!isset($isearch_config['stop_words_length']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD stop_words_length TINYINT DEFAULT 2, ".
            "ADD form_show_groups TINYINT DEFAULT 1, ".
            "ADD search_internet TINYINT DEFAULT 1, ".
            "ADD results_frame VARCHAR(255), ".
            "ADD search_help_link TINYINT DEFAULT 1, ".
            "ADD search_box_width INT DEFAULT 40, ".
            "ADD show_size BOOL DEFAULT 1, ".
            "ADD show_title BOOL DEFAULT 1, ".
            "ADD error_reporting INT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }
            else if (!mysql_query("UPDATE $isearch_table_info SET results_frame='_self' WHERE id='1'", $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            $query = "ALTER TABLE $isearch_table_info CHANGE strip_defaults strip_defaults TEXT NOT NULL";
            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.5 are present in the table and add them if not. */
        if (!isset($isearch_config['javascript_link_search']))
        {
            $query = "ALTER TABLE $isearch_table_info ".
            "ADD javascript_link_search TINYINT DEFAULT 0";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.7 are present in the table and add them if not. */
        if (!isset($isearch_config['title_rank']))
        {
            $query = "ALTER TABLE $isearch_table_info ".
            "ADD title_rank INT DEFAULT 10, ".
            "ADD search_all TINYINT DEFAULT 0, ".
            "ADD search_partial TINYINT DEFAULT 0, ".
            "ADD form_show_advanced TINYINT DEFAULT 1, ".
            "ADD form_show_partial TINYINT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.7a are present in the table and add them if not. */
        if (!isset($isearch_config['allow_dashes']))
        {
            $query = "ALTER TABLE $isearch_table_info ".
            "ADD allow_dashes TINYINT DEFAULT 0";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.8 are present in the table and add them if not. */
        if (!isset($isearch_config['extra_link_display']))
        {
            $query = "ALTER TABLE $isearch_table_info ".
            "ADD extra_link_display TINYINT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.9 are present in the table and add them if not. */
        if (!isset($isearch_config['suggestions']))
        {
            $query = "ALTER TABLE $isearch_table_info ".
            "ADD url_rank INT DEFAULT 0, ".
            "ADD max_displayed_description_length INT DEFAULT 0, ".
            "ADD max_displayed_title_length INT DEFAULT 0, ".
            "ADD suggestions TINYINT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.10 are present in the table and add them if not. */
        if (!isset($isearch_config['ignore_image_alt_tags']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD prevnext_type INT DEFAULT 2, " .
            "ADD reading_mechanism TINYINT DEFAULT 0, " .
            "ADD ignore_image_alt_tags TINYINT DEFAULT 0";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.12 are present in the table and add them if not. */
        if (!isset($isearch_config['test_mode']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD prevnext_num INT DEFAULT 10, " .
            "ADD show_time BOOL DEFAULT 1, " .
            "ADD test_mode TINYINT DEFAULT 0";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.13 are present in the table and add them if not. */
        if (!isset($isearch_config['soundex']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD soundex TINYINT DEFAULT 2," .
            "ADD allow_colons TINYINT DEFAULT 0, " .
            "ADD proxy_enable TINYINT DEFAULT 0, " .
            "ADD proxy_host VARCHAR(255) DEFAULT '' NOT NULL, " .
            "ADD proxy_pass VARCHAR(255) DEFAULT '' NOT NULL, " .
            "ADD proxy_port INT DEFAULT 8080, " .
            "ADD proxy_user VARCHAR(255) DEFAULT '' NOT NULL, " .
            "ADD total_searches TINYINT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }

        /* Check whether fields introduced with version 2.14 are present in the table and add them if not. */
        if (!isset($isearch_config['check_empty_search']))
        {
            $query = "ALTER TABLE $isearch_table_info " .
            "ADD check_empty_search TINYINT DEFAULT 1, " .
            "ADD show_admin_tooltips TINYINT DEFAULT 1";

            if (!mysql_query($query, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
            }

            /* Reread the configuration settings */
            $changed = True;
        }
    }

    if ($changed)
    {
        /* Tables have changed. Close and reopen the component */
        isearch_close();
        isearch_open();
    }

    $query_body = "id INT NOT NULL primary key auto_increment, " .
                  "url VARCHAR(255), " .
                  "description LONGTEXT, " .
                  "stripped_body LONGTEXT, " .
                  "words LONGTEXT, " .
                  "title TEXT, " .
                  "state VARCHAR(255), " .
                  "temp_referrer_id INT, " .
                  "referrer_id INT DEFAULT 0, " .
                  "cache LONGTEXT, " .
                  "size INT DEFAULT 0, " .
                  "base VARCHAR(255), " .
                  "sig VARCHAR(255), " .
                  "priority FLOAT DEFAULT -1, " .
                  "lastmod INT, " .
                  "changefreq VARCHAR(255),
				  `regionID` int(11) NOT NULL default '1' ";

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_urls", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_urls ($query_body)";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    else
    {
        mysql_query("ALTER TABLE $isearch_table_urls " .
            "ADD priority FLOAT DEFAULT -1, " .
            "ADD lastmod INT, " .
            "ADD changefreq VARCHAR(255) ", $isearch_db);
    }

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_urls_new", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_urls_new ($query_body)";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    else
    {
        mysql_query("ALTER TABLE $isearch_table_urls_new " .
            "ADD priority FLOAT DEFAULT -1, " .
            "ADD lastmod INT, " .
            "ADD changefreq VARCHAR(255) ", $isearch_db);
    }

    isearch_createIndex($isearch_table_urls, 'url');
    isearch_createIndex($isearch_table_urls_new, 'url');

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_words", $isearch_db))
    {
        $query = "(" .
                 "word VARCHAR(255), " .
                 "score INT4, " .
                 "id INT4, `regionID` int(11) NOT NULL default '1', " .
                 "KEY(word),KEY(regionID))";
        if (!mysql_query("CREATE TABLE $isearch_table_words $query", $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
        if (!mysql_query("CREATE TABLE $isearch_table_words_new $query", $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    isearch_createIndex($isearch_table_words, 'word');
    isearch_createIndex($isearch_table_words_new, 'word');

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_spider_log", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_spider_log (" .
                 "id INT4 NOT NULL primary key auto_increment, " .
                 "msg TEXT, `regionID` int(11) NOT NULL default '1',
  KEY  (`regionID`)" .
                 ")";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_search_log", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_search_log (" .
                 "id INT4 NOT NULL primary key auto_increment, " .
                 "search_term TEXT, " .
                 "time INT4, " .
                 "matches INT4,
				 `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`)" .
                 ")";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }

    mysql_query("ALTER TABLE $isearch_table_search_log ADD checked INT DEFAULT 0", $isearch_db);

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_links", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_links (" .
                 "id INT4 NOT NULL primary key auto_increment, " .
                 "url VARCHAR(255), " .
                 "keywords TEXT, " .
                 "description TEXT, " .
                 "title TEXT,`regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`)" .
                 ")";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_links_words", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_links_words (" .
                 "id INT4, " .
                 "word VARCHAR(255), " .
                 "score INT4, `regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`)," .
                 "KEY(word))";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    isearch_createIndex($isearch_table_links_words, 'word');

    /* Check whether tables exist and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_alts", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_alts (" .
                 "id INT4 NOT NULL primary key auto_increment, " .
                 "keyword VARCHAR(255), " .
                 "alternative VARCHAR(255), " .
                 "redirect TINYINT DEFAULT 0,`regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`)" .
                 ")";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    isearch_createIndex($isearch_table_links_words, 'word');

    /* Check whether table exists and create if necesary */
    if (! mysql_query("DESCRIBE $isearch_table_admin_log", $isearch_db))
    {
        $query = "CREATE TABLE $isearch_table_admin_log (" .
                 "id INT4 NOT NULL primary key auto_increment, " .
                 "msg TEXT, " .
                 "time INT4,`regionID` int(11) NOT NULL default '1',
  KEY `regionID` (`regionID`)" .
                 ")";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " ($query) File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
}

if ($isearch_action == 'update')
{

    if ($tabData[$isearch_tab]['name'] == 'Groups')
    {
        $new_isearch_groups = '';
        for ($i = 0; $i < $_POST['isearch_num_groups']; $i++)
        {
            $name = str_replace(' ', '+', trim(stripslashes($_POST['new_isearch_group_name' . $i])));

            if ($name != '')
            {
                $url = str_replace(' ', '+', trim(stripslashes($_POST['new_isearch_group_url' . $i])));
                $regexp = str_replace(' ', '+', trim(stripslashes($_POST['new_isearch_group_regexp' . $i])));
                if ($new_isearch_groups != '')
                {
                    $new_isearch_groups .= ' ';
                }
                $new_isearch_groups .= $name . ' ' . $url . ' ' . $regexp;
            }
        }

        if (!mysql_query("UPDATE $isearch_table_info SET groups='" . mysql_escape_string2($new_isearch_groups) . "' WHERE id='".intvale($regionID)."'", $isearch_db))
        {
            isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
        isearch_adminLog('Updated group settings');
    }

    if (isset($_POST['isearch_num_links']))
    {
        if (!isset($_POST['isearch_links_add']))
        {
            /* Delete the table. */
            if (!mysql_query("DELETE FROM $isearch_table_links", $isearch_db))
            {
                isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
            if (!mysql_query("DELETE FROM $isearch_table_links_words", $isearch_db))
            {
                isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
        }

        for ($i = 0; $i < $_POST['isearch_num_links']; $i++)
        {
            $url = str_replace(' ', '+', trim(stripslashes($_POST['new_isearch_links_url' . $i])));
            $keywords = isearch_cleanString(stripslashes($_POST['new_isearch_links_keywords' . $i]), 'utf-8');
            $title = preg_replace('#[ ,]+#', ' ', trim(stripslashes($_POST['new_isearch_links_title' . $i])));
            $description = preg_replace('#[ ,]+#', ' ', trim(stripslashes($_POST['new_isearch_links_description' . $i])));

            if ($url != '')
            {
                /* Add new entry. */
                if (!mysql_query ("INSERT INTO $isearch_table_links (url, keywords, description, title, regionID) VALUES ('".mysql_escape_string2($url)."', '".mysql_escape_string2($keywords)."', '".mysql_escape_string2($description)."', '".mysql_escape_string2($title)."',".$regionID.")", $isearch_db))
                {
                    isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                }

                $query = "INSERT INTO $isearch_table_links_words (word, id, score, regionID) VALUES ";
                $needComma = False;
                $id = mysql_insert_id();
                $words = preg_split('/[[:space:]]+/', $keywords);
                foreach ($words as $word)
                {
                    if ($needComma)
                    {
                        $query .= ',';
                    }
                    $needComma = True;
                    $query .= "('$word', '$id', '100', ".$regionID.")";
                }
                if (!mysql_query($query, $isearch_db))
                {
                    isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                }
            }
        }
        isearch_adminLog('Updated link settings', 7);
    }

    if (isset($_POST['isearch_num_alts']))
    {
        if (!isset($_POST['isearch_alts_add']))
        {
            /* Delete the table. */
            if (!mysql_query("DELETE FROM $isearch_table_alts", $isearch_db))
            {
                isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
        }
        for ($i = 0; $i < $_POST['isearch_num_alts']; $i++)
        {
            $keyword = isearch_cleanString(stripslashes($_POST['new_isearch_alts_keyword' . $i]), 'utf-8');
            $alt = isearch_cleanString(stripslashes($_POST['new_isearch_alts_alt' . $i]), 'utf-8');
            $redirect = isset($_POST['new_isearch_alts_redirect'.$i]) ? 1 : 0;

            if ($keyword != '')
            {
                /* Add new entry. */
                if (!mysql_query ("INSERT INTO $isearch_table_alts (keyword, alternative, redirect, regionID) VALUES ('".mysql_escape_string2($keyword)."', '".mysql_escape_string2($alt)."', '$redirect',".$regionID.")", $isearch_db))
                {
                    isearch_adminLog('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                }
            }
        }
        isearch_adminLog('Updated suggestion settings', 7);
    }



    if (isset($new_isearch_remove_get_vars))
    {
        $new_isearch_remove_get_vars = preg_replace("#[,\.[:space:]]+#", " ", $new_isearch_remove_get_vars);
    }

    for ($j = 0; $j < count($tabData[$isearch_tab]['controls']); $j ++)
    {
        $varname = $tabData[$isearch_tab]['controls'][$j]['varname'];
        $isearch_varname="isearch_$varname";
        $new_isearch_varname="new_$isearch_varname";

        if ($tabData[$isearch_tab]['controls'][$j]['type'] == 'checkbox')
        {
            $$new_isearch_varname = isset($_POST[$new_isearch_varname]) ? 1 : 0;
        }
        else
        {
            $$new_isearch_varname = isset($_POST[$new_isearch_varname]) ? stripslashes($_POST[$new_isearch_varname]) : '';
        }

        if (isset($tabData[$isearch_tab]['controls'][$j]['min']))
        {
            if ($$new_isearch_varname < $tabData[$isearch_tab]['controls'][$j]['min'])
            {
                $$new_isearch_varname = $tabData[$isearch_tab]['controls'][$j]['min'];
            }
        }

        if (isset($tabData[$isearch_tab]['controls'][$j]['max']))
        {
            if ($$new_isearch_varname > $tabData[$isearch_tab]['controls'][$j]['max'])
            {
                $$new_isearch_varname = $tabData[$isearch_tab]['controls'][$j]['max'];
            }
        }

        if (is_array($isearch_config[$varname]))
        {
            $$new_isearch_varname = preg_replace("#[[:cntrl:][:space:]]+#", " ", $$new_isearch_varname);
        }
        $$new_isearch_varname = trim($$new_isearch_varname);

        $allowedExtChanged = False;
        if ($varname == 'pdf_support')
        {
            if (($isearch_config['pdf_support'] == '0') && ($$new_isearch_varname != '0'))
            {
                if (!in_array('pdf', $isearch_config['allowed_ext']))
                {
                    $isearch_config['allowed_ext'][] = 'pdf';
                    $allowedExtChanged = True;
                }
            }
            else if (($isearch_config['pdf_support'] != '0') && ($$new_isearch_varname == '0'))
            {
                foreach (array_keys($isearch_config['allowed_ext']) as $key)
                {
                    if ($isearch_config['allowed_ext'][$key] == 'pdf')
                    {
                        unset($isearch_config['allowed_ext'][$key]);
                        $allowedExtChanged = True;
                    }
                }
            }
        }

        if ($varname == 'msword_support')
        {
            if (($isearch_config['msword_support'] == '0') && ($$new_isearch_varname != '0'))
            {
                if (!in_array('doc', $isearch_config['allowed_ext']))
                {
                    $isearch_config['allowed_ext'][] = 'doc';
                    $allowedExtChanged = True;
                }
            }
            else if (($isearch_config['msword_support'] != '0') && ($$new_isearch_varname == '0'))
            {
                foreach (array_keys($isearch_config['allowed_ext']) as $key)
                {
                    if ($isearch_config['allowed_ext'][$key] == 'doc')
                    {
                        unset($isearch_config['allowed_ext'][$key]);
                        $allowedExtChanged = True;
                    }
                }
            }
        }

        if ($allowedExtChanged)
        {
            $value = implode(" ", $isearch_config['allowed_ext']);
            if (!mysql_query("UPDATE $isearch_table_info SET allowed_ext='" . mysql_escape_string2($value) . "' WHERE id=".$regionID, $isearch_db))
            {
                echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
            }
            isearch_adminLog('Automatically updated allowed extension setting', 7);
        }

        /* Query the old value */
        if (!$result = mysql_query("SELECT $varname FROM $isearch_table_info WHERE id=".$regionID, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        if ($item = mysql_fetch_object($result))
        {
            if ($item->$varname != $$new_isearch_varname)
            {
                if (!mysql_query("UPDATE $isearch_table_info SET $varname='" . mysql_escape_string2($$new_isearch_varname) . "' WHERE id=".$regionID, $isearch_db))
                {
                    echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
                }
                isearch_adminLog("Updated $varname => " . $$new_isearch_varname, 7);
            }
        }
    }

    /* Since configuration has changed, ensure that all pages will be spidered
     * again.
     */
    if (!mysql_query("UPDATE $isearch_table_urls SET sig='' WHERE regionID = ".$regionID, $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }

    isearch_close();
    isearch_open();
}

/* Output header tabs */

$tabsPerRow = ceil($tabCount / 2);
$tabCellWidth = floor(100 / $tabsPerRow) . "%";

echo '

<TABLE CLASS="tab_names" WIDTH="100%">
<TR>
';

for ($i = 0; $i < $tabCount; $i++)
{
    if ($i == $tabsPerRow)
    {
        echo '</tr><TR>
';
    }

    if (isset($tabData[$i]['target']))
    {
        $target = $tabData[$i]['target'];
    }
    else
    {
        $target = "_self";
    }

    if (isset($tabData[$i]['url']))
    {
        $url = $tabData[$i]['url'];
        if ($target == '_self')
        {
            $url .= '&isearch_tab='.$i;
        }
    }
    else
    {
        $url = $_SERVER['PHP_SELF'].'?isearch_tab='.$i;
    }

    $windowStatus = strip_tags($tabData[$i]['description']);
    $tooltip = $tabData[$i]['description'];
    $enabled = True;

    echo '<td ';
    if ($i == $isearch_tab)
    {
        echo 'CLASS="selected_tab" ';
    }
    else if (isset($tabData[$i]['enabled']) && (! $tabData[$i]['enabled']))
    {
        echo 'CLASS="disabled_tab" ';
        $tooltip = '<DIV style="color: red;">Only available in the professional version.</DIV><BR>' . $tooltip;
        $windowStatus = 'Only available in the professional version.';
        $enabled = False;
    }

    echo 'WIDTH="'.$tabCellWidth.'" ALIGN=CENTER>';
    echo $enabled ? '<A TARGET="'.$target.'" href="'.$url.'" ' : '<DIV ';
    echo 'CLASS="tab" '.showTooltip($tabData[$i]['name'], $tooltip).'>'.$tabData[$i]['name'].'';
    echo $enabled ? '</A>' : '</DIV>';
    echo '</td>
';
}

if (($i % 2) == 1)
{
    echo "<td WIDTH=\"$tabCellWidth\">&nbsp;</td>";
}

echo '</tr>
</TABLE>

';

if ($isearch_action == "reset_config")
{
    if (!mysql_query("DROP TABLE $isearch_table_info", $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }

    isearch_createTables();
}
else if ($isearch_action == "reset_urls")
{
    if (!mysql_query("DROP TABLE $isearch_table_urls", $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    if (!mysql_query("DROP TABLE $isearch_table_urls_new", $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    if (!mysql_query("DROP TABLE $isearch_table_words", $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    if (!mysql_query("DROP TABLE $isearch_table_words_new", $isearch_db))
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }

    isearch_createTables();
}
else if ($isearch_action == 'google_ping')
{
    $url = 'www.google.com/webmasters/sitemaps/ping?sitemap=http://'.$_SERVER['HTTP_HOST'];
    $url .= str_replace('admin/index.php', 'google_sitemap.php', $_SERVER['PHP_SELF']);

    isearch_readFile($url);
}
else if ($isearch_action == 'clear_search_log')
{
    echo "<p>Search log cleared.";
    isearch_clearSearchLog();
}
else if ($isearch_action == 'clear_admin_log')
{
    echo "<p>Admin log cleared.";
    isearch_clearAdminLog();
}
else if ($isearch_action == 'clear_spider_log')
{
    echo "<p>Spider log cleared.";
    isearch_clearLog();
}
else if ($isearch_action == 'browse')
{
    $isearch_browse_action = isearch_getPostVar("isearch_browse_action");
    $isearch_browse_start = isearch_getPostVar("isearch_browse_start", 0);
    $isearch_browse_num_per_page = 20;

    if ($isearch_browse_action == 'words')
    {
        /* Show list of words */

        $result = mysql_query("SELECT DISTINCT word, score, count(word) AS num FROM $isearch_table_words WHERE regionID = ".$regionID." GROUP BY word ORDER BY num DESC");
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            echo '<p>All words and the number of pages containing that word.
<p><TABLE BORDER=1>
<TR><th>Word</th><th>Matching Pages</th></tr>
';
            while ($item = mysql_fetch_object($result))
            {
                echo "<TR><td><A HREF=\"".$_SERVER['PHP_SELF']."?isearch_action=browse&isearch_browse_action=word&isearch_browse_word=$item->word\">$item->word</A></td><td>$item->num</td></tr>";
            }
            echo '</TABLE>';
        }
    }
    else if ($isearch_browse_action == 'word')
    {
        /* Show a single word */
        $isearch_browse_word = isearch_getPostVar("isearch_browse_word", 0);

        echo "<p>Pages containing the word '$isearch_browse_word'";

        $result = mysql_query("SELECT word, score, id FROM $isearch_table_words WHERE regionID = ".$regionID." AND word='$isearch_browse_word' ORDER BY score DESC");
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            echo '<p><TABLE BORDER=1>
<TR><th>URL</th><th>Score</th><th>&nbsp;</th></tr>
';
            while ($item = mysql_fetch_object($result))
            {
                $result2 = mysql_query("SELECT url FROM $isearch_table_urls WHERE id='$item->id'");
                $item2 = mysql_fetch_object($result2);
                echo "<TR><td><A TARGET=\"_blank\" HREF=\"$item2->url\">$item2->url</A></td><td>$item->score</td><td><A HREF=\"".$_SERVER['PHP_SELF']."?isearch_action=browse&isearch_browse_action=url&isearch_browse_id=$item->id\">Details</A></td></tr>";
            }
            echo '</TABLE>';
        }
    }
    else if ($isearch_browse_action == 'url')
    {
        /* Show a single URL */
        $isearch_browse_id = isearch_getPostVar("isearch_browse_id", 0);

        $result = mysql_query("SELECT id, url, title FROM $isearch_table_urls WHERE id='$isearch_browse_id'");
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            echo "<p>Details of words found on the page:\n";
            while ($item = mysql_fetch_object($result))
            {
                echo " <A TARGET=\"_blank\" HREF=\"$item->url\">$item->url</A> : $item->title";
            }
        }

        $result = mysql_query("SELECT word, score FROM $isearch_table_words WHERE id='$isearch_browse_id' ORDER BY word");
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            echo '<p><TABLE BORDER=1>
<TR><th>Word</th><th>Score</th></tr>
';
            while ($item = mysql_fetch_object($result))
            {
                echo "<TR><td><A HREF=\"".$_SERVER['PHP_SELF']."?isearch_action=browse&isearch_browse_action=word&isearch_browse_word=$item->word\">$item->word</A></td><td>$item->score</td></tr>";
            }
            echo '</TABLE>';
        }
    }
    else
    {
        /* Show list of URLs */
        $result = mysql_query("SELECT id, url, title FROM $isearch_table_urls WHERE regionID = ".$regionID." ORDER BY URL LIMIT $isearch_browse_start, $isearch_browse_num_per_page");
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            echo "<p>Showing URLs ".($isearch_browse_start + 1)." to " . ($isearch_browse_start + mysql_num_rows($result)) .":<p><TABLE BORDER=1>\n";
            $count = 0;
            while ($item = mysql_fetch_object($result))
            {
                $count ++;
                echo "<TR><td><A TARGET=\"_blank\" HREF=\"$item->url\">$item->url</A></td><td>$item->title</td><td><A HREF=\"".$_SERVER['PHP_SELF']."?isearch_action=browse&isearch_browse_action=url&isearch_browse_id=$item->id\">Details</A></td></tr>";
            }
            echo "</TABLE>\n<p>\n";
            if ($isearch_browse_start > 0)
            {
                echo '<A href="'.$_SERVER['PHP_SELF'].'?isearch_action=browse&isearch_browse_action=urls&isearch_browse_start='.($isearch_browse_start-$isearch_browse_num_per_page).'">Previous Page</A><BR>';
            }
            if ($count == $isearch_browse_num_per_page)
            {
                echo '<A href="'.$_SERVER['PHP_SELF'].'?isearch_action=browse&isearch_browse_action=urls&isearch_browse_start='.($isearch_browse_start+$isearch_browse_num_per_page).'">Next Page</A>';
            }
        }
    }

   ?>

<CENTER>
<HR>
<H2><A href="$_SERVER['PHP_SELF']?isearch_action=browse">Browse URLs</A></H2>
<H2><A href="$_SERVER['PHP_SELF']?isearch_action=browse&isearch_browse_action=words">Browse Words</A></H2>
<H2><A href="$_SERVER['PHP_SELF']">Back to Configuration</A></H2></CENTER>
<?php     exit;
}
else if (($isearch_action == 'add_remove_respider'))
{
    $isearch_add_remove_respider_action = isearch_getPostVar('isearch_add_remove_respider_action');
    $isearch_show_combo = isearch_getPostVar('isearch_show_combo', (isearch_getUrlCount() < 100) ? '1' : '0');

    if (isearch_getUrlCount(True) == 0)
    {
        isearch_copyUrlTables(True);
    }

    if ($isearch_add_remove_respider_action == 'save')
    {
        $isearch_add = trim(stripslashes(isearch_getPostVar('isearch_add')));
        $isearch_add = ($isearch_add == '') ? array() : preg_split('/[[:space:]]+/', $isearch_add);
        $isearch_delete = trim(stripslashes(isearch_getPostVar('isearch_delete')));
        $isearch_delete = ($isearch_delete == '') ? array() : preg_split('/[[:space:]]+/', $isearch_delete);

        foreach ($isearch_add as $url)
        {
            $result = mysql_query("SELECT id FROM $isearch_table_urls_new WHERE regionID = ".$regionID." AND url='".mysql_escape_string2($url)."'", $isearch_db);
            if (!$result)
            {
                echo 'ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__;
            }
            else if ($item = mysql_fetch_object($result))
            {
                /* Already in database */
                mysql_query("UPDATE $isearch_table_urls_new SET state='new' WHERE id='".$item->id."'", $isearch_db);
                mysql_query("DELETE $isearch_table_words_new SET state='new' WHERE id='".$item->id."'", $isearch_db);
            }
            else
            {
                mysql_query("INSERT INTO $isearch_table_urls_new (url, temp_referrer_id, state, regionID) VALUES ('".mysql_escape_string2($url)."', '-1', 'new', ".$regionID.")", $isearch_db);
            }
        }

        foreach ($isearch_delete as $url)
        {
            $result = mysql_query("SELECT id FROM $isearch_table_urls_new WHERE regionID = ".$regionID." AND url='".mysql_escape_string2($url)."'", $isearch_db);
            if (!$result)
            {
                echo 'ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__;
            }
            else if ($item = mysql_fetch_object($result))
            {
                /* Already in database */
                mysql_query("DELETE FROM $isearch_table_urls_new WHERE id='".$item->id."'", $isearch_db);
                mysql_query("DELETE FROM $isearch_table_words_new WHERE id='".$item->id."'", $isearch_db);
            }
        }
    }
    echo '

<p>Using this page you can add new URLs to the search index, remove existing
URLs from the search index or cause exising URLs to be respidered. Any other
URLs in the search index will not be affected. This allows you to make changes
without having to respider your whole site.

<p>Note that URLs added or deleted here will not be added or deleted
automatically next time your site is fully spidered.

<p>Use the text areas below to enter a list of URLs (space or line seperated) to
add/respider or remove. URLs must be absolute and should begin with
&quot;http://&quot;. Once you have entered the URLs, click &quot;Save&quot;, then
click &quot;Spider Changes&quot;

<p>
<CENTER>
<TABLE>
 <FORM method="post" action="'.$_SERVER['PHP_SELF'].'" name="form1">

  <TR><td ALIGN="center" colspan=2>
';

    if ($isearch_show_combo)
    {
        echo '<select name="combo">';
        $result = mysql_query("SELECT url FROM $isearch_table_urls_new WHERE regionID = ".$regionID);
        if (!$result)
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
        else
        {
            while ($item = mysql_fetch_object($result))
            {
                echo '<option VALUE="'.$item->url.'">'.$item->url.'</option>'."\n";
            }
        }
        echo '</select>
<scripttype="text/javascript">
<!--
function copyUrl(targetField)
{
    srcVal = document.form1.combo.options[document.form1.combo.selectedIndex].value;
    if (targetField.value != "")
    {
        targetField.value = targetField.value + "\n";
    }
    targetField.value = targetField.value + srcVal;
}
-->
</script>
<A href="javascript:copyUrl(document.form1.isearch_add)" onMouseOver="window.status=\'Add URL to index\';return true;">Add/Respider</A>
<A href="javascript:copyUrl(document.form1.isearch_delete)" onMouseOver="window.status=\'Delete URL from index\';return true;">Delete</A>
';
    }
    else
    {
        echo '<A href="'.$_SERVER['PHP_SELF'].'?isearch_action=add_remove_respider&isearch_show_combo=1">Show URLs</A>';
    }

    echo '
   </td></tr>
  <TR><td>Add/Respider URLs:<td><textarea class="form-control" cols="60" name="isearch_add" rows="10"></textarea></td></tr>
  <TR><td>Delete URLs:<td><textarea class="form-control" cols="60" name="isearch_delete" rows="10"></textarea></td></tr>
  <TR><td colspan=2 ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" VALUE="Save"></td></tr>
  <input TYPE="hidden" name="isearch_action" VALUE="add_remove_respider">
  <input TYPE="hidden" name="isearch_add_remove_respider_action" VALUE="save">
  <input TYPE="hidden" name="isearch_show_combo" VALUE="'.$isearch_show_combo.'">
 </FORM>
 <FORM method="get" action="reindex_frame.php" target="isearch_reindex_frame">
  <TR><td ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" VALUE="Spider Changes"></td></tr>
  <input TYPE="hidden" name="reset" VALUE="false">
 </FORM>
</TABLE>
<HR>
<H2><A href="'.$_SERVER['PHP_SELF'].'">Back to Configuration</A></H2></CENTER>'; exit;
}
else if (($isearch_action == 'backup_restore') || ($isearch_action == 'restore'))
{
    $isearch_backup_data = isearch_getPostVar('isearch_backup_data');
    if ($isearch_action == 'restore')
    {
        $query = "UPDATE $isearch_table_info SET " . preg_replace("/\\s+/", ' ', stripslashes($isearch_backup_data)) . " WHERE id=".intval($regionID)."";
        if (!mysql_query($query, $isearch_db))
        {
            echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
        }
    }
    $resultInfo = mysql_query ("SELECT * FROM $isearch_table_info WHERE id=".intval($regionID)."", $isearch_db);
    $config = mysql_fetch_array($resultInfo, MYSQL_ASSOC);
    $backup = '';
    foreach (array_keys($config) as $key)
    {
        if ($backup != '')
        {
            $backup .= ",\n";
        }
        $backup .= $key."='".mysql_escape_string2($config[$key])."'";
    }

   ?>

<p>The text box below shows the current settings. You can copy these to a text file.

<p>To restore the settings, simply copy the saved settings, then paste into the text field below, then click &quot;Restore&quot;.

<p><CENTER>
<TABLE class="form-table">
 <TR>
  <td>
   <FORM action="$_SERVER['PHP_SELF']" method="post">
    <textarea class="form-control" cols="40" name="isearch_backup_data" rows="20">$backup</textarea>
    <input TYPE="hidden" name="isearch_action" VALUE="restore">
    <input type="submit" class="btn btn-default btn-secondary" VALUE="Restore">
   </FORM>
  </td>
 </tr>
</TABLE>
<HR>
<H2><A href="$_SERVER['PHP_SELF']">Back to Configuration</A></H2></CENTER><?php }


/* Force re-read of configuration */
isearch_close();
isearch_open();

/* Output the configuration form */

$tabName = $tabData[$isearch_tab]['name'];
$tabDescription= $tabData[$isearch_tab]['description'];

echo '

<p>
<CENTER>
<TABLE CLASS="tab_header" WIDTH="100%">
  <TR>
    <td><B>'.$tabName.' - '.$tabDescription.'</B></td>
  </tr>
</TABLE

<p>
<table class="form-table" style="width:100%">
  <TR>
    <td colspan=3>
    <TABLE BORDER=0 WIDTH=100%>
    <TR>
    <FORM method="post" action="'.$_SERVER['PHP_SELF'].'">
     <input TYPE="hidden" name="isearch_action" VALUE="reset_urls">
      <td WIDTH="25%" ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" VALUE="Reset URL Index" '.showTooltip('Reset URL Index', 'Clears the index of pages that you have spidered.').'></td>
    </FORM>
    <FORM method="get" action="reindex_frame.php" target="isearch_reindex_frame">
      <td WIDTH="25%" ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" VALUE="Spider" '.showTooltip('Spider', 'Spiders your site and builds an index of search words and pages.').'></td>
      <input TYPE="hidden" name="reset" VALUE="true">
    </FORM>

    <FORM method="post" action="'.$_SERVER['PHP_SELF'].'">
     <input TYPE="hidden" name="isearch_action" VALUE="reset_config">
      <td WIDTH="25%" ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" VALUE="Reset Settings" '.showTooltip('Reset Settings', 'Resets all settings to their default values.').'></td>
    </FORM>

    <FORM method="post" action="'.$_SERVER['PHP_SELF'].'">
     <td WIDTH="25%" ALIGN="center"><input type="submit" class="btn btn-default btn-secondary" name="isearch_submit" VALUE="Save" '.showTooltip('Save', 'Saves any changes that you have made to the configuration options.').'></td>
    </tr>
    </TABLE>
  </tr>
';

for ($j = 0; $j < count($tabData[$isearch_tab]['controls']); $j ++)
{
    echo "<TR>\n";
    $name        = $tabData[$isearch_tab]['controls'][$j]['name'];
    $varname     = $tabData[$isearch_tab]['controls'][$j]['varname'];
    $description = $tabData[$isearch_tab]['controls'][$j]['description'];

    if (is_array($isearch_config[$varname]))
    {
        $value = implode(" ", $isearch_config[$varname]);
    }
    else
    {
        $value = $isearch_config[$varname];
    }

    echo "<td><B>$name</B> ".showTooltipIcon($name, $description)."</td>\n";
    switch ($tabData[$isearch_tab]['controls'][$j]['type'])
    {
        case 'text':
            $size        = $tabData[$isearch_tab]['controls'][$j]['size'];
            $maxlength   = $tabData[$isearch_tab]['controls'][$j]['maxlength'];
            echo "<td><input maxlength=\"$maxlength\" NAME=\"new_isearch_$varname\" SIZE=\"$size\" VALUE=\"$value\"></td>\n";
        break;

        case 'textarea':
            echo "<td><textarea class=\"form-control\" cols=\"40\" NAME=\"new_isearch_$varname\" rows=\"5\">$value</textarea></td>\n";
        break;

        case 'checkbox':
            $checked = $isearch_config[$varname] ? ' checked' : '' ;
            echo "<td><input TYPE=\"checkbox\" NAME=\"new_isearch_$varname\"$checked></td>\n";
        break;

        case "combo":
            echo "<td><select NAME=\"new_isearch_$varname\">\n";
            if (isset($tabData[$isearch_tab]['controls'][$j]['items']))
            {
                foreach ($tabData[$isearch_tab]['controls'][$j]['items'] as $item)
                {
                    echo "<option value=\"" . $item['value'] . "\"";
                    if ($isearch_config[$varname] == $item['value'])
                    {
                        echo " selected";
                    }
                    echo ">" . $item['description'] . "</option>\n";
                }
            }
            else
            {
                $itemvar = $tabData[$isearch_tab]['controls'][$j]['itemvar'];
                foreach ($$itemvar as $item)
                {
                    echo "<option value=\"" . $item . "\"";
                    if ($isearch_config[$varname] == $item)
                    {
                        echo " selected";
                    }
                    echo ">" . $item . "</option>\n";
                }
            }
            echo "</select></td>\n";
        break;

        default:
            echo "<H1>ERROR - Unknown type " . $tabData[$isearch_tab]['controls'][$j]['type'] . "</H1>\n";
        break;
    }

    if ($isearch_config['show_admin_tooltips'])
    {
        echo "<NOSCRIPT><td><SMALL>$description</SMALL></td></NOSCRIPT>\n";
    }
    else
    {
        echo "<td><SMALL>$description</SMALL></td>\n";
    }
    echo "</tr>\n\n";
}

if ($tabName == 'Groups')
{
    echo '<TR><td colspan=3>Each group has a name, a &quot;URL beginning&quot; setting and a &quot;URL regexp&quot; setting.
Any URLs that match a groups parameters will be included in that group. A URL can belong to multple groups.
All URLs belong to the &quot;All&quot; group.
<p>To use groups you must enter a group name, followed by either/both the beginning of the URL that matches that group or a regexp of the URL that matches that group.
Any page that begins the with the &quot;URL beginning&quot; setting or matches the &quot;URL regexp&quot; setting will be in the group.
<p>To delete a group, delete the name and click &quot;Save&quot;. Once you have filled all rows below, click &quot;Save&quot; to create more blank rows.</td></tr>
';


    echo '<TR><th>Group Name</th><th>Group URL beginning</th><th>Group URL regexp</th></tr>';

    $numGroups = floor(count($isearch_config['groups']) / 3);

    for ($i = 0; $i < $numGroups + 5; $i++)
    {
        echo "<TR>\n";
        if ($i < $numGroups)
        {
            $name = str_replace('+', ' ', $isearch_config['groups'][$i * 3]);
            $url_beginning = $isearch_config['groups'][($i * 3)+1];
            $url_regexp = $isearch_config['groups'][($i * 3)+2];
        }
        else
        {
            $name = '';
            $url_beginning = '';
            $url_regexp = '';
        }
        echo '<td><input maxlength="255" name="new_isearch_group_name' . $i . '" SIZE="20" VALUE="' . $name . '"></td>' . "\n";
        echo '<td><input maxlength="255" name="new_isearch_group_url' . $i . '" SIZE="40" VALUE="' . $url_beginning . '"></td>' . "\n";
        echo '<td><input maxlength="255" name="new_isearch_group_regexp' . $i . '" SIZE="40" VALUE="' . $url_regexp . '"></td>' . "\n";
        echo "</tr>\n\n";
    }
    echo '<input TYPE="hidden" name="isearch_num_groups" VALUE="' . $i . '">' . "\n";



}
else if ($tabName == 'Links')
{
    echo '<TR><td colspan=3>To use links you must enter a URL, keywords that display that URL, a title and a description.
<p>To delete a link delete the URL and click &quot;Save&quot;. Once you have filled all rows below, click &quot;Save&quot; to create more blank rows.</td></tr>
';

    echo '<TR>
<td colspan=3><TABLE BORDER=0 WIDTH="100%" CELLPADDING="0">
<TR>
<th>Link URL</th>
<th>Keywords</th>
<th>Title</th>
<th>Description</th>
</tr>
';
    $result = mysql_query("SELECT * FROM $isearch_table_links ORDER BY id");
    if (!$result)
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    else
    {
        $numLinks = mysql_num_rows($result);

        for ($i = 0; $i < $numLinks + 5; $i++)
        {
            echo "<TR>\n";
            if ($i < $numLinks)
            {
                $item = mysql_fetch_object($result);
                $url = $item->url;
                $keywords = $item->keywords;
                $title = $item->title;
                $description = $item->description;
            }
            else
            {
                $url = '';
                $keywords = '';
                $title = '';
                $description = '';
            }
            echo '<td><input maxlength="255" name="new_isearch_links_url'.$i.'" SIZE="25" VALUE="' . $url . '"></td>' . "\n";
            echo '<td><textarea class="form-control" cols="20" name="new_isearch_links_keywords'.$i.'" rows="5">'.$keywords.'</textarea></td>'."\n";
            echo '<td><textarea class="form-control" cols="20" name="new_isearch_links_title'.$i.'" rows="5">'.$title.'</textarea></td>'."\n";
            echo '<td><textarea class="form-control" cols="20" name="new_isearch_links_description'.$i.'" rows="5">'.$description.'</textarea></td>'."\n";
            echo "</tr>\n\n";
        }
        echo '<input TYPE="hidden" name="isearch_num_links" VALUE="' . $i . '">' . "\n";
        while ($item = mysql_fetch_object($result))
        {
            echo "<TR><td><A HREF=\"$item->url\">$item->word</A></td><td>$item->num</td></tr>";
        }
    }


    echo "</TABLE></td></tr>\n";

    if (!$isearch_pro) echo "<TR><td colspan=3><B>Only available in the <A HREF=\"http://www.iSearchTheNet.com/pro\" TARGET=\"_blank\">professional version</A>.</B></td></tr>";
}
else if ($tabName == 'Suggestions')
{
    echo "<TR>\n";
    echo '<td colspan=3>Suggestions allow you to suggest alternative options to your visitors when they search for specific terms. You could use this for common mispellings.
<p>If the redirect column is checked, the search will automatically replace the keyword with the alternative. If it is not checked, the user will be presented with a message giving the option to repeat the search with the alternative.
<p>To delete an alternative, delete the keyword and click &quot;Save&quot;. Once you have filled all rows below, click &quot;Save&quot; to create more blank rows.
</td>
';
    echo "</tr>\n";

    echo '<TR><td colspan=3></td></tr>
<TR>
<td colspan=3><TABLE BORDER=0 WIDTH="100%" CELLPADDING="0">
<TR>
<th ALIGN=center>Keyword</th>
<th ALIGN=center>Alternative</th>
<th ALIGN=center>Redirect</th>
</tr>
';

    $result = mysql_query("SELECT * FROM $isearch_table_alts ORDER BY keyword");
    if (!$result)
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    else
    {
        $numLinks = mysql_num_rows($result);

        for ($i = 0; $i < $numLinks + 5; $i++)
        {
            echo "<TR>\n";
            if ($i < $numLinks)
            {
                $item = mysql_fetch_object($result);
                $keyword = $item->keyword;
                $alternative = $item->alternative;
                $redirect = $item->redirect ? ' checked' : '';
            }
            else
            {
                $keyword = '';
                $alternative = '';
                $redirect = '';
            }
            echo '<td ALIGN=center><input maxlength="255" name="new_isearch_alts_keyword'.$i.'" SIZE="25" VALUE="'.$keyword.'"></td>
<td ALIGN=center><input maxlength="255" name="new_isearch_alts_alt'.$i.'" SIZE="25" VALUE="'.$alternative.'"></td>
<td ALIGN=center><input TYPE="checkbox" name="new_isearch_alts_redirect'.$i.'"'.$redirect.'></td>
</tr>
';
        }
        echo '<input TYPE="hidden" name="isearch_num_alts" VALUE="' . $i . '">' . "\n";
        while ($item = mysql_fetch_object($result))
        {
            echo "<TR><td><A HREF=\"$item->url\">$item->word</A></td><td>$item->num</td></tr>";
        }
    }


    echo "</TABLE></td></tr>\n";

    if (!$isearch_pro) echo "<TR><td colspan=3><B>Only available in the <A HREF=\"http://www.iSearchTheNet.com/pro\" TARGET=\"_blank\">professional version</A>.</B></td></tr>";
}
else if ($tabName == 'Smart Log')
{
    echo "<TR>\n";
    echo '<td colspan=3>Allows you to make intelligent decisions about searches that users have performed that did not return any results.
</td>
';
    echo "</tr>\n";

    echo "<TR><td colspan=3></td></tr>\n";

    $deleteTerms = array();
    foreach (array_keys($_POST) as $key)
    {
        if (preg_match('/^new_isearch_checked[0-9]+$/', $key))
        {
            $deleteTerms[] = $_POST[$key];
        }
    }

    if ($isearch_action == 'prompt_suggestion')
    {
        $keywords = $_REQUEST['keywords'];
        echo '<TR><td colspan=3>Enter a new suggestion for the keyword(s) '.$keywords.'<p>
<TABLE BORDER=1>
<TR>
 <th ALIGN=center>Keyword</th>
 <th ALIGN=center>Alternative</th>
 <th ALIGN=center>Redirect</th>
</tr>
<TR>
 <td ALIGN=center><input maxlength="255" name="new_isearch_alts_keyword0" SIZE="25" VALUE="'.$keywords.'"></td>
 <td ALIGN=center><input maxlength="255" name="new_isearch_alts_alt0" SIZE="25" VALUE=""></td>
 <td ALIGN=center><input TYPE="checkbox" name="new_isearch_alts_redirect0"></td>
</tr>
</TABLE>
<input name="isearch_num_alts" VALUE="1" TYPE="hidden">
<input name="isearch_alts_add" VALUE="1" TYPE="hidden">
</td></tr>
';
    }
    else if ($isearch_action == 'prompt_link')
    {
        $keywords = $_REQUEST['keywords'];
        echo '<TR><td colspan=3>Enter a new link for the keywords(s) '.$keywords.'<p>
<TABLE BORDER=1>
<TR>
 <th>Link URL</th>
 <th>Keywords</th>
 <th>Title</th>
 <th>Description</th>
</tr>
<TR><td><input maxlength="255" name="new_isearch_links_url0" SIZE="25" VALUE=""></td>
<td><textarea class="form-control" cols="20" name="new_isearch_links_keywords0" rows="5">'.$keywords.'</textarea></td>
<td><textarea class="form-control" cols="20" name="new_isearch_links_title0" rows="5"></textarea></td>
<td><textarea class="form-control" cols="20" name="new_isearch_links_description0" rows="5"></textarea></td>
</TABLE>
<input name="isearch_num_links" VALUE="1" TYPE="hidden">
<input name="isearch_links_add" VALUE="1" TYPE="hidden">
</td></tr>
';
    }

    echo "<TR>\n";
    echo '<td colspan=3><TABLE BORDER=0 WIDTH="100%" CELLPADDING="0">' . "\n";
    echo "<TR>\n";
    echo "<th ALIGN=center>Keyword(s)</th>\n";
    echo "<th ALIGN=center>Actions</th>\n";
    echo "<th ALIGN=center>Remove</th>\n";
    echo "</tr>\n\n";

    $result = mysql_query("SELECT DISTINCT search_term FROM $isearch_table_search_log WHERE regionID = ".$regionID." AND matches='0' AND checked='0' ORDER BY search_term");
    if (!$result)
    {
        echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
    }
    else
    {
        $terms = array();
        while ($item = mysql_fetch_object($result))
        {
            $term = $item->search_term;
            if ($isearch_config['allow_dashes'])
            {
                $term = preg_replace('#[+"]#', ' ', $term);
                $term = str_replace('- ', ' ', $term);
                $term = str_replace(' -', ' ', $term);
            }
            else
            {
                $term = preg_replace('#[-+"]#', ' ', $term);
            }

            $term = preg_replace('#[[:space:]]+#', ' ', $term);
            $term = trim($term);

            if (in_array($term, $deleteTerms))
            {
                $result2 = mysql_query("UPDATE $isearch_table_search_log SET checked='1' WHERE regionID = ".$regionID." AND search_term='".mysql_escape_string2($item->search_term)."'");
                if (!$result2)
                {
                    echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
                }
            }
            else if ($term != '')
            {
                $terms[] = $term;
            }
        }
        $terms = array_unique($terms);

        $i = 0;
        foreach ($terms as $term)
        {
            $words = preg_split('/[[:space:]]+/', $term);
            $found = False;
            foreach ($words as $word)
            {
                $result2 = mysql_query("SELECT id FROM $isearch_table_alts WHERE regionID = ".$regionID." AND keyword='".mysql_escape_string2($word)."'");
                if (!$result2)
                {
                    echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
                }
                if (mysql_num_rows($result2) > 0)
                {
                    $found = True;
                    break;
                }
                $result2 = mysql_query("SELECT id FROM $isearch_table_links_words WHERE regionID = ".$regionID." AND word='".mysql_escape_string2($word)."'");
                if (!$result2)
                {
                    echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
                }
                if (mysql_num_rows($result2) > 0)
                {
                    $found = True;
                    break;
                }
            }

            if ($found)
            {
                $result2 = mysql_query("UPDATE $isearch_table_search_log SET checked='1' WHERE regionID = ".$regionID." AND search_term='".mysql_escape_string2($term)."'");
                if (!$result2)
                {
                    echo "<p>MySQL Error : " . mysql_error() . " File: " . __FILE__ . " Line: " . __LINE__;
                }
            }
            else
            {
                echo '<TR>
<td ALIGN=center>'.$term.'</td>
<td ALIGN=center><A TARGET="_blank" href="../index.php?s='.urlencode($term).'">Search</A>&nbsp;|&nbsp;<A href="'.$_SERVER['PHP_SELF'].'?isearch_action=prompt_suggestion&isearch_tab='.$isearch_tab.'&keywords='.urlencode($term).'">Add&nbsp;Suggestion</A>&nbsp;|&nbsp;<A href="'.$_SERVER['PHP_SELF'].'?isearch_action=prompt_link&isearch_tab='.$isearch_tab.'&keywords='.urlencode($term).'">Add&nbsp;Link</A></td>
<td ALIGN=center><input TYPE="checkbox" name="new_isearch_checked'.$i++.'" VALUE="'.urlencode($term).'"></td>
</tr>
';
            }
        }
    }


    echo "</TABLE></td></tr>\n";

    if (!$isearch_pro) echo "<TR><td colspan=3><B>Only available in the <A HREF=\"http://www.iSearchTheNet.com/pro\" TARGET=\"_blank\">professional version</A>.</B></td></tr>";
}

echo '
  <TR>
    <td colspan=3 ALIGN=CENTER>
      <input type="submit" class="btn btn-default btn-secondary" name="isearch_submit" VALUE="Save" '.showTooltip('Save', 'Saves any changes that you have made to the configuration options.').'>
    </td>
  </tr>
<input TYPE="hidden" name="isearch_action" VALUE="update">
<input TYPE="hidden" name="isearch_tab" VALUE="' . $isearch_tab . '">
</FORM>
</TABLE>
</CENTER>

';

if ($tabName == 'Logging')
{

    echo '
<p>iSearch maintains three logs:

<UL>
<LI>The spider log keeps a log of messages generated when pages are spidered.
<LI>The search log records searches that have been performed on your site.
<LI>The admin log records admin actions.
</UL>

<p>The spider log is cleared automatically whenever spidering is restarted. The
search log and admin log are only cleared when the &quot;Clear Search Log&quot; or &quot;Clear Admin Log&quot; button below is pressed.

<p>
<CENTER>
<TABLE>
 <TR>
  <td><A href="log.php" target="isearch_spider_log">View Spider Log</A></td>
  <td><A href="'.$_SERVER['PHP_SELF'].'?isearch_action=clear_spider_log">Clear Spider Log</A></td>
 </tr>

 <TR>
  <td><A href="search_log.php" target="isearch_search_log">View Search Log</A></td>
  <td><A href="'.$_SERVER['PHP_SELF'].'?isearch_action=clear_search_log">Clear Search Log</A></td>
 </tr>

 <TR>
  <td><A href="admin_log.php" target="isearch_admin_log">View Admin Log</A></td>
  <td><A href="'.$_SERVER['PHP_SELF'].'?isearch_action=clear_admin_log">Clear Admin Log</A></td>
 </tr>
</TABLE>
</CENTER>
';
}

if (isset($tabData[$isearch_tab]['notes']))
{
    echo '<H3>Notes</H3>'.$tabData[$isearch_tab]['notes'];
}








if ($isearch_admin_password != '')
{
    ?>
<center>
<p><small><A href="$_SERVER['PHP_SELF']?isearch_password">Logout</A></small>
<?php

}

isearch_close(); ?>
</center><!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
<?php
if(is_resource($rsLoggedIn)) {
mysql_free_result($rsLoggedIn);
}
?>
