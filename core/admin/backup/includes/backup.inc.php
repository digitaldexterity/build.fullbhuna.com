<?php 

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

if(!function_exists("backupUploads")) {
function backupUploads() {
	// find an upload not backed up
	$select = "SELECT ID FROM uploads WHERE backup = 0 ORDER BY createddatetime LIMIT 1";
}
}
if(!function_exists("backupNextFileInFolder")) {
function backupNextFileInDirectory($localpath, $ftpserver=array()) {
	global $database_aquiescedb, $aquiescedb;
	// assumes  localpath and $ftpserver[path'] are synced to same root path
	
	//$ftpserver['newer'] = true if files are newer then replace
	
	// GET REMOTE ARRAY
	
	// set up basic connection
	if(isset($ftpserver['server']) && trim($ftpserver['server'])!="") { // server exists
	$conn_id = ftp_connect($ftpserver['server'], 21, 1200) or die("Couldn't connect to ".$ftpserver['server']);
//echo "Connected. ";
	// login with username and password
	ftp_login($conn_id, $ftpserver['username'], $ftpserver['password']) or die("login to $server failed");
	$result  = ftp_pasv ($conn_id, TRUE);
	$ftppath = isset($ftpserver['path']) ? $ftpserver['path'] : '.';
	
	$ftp_detail_list = ftp_rawlist ($conn_id , $ftppath);
	if(!$ftp_detail_list) { // assume directory doesn't exist
	
		ftp_mksubdirs($conn_id,".",$ftppath);
		ftp_chdir($conn_id, "/"); //back to root
		$ftp_detail_list = ftp_rawlist ($conn_id , $ftppath);
		if(!$ftp_detail_list) {
			die("Can't find or create FTP directory: ".$ftppath);			
		}
	}	
	
	
	foreach($ftp_detail_list as $key=>$value) {
		 $chunks = preg_split("/\s+/", $value); 
		 list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
          $item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file'; 
          array_splice($chunks, 0, 8); // remove all except file name
		  $item['filename'] = implode(" ",$chunks);
		  $item['timestamp'] = strpos($item['time'],":")>0 ? strtotime(date('Y')."-".date('m', strtotime($item['month']))."-". $item['day']." ".$item['time']) : strtotime($item['time']."-".date('m', strtotime($item['month']))."-". $item['day']." 00:00:00");
        // $detail_remote_list[implode(" ", $chunks)] = $item; 
		  $detail_remote_list[$item['filename']] = $item; 
		//echo $value."<br>";
	}	
	
	$ftp_list = ftp_nlist ($conn_id , $ftppath);
	
	
	// GET LOCAL ARRAY
	$local_list = scandir($localpath);
	$detail_local_list = listDirectoryFiles($localpath);
	
	
	echo "<table><tr>";	
	echo "<td><h2>Local</h2><pre>";
	print_r($detail_local_list);
	echo "</pre></td>";	
	echo "<td><h2>Remote</h2><pre>";	
	print_r($detail_remote_list);	
	echo "</pre></td>";	
	echo "</tr></table>";
	
	$uploaded = false;
	foreach($detail_local_list as $key=>$file) {		
		$safe_filename = preg_replace("/[^a-zA-Z0-9._\-\s]/","",$file['filename']); //die($safe_filename);
		// time difference if FTP time is greater than 1 month - 1 day otherwise 10 minutes
		//$time_difference = (isset($detail_remote_list[$safe_filename]['timestamp']) && time() - $detail_remote_list[$safe_filename]['timestamp']>60*60*24*30) ? 60*60*24 : 60*10;
		$time_difference = 60*60*24; // 1 day
		
		if(is_file($localpath.$file['filename']) && !isset($detail_remote_list[$safe_filename]) || (isset($ftpserver['newer']) && $ftpserver['newer']==true && ($detail_local_list[$key]['timestamp'] - $detail_remote_list[$safe_filename]['timestamp'])>$time_difference)) {	// found local file not in remote list to back up
	
			mysql_select_db($database_aquiescedb, $aquiescedb);
			$insert = "INSERT INTO backup (backupfilename, autobackuptype, backupcontenttype, statusID, createdbyID, createddatetime) VALUES (".GetSQLValueString($localpath.$file['filename'], "text").",1,3,0,0,NOW())";
			mysql_query($insert, $aquiescedb) or die(mysql_error());
			$backupID = mysql_insert_id();
		
			$ret = ftp_nb_put($conn_id, $ftppath.$safe_filename, $localpath.$file['filename'], FTP_BINARY, ftp_size($conn_id,$remote_file)); // auto resume if needbe...
			while ($ret == FTP_MOREDATA) {   
				// add other code here
				// Continue uploading...
				$ret = ftp_nb_continue($conn_id);
			}
			
			if ($ret == FTP_FINISHED) {				
				$uploaded = $ftppath.$safe_filename;
				mysql_select_db($database_aquiescedb, $aquiescedb);
				$insert = "UPDATE backup SET remotefilename = ".GetSQLValueString($ftppath.$safe_filename,"text").",statusID = 1 WHERE ID = ".$backupID;
				mysql_query($insert, $aquiescedb) or die(mysql_error());
			} else {
				$uploaded = false;
			}
			
		} // end found file
		
		if($uploaded!==false) break; // once uploaded a file, exit loop		
	} // end for each

	@ftp_close($conn_id);
	return $uploaded;	
	} // server exists
	return false;
}
}


function listDirectoryFiles($dir = ".") 
    { 
        $listDir = array(); 
		$i = 0;
        if($handler = opendir($dir)) { 
            while (($sub = readdir($handler)) !== FALSE) { 
                if ($sub != "." && $sub != ".." && $sub != "Thumb.db") { 
                    if(is_file($dir."/".$sub)) { 
                        $listDir[$sub]['filename'] = $sub; 
						$listDir[$sub]['timestamp'] = filemtime($dir."/".$sub); 
                    }
                } 
				$i++;
            }    
            closedir($handler); 
        } 
        return $listDir;    
    }
	
	
if(!function_exists("dropboxUpload")) {
	function dropboxUpload($filename, $remote_path="/") {



		$api_url = 'https://content.dropboxapi.com/2/files/upload'; //dropbox api url
        $token = DROPBOX_ACCESS_TOKEN; // oauth token
		
		

        $headers = array('Authorization: Bearer '. $token,
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.
            json_encode(
                array(
                    "path"=> $remote_path. basename($filename),
                    "mode" => "add",
                    "autorename" => true,
                    "mute" => false
                )
            )

        );

        $ch = curl_init($api_url);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_POST, true);

        $path = $filename;


        $fp = fopen($path, 'rb');
        $filesize = filesize($path);

         curl_setopt($ch, CURLOPT_POSTFIELDS, fread($fp, $filesize));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1); // debug

        $response = curl_exec($ch);


        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);



        echo($response.'<br/>');
        echo($http_code.'<br/>');

        curl_close($ch);
		
}
}


