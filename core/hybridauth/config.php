<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return
		array(
			"base_url" => "http://".$_SERVER['HTTP_HOST']."/core/hybridauth/",
			"providers" => array(
				
			
				"Facebook" => array(
					"enabled" => true,
					"keys" => array("id" => "708983032599655", "secret" => "c7ffe8e86c3b50e17723ed4ba61a255e"),
					 "scope" => "email" // optional
				),
				
				// twitter
            "Twitter" => array ( // 'key' is your twitter application consumer key
               "enabled" => true,
               "keys" => array ( "key" => "5bkq6USYdKCHcEX74yJq6eNfX", "secret" => "kwDwTDFGVi1T3DWr3g0P6ugTOJH1h5yX97M8RLK9wvW7zBqwpP" )
            ),
				
			),
			// If you want to enable logging, set 'debug_mode' to true.
			// You can also set it to
			// - "error" To log only error messages. Useful in production
			// - "info" To log info and error messages (ignore debug messages)
			"debug_mode" => false,
			// Path to file writable by the web server. Required if 'debug_mode' is not false
			"debug_file" => "",
);
