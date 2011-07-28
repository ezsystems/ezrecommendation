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
	
	/*
	 * 
	 */
	public static function send_http_request($url, $path ){
	
			$ini = eZINI::instance('ezyoochoose.ini');
		
	        eZLog::write('[ezyoochoose] Trying request '.$url.$path, 'debug.log', 'var/log');
			
			$fp = fsockopen( $url, 80, $errno, $errstr, 30);	        
	        
			if ($fp) {
				
			    $out = "GET ".$path." HTTP/1.0\r\n";
			    $out .= "Host: ".$url."\r\n";
			    $out .= "Connection: Close\r\n\r\n";		    
			    		    
			    fwrite($fp, $out);			    
			    
			    eZLog::write('[ezyoochoose] Sending request '.$url.$path, 'debug.log', 'var/log');
			    
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
					
					eZLog::write('[ezyoochoose] Received answer '.var_export($header, true), 'debug.log', 'var/log');
					
			    }
							
			    fclose($fp);
				
			    return true;

			}
			else {
				
				eZLog::write('[ezyoochoose] Could not connect to server with url: '.$url, 'error.log', 'var/log');
				return false;
				
			}
			
	}
	
	
	
	/*
	 * 
	 */
	public static function send_reco_request($url, $path ){
		//reco.yoochoose.net/ebl/10053/top_selling.json?itemid=262&numrecs=10&itemtypeid=3
		
		/* Beginn https:443 */
/*		$ini = eZINI::instance('ezyoochoose.ini');
		$customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
		$LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
		$fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);
*/		/*end https:443*/
		
		eZLog::write('[ezyoochoose] Trying request '.$url.$path, 'debug.log', 'var/log');
		/* Beginn http:80 */
		
		$fp = fsockopen( $url, 80, $errno, $errstr, 30);
		/*end http:80*/
		
		if ($fp) {
		
			/* Beginn https:443 */	
/*			$auth=base64_encode($customerID.":".$LicKey); 
			$out = "POST ".$path." HTTP/1.0\r\n";
			$out .= "Host: ".$url."\r\n";
			$out .= "Accept: text/html\r\n";	
*/			$out .= "Authorization: Basic $auth\r\n\r\n";
			/*end https:443*/
		
			/* Beginn http:80 */		
			$out = "GET ".$path." HTTP/1.0\r\n";
		    $out .= "Host: ".$url."\r\n";
		    $out .= "Connection: Close\r\n\r\n";	
			/*end http:80*/
		    
		    eZLog::write('[ezyoochoose] Sending request '.$url.$path, 'debug.log', 'var/log');
		    
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
		
				
				eZLog::write('[ezyoochoose] Received answer '.var_export($header, true), 'debug.log', 'var/log');
				eZLog::write('[ezyoochoose] Received recommendations '.var_export($content , true), 'debug.log', 'var/log');
								 
		    }	
		    	
		    fclose($fp);			
			
		    return json_decode(substr( $content, 2 ) );
		
			}else{

				eZLog::write('[ezyoochoose] Could not connect to server with url: '.$url, 'error.log', 'var/log');
				return false;
				
			}

	}	
	
	
	/*
	 * 
	 */
	public static function sendExportContent($data, $solution){

		$ini = eZINI::instance('ezyoochoose.ini');
		
		$url = $ini->variable( 'URLSettings', 'ExportURL' );
		$customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
		$LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
		$mapSetting = $ini->variable( 'SolutionMapSettings', $solution );
		
		$path = "/$mapSetting/$customerID/item";
	
		$contenttype = "text/xml";

		eZLog::write('[ezyoochoose] Trying request '.$url.$path, 'debug.log', 'var/log');
		
		$fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

			if ($fp) {

				eZLog::write('[ezyoochoose] Sending request '.$url.$path, 'debug.log', 'var/log');
				
				$auth=base64_encode($customerID.":".$LicKey); 

			    $out = "POST ".$path." HTTP/1.0\r\n";
			    $out .= "Host: ".$url."\r\n";
			    $out .= "Content-type:$contenttype\r\n";	    
				$out .= "Content-Length: ".strlen($data)."\r\n";  	
				$out .= "Accept: text/html\r\n";				
				$out .= "Authorization: Basic $auth\r\n\r\n";					
				
				fwrite($fp, $out);
				fwrite($fp, $data);
			    
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

					
					eZLog::write('[ezyoochoose] Received answer '.var_export($header, true), 'debug.log', 'var/log');
					
			    }
							
			    fclose($fp);
				
			    return true;

			}
			else {
				
				eZLog::write('[ezyoochoose] Could not connect to server with url: '.$url, 'error.log', 'var/log'); 
				return false;
				
			}	

	}	
	
}