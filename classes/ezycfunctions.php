<?php

/**
 * File containing the eZyoochooseFunctions class for sending requests to the yoochoose server
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */


class ezYCFunctions{
	
	
	public static function send_http_request($url, $path ){
	
	        $fp = fsockopen( $url, 80, $errno, $errstr, 30);
	 			   		
			if ($fp) {
				
			    $out = "GET ".$path." HTTP/1.0\r\n";
			    $out .= "Host: ".$url."\r\n";
			    $out .= "Connection: Close\r\n\r\n";		    
			    		    
			    fwrite($fp, $out);

			    $ini = eZINI::instance('ezyoochoose.ini');
			    
			    if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){

				    $content = "";
				    $header = "";
				    
					$header_passed = "not yet";
					
					while( !feof( $fp ) ) {
					    $line = fgets( $fp, 128 );
					    if( $line == "\r\n" && $header_passed == "not yet" ) {
					        $header_passed = "passed";
					    }
					    if( $header_passed == "passed" ) {
					        $content .= $line;
					    }else{
					    	$header .= $line;
					    }
					}
					
					eZLog::write($url.$path.$data, 'debug.log', 'var/log');
					eZLog::write(var_export($header, true), 'debug.log', 'var/log');
					
			    }
							
			    fclose($fp);
				
			    return true;

			}
			else {
				
				return false;
				
			}
			
	}
	
	
	
	public static function send_reco_request($url, $path ){
		
		$fp = fsockopen( $url, 80, $errno, $errstr, 30);
			
		if ($fp) {
				
	    	$out = "GET ".$path." HTTP/1.0\r\n";
		    $out .= "Host: ".$url."\r\n";
		    $out .= "Connection: Close\r\n\r\n";		    
		    		    
		    fwrite($fp, $out);
		    
		     $ini = eZINI::instance('ezyoochoose.ini');
		    
		    if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){
		    	
			    $content = "";
			    $header = "";
			    
				$header_passed = "not yet";
				
				while( !feof( $fp ) ) {
				    $line = fgets( $fp, 128 );
				    if( $line == "\r\n" && $header_passed == "not yet" ) {
				        $header_passed = "passed";
				    }
				    if( $header_passed == "passed" ) {
				        $content .= $line;
				    }else{
				    	$header .= $line;
				    }
				}
				
				eZLog::write($url.$path, 'debug.log', 'var/log');
				eZLog::write($header, 'debug.log', 'var/log');
				eZLog::write(var_export($content , true), 'debug.log', 'var/log');
				
				 
		    }	
		    	
		    fclose($fp);			
			
		    return json_decode(substr( $content, 2 ) );
		
			}else{

				eZLog::write('eZYoochoose: could not connect to server with url: '.$url, 'error.log', 'var/log');
				return false;
				
			}

	}		
	
}