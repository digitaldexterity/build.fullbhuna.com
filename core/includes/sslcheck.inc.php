<?php // check to see if https EVEN IF OVER PROXY e.g. Cloudflare - if not make so

if(!function_exists("isSSL")) {
function isSSL()
    {
        if( !empty( $_SERVER['HTTPS'] ) && strtolower($_SERVER['HTTPS']) != 'off')
            return true;

        if( !empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
            return true;
			
		if( !empty( $_SERVER['HTTP_X_PROTO'] ) && ($_SERVER['HTTP_X_PROTO'] == 'https'|| strtolower($_SERVER['HTTP_X_PROTO'])=='ssl') )
            return true;

        return false;
    }
}

if(defined('USE_SSL') && USE_SSL == true && !isset($nossl) && !isset($is_cron)) {  
    if (!isSSL()) {
		//print_r($_SERVER);die();
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}
}  



?>