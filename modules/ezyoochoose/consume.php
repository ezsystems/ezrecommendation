<?php

/**
 * File containing the eZyoochooseFunctions request implementation
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

$http = eZHTTPTool::instance();
$ini = eZINI::instance('ezyoochoose.ini');

if ( $ini->hasVariable( 'URLSettings', 'RequestURL' ) ){
	
	$url = $ini->variable( 'URLSettings', 'RequestURL' );

}else{
	
	eZLog::write('eZYoochoose: no url found for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');	
	return false;
	
}


if ( $http->hasGetVariable('productid') && $http->hasGetVariable('eventtype') && $http->hasGetVariable('itemtypeid') && $http->hasGetVariable('itemid')){
	
	$productid = $http->getVariable('productid');
	
	if ( $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
		
		$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
		 
	}else{
		
		eZLog::write('eZYoochoose: no CustomerID found for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');	
		return false;
		
	}
	
	require_once( 'extension/ezyoochoose/classes/ezycfunctions.php' );
	
	$path = '/';
	$params = array();
	
	$path .= $productid;  
	
	$path .= '/'.$client_id;
	
	$eventtype = $http->getVariable('eventtype');
	$path .= '/'.$eventtype;
	
	$user = eZUser::currentuser();
	
	if ($user->Login == 'anonymous' && $http->hasGetVariable('sid')){
		
		$userid = $http->getVariable('sid');
		$path .= '/'.$userid;
		
	}elseif($user->Login != 'anonymous' && $http->hasGetVariable('userid') ){
		
		$userid = $http->getVariable('userid');
		
		if ( $http->hasGetVariable('map') && $http->getVariable('map') == 1 && $http->hasGetVariable('sid') ){

			$sid = $http->getVariable('sid');
			
			$path_for_transfer = '/';
			$path_for_transfer .= $productid;
			$path_for_transfer .= '/'.$client_id;
			$path_for_transfer .= '/transfer';
			$path_for_transfer .= '/'.$sid;
			$path_for_transfer .= '/'.$userid;
						
			ezYCFunctions::send_http_request($url, $path_for_transfer);
			
		}

		$path .= '/'.$userid;

	}
	
	$itemtypeid = $http->getVariable('itemtypeid');
	$path .= '/'.$itemtypeid;
	
	$itemid = $http->getVariable('itemid');
		$path .= '/'.$itemid;	
	
	if ($http->hasGetVariable('categorypath')){
		$params['categorypath'] = $http->getVariable('categorypath');
	}

	if ($http->hasGetVariable('quantity')){
		$params['quantity'] = $http->getVariable('quantity');
	}
	
	if ($http->hasGetVariable('price')){
		$params['price'] = $http->getVariable('price');
	}
	
	if ($http->hasGetVariable('currency')){
		$params['currency'] = $http->getVariable('currency');
	}
	
	if ($http->hasGetVariable('timestamp')){
		$params['timestamp'] = $http->getVariable('timestamp');
	}
	
	if ($http->hasGetVariable('rating')){
		$params['rating'] = $http->getVariable('rating');
	}
	
	$params_array = array();
 	foreach (array_keys($params) as $key) {
        array_push($params_array, urlencode($key) ."=".urlencode($params[$key]));
	}
	
	$params_data = '';
	if (!empty($params_array)){
		$params_data = '?';
		$params_data .= implode("&", $params_array);	
	}
	
	ezYCFunctions::send_http_request($url, $path.$params_data);
		
}else{

	eZLog::write('eZYoochoose: required variable not set in request.', 'error.log', 'var/log');	
	return false;
	
}

eZExecution::cleanExit();

?>