<?php function getBrowser($u_agent) 
{ 
	// php.net forum answer updated by P Egan for IE11, Edge, iOS and Android
    // $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Other';
	$ub = "";
    
    $version= "";
	$pattern= "";

    //First get the platform?
	if (preg_match('/iPad|iPhone/i', $u_agent)) {
        $platform = 'iOS';
    }
	elseif (preg_match('/Android/i', $u_agent)) {
        $platform = 'Android';
    }
    elseif (preg_match('/linux/i', $u_agent)) {
        $platform = 'Linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'Mac OS';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'Windows';
    } else {
		$platform = 'Other';
	}
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/Opera|Opr/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
	elseif(preg_match('/Edge/i',$u_agent)) 
    { 
        $bname = 'Microsoft Edge'; 
        $ub = "Edge"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Trident/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "rv"; 
    }
	elseif(preg_match('/MSIE/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    }
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    }
	
	if($ub!="") {
    
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?P<browser>' . join('|', $known) .
		')[/\: ]+(?P<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		
		// see how many we have
		$i = count($matches['browser']);
		if ($i > 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= isset($matches['version'][0]) ? $matches['version'][0] : "";
		}
	}
    
    // check if we have a number
    //if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 


// gets which os from the browser string
function get_os_data ( $pv_browser_string, $pv_browser_name, $pv_version_number  )
{
	// initialize variables
	$os_working_type = '';
	$os_working_number = '';
	/*
	packs the os array. Use this order since some navigator user agents will put 'macintosh' 
	in the navigator user agent string which would make the nt test register true
	*/
	$a_mac = array( 'intel mac', 'ppc mac', 'mac68k' );// this is not used currently
	// same logic, check in order to catch the os's in order, last is always default item
	$a_unix_types = array( 'dragonfly', 'freebsd', 'openbsd', 'netbsd', 'bsd', 'unixware', 'solaris', 'sunos', 'sun4', 'sun5', 'suni86', 'sun', 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux', 'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant', 'dec', 'sinix', 'unix' );
	// only sometimes will you get a linux distro to id itself...
	$a_linux_distros = array( 'ubuntu', 'kubuntu', 'xubuntu', 'mepis', 'xandros', 'linspire', 'winspire', 'jolicloud', 'sidux', 'kanotix', 'debian', 'opensuse', 'suse', 'fedora', 'redhat', 'slackware', 'slax', 'mandrake', 'mandriva', 'gentoo', 'sabayon', 'linux' );
	$a_linux_process = array ( 'i386', 'i586', 'i686' );// not use currently
	// note, order of os very important in os array, you will get failed ids if changed
	$a_os_types = array( 'android', 'blackberry', 'iphone', 'palmos', 'palmsource', 'symbian', 'beos', 'os2', 'amiga', 'webtv', 'mac', 'nt', 'win', $a_unix_types, $a_linux_distros );
	
	//os tester
	$i_count = count( $a_os_types );
	for ( $i = 0; $i < $i_count; $i++ )
	{
		// unpacks os array, assigns to variable $a_os_working
		$os_working_data = $a_os_types[$i];
		/*
		assign os to global os variable, os flag true on success
		!strstr($pv_browser_string, "linux" ) corrects a linux detection bug
		*/
		if ( !is_array( $os_working_data ) && strstr( $pv_browser_string, $os_working_data ) && !strstr( $pv_browser_string, "linux" ) )
		{
			$os_working_type = $os_working_data;
			
			switch ( $os_working_type )
			{
				// most windows now uses: NT X.Y syntax
				case 'nt':
					if ( strstr( $pv_browser_string, 'nt 6.1' ) )// windows 7
					{
						$os_working_number = 6.1;
					}
					elseif ( strstr( $pv_browser_string, 'nt 6.0' ) )// windows vista/server 2008
					{
						$os_working_number = 6.0;
					}
					elseif ( strstr( $pv_browser_string, 'nt 5.2' ) )// windows server 2003
					{
						$os_working_number = 5.2;
					}
					elseif ( strstr( $pv_browser_string, 'nt 5.1' ) || strstr( $pv_browser_string, 'xp' ) )// windows xp
					{
						$os_working_number = 5.1;//
					}
					elseif ( strstr( $pv_browser_string, 'nt 5' ) || strstr( $pv_browser_string, '2000' ) )// windows 2000
					{
						$os_working_number = 5.0;
					}
					elseif ( strstr( $pv_browser_string, 'nt 4' ) )// nt 4
					{
						$os_working_number = 4;
					}
					elseif ( strstr( $pv_browser_string, 'nt 3' ) )// nt 4
					{
						$os_working_number = 3;
					}
					break;
				case 'win':
					if ( strstr( $pv_browser_string, 'vista' ) )// windows vista, for opera ID
					{
						$os_working_number = 6.0;
						$os_working_type = 'nt';
					}
					elseif ( strstr( $pv_browser_string, 'xp' ) )// windows xp, for opera ID
					{
						$os_working_number = 5.1;
						$os_working_type = 'nt';
					}
					elseif ( strstr( $pv_browser_string, '2003' ) )// windows server 2003, for opera ID
					{
						$os_working_number = 5.2;
						$os_working_type = 'nt';
					}
					elseif ( strstr( $pv_browser_string, 'windows ce' ) )// windows CE
					{
						$os_working_number = 'ce';
						$os_working_type = 'nt';
					}
					elseif ( strstr( $pv_browser_string, '95' ) )
					{
						$os_working_number = '95';
					}
					elseif ( ( strstr( $pv_browser_string, '9x 4.9' ) ) || ( strstr( $pv_browser_string, ' me' ) ) )
					{
						$os_working_number = 'me';
					}
					elseif ( strstr( $pv_browser_string, '98' ) )
					{
						$os_working_number = '98';
					}
					elseif ( strstr( $pv_browser_string, '2000' ) )// windows 2000, for opera ID
					{
						$os_working_number = 5.0;
						$os_working_type = 'nt';
					}
					break;
				case 'mac':
					if ( strstr( $pv_browser_string, 'os x' ) )
					{
						// if it doesn't have a version number, it is os x;
						if ( strstr( $pv_browser_string, 'os x ' ) )
						{
							// numbers are like: 10_2.4, others 10.2.4
							$os_working_number = str_replace( '_', '.', get_item_version( $pv_browser_string, 'os x' ) );
						}
						else
						{
							$os_working_number = 10;
						}
					}
					/*
					this is a crude test for os x, since safari, camino, ie 5.2, & moz >= rv 1.3
					are only made for os x
					*/
					elseif ( ( $pv_browser_name == 'saf' ) || ( $pv_browser_name == 'cam' ) ||
						( ( $pv_browser_name == 'moz' ) && ( $pv_version_number >= 1.3 ) ) ||
						( ( $pv_browser_name == 'ie' ) && ( $pv_version_number >= 5.2 ) ) )
					{
						$os_working_number = 10;
					}
					break;
				case 'iphone':
					$os_working_number = 10;
					break;
				default:
					break;
			}
			break;
		}
		/*
		check that it's an array, check it's the second to last item
		in the main os array, the unix one that is
		*/
		elseif ( is_array( $os_working_data ) && ( $i == ( $i_count - 2 ) ) )
		{
			$j_count = count($os_working_data);
			for ($j = 0; $j < $j_count; $j++)
			{
				if ( strstr( $pv_browser_string, $os_working_data[$j] ) )
				{
					$os_working_type = 'unix'; //if the os is in the unix array, it's unix, obviously...
					$os_working_number = ( $os_working_data[$j] != 'unix' ) ? $os_working_data[$j] : '';// assign sub unix version from the unix array
					break;
				}
			}
		}
		/*
		check that it's an array, check it's the last item
		in the main os array, the linux one that is
		*/
		elseif ( is_array( $os_working_data ) && ( $i == ( $i_count - 1 ) ) )
		{
			$j_count = count($os_working_data);
			for ($j = 0; $j < $j_count; $j++)
			{
				if ( strstr( $pv_browser_string, $os_working_data[$j] ) )
				{
					$os_working_type = 'lin';
					// assign linux distro from the linux array, there's a default
					//search for 'lin', if it's that, set version to ''
					$os_working_number = ( $os_working_data[$j] != 'linux' ) ? $os_working_data[$j] : '';
					break;
				}
			}
		}
	}

	// pack the os data array for return to main function
	$a_os_data = array( $os_working_type, $os_working_number );

	return $a_os_data;
}
