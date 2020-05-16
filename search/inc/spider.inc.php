<?php



/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet/isearch             *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

if ( !defined('IN_ISEARCH') )
{
    die('Hacking attempt');
}

if(!function_exists("mysql_escape_string2")) {
function mysql_escape_string2($theValue) {
	$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
	return $theValue;
}
}

/* Parse the robots.txt stored in the database */
function isearch_parseRobots($domain)
{
    global $regionID, $isearch_config;
    global $isearch_table_info;
    global $isearch_db;
    global $isearch_base;

    $isearch_config['robots_domains'][] = $domain;

    // Store in a temp veriable to allow other relative URLs to be evaluated
    // after parsing robots.txt.
    $isearch_base_tmp = $isearch_base;
    $allData = isearch_readFile("http://$domain/robots.txt");
    $isearch_base = $isearch_base_tmp;

    if ($allData != '')
    {
        $allData = strtolower($allData);
        $lines = preg_split("/(\r|\n)/", $allData);
        $validUseragent = False;
        $matched = False;

        foreach ($lines as $line)
        {
            $line = preg_replace('/#.*$/', '', $line);
            $line = preg_replace('#[[:space:]]+#', ' ', $line);
            $temp = explode(':', $line, 2);
            if (count($temp) == 2)
            {
                $field = trim($temp[0]);
                $value = trim($temp[1]);
                if ($field == 'user-agent')
                {
                    $validUseragent = False;
                    $useragents = explode(' ', $value);
                    foreach ($useragents as $useragent)
                    {
                        if (($useragent == 'isearch') || (($useragent == '*') && (!$matched)))
                        {
                            $matched = True;
                            $validUseragent = True;
                        }
                    }
                }
                else if (($validUseragent) && ($field == 'disallow'))
                {
                    if ($value == '')
                    {
                        /* This is an allow - remove all previous disallows */
                        unset($disallow);
                    }
                    else
                    {
                        $disallow[] = $value;
                    }
                }
            }
        }

        if (isset($disallow))
        {
            foreach ($disallow as $temp)
            {
                if ($temp{0} != '/')
                {
                    $temp = '/' . $temp;
                }
                $url = "^http://$domain$temp";
                $url = preg_replace('#\.#', '\.', $url);
                $url = preg_replace('#\*#', '.*', $url);
                $url = preg_replace('#\?#', '\?', $url);
                $url = preg_replace('#\+#', '\+', $url);
                $isearch_config['robots_excludes'][] = $url;
            }
        }
    }

    if (!mysql_query("UPDATE $isearch_table_info SET robots_domains='" . mysql_escape_string2(implode(" ", $isearch_config['robots_domains'])) . "', robots_excludes='" . mysql_escape_string2(implode(" ", $isearch_config['robots_excludes'])) . "' WHERE id='".intval($regionID)."'", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }
	
}


/* Clear the iSearch log file */
function isearch_clearLog()
{
    global $isearch_table_spider_log;
    global $regionID, $isearch_db;

    mysql_query("DELETE FROM $isearch_table_spider_log WHERE regionID =".$regionID, $isearch_db);
}


/* Return the contents of the spider log */
function isearch_getLog()
{
    global $regionID, $isearch_table_spider_log;
    global $isearch_db;

    $log = '';

    $result = mysql_query("SELECT * FROM $isearch_table_spider_log WHERE  regionID = ".intval($regionID)."   ORDER BY id", $isearch_db);
    if ($result)
    {
        while ($item = mysql_fetch_object($result))
        {
            $log .= $item->msg . "<BR>\n";
        }
    }

    return $log;
}


/* Save the string in the iSearch log file */
function isearch_log($string, $level=1)
{
    global $regionID, $isearch_table_spider_log;
    global $isearch_db;
    global $isearch_config;

    if ($level <= $isearch_config['log_level'])
    {
        mysql_query("INSERT INTO $isearch_table_spider_log (msg, regionID) VALUES ('" . mysql_escape_string2($string) . "', ".intval($regionID).")", $isearch_db);
    }

    if ($level <= $isearch_config['log_echo_level'])
    {
        echo $string . "<BR>\n";
    }
}


/* Clean up a string to make it suitable for storing in search index */
function isearch_cleanString($data, $charset)
{
    global $isearch_config;

    if ($isearch_config['char_set_8_bit'])
    {
        /* Convert to lower case, doing accented character conversion correctly */
        $data = strtr($data, 'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞ' . chr(0x8a) . chr(0x8e) ,
                             'abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþ' . chr(0x9a) . chr(0x9e) );
    }

    /* Strip out all HTML tags */
    $data = strip_tags($data);

    /* Replace some breaking chars with spaces */
    $data = preg_replace('#[\\.,;\?!]+#', ' ', $data);

    if (!$isearch_config['allow_dashes'])
    {
        /* Replace dashes with spaces */
        $data = str_replace('-', ' ', $data);
    }

    $extraChars = '';
    if ($isearch_config['allow_colons'])
    {
        $extraChars = ':';
    }
    else
    {
        /* Replace dashes with spaces */
        $data = str_replace(':', ' ', $data);
    }

    if ($isearch_config['char_set_8_bit'])
    {
        /* Strip out all characters except whitespace numeric and alpha */
        $data = preg_replace('/([^-@0-9a-z' . $extraChars . chr(0xbf) . '-' . chr(0xff) . chr(0x9a) . chr(0x9e) . '\s])/', '', $data);
    }

    /* Convert from source charset to charset used on results page */
    if ((function_exists('iconv')) && ($charset != $isearch_config['char_set']) && ($charset != '') && ($isearch_config['char_set'] != ''))
    {
        isearch_log("INFO: Converting $charset -> " . $isearch_config['char_set'], 5);
        $data = iconv($charset, $isearch_config['char_set'], $data);
    }

    /* Compact all white space into a single space character */
    $data = preg_replace("/\\s+/", ' ', $data);

    /* Strip white space from beginning and end of the string */
    $data = trim($data);

    return $data;
}


function isearch_fread($handle, $length = 2147483647)
{
    $bytesToRead = $length;

    $contents = '';
    while((!feof($handle)) && ($bytesToRead > 0))
    {
        $data = fread($handle, ($bytesToRead > 16384) ? 16384 : $bytesToRead);
        $bytesToRead -= strlen($data);
        $contents .= $data;
    }
    if (!feof($handle))
    {
        isearch_log('WARNING: &nbsp;&nbsp;&nbsp;&nbsp;File reading was truncated at '.($length/1024).' kbytes', 3);
    }
    return $contents;
}


function isearch_relativeToAbsoluteUrl($newUrl, $relativeToUrl)
{
    /* Convert to absolute reference */
    if (preg_match('#^([a-z]+):#i', $newUrl))
    {
        $absoluteUrl = $newUrl;
    }
    else
    {
        $relativeParts = @parse_url($relativeToUrl);
        if ((!isset($relativeParts['scheme'])) || (!isset($relativeParts['host'])))
        {
            /* Unable to parse relativeToUrl */
            isearch_log("WARNING: &nbsp;&nbsp;&nbsp;&nbsp;Unable to parse relativeToUrl [$relativeToUrl]", 3);
            return;
        }
        unset($relativeParts['query']);
        unset($relativeParts['fragment']);

        if (preg_match('#^/#', $newUrl))
        {
            /* New URL begins with a slash. It is within the site */

            unset($relativeParts['path']);
            $siteUrl = glue_url($relativeParts);

            $absoluteUrl = $siteUrl . $newUrl;
        }
        else
        {
            /* A relative reference (must be within this site) */

            /* Remove filename following the last slash */
            if (isset($relativeParts['path']))
            {
                $path = preg_replace('#/[^/]*\.[^/]*$#', '/', $relativeParts['path']);
                if (preg_match('#/$#', $path))
                {
                    $path .= $newUrl;
                }
                else
                {
                    $path .= '/' . $newUrl;
                }
            }
            else
            {
                $path = '/' . $newUrl;
            }
            $path = preg_replace('#/\.$#', '', $path);           /* Remove ending "/." */
            $path = preg_replace('#/(\./)+#', '/', $path);       /* Remove any "." references */
            $path = preg_replace('#/+/#', '/', $path);           /* Remove excess slashes */

            /* Resolve any ".." references */
            $temp = explode('/', $path);
            for ($i = 1; $i < count($temp); $i++)
            {
                if (($temp[$i] == "..") && ($i > 1))
                {
                    for ($j = $i + 1; $j < count($temp); $j ++)
                    {
                        $temp[$j-2] = $temp[$j];
                    }
                    unset($temp[count($temp)-1]);
                    unset($temp[count($temp)-1]);
                    $i = $i - 2;
                }
            }
            $relativeParts['path'] = implode('/', $temp);
            $absoluteUrl = glue_url($relativeParts);
        }
    }

    return $absoluteUrl;
}


function isearch_readFile($url, $depth=0)
{
    global $isearch_config;
    global $isearch_version;
    global $isearch_header;
    global $isearch_base;
    global $isearch_url_fopen_detected, $isearch_curl_detected, $isearch_sockets_detected;

    $isearch_header = array();

    isearch_log("TRACE: isearch_readFile($url, $depth)", 10);

    if ($depth >= 10)
    {
        /* Inline frame depth of 10 */
        isearch_log("WARNING: Inline frame depth limit $depth exceeded", 3);
        return '';
    }

    if ($isearch_config['url_search'] != '')
    {
        $url = preg_replace("#".$isearch_config['url_search']."#", $isearch_config['url_replace'], $url);
        isearch_log("INFO: Using replaced URL $url", 5);
    }


    if ($isearch_config['reading_mechanism'] == 0)
    {
        /* Autodetect */
        if (($isearch_url_fopen_detected) && (!$isearch_config['proxy_enable']))
        {
            $reading_mechanism = 1;   /* fopen */
        }
        else if (($isearch_sockets_detected) && (preg_match('#^http://#i', $url)))
        {
            $reading_mechanism = 2;   /* sockets */
        }
        else if ($isearch_curl_detected)
        {
            $reading_mechanism = 3;   /* curl */
        }
        else
        {
            isearch_log('ERROR: Unable to detect a suitable reading mechanism.', 1);
            return '';
        }
    }
    else
    {
        $reading_mechanism = $isearch_config['reading_mechanism'];
    }

    if ($reading_mechanism == 1)
    {
        /* Use fopen/fread */
        $docData = '';
        @ini_set('user_agent', "iSearch/$isearch_version");
        $base = $url;
        if ($isearch_config['basic_authorization'] != '')
        {
            $url = str_replace('//', '//'.$isearch_config['basic_authorization'].'@');
        }
        $fp = @fopen($url, 'r');
		
        if ($fp)
        {
            if (function_exists('stream_get_meta_data'))
            {
                /* Prior to PHP 4.3.0 use $http_response_header instead of stream_get_meta_data() */
                $meta_data = stream_get_meta_data($fp);
                $header_data = $meta_data['wrapper_data'];
            }
            else
            {
                $header_data = $http_response_header;
            }

            $header = array();
            foreach($header_data as $headerLine)
            {
                $data = explode(': ', $headerLine, 2);
                if (count($data) == 2)
                {
                    $header[strtolower($data[0])] = $data[1];
                }
            }

            if (isset($header['content-location']))
            {
                $base = $header['content-location'];
            }
            else if (isset($header['location']))
            {
                $base = $header['location'];
            }

            $docData = isearch_fread($fp, $isearch_config['max_file_size']);
            fclose($fp);
        }
        else
        {
            isearch_log("WARNING: Unable to fopen URL [$url]", preg_match('#/robots\.txt$#i', $url) ? 9 : 3);
            return '';
        }
    }
    else
    {
        $recurse = 10;
        while (1)
        {
            /* Check URL and determine whether this is a file or directory */
            $urlParts = @parse_url($url);
            if ((!isset($urlParts['scheme'])) || (!isset($urlParts['host'])))
            {
                isearch_log("WARNING: Unable to parse URL [$url]", 3);
                return '';
            }

            if (!preg_match('#^(https?|ftps?)$#i', $urlParts['scheme']))
            {
                isearch_log("WARNING: Unsupported URL scheme " . $urlParts['scheme'] . " [$url]", 4);
                return '';
            }

            if ($reading_mechanism == 2)
            {
                isearch_log("INFO: Reading $url using sockets", 5);

                if ($urlParts['scheme'] != 'http')
                {
                    isearch_log("WARNING: URL scheme " . $urlParts['scheme'] . " not supported by sockets. Use CURL library. [$url]", 3);
                    return '';
                }

                if ($isearch_config['proxy_enable'])
                {
                    $host = $isearch_config['proxy_host'];
                    $port = $isearch_config['proxy_port'];
                }
                else
                {
                    $host = $urlParts['host'];
                    $port = isset($urlParts['port']) ? $urlParts['port'] : 80;
                }

                $sock = fsockopen($host, $port, $errno, $errstr);
                if (!$sock)
                {
                    isearch_log("ERROR: Unable to open socket to " . $host . " " . $port . " - $errno : $errstr", 1);
                    return '';
                }

                $request = "GET $url HTTP/1.0\r\n";
                $request .= "Host: $host\r\n";
                if (($isearch_config['proxy_enable']) && ($isearch_config['proxy_user'] != ''))
                {
                    $request .= "Proxy-Authorization: Basic " . base64_encode ($isearch_config['proxy_user'].':'.$isearch_config['proxy_pass']) . "\r\n";
                }

                $request .= "User-Agent: iSearch/$isearch_version\r\n";
                if ($isearch_config['basic_authorization'] != '')
                {
                    $request .= "Authorization: Basic " . base64_encode($isearch_config['basic_authorization']) . "\r\n";
                }
                $request .= "Connection: Close\r\n\r\n";

                fputs($sock, $request);

                $allData = isearch_fread($sock, $isearch_config['max_file_size']);
                fclose($sock);
            }
            else
            {
                /* Use the CURL library */
                isearch_log("INFO: Reading $url using CURL", 5);
				
                $ch = curl_init($url);

//                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, "iSearch/$isearch_version");
                curl_setopt($ch, CURLOPT_HEADER, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_HEADER, TRUE);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                curl_setopt($ch, CURLOPT_COOKIEJAR,  "my_cookies.txt");     // Initiates cookie file if needed
                curl_setopt($ch, CURLOPT_COOKIEFILE, "my_cookies.txt");     // Uses cookies from previous session if exist

                if ($isearch_config['proxy_enable'])
                {
                    if ($isearch_config['proxy_user'] != '')
                    {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $isearch_config['proxy_user'].':'.$isearch_config['proxy_pass']);
                    }
                    curl_setopt($ch, CURLOPT_PROXY, $isearch_config['proxy_host']);
                    curl_setopt($ch, CURLOPT_PROXYPORT, $isearch_config['proxy_port']);
                }
                if ($isearch_config['basic_authorization'] != '')
                {
                    curl_setopt($ch, CURLOPT_USERPWD, $isearch_config['basic_authorization']);
                }

                $allData = curl_exec($ch);

                curl_close($ch);
            }

            @list($headerData, $docData) = explode("\r\n\r\n", $allData, 2);
            $headerLines = explode("\r\n", $headerData);
            $status = $headerLines[0];
            isearch_log("INFO: Status Line $status", 8);

            $header = array();
            for ($i = count($headerLines)-1; $i > 0; $i--)
            {
                $data = explode(': ', $headerLines[$i], 2);
                if (count($data) == 2)
                {
                    $header[strtolower($data[0])] = $data[1];
                }
            }

            /* Check status code:
             * "200"   ; OK
             * "201"   ; Created
             * "202"   ; Accepted
             * "204"   ; No Content
             * "301"   ; Moved Permanently
             * "302"   ; Moved Temporarily
             * "304"   ; Not Modified
             * "400"   ; Bad Request
             * "401"   ; Unauthorized
             * "403"   ; Forbidden
             * "404"   ; Not Found
             * "500"   ; Internal Server Error
             * "501"   ; Not Implemented
             * "502"   ; Bad Gateway
             * "503"   ; Service Unavailable
             */
            $temp = explode(' ', $status, 3);
            if (count($temp) < 2)
            {
                isearch_log('ERROR: Unable to read status code', 1);
                return '';
            }

            $statusCode = $temp[1];

            if (($statusCode == '301') || ($statusCode == '302'))
            {
                /* Redirection. Get new location */
                if ($recurse <= 1)
                {
                    /* Recursion limit reached */
                    isearch_log('ERROR: URL recursion limit 10 exceeded', 1);
                    return '';
                }

                $url = $header['location'];
                $recurse = $recurse - 1;
            }
            else if ($statusCode >= 300)
            {
                isearch_log("WARNING: HTTP Error : $status [$url]", preg_match('#/robots\.txt$#i', $url) ? 9 : 3);
                return '';
            }
            else
            {
                /* We have read the file */
                break;
            }
        }

        if (isset($header['content-location']))
        {
            $base = $header['content-location'];
        }
        else
        {
            $base = $url;
        }
    }

    if (preg_match("#<BASE\\s+[^>]*?HREF\\s*=\\s*['\"]?([^>]+?)['\"]?[\\s>]#i", $docData, $matches) == 1)
    {
        /* Found a "<BASE href=" tag in the document head */
        $base = $matches[1];
    }

    /* Search for inline frames and replace them with frame contents */
    $regexp = "#<IFRAME[^>]*?\\sSRC\\s*=\\s*['\"]?(.*?)[\\s'\"][^>]*>#i";
    $matchCount = preg_match_all($regexp, $docData, $matches);
    for ($i = 0; $i < $matchCount; $i++)
    {
        $frameUrl = isearch_relativeToAbsoluteUrl($matches[1][$i], $base);
        isearch_log("INFO: Reading inline frame : $frameUrl", 5);
        $frameData = isearch_readFile($frameUrl, $depth+1);
        $docData = preg_replace($regexp, $frameData, $docData, 1);
    }

    if ($isearch_config['javascript_link_search'] == 2)
    {
        /* Search for external JavaScript files and replace them with file contents */
        $regexp = "#<script[^>]*?\\sSRC\\s*=\\s*['\"](.*?)['\"][^>]*>#i";
        $matchCount = preg_match_all($regexp, $docData, $matches);
        for ($i = 0; $i < $matchCount; $i++)
        {
            $jsUrl = isearch_relativeToAbsoluteUrl($matches[1][$i], $base);
            isearch_log("INFO: Reading javascript : $jsUrl", 5);
            $jsData = isearch_readFile($jsUrl, $depth+1);
            $docData = preg_replace($regexp, "<script>\n<!--\n".$jsData."\n-->\n</script>\n", $docData, 1);
        }
    }

    $isearch_header = $header;
    $isearch_base = $base;

    return $docData;
}


function isearch_execConvert($data, $type)
{
    global $isearch_config;

    // Create a temporary filename to use for the conversion
    $tmpfname = tempnam($isearch_config['tmpdir'], "iSearch");

    // Write data to the temp file
    $fh = fopen($tmpfname, 'wb');
    if (!$fh)
    {
        isearch_log("ERROR: Unable to open tmp file $tmpfname", 1);
        return '';
    }
    fwrite($fh, $data);
    fclose($fh);

    // Execute the command
    $cmd = $isearch_config[$type . '_exec'];
    if ($cmd == '')
    {
        isearch_log("ERROR: Configuration error - executable for $type not set", 1);
        return '';
    }

    exec("$cmd $tmpfname", $dummy, $retval);

    isearch_log("INFO: Executed command $cmd $tmpfname, Return Code $retval", 5);

    // Read the stderr and stdout files
    $err = '';
    $fileData = @file($tmpfname . '.err');
    if ($fileData)
    {
        $err = @implode("\n", $fileData);
    }
    $text = @implode("\n", @file($tmpfname . '.txt'));

    // Delete the temporary files
    unlink($tmpfname);
    @unlink($tmpfname . '.err');
    unlink($tmpfname . '.txt');

    if ($err != '')
    {
        isearch_log("ERROR: Executed command $cmd $tmpfname, Error Msg: $err", 1);
        return '';
    }

    // Check the first word of the text file
    $firstWord = sscanf($text, ' %s ', $firstWord);
    if (!preg_match('#<html>#i', $firstWord))
    {
        // Wrap text in HTML
        $text = "<html><head><title>Document</title></head><body><PRE>$text</PRE></body></html>";
    }
    return $text;
}


function isearch_onlineConvert($data, $type)
{
    global $isearch_config;
    global $isearch_version;

    $host='convert.iSearchTheNet.com';
    $port=80;
    $path='/';
    $query="?type=$type&gzip=0";

    $sock = fsockopen($host, $port, $errno, $errstr);
    if (!$sock)
    {
        isearch_log("ERROR: Unable to open socket $host $port - $errno : $errstr", 1);
        return '';
    }

    $boundary = '---------------------------' . md5('boundary');

    $postValues  = "--$boundary\r\n";
    $postValues .= "Content-Disposition: form-data; name=\"file.1\"; filename=\"file.1\"\r\n";
    $postValues .= "Content-Type: application/$type\r\n";
    $postValues .= "\r\n";
    $postValues .= "$data\r\n";
    $postValues .= "--$boundary--\r\n\r\n";

    $request  = "POST http://$host$path$query HTTP/1.0\r\n";
//    $request .= "User-Agent: iSearch/$isearch_version\r\n";
//    $request .= "Host: $host\r\n";
//    $request .= "Authorization: ISEARCH " . $isearch_config['online_id'] . "\r\n";
    $request .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";
    $request .= "Content-Length: " . strlen( $postValues ) . "\r\n";
//    $request .= "Connection: Close\r\n";
    $request .= "\r\n";

    fputs($sock, $request.$postValues);

    /* Read status line */
    $status = fgets($sock, 1024);

    while (!feof($sock))
    {
        $line = trim(fgets($sock, 1024));
        if ($line == '')
        {
            break;
        }
    }
    $convertedData = isearch_fread($sock);
    fclose($sock);

    $temp = explode(' ', $status, 3);
    if ($temp[1] != '200')
    {
        isearch_log("ERROR: Online conversion error: $status", 1);
        return $data;
    }

    if ((strlen($convertedData) > 10) && ($convertedData[0] == 0x1f) && ($convertedData[1] == 0x8b))
    {
        $convertedData = gzinflate(substr($convertedData,10));
    }

    return $convertedData;
}

if (!function_exists('html_entity_decode'))
{
    // This was new in PHP 4.3.0
    function html_entity_decode($str, $quoteStyle, $charset)
    {
        global $isearch_htmlToAsciiTrans;
        if (!isset($isearch_htmlToAsciiTrans))
        {
            /* Translate from HTML to ASCII */
            $isearch_htmlToAsciiTrans = array_flip(get_html_translation_table(HTML_ENTITIES));
        }
        return strtr($str, $isearch_htmlToAsciiTrans);
    }
}

function isearch_html_to_ascii($str)
{
    global $isearch_config;

    $str = @html_entity_decode($str, ENT_QUOTES, $isearch_config['char_set']);
    //$str = preg_replace('/&#x([a-f0-9]+);?/mei', "chr(0x\\1)", preg_replace('/&#(\d+);?/me', "chr('\\1')", $str));

	$str = preg_replace_callback(
	'/&#(\d+);?/m', 
	 function($matches) { 
	 	return chr($matches[1]); },
	 $str);
	
	$str = preg_replace_callback('/&#x([a-f0-9]+);?/mi',
	 function($matches) { 
	 	return chr("0x".$matches[1]); },
	 $str);
	
    return $str;
}

/* Parse HTTP date format - one of the following:
 *      Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
 *      Sunday, 06-Nov-94 08:49:37 GMT ; RFC 850, obsoleted by RFC 1036
 *      Sun Nov  6 08:49:37 1994       ; ANSI C's asctime() format
 */
function isearch_parseHttpDate($httpDate)
{
    $months = array('jan'=>1, 'feb'=>2, 'mar'=>3, 'apr'=>4, 'may'=>5, 'jun'=>6, 'jul'=>7, 'aug'=>8, 'sep'=>9, 'oct'=>10, 'nov'=>11, 'dec'=>12);
    $time = 0;
    if (preg_match("#^[a-z]+,? +([0-9]{1,2})[ -]+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[ -]+([0-9]{2,4}) +([0-9]{2}):([0-9]{2}):([0-9]{2})#i", $httpDate, $matchName) == 1)
    {
        $day = $matchName[1];
        $monthName = strtolower($matchName[2]);
        $year = $matchName[3];
        $hour = $matchName[4];
        $min = $matchName[5];
        $sec = $matchName[6];
    }
    else if (preg_match("#^[a-z]+,?[ -]+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) +([0-9]{2}):([0-9]{2}):([0-9]{2}) +([0-9]{2,4})#i", $httpDate, $matchName) == 1)
    {
        $day = $matchName[2];
        $monthName = strtolower($matchName[1]);
        $year = $matchName[6];
        $hour = $matchName[3];
        $min = $matchName[4];
        $sec = $matchName[5];
    }
    else
    {
        isearch_log('WARNING: Unknown date format : ' . $httpDate, 7);
    }

    if (isset($day))
    {
        if ($year < 70)
        {
            $year += 2000;
        }
        else if ($year < 100)
        {
            $year += 1900;
        }

        $time = gmmktime($hour, $min, $sec, $months[$monthName], $day, $year);
    }

    return $time;
}


/* Spider a single file. Returns true if there are more files to spider, else false */
function isearch_indexAFile($verbose = True)
{
    global $isearch_table_info, $isearch_table_urls, $isearch_table_urls_new, $isearch_table_words, $isearch_table_words_new;
    global $isearch_db, $regionID;
    global $isearch_config;
    global $isearch_header;
    global $isearch_base;

    if (! $verbose)
    {
        /* Disable display of messages. */
        $isearch_config['log_echo_level'] = 0;
    }

    $resultUrls = mysql_query("SELECT * FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID)." AND state='new' LIMIT 1", $isearch_db);
    if (!$resultUrls)
    {
        /* MySQL error. Sleep and try again */
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        sleep(5);
        return True;
    }

    $newState = 'error';

    if (mysql_num_rows($resultUrls) != 1)
    {
        isearch_log('INFO: Indexing completed.', 2);

        /* Indexing has completed */
        $now = time();
        if (!mysql_query("UPDATE $isearch_table_info SET last_update='$now' WHERE id=".intval($regionID), $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        $result = mysql_query("SELECT url, state FROM $isearch_table_urls_new WHERE regionID = ".intval($regionID)." AND  state!='ok'", $isearch_db);
        if (!$result)
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
        else if (mysql_num_rows($result) > 0)
        {
            isearch_log('INFO: Deleting the following URLs:', 4);
            while ($item = mysql_fetch_object($result))
            {
                isearch_log('INFO: &nbsp;&nbsp;' . $item->url . ' (' . $item->state . ')', 4);
            }
        }

        /* Delete any unfound references */
        if (!mysql_query("DELETE FROM $isearch_table_urls_new WHERE regionID = ".intval($regionID)." AND  state!='ok'", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        /* Update referrer_id fields */
        $result = mysql_query("SELECT id, temp_referrer_id FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID)."", $isearch_db);
        if (!$result)
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
        else if (mysql_num_rows($result) > 0)
        {
            mysql_query("LOCK TABLES $isearch_table_urls_new WRITE", $isearch_db);
            while ($item = mysql_fetch_object($result))
            {
                if (!mysql_query("UPDATE $isearch_table_urls_new SET referrer_id='$item->temp_referrer_id', regionID = ".intval($regionID)." WHERE id='$item->id'", $isearch_db))
                {
                    isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                }
            }
            mysql_query("UNLOCK TABLES", $isearch_db);
        }

        if ($isearch_config['test_mode'] == 0)
        {
            /* Swap the old and new tables */
            $backup_words = $isearch_table_words . '_tmp_backup';
            $backup_urls = $isearch_table_urls . '_tmp_backup';
            if (!mysql_query("RENAME TABLE $isearch_table_words TO $backup_words, " .
                                          "$isearch_table_words_new TO $isearch_table_words, " .
                                          "$backup_words TO $isearch_table_words_new, " .
                                          "$isearch_table_urls TO $backup_urls, " .
                                          "$isearch_table_urls_new TO $isearch_table_urls, " .
                                          "$backup_urls TO $isearch_table_urls_new", $isearch_db))
            {
                isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
        }

        /* Empty the new words table */
        if (!mysql_query("DELETE FROM $isearch_table_words_new WHERE  regionID = ".intval($regionID)."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        /* Empty the new urls table */
        if (!mysql_query("DELETE FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID)."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        /* Optimize the tables */
        if (!mysql_query("OPTIMIZE TABLE $isearch_table_urls, $isearch_table_words", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        return False;
    }
    else
    {

        $itemUrl = mysql_fetch_object($resultUrls);

        $allData = isearch_readFile($itemUrl->url);

        if ($allData == '')
        {
            isearch_log("ERROR: Unable to open URL [$itemUrl->url]", 1);
            $newState = 'notfound';
        }
        else
        {
            $size = strlen($allData);
            $sig = md5($allData);

            /* Look for a duplicate page */
            $resultSig = mysql_query("SELECT * FROM $isearch_table_urls_new WHERE regionID = ".intval($regionID)." AND  sig='$sig' AND size='$size' AND NOT url='" . mysql_escape_string2($itemUrl->url) . "'", $isearch_db);
            if (!$resultSig)
            {
                isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
            else if (mysql_num_rows($resultSig) > 0)
            {
                isearch_log("INFO: Duplicate URL - will not be processed [$itemUrl->url]", 4);
                $newState = 'duplicate';
            }
            else
            {
                /* No duplicates found. */

                isearch_log("INFO: Processing URL [$itemUrl->url]", 2);


                if (preg_match('%\.(doc|pdf)(\?.*|#.*)?$%i', $itemUrl->url, $matches))
                {
                    if (strtolower($matches[1]) == 'doc')
                    {
                        switch ($isearch_config['msword_support'])
                        {
                            case 0:
                                /* Do nothing */
                                isearch_log("WARNING: Word support disabled", 3);
                                break;
                            case 1:
                                $allData = isearch_execConvert($allData, 'msword');
                                break;
                            case 2:
                                $allData = isearch_onlineConvert($allData, 'msword');
                                break;
                            default:
                                isearch_log("ERROR: Illegal Word document support setting", 1);
                                break;
                        }
                    }
                    else
                    {
                        switch ($isearch_config['pdf_support'])
                        {
                            case 0:
                                /* Do nothing */
                                isearch_log("WARNING: PDF support disabled", 3);
                                break;
                            case 1:
                                $allData = isearch_execConvert($allData, 'pdf');
                                break;
                            case 2:
                                $allData = isearch_onlineConvert($allData, 'pdf');
                                break;
                            default:
                                isearch_log("ERROR: Illegal PDF support setting", 1);
                                break;
                        }
                    }
                }


                if (($isearch_config['keep_cache']) && ($isearch_config['test_mode'] == 0))
                {
                    $cache = $allData;
                }
                else
                {
                    $cache = '';
                }

                /* Strip out all control characters and replace with spaces.
                 * Compact all white space into a single space character.
                 */
                $allData = preg_replace("/\\s+/", ' ', $allData);

                $tdata = preg_split('#</head[^>]*>#i', $allData, 2);
                if (count($tdata) < 2)
                {
                    $tdata = preg_split('#<body#', $allData, 2);
                    if (count($tdata) < 2)
                    {
                        isearch_log('WARNING: &lt;BODY&gt; and &lt;/HEAD&gt; tags not found', 4);
                        $headData = '';
                        $bodyData = $allData;
                    }
                    else
                    {
                        $headData = $tdata[0];
                        $bodyData = '<body' . $tdata[1];
                    }
                }
                else
                {
                    $headData = $tdata[0];
                    $bodyData = $tdata[1];
                }

                /* Strip out HTML comments from head data */
                $headData = preg_replace('/<!--.+?-->/','',$headData);
                $headData = preg_replace("/\\s+/", ' ', $headData);

                /* Strip out all HTML tags except ones we are interested in */
                /* Includes workaround for PHP bug. See http://bugs.php.net/bug.php?id=21311 */
                $headData = strip_tags(preg_replace("/<\!DOCTYPE [^>]*>/i", '', $headData), '<meta><title><?php echo $site_name; ?> - <base>');

                $keyWords = '';
                $description = '';
                $title = '';
                $index = True;
                $follow = True;

                $matchCount = preg_match_all("#<META\\s+([^>]*?)\\s*>#i", $allData, $matches);
                for ($i = 0; $i < $matchCount; $i++)
                {
                    if (preg_match("#CONTENT\\s*=\\s*(['\"])(.*?)\\1#i", $matches[1][$i], $matchContent) == 1)
                    {
                        $metaContent = $matchContent[2];
                        if (preg_match("#NAME\\s*=\\s*['\"]?(.*?)['\"]?(\\s|$)#i", $matches[1][$i], $matchName) == 1)
                        {
                            // <meta name="keywords" content="keyword list">
                            // <meta name="description" content="description">
                            // <meta name="robots" content="nofollow,noindex,noarchive">

                            $metaName = strtolower($matchName[1]);
                            if ($metaName == 'keywords')
                            {
                                $keyWords = preg_replace('/,/', ' ', $metaContent);
                            }
                            else if ($metaName == 'description')
                            {
                                $description = $metaContent;
                            }
                            else if ($metaName == 'robots')
                            {
                                if (preg_match('#noindex#i', $metaContent))
                                {
                                    $index = False;
                                }
                                if (preg_match('#nofollow#i', $metaContent))
                                {
                                    $follow = False;
                                }
                                if (preg_match('#noarchive#i', $metaContent))
                                {
                                    $cache = '';
                                }
                            }
                        }
                        else if (preg_match("#HTTP-EQUIV\\s*=(['\"])(.*?)\\1#i", $matches[1][$i], $matchEquiv) == 1)
                        {
                            $isearch_header[strtolower($matchEquiv[1])] = $metaContent;
                        }
                    }
                }

                // Determine character set
                $charset = $isearch_config['char_set'];
                if ((isset($isearch_header['content-type'])) &&
                    (preg_match("#(^|\\s)\\s*CHARSET\\s*=\\s*['\"]?(.*?)['\"]?(\\s|$)#i", $isearch_header['content-type'], $matches) == 1))
                {
                    $charset = $matches[2];
                }

                $lastModified = 0;
                if (isset($isearch_header['last-modified']))
                {
                    $lastModified = isearch_parseHttpDate($isearch_header['last-modified']);
                }

                $changefreq = '';
                $priority = -1;

                if ($follow)
                {
                    $tdata1 = preg_split('#(<!-- ISEARCH_BEGIN_FOLLOW -->|</nofollow>)#i', ' ' . $bodyData);
                    if (count($tdata1) > 1)
                    {
                        /* At least 1 found. */

                        $followData = '';

                        /* Check for an END_INDEX before the first BEGIN_INDEX */
                        $tdata2 = preg_split('#(<!-- ISEARCH_END_FOLLOW -->|<nofollow>)#i', $tdata1[0]);
                        if (count($tdata2) > 1)
                        {
                            /* And END_INDEX was found. Add anything before it into the follow data */
                            $followData .= $tdata2[0];
                        }

                        for ($i = 1; $i < count($tdata1); $i++)
                        {
                            $tdata2 = preg_split('#(<!-- ISEARCH_END_FOLLOW -->|<nofollow>)#i', $tdata1[$i]);
                            $followData .= $tdata2[0];
                        }
                    }
                    else
                    {
                        $followData = $bodyData;
                    }

                    $urls = array();

                    /* Do agressive link searching */
                    if ($isearch_config['aggressive_link_search'])
                    {
                        $matchCount = preg_match_all("~(https?|ftps?)://[^'\"\\s>]*~i", $allData, $matches);
                        for ($i = 0; $i < $matchCount; $i++)
                        {
                            $urls[] = $matches[0][$i];
                        }

                        /* Find any links */
                        $matchCount = preg_match_all("~<(A|AREA)\\s+([^>]*?\\s+)*?HREF\\s*=\\s*'\\s*([^>]+?)\\s*'~i", $followData, $matches);
                        for ($i = 0; $i < $matchCount; $i++)
                        {
                            $urls[] = $matches[3][$i];
                        }
                    }

                    /* Do JavaScript link searching */
                    if ($isearch_config['javascript_link_search'])
                    {
                        /* Search for window.open() calls */
                        $matchCount = preg_match_all("~window.open\\s*\\(\\s*'(.+?)',~i", $allData, $matches);
                        for ($i = 0; $i < $matchCount; $i++)
                        {
                            if ($matches[1][$i] != '')
                            {
                                $urls[] = $matches[1][$i];
                            }
                        }
                        $matchCount = preg_match_all("~window.open\\s*\\(\\s*\"(.+?)\",~i", $allData, $matches);
                        for ($i = 0; $i < $matchCount; $i++)
                        {
                            if ($matches[1][$i] != '')
                            {
                                $urls[] = $matches[1][$i];
                            }
                        }
                    }

                    /* Remove JavaScript and comments */
                    $followData = preg_replace('#(<script[^>]*?>.*?</script>)|(<!--.*?-->)#i', '', $followData);

                    /* Find any links */
                    $matchCount = preg_match_all("~<(A|AREA)\\s+([^>]*?\\s+)*?HREF\\s*=\\s*\"\\s*([^>]+?)\\s*\"~i", $followData, $matches);
                    for ($i = 0; $i < $matchCount; $i++)
                    {
                        $urls[] = $matches[3][$i];
                    }

                    if ($isearch_config['follow_frames'])
                    {
                        /* Search for frames, and add the referenced docs to the URL list */
                        $matchCount = preg_match_all("~<FRAME\\s+(.*?)>~i", $allData, $matches);
                        for ($i = 0; $i < $matchCount; $i++)
                        {
                            $matchCount2 = preg_match_all("~SRC\\s*=\\s*\"\\s*([^>]+?)\\s*\"~i", $matches[1][$i], $matches2);
                            for ($j = 0; $j < $matchCount2; $j++)
                            {
                                $urls[] = $matches2[1][$j];
                            }
                        }
                    }

                    $lastUrl = '';
                    sort($urls);
                    foreach ($urls as $url)
                    {
                        $decodedUrl = preg_replace('/#.*$/', '', isearch_html_to_ascii($url));
                        if (($decodedUrl != $lastUrl) && (!preg_match('#^ *javascript:#i', $decodedUrl)))
                        {
                            isearch_addUrl(isearch_relativeToAbsoluteUrl($decodedUrl, $isearch_base), $itemUrl->id);
                            $lastUrl = $decodedUrl;
                        }
                    }
                }

                if ($isearch_config['test_mode'] == 1)
                {
                    $newState = 'ok';
                }
                else if ($index)
                {
                    /* Find a "<title></title>" tag in the document head */
                    if (preg_match("#<title>\\s*(.*?)\\s*</title>#i", $headData, $matches) == 1)
                    {
                        $title = $matches[1];
                    }
                    else
                    {
                        $title = $itemUrl->url;
                    }

                    /* Replace breaking tags and other special chars with spaces */
                    $bodyData = preg_replace('/(<(hr|br|p|td|th)(>| [^>]*>))|(&nbsp;?)/i', ' ', $bodyData);

                    $tdata1 = preg_split('#(<!-- ISEARCH_BEGIN_INDEX -->|</noindex>)#i', ' ' . $bodyData);
                    if (count($tdata1) > 1)
                    {
                        /* At least 1 found. */

                        $bodyData = '';

                        /* Check for an END_INDEX before the first BEGIN_INDEX */
                        $tdata2 = preg_split('#(<!-- ISEARCH_END_INDEX -->|<noindex>)#i', $tdata1[0]);
                        if (count($tdata2) > 1)
                        {
                            /* And END_INDEX was found. Add anything before it into the bodyData */
                            $bodyData .= $tdata2[0];
                        }

                        for ($i = 1; $i < count($tdata1); $i++)
                        {
                            $tdata2 = preg_split('#(<!-- ISEARCH_END_INDEX -->|<noindex>)#i', $tdata1[$i]);
                            $bodyData .= $tdata2[0];
                        }
                    }

                    /* Strip out JavaScript and HTML comments
                     */
                    $bodyData = preg_replace('#(<script[^>]*?>.*?</script>)|(<!--.*?-->)#i', '', $bodyData);

                    /* Strip out all HTML tags except special ones */
                    $bodyData = strip_tags($bodyData, '<h1><h2><h3><h4><h5><img>');

                    if (! $isearch_config['ignore_image_alt_tags'])
                    {
                        /* Replace images with their alt text */
                        $bodyData = preg_replace('#<IMG\\s[^>]*?ALT\\s*=\\s*("|\')(.*?)\\1.*?>#i', ' \\2 ', $bodyData);
                    }

                    $bigWords = '';

                    $matchCount = preg_match_all("~<H[1-5]>\\s*(.*?)\\s*</H[1-5]>>~i", $bodyData, $matches);
                    for ($i = 0; $i < $matchCount; $i++)
                    {
                        $bigWords .= ' ' . $matches[1][$i];
                    }
					

                    /* Translate from HTML to ASCII */
                    $bodyData = isearch_html_to_ascii($bodyData);
                    $bigWords = isearch_html_to_ascii($bigWords);
                    $keyWords = isearch_html_to_ascii($keyWords);
                    $titleWords = isearch_html_to_ascii($title);

                    /* Keep stripped copy of document body */
                    $strippedBody = preg_replace("/\\s+/", ' ', strip_tags($bodyData));

                    /* Strip out unwanted characters from the strings that we
                     * search on
                     */
                    $bodyData = isearch_cleanString($bodyData, $charset);
                    $bigWords = isearch_cleanString($bigWords, $charset);
                    $keyWords = isearch_cleanString($keyWords, $charset);
                    $titleWords = isearch_cleanString($titleWords, $charset);

                    $urlWords = preg_replace('#((https?://)|[+/\\\\\.-_]|(%20))#i', ' ', $itemUrl->url);
                    $urlWords = isearch_cleanString($urlWords, $charset);

                    $score = array();

                    for ($i = 0; $i < 5; $i++)
                    {
                        if ($i == 0)
                        {
                            $words = explode(' ', $bodyData);
                            $wordScore = $isearch_config['word_rank'];
                        }
                        else if ($i == 1)
                        {
                            $words = explode(' ', $bigWords);
                            $wordScore = $isearch_config['heading_rank'];
                        }
                        else if ($i == 2)
                        {
                            $words = explode(' ', $keyWords);
                            $wordScore = $isearch_config['keyword_rank'];
                        }
                        else if ($i == 3)
                        {
                            $words = explode(' ', $titleWords);
                            $wordScore = $isearch_config['title_rank'];
                        }
                        else if ($i == 4)
                        {
                            $words = explode(' ', $urlWords);
                            $wordScore = $isearch_config['url_rank'];
                        }

                        if ($wordScore == 0)
                        {
                            // Skip if wordScore is zero
                            continue;
                        }

                        foreach ($words as $word)
                        {
                            if (($word != '') && (!in_array($word, $isearch_config['stop_words'])) && (strlen($word) > $isearch_config['stop_words_length']))
                            {
                                if (isset($score[$word]))
                                {
                                    $score[$word] += $wordScore;
                                }
                                else
                                {
                                    $score[$word] = $wordScore;
                                }
                            }
                        }
                    }

                    $query = "INSERT INTO $isearch_table_words_new (word, id, score, regionID) VALUES ";
                    $needComma = False;
					/******************************/
					
                    foreach (array_keys($score) as $word)
                    {
                        if ($needComma)
                        {
                            $query .= ',';
                        }
                        $needComma = True;
                        $query .= "('$word', '".$itemUrl->id."', '".$score[$word]."',".intval($regionID).")";
                    }

                    if (!mysql_query($query, $isearch_db))
                    {
                        isearch_log('ERROR: MySQL error : '. $query. mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                    }


                    /* Update this URL's details in the database */
                    if (!mysql_query("UPDATE $isearch_table_urls_new SET " .
                                     "title='" . mysql_escape_string2($title) . "', " .
                                     "description='" . mysql_escape_string2($description) . "', " .
                                     "cache='" . mysql_escape_string2($cache) . "', " .
                                     "stripped_body='" . mysql_escape_string2($strippedBody) . "', " .
                                     "words=' " . mysql_escape_string2($bodyData) . " ', " .
                                     "size='$size', " .
                                     "base='" . mysql_escape_string2($isearch_base) . "', " .
                                     "priority='$priority', " .
                                     "changefreq='" . mysql_escape_string2($changefreq) . "', " .
                                     "lastmod='$lastModified', " .
                                     "sig='" . mysql_escape_string2($sig) . "', " .
									 "regionID=".intval($regionID)." " .
                                     "WHERE id='" . $itemUrl->id . "'", $isearch_db))
                    {
                        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                    }
                    $newState = 'ok';
                }
                else    /* if ($index) */
                {
                     $newState = 'exclude';
                }
            }
        }
    }

    if (!mysql_query("UPDATE $isearch_table_urls_new SET state='$newState' WHERE id='" . $itemUrl->id . "'", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }

    return True;
}


/* Glue the fragments from a parse_url together. */
function glue_url($parsed)
{
    if (! is_array($parsed))
    {
        return false;
    }

    if (isset($parsed['scheme']))
    {
        $sep = (strtolower($parsed['scheme']) == 'mailto' ? ':' : '://');
        $uri = $parsed['scheme'] . $sep;
    }
    else
    {
        $uri = '';
    }

    if (isset($parsed['pass']))
    {
        $uri .= "$parsed[user]:$parsed[pass]@";
    }
    elseif (isset($parsed['user']))
    {
        $uri .= "$parsed[user]@";
    }

    if (isset($parsed['host']))
    {
        $uri .= $parsed['host'];
    }
    if (isset($parsed['port']))
    {
        $uri .= ":$parsed[port]";
    }
    if (isset($parsed['path']))
    {
        $uri .= $parsed['path'];
    }
    if (isset($parsed['query']))
    {
        $uri .= "?$parsed[query]";
    }
    if (isset($parsed['fragment']))
    {
        $uri .= "#$parsed[fragment]";
    }

    return $uri;
}


/* Add a URL to the search index */
function isearch_addUrl($absoluteUrl, $referrer_id)
{
    global $isearch_table_urls_new;
    global $isearch_db, $regionID;

    global $isearch_config;

    isearch_log("INFO: &nbsp;&nbsp;Checking URL [$absoluteUrl]", 7);

    /* Split the absolute URL into component parts */
    $absoluteParts = @parse_url($absoluteUrl);

    if ((!isset($absoluteParts['scheme'])) || (!isset($absoluteParts['host'])))
    {
        /* Unable to parse URL */
        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Unable to parse absoluteUrl [$absoluteUrl]", 4);
        return;
    }

    $absoluteParts['scheme'] = strtolower($absoluteParts['scheme']);
    if (!preg_match('#^(https?|ftps?)$#i', $absoluteParts['scheme']))
    {
        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting unsupported URL format [$absoluteUrl]", 6);
        return;
    }

    /* Remove any anchor reference in the URL */
    unset($absoluteParts['fragment']);

    /* Split the GET variables and remove unnecessary ones */
    if ((count($isearch_config['remove_get_vars']) == 1) && ($isearch_config['remove_get_vars'][0] == '*'))
    {
        /* Strip all GET variables */
        unset($absoluteParts['query']);
    }
    else if (isset($absoluteParts['query']))
    {
        $temp = explode('&', $absoluteParts['query']);

        foreach (array_keys($temp) as $key)
        {
            $varName = preg_replace('/=.*$/i', '', $temp[$key]);
            if (in_array($varName, $isearch_config['remove_get_vars']))
            {
                unset($temp[$key]);
            }
        }

        $absoluteParts['query'] = implode('&', $temp);

        if ($absoluteParts['query'] == '')
        {
            unset($absoluteParts['query']);
        }
    }

    /* Handle the www subdomain according to the value of $isearch_config['www_option'].
     *  1 = Leave as is
     *  2 = Strip www subdomains
     *  3 = Add www subdomain
     */
    if ($isearch_config['www_option'] == 2)
    {
        $absoluteParts['host'] = preg_replace('/^www\./i', '', $absoluteParts['host']);
    }
    else if ($isearch_config['www_option'] == 3)
    {
        if (!preg_match('#^www\.#i', $absoluteParts['host']))
        {
            $absoluteParts['host'] = 'www.' . $absoluteParts['host'];
        }
    }

  //Replace space characters with "+" characters
    if (isset($absoluteParts['path']))
    {
        $absoluteParts['path'] = str_replace(' ', '+', $absoluteParts['path']);
    }

   // Glue URL parts together again
    $absoluteUrl = glue_url($absoluteParts);

    if (isset($absoluteParts['path']))
    {
        $fileName = preg_replace('#.*/#i', '', $absoluteParts['path']);
        $temp = explode('.', $fileName);
        if (count($temp) < 2)
        {
            $fileExtension = '';
        }
        else
        {
            $fileExtension = strtolower($temp[count($temp)-1]);
        }
    }
    else
    {
        $fileExtension = '';
        $fileName = '/';
    }

    isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Absolute URL [$absoluteUrl]", 6);

    $allowed = False;
    if ($fileExtension == '')
    {
        /* Treat no file extension as a directory. */
        if ($isearch_config['directory_handling'] != 0)
        {
            /* Directories are allowed */
            $allowed = True;

            if ($isearch_config['directory_handling'] == 2)
            {
                /* Add trailing slash to directories */
                $absoluteUrl = preg_replace('#([^/])$#', '\1/', $absoluteUrl);
            }
            else if ($isearch_config['directory_handling'] == 3)
            {
                /* Strip trailing slash from directories */
                $absoluteUrl = preg_replace('#/$#', '', $absoluteUrl);
            }
        }
    }
    else
    {
        /* Check whether this is a default file name, and strip it if so. */
        foreach ($isearch_config['strip_defaults'] as $item)
        {
            if (($item != '') && ($item == $fileName))
            {
                $absoluteUrl = str_replace("/$item", '/', $absoluteUrl);
                $allowed = True;
                break;
            }
        }

        /* Check whether this file extension is allowed. Always allow PDF files
         * when PDF is enabled and DOC files when Word is enabled.
         */
        if ((in_array($fileExtension, $isearch_config['allowed_ext'])) ||
            (($isearch_config['pdf_support'] != 0) && ($fileExtension == 'pdf')) ||
            (($isearch_config['msword_support'] != 0) && ($fileExtension == 'doc')))
        {
            $allowed = True;
        }
    }

    if (!$allowed)
    {
        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting URL - it is not in allowed file extensions [$absoluteUrl]", 5);
    }
    else
    {
        /* Check whether it's already in the table of known URLs */
        $result = mysql_query("SELECT state, id FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID)." AND url='".mysql_escape_string2($absoluteUrl)."'", $isearch_db);
        if (!$result)
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
        else if (mysql_num_rows($result) > 0)
        {
            /* Already in database */
            isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;URL allowed, but already in database [$absoluteUrl]", 6);
            if ($item = mysql_fetch_object($result))
            {
                if (($item->state == 'new') && ($item->id != $referrer_id))
                {
                    if (!mysql_query("UPDATE $isearch_table_urls_new SET temp_referrer_id='$referrer_id' WHERE regionID = ".intval($regionID)." AND  url='".mysql_escape_string2($absoluteUrl)."'", $isearch_db))
                    {
                        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
                    }
                }
            }
            return;
        }

        $allowed = False;

        /* Check that this URL is in the allowed list */
        foreach (isearch_expandList($isearch_config['allowed_urls_beginning']) as $item)
        {
            if (($item != '') && ($item == substr($absoluteUrl, 0, strlen($item))))
            {
                $allowed = True;
                break;
            }
        }

        foreach (isearch_expandList($isearch_config['allowed_urls']) as $item)
        {
            if (($item != '') && (preg_match("#".$item."#i", $absoluteUrl)))
            {
                $allowed = True;
                break;
            }
        }

        if (!$allowed)
        {
            isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting URL - it  is not in allowed URL list [$absoluteUrl]", 5);
        }
        else
        {
            if (($absoluteParts['scheme'] == 'http') && (!in_array($absoluteParts['host'], $isearch_config['robots_domains'])))
            {
                /* Parse the robots.txt for this domain */
                $host = $absoluteParts['host'];
                if (isset($absoluteParts['port']))
                {
                    $host .= ':'.$absoluteParts['port'];
                }
                isearch_parseRobots($host);
            }

            /* Check that this URL is not in the disallowed list */
            foreach (isearch_expandList($isearch_config['exclude_urls_beginning']) as $item)
            {
                if (($item != '') && ($item == substr($absoluteUrl, 0, strlen($item))))
                {
                    $allowed = False;
                    isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting URL - it is in disallowed URL beginning list [$absoluteUrl]", 5);
                    break;
                }
            }

            if ($allowed)
            {
                foreach (isearch_expandList($isearch_config['exclude_urls']) as $item)
                {
                    if (($item != '') && (preg_match("#".$item."#i", $absoluteUrl)))
                    {
                        $allowed = False;
                        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting URL - it is in disallowed URL regexp list [$absoluteUrl]", 5);
                        break;
                    }
                }
            }

            if ($allowed)
            {
                /* Check the robots.txt excludes list */
                foreach ($isearch_config['robots_excludes'] as $item)
                {
                    if (($item != '') && (preg_match("#".$item."#i", $absoluteUrl)))
                    {
                        $allowed = False;
                        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;Rejecting URL - it is disallowed by robots.txt [$absoluteUrl]", 5);
                        break;
                    }
                }
            }
        }
    }

    if ($allowed)
    {
        /* Add it */
        if (!mysql_query("INSERT INTO $isearch_table_urls_new (url, temp_referrer_id, state, regionID) VALUES ('".mysql_escape_string2($absoluteUrl)."', '$referrer_id', 'new', ".intval($regionID).")", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
        isearch_log("INFO: &nbsp;&nbsp;&nbsp;&nbsp;URL allowed, and added [$absoluteUrl]", 4);
    }
}


/* Expand a list of URLs. If an entry begins with an "@" it is treated as a
 * filename containing more URLs.
 */
function isearch_expandList($list, $depth=0)
{
    $items = array();
    if ($depth > 20)
    {
        isearch_log('ERROR: Too much recursion in isearch_expandUrlList', 1);
        return $items;
    }

    foreach ($list as $item)
    {
        if ($item{0} == '@')
        {
            $lines = @file(substr($item, 1));
            if (is_array($lines))
            {
                $newList = array();
                foreach ($lines as $line)
                {
                    $newList[] = trim($line);
                }
                $items = array_merge($items, isearch_expandList($newList, $depth + 1));
            }
            else
            {
                isearch_log('WARNING: unable to read file : ' . substr($item, 1));
            }
        }
        else
        {
            $items[] = $item;
        }
    }
    return $items;
}


/* Reset the search index to allow site to be re-spidered */
function isearch_reset()
{
    global $isearch_table_urls_new, $isearch_table_info, $isearch_table_words_new;
    global $isearch_db, $regionID;

    global $isearch_config;

    /* Clear the spider log */
    isearch_clearLog();

    isearch_log('INFO: Starting spidering ' . date('dS F Y h:i:s A'), 3);

    /* Delete all entries from the new databases */
    if (!mysql_query("DELETE FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID)."", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }

    if (!mysql_query("DELETE FROM $isearch_table_words_new WHERE  regionID = ".intval($regionID)."", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }

    $urls = isearch_expandList($isearch_config['start_urls']);

    /* Add all start URLs to database */
    foreach ($urls as $url)
    {
        /* Find whether this URL refers to a directory */
        $urlParts = @parse_url($url);
        if ((!isset($urlParts['scheme'])) || (!isset($urlParts['host'])))
        {
            /* Unable to parse URL */
            isearch_log("ERROR: Unable to parse start URL [$url]", 1);
        }
        else
        {
            $filePath = isset($urlParts['path']) ? $urlParts['path'] : '';
            $fileName = preg_replace('#.*/#i', '', $filePath);
            $temp = explode('.', $fileName);
            if (count($temp) < 2)
            {
                $fileExtension = '';
            }
            else
            {
                $fileExtension = $temp[count($temp)-1];
            }

            if ($fileExtension == '')
            {
                /* Treat no file extension as a directory. Check whether there is a
                 * trailing slash on the URL and add it if necessary.
                 */
                if ($isearch_config['directory_handling'] == 2)
                {
                    /* Add trailing slash to directories */
                    $url = preg_replace('#([^/])$#', '\1/', $url);
                }
                else if ($isearch_config['directory_handling'] == 3)
                {
                    /* Strip trailing slash from directories */
                    $url = preg_replace('#/$#', '', $url);
                }
            }

            if (!mysql_query("INSERT INTO $isearch_table_urls_new (url, temp_referrer_id, state, regionID) VALUES ('".mysql_escape_string2($url)."', '-1', 'new', ".intval($regionID).")", $isearch_db))
            {
                isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
            }
            isearch_log("INFO: Added start URL [$url]", 5);
        }
    }

    /* Reset the robots.txt exclude list */
    $isearch_config['robots_domains'] = array();
    $isearch_config['robots_excludes'] = array();
    if (!mysql_query("UPDATE $isearch_table_info SET robots_domains='', robots_excludes='' WHERE id='".intval($regionID) ."'", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }

    /* Clear the last_update time to indicate that we are currently spidering */
    if (!mysql_query("UPDATE $isearch_table_info SET last_update='0' WHERE id='".intval($regionID) ."'", $isearch_db))
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }
}


/* Get the number of URLs that are in the database with the specified state (or
 * all if state is empty).
 */
function isearch_getUrlCount($new = False, $state = '')
{
    global $isearch_table_urls, $isearch_table_urls_new;
    global $isearch_db, $regionID;

    $count = 0;

    $query = 'SELECT COUNT(*) FROM ' . ($new ? $isearch_table_urls_new : $isearch_table_urls);

    if ($state != '')
    {
        $query .= " WHERE regionID = ".intval($regionID) ." AND state='$state'";
    }
    $result = mysql_query($query, $isearch_db);
    if (!$result)
    {
        isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
    }
    else
    {
        $count = mysql_result($result, 0, 0);
    }

    return $count;
}


/* Copy the current spidered tables to the search tables. This makes them
 * searchable.
 */
function isearch_copyUrlTables($toNew = False)
{
    global $isearch_table_urls, $isearch_table_urls_new;
    global $isearch_table_words, $isearch_table_words_new;
    global $isearch_db, $regionID;

    if ($toNew)
    {
        mysql_query("LOCK TABLES $isearch_table_urls_new WRITE, $isearch_table_words_new WRITE,  $isearch_table_urls READ, $isearch_table_words READ", $isearch_db);
        if (!mysql_query("DELETE FROM $isearch_table_urls_new WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("DELETE FROM $isearch_table_words_new WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("INSERT INTO $isearch_table_urls_new SELECT * FROM $isearch_table_urls WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("INSERT INTO $isearch_table_words_new SELECT * FROM $isearch_table_words WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
    }
    else
    {
        mysql_query("LOCK TABLES $isearch_table_urls WRITE, $isearch_table_words WRITE,  $isearch_table_urls_new READ, $isearch_table_words_new READ", $isearch_db);
        if (!mysql_query("DELETE FROM $isearch_table_urls WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("DELETE FROM $isearch_table_words WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("INSERT INTO $isearch_table_urls SELECT * FROM $isearch_table_urls_new WHERE  regionID = ".intval($regionID) ." AND state='ok'", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }

        if (!mysql_query("INSERT INTO $isearch_table_words SELECT * FROM $isearch_table_words_new WHERE regionID = ".intval($regionID) ."", $isearch_db))
        {
            isearch_log('ERROR: MySQL error : ' . mysql_error() . " in " . __FILE__ . " line " . __LINE__, 1);
        }
    }
    mysql_query("UNLOCK TABLES", $isearch_db);
}

$isearch_url_fopen_detected = (bool) ini_get('allow_url_fopen');
$isearch_curl_detected = (function_exists('curl_init')) ? True : False;
$isearch_sockets_detected = (function_exists('fsockopen')) ? True : False;

?>
