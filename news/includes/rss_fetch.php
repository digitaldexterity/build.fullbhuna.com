<?php // Copyright 2009 Paul Egan ?><?php 
chdir(dirname(__FILE__));
require_once('../../core/includes/framework.inc.php'); ?>
<?php


class rss_parser {
  	var $update_interval = 0;//360; // 6 hours
	
  /* How often to fetch the rss filein minutes
     A cached version will be used between updates    */

  	var $data_directory = UPLOAD_ROOT;
  /* Where to store the rss data from the feeds
     Note: an absolute path is better than a relative path here
     unless you plan on keeping the script to display the feeds
     in the same folder as this file and the feeds.   */



  /* NO NEED TO EDIT BELOW HERE UNLESS YOU KNOW WHAT YOU ARE DOING  */

 
  	var $rss_url;
	var $dateformat;
  	var $num_to_show;
  	var $offset;  //added in version 0.4.3
  	var $do_update;
  	var $tags = array();
  	var $content;
  	var $rss = array();
	var $rss_data;
  	var $feed_title;
  	var $feed_link;
  	var $feed_description;
	var $errors = "";
	var $useragent = "Full Bhuna RSS Reader";
	var $linebreak;
  	var $my_html;
	
  function rss_parser($url, $numtoshow = 10, $html = "", $update = FALSE, $offset = 1, $dateformat = "d/m/Y", $linebreak = true)

  	{
		$this->dateformat = $dateformat;
		$this->rss_url = $url;
		$this->num_to_show = $numtoshow;
		$this->do_update = $update;
		$this->my_html = preg_replace("/(#{.*?):(.*?})/", "\\1__\\2", $html); //xx:xx tag workaround
		$this->offset = --$offset;
		$this->linebreak = $linebreak;
		$this->content = $this->fetch_feed(); 
		
		$this->parse_feed();
		$this->show();
  	}


  /* string */
  	function fetch_feed()
  	{ 
		global $rss_data, $useragent;
		$url = str_replace("feed:", "http:", $this->rss_url);
		
		$url_parts = parse_url($url);
		$port = $url_parts['scheme']=="https" ? 443 : 80;
		$host = $url_parts['scheme']=="https" ? "ssl://" : "";
		$host .= $url_parts['host'];

		$filename = $url_parts['host'] . str_replace("/", ",", $url_parts['path']);
		if(file_exists($this->data_directory . $filename)) {
			$last = filemtime($this->data_directory . $filename);
			 $create = 0;
			if(time() - $last > $this->update_interval * 60 || $this->update_interval == 0) 					{
				$update = 1;
			} else {
				$update = 0;
			}
		} else {
			$create = 1;
			$update = 1;
		}


		if($create == 1 || ($this->do_update == TRUE && $update == 1)) { 
			// try with cURL firts, if available
		if(function_exists('curl_version')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
				curl_setopt($ch, CURLOPT_HEADER, 0);				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_USERAGENT, $useragent);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$rss_data = curl_exec($ch);
        		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        		curl_close($ch); 
		
			} else {		
				$fp = @fsockopen($host, $port, $errno, $errstr, 5);		 
				if (!$fp) {
					$errors .= "Couldn't open rss feed ".$url.":".$port." in {$_SERVER['PHP_SELF']}<br />\n";
					$errors .= " - ".$errstr." (".$errno>")<br />\n";
					return;
				}		
				fputs($fp, "GET {$url} HTTP/1.0\r\n"
						."Host: " . $url_parts['host'] . "\r\n"
						."User-Agent: ".$useragent."\r\n"
						."Connection: Close\r\n\r\n");		
				while(!feof($fp)) {
					$rss_data .= @fread($fp, 1024);
				}
				list(, $rss_data) = explode("\r\n\r\n", $rss_data, 2);
				
				
				$rss_data = fixXMLEncodingFromHTTP($rss_data);    
			}
			
			
			

	
			$output = @fopen($this->data_directory . $filename, "w+b");
			if(!$output) {
				return str_replace("&apos;","&#39;",$rss_data); // replace apostrophe for IE
			} else {
				flock($output, LOCK_EX);
				fputs($output, $rss_data);
				flock($output, LOCK_UN);
				fclose($output);
			}
			return $rss_data;
		} //update
		return file_get_contents($this->data_directory . $filename);
	
	}

  /* void */
  function parse_feed()
  {
	  $description = array();
	  global $errors;
    preg_match("/<title>(.*?)<\/title>/", $this->content, $title);
    $this->feed_title = @$title[1];

    preg_match("/<link>(.*?)<\/link>/", $this->content, $link);
    $this->feed_link = @$link[1];

     preg_match("/(description)[^>]*>(.*?)<\/(description)>/", $this->content, $description);
	// pre replace added strips out non-latin
    $this->feed_description = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', @$description[1]);
	
	 preg_match("/<pubDate>(.*?)<\/pubDate>/", $this->content, $pubDate);
	 
	 $pubDate[1] =  date($this->dateformat, strtotime(@$pubDate[1]));
    $this->feed_pubDate = $pubDate[1];
	
	

    preg_match_all("/<(item|entry)[^>]*>(.*?)<\/(item|entry)>/s", $this->content, $items);
    if (sizeof($items[0]) == 0) {
      $errors .= "No item elements found in rss feed.<br />\n";
    }
	
	

    for($i = 0; $i < sizeof($items[0]); ++$i) {
      preg_match_all("/(?:<([\w:]*)[^>]*>\s*(?:<!\[CDATA\[)?(.*?)(?:]]>)?\s*<\/\\1>)+?/si", preg_replace("/<(item|entry)[^>]*>/", "", $items[0][$i]), $elements);
	 
	
	   
      for($j = 0; $j < sizeof($elements[0]); ++$j) {
        $elements[1][$j] = str_replace(":", "__", $elements[1][$j]);  //regex fix for items with : like dc:date
        $this->rss[$i][$elements[1][$j]] = addslashes(trim($this->unhtmlentities($elements[2][$j])));
		 $this->rss[$i][$elements[1][$j]] = ($this->linebreak) ?  $this->rss[$i][$elements[1][$j]] : trim(preg_replace('/\s+/', ' ',  $this->rss[$i][$elements[1][$j]]));
		
      }
	  
	  /* get enclosure */
	  $j++;	  
	  preg_match('/< *enclosure[^>]*url *= *["\']?([^"\']*)/i',$items[0][$i], $url );
	  $this->rss[$i]["enclosure"] = (isset($url[1])) ? "<img src=\"".$url[1]."\">" : "";
	  
	  /* get atom link */
	if(!isset($link[1])) {
	  preg_match_all("/<link.*?href\s*=\s*['\"](.*?)['\"]/", $items[0][$i], $href);
	   if(is_array($href) && count($href)>0) { 
	  
	  		$link = array_pop($href[1]);// the last link is the relevant one in the  feeds so far, so reverse order to get	   		
			$this->rss[$i]["link"] = $link;		
	   }	  
    }
	}
  }

  
  /* void */
  function show()
  {
    if($this->my_html == "") {
      $this->show_html();
    } else {
      $this->show_user_html();
    }
  }

  function show_html()
  {
    $show = (sizeof($this->rss)  > $this->num_to_show ? $this->num_to_show : sizeof($this->rss));
    for($i = $this->offset; $i < $this->offset + $show; ++$i) {
      echo "- <a href=\"{$this->rss[$i]['link']}\" target=\"_new\" rel=\"nofollow\">{$this->rss[$i]['title']}</a><br />\n";
    }
  }

  function show_user_html()
  { 
 
    $show = (sizeof($this->rss) > $this->num_to_show + $this->offset ? $this->num_to_show : sizeof($this->rss));
    $show = ($this->offset + $this->num_to_show > sizeof($this->rss) ? sizeof($this->rss) - $this->offset : $this->num_to_show);
    for($i = $this->offset; $i < $this->offset + $show; ++$i) {
      extract($this->rss[$i]); // allfields to variables
	  $description =  isset($content) ? $content : $description; // atom to rss
	  
	  $description = (getProtocol()=="https") ? str_replace("http://","https://",$description) : $description; // to keep SSL sites secure  
	  $pubDate =  isset($published) ? $published : $pubDate; // atom to rss
	  $pubDate =  date($this->dateformat, strtotime($pubDate));
      //$item = stripslashes(preg_replace("/#\{([^}]+)}/e", "$\\1",$this->my_html));
	  /* for PHP 7 replaced preg_replace with /e  modfier with preg_replace_callback function */
	  
	 $item = $this->my_html;
	 
	 
	  $item = preg_replace_callback(
    		"/#\{([^}]+)}/",
    		function($matches) use ($title, $description, $pubDate) {
			
				return ${$matches[1]};       
    	}, $item);

	$item = stripslashes($item);
	

	  $item = str_replace("[x]","[".$i."]", $item); // added by me to allow arrays for ticker.js
	  
	  echo $item;
    }
	
  }

  function unhtmlentities($string)
  {
	
   
  return html_entity_decode($string);
  }

} // end class


function transformFromContentTypeToUTF8($str) {
    
    if (isset($_SERVER['CONTENT_TYPE']) && preg_match('#charset=([^/s^;]+)#',$_SERVER['CONTENT_TYPE'],$matches)) {
        if ($matches[1] == 'UTF-8') {
            return $str;
        }
        if ($matches[1] == "ISO-8859-1") {
            return utf8_encode($str);
        }
        return iconv($matches[1],"UTF-8",$str);
    } 
    //if no charset, then return as it came
    return $str;
	       

}

function fixXMLEncodingFromHTTP($xml) {
    if (!preg_match("#<?xml[^>]+encoding=#",$xml)) {
        return transformFromContentTypeToUTF8($xml);   
}
return $xml;
}


if(!function_exists("file_get_contents")) { // for PHP <4.3
	function file_get_contents($filename)
  {
  $handle = fopen($filename, "rb");
  $contents = fread($handle, filesize($filename));
  fclose($handle);
  return $contents;
  }
}

?>