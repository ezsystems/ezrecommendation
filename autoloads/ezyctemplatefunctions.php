<?php

/**
 * File containing the eZyoochooseFunctions class for generating the html output
 *
 * @copyright Copyright (C) 2010-2011 yoochoose GmbH. All rights reserved.
 * @license eZ Proprietary Extension License (PEL), Version 1.3
 * @version 1.0.0
 * @package ezyoochoose
 */

include_once( 'lib/ezutils/classes/ezini.php' );

class ezYCTemplateFunctions {

	function ezYCTemplateFunctions() {
		$this->Operators = array( 	'generate_html',
									'generate_common_event',		
									'generate_buy_event',		
									'generate_rate_event',		
									'get_recommendations',
									'track_rendered_items'
                                  );
	}

	function &operatorList()
	{
		return $this->Operators;
	}

	function namedParameterPerOperator()
	{
		return true;
	}

	function namedParameterList()
	{
		return array( 	'generate_html' => array(
	                        'module_result' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'event_type' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'generate_common_event' => array(
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'event_type' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'generate_buy_event' => array(
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'quantity' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),
	                        'price' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),
	                        'currency' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'generate_rate_event' => array(
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'rating' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'get_recommendations' => array(
	                        'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'scenario' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),
							'limit' => array( 'type' => 'integer',
	                                            'required' => false,
	                                            'default' => 5 ),
							'category_based' => array( 'type' => 'boolean',
	                                            'required' => false,
	                                            'default' => false )
									),
							'track_rendered_items' => array(
									'solution' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),
									'itemtypeid' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' ),
									'items' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )									
									)
								
								);
	}

	function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace,
	&$currentNamespace, &$operatorValue, &$namedParameters )
	{
		switch ( $operatorName )
		{
			case 'generate_html':
				{
					$operatorValue = $this->generate_html_from_module_result( 
																$namedParameters['module_result'],
																$namedParameters['solution'],
																$namedParameters['event_type']
													 );
				} break;
			case 'generate_common_event':
				{
					$operatorValue = $this->generate_common_event( 
																$namedParameters['node'],
																$namedParameters['solution'],
																$namedParameters['event_type']
													 );
				} break;
			case 'generate_buy_event':
				{
					$operatorValue = $this->generate_buy_event( 
																$namedParameters['node'],
																$namedParameters['solution'],
																$namedParameters['quantity'],
																$namedParameters['price'],
																$namedParameters['currency']
													 );
				} break;
			case 'generate_rate_event':
				{
					$operatorValue = $this->generate_rate_event( 
																$namedParameters['node'],
																$namedParameters['solution'],
																$namedParameters['rating']
													 );
				} break;
			case 'get_recommendations':
				{
					$operatorValue = $this->get_recommendations( 
																$namedParameters['solution'],
																$namedParameters['scenario'],
																$namedParameters['node'],
																$namedParameters['limit'],
																$namedParameters['category_based']
													 );
				} break;
			case 'track_rendered_items':
				{
					$operatorValue = $this->track_rendered_items( 
																$namedParameters['solution'],
																$namedParameters['itemtypeid'],
																$namedParameters['items']																
													 );
				} break;
		 
		}
	}

	
	function get_current_url() {
		
		$serverURL = '/ezyoochoose/request';
		return $serverURL;
	}
	
	
		 
	function get_html( $params ) {
		
		$serverURL = $this->get_current_url();
		
		$res = $serverURL.$params;
						
		return $res;
	}	 


	
	function get_html_for_event( $params, $userid ) {
		
		$serverURL = $this->get_current_url();
		
		$res = 'onclick="ezyc.evt(\''.$serverURL.$params.'\', '.$userid.')"';
						
		return $res;
	}

	

	function get_html_for_rendered_items( $params, $userid ) {
		
		$serverURL = $this->get_current_url();
		
		$res = '<script type="text/javascript">ezyc.evt(\''.$serverURL.$params.'\', '.$userid.')</script>';
						
		return $res;
	}	
	
	
	
	function get_current_user_id( ) {
		
		$current_user = eZUser::currentUser ();
		if ($current_user->Login == 'anonymous'){
			
			$userid = 10;
			
		}else{
			
			$userid = $current_user->attribute( 'contentobject_id' );
			
		}
		
		return $userid;
	}

	
	function generate_recommendations_array( $raw_recommendations ){
		
		$recommendations_array = array();
		$i = 0;

		foreach ($raw_recommendations as $rec){
			foreach ($rec as $rec2){
				$recommendations_array[$i]['reason']= $rec2->reason;
				$recommendations_array[$i]['itemType']= $rec2->itemType;
				$recommendations_array[$i]['itemId']= $rec2->itemId;
				$recommendations_array[$i]['relevance']= $rec2->relevance;
				$i++;
			}
		}
		
		if (!empty($recommendations_array))
			return $recommendations_array;
		else
			return false;
	}
	
	
	
	function generate_common_event( $node, $solution, $event_type )
	{
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $solution );
			
			if ($ini->hasVariable( 'ClientIdSettings', $productid ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', $productid );
			
			}else{
					
				eZLog::write('eZYoochoose: missing MapSettings for '.$productid.' in ClientIdSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}
			
			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
						
			$itemid = $node->NodeID;
						
			$categorypath = $node->PathString;	
			
			$current_user_id = $this->get_current_user_id();
			
			$params = '?productid='.$productid.'&eventtype='.$event_type;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.$categorypath;

			$res = $this->get_html_for_event( $params, $current_user_id );
				
		}else{
			
			eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			
		}
		

		return $res;
		
	}

	
	function generate_buy_event( $node, $solution, $quantity, $price, $currency )
	{
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $solution );
			
			if ($ini->hasVariable( 'ClientIdSettings', $productid ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', $productid );
			
			}else{
					
				eZLog::write('eZYoochoose: missing MapSettings for '.$productid.' in ClientIdSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}
			
			if (!is_int($price)){
				
				eZLog::write('eZYoochoose: use only integer for price', 'error.log', 'var/log');
				return false;
				
			}
			
			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
						
			$itemid = $node->NodeID;
						
			$current_user_id = $this->get_current_user_id();
			
			$params = '?productid='.$productid.'&eventtype=buy';
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'quantity' ).'='.$quantity;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'price' ).'='.$price;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'currency' ).'='.$currency;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'timestamp' ).'='.time();

			$res = $this->get_html_for_event( $params, $current_user_id );
				
		}else{
			
			eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			
		}
		

		return $res;
		
	}
	
	
	function generate_rate_event( $node, $solution, $rating )
	{
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $solution );
			
			if ($ini->hasVariable( 'ClientIdSettings', $productid ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', $productid );
			
			}else{
					
				eZLog::write('eZYoochoose: missing MapSettings for '.$productid.' in ClientIdSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}
			
			if ( !is_int( $rating ) || ( $rating <= 0 ) || ( $rating>=100 ) ) {
				
				eZLog::write('eZYoochoose: use only intger between 0 and 100 for rating', 'error.log', 'var/log');
				return false;
				
			}
			
			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
						
			$itemid = $node->NodeID;
						
			$current_user_id = $this->get_current_user_id();
			
			$params = '?productid='.$productid.'&eventtype=rate';
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'rating' ).'='.$rating;
			
			$res = $this->get_html_for_event( $params, $current_user_id );
				
		}else{
			
			eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			
		}
		

		return $res;
		
	}
	
	
	
	
	function generate_html_from_module_result( $module_result, $solution, $event_type )
	{
		
			$ini = eZINI::instance('ezyoochoose.ini');
	
			if ( $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
				
				$productid = $ini->variable( 'SolutionMapSettings', $solution );
				
				$content_info = $module_result['content_info'];
				
				if ($content_info){
					
					$itemtypeid = $content_info['class_id'];
					$itemid = $content_info['node_id'];
					
					$item_node = eZContentObjectTreeNode::fetch($itemid);
					$categorypath = $item_node->PathString;	
					
					$current_user = eZUser::currentUser ();
					$current_user_id = $current_user->attribute( 'contentobject_id' );
					
					$params = '?productid='.$productid.'&eventtype='.$event_type;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.$categorypath;
								
					$res = $this->get_html( $params );
					
				}else{
					
					$res = false;
					
				}
					
			}else{
				
				eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				
			}
		
		return $res;

	}
	
	
	function get_recommendations($solution, $scenario, $node, $limit, $category_based){

		$ini = eZINI::instance('ezyoochoose.ini');
		
		if ( $ini->hasVariable( 'URLSettings', 'RecoURL' ) && $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ExtensionSettings', 'usedExtension' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' )){
	
			$url = $ini->variable( 'URLSettings', 'RecoURL' );
			$extension = $ini->variable( 'ExtensionSettings', 'usedExtension' );
			
			$productid = $ini->variable( 'SolutionMapSettings', $solution );
			
			if ( $ini->hasVariable( 'ClientIdSettings', $productid ) ){
			
				$client_id = $ini->variable( 'ClientIdSettings', $productid );
				
				$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
						
				$itemid = $node->NodeID;
				
				if ($productid == 'news'){
					$current_user = eZUser::currentUser ();
					$current_user_id = $current_user->attribute( 'contentobject_id' );
				}
				
				$path = '/'.$productid;
				$path .= '/'.$client_id;
				
				if ($productid == 'news'){
					$path .= '/'.$current_user_id;
				}
				
				$path .= '/'.$itemtypeid;
				$path .= '/'.$scenario.'.'.$extension;
				$path .= '?'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.urlencode($itemid);
					
				if ($limit && $ini->hasVariable( 'ParameterMapSettings', 'limit' ) ) {
					$path .= '&'.$ini->variable( 'ParameterMapSettings', 'limit' ).'='.urlencode($limit);
				}
				
				if ($category_based && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) ) {
					$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
					$path .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.urlencode($itemtypeid);
				}
				
				require_once( 'extension/ezyoochoose/classes/ezycfunctions.php' );
				
				$recommendations = ezYCFunctions::send_reco_request($url, $path);
				
				return $this->generate_recommendations_array($recommendations);
			 
			}else{
			
				eZLog::write('eZYoochoose: no clientid found for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');	
				return false;
			
			}
								
		}else{
			
			eZLog::write('eZYoochoose: missing settings in ezyoochoose.ini.', 'error.log', 'var/log');			
			return false;
			
		}
		
			
	}
	
	
	function track_rendered_items( $solution, $itemtypeid, $items )
	{
		
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionMapSettings', $solution ) && $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $solution );
			
			if ($ini->hasVariable( 'ClientIdSettings', $productid ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', $productid );
			
			}else{
					
				eZLog::write('eZYoochoose: missing MapSettings for '.$productid.' in ClientIdSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}
			
			$current_user_id = $this->get_current_user_id();
			
			$params = '?productid='.$productid.'&eventtype=rendered';
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$items;
			$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
			
			$res = $this->get_html_for_rendered_items( $params, $current_user_id );
				
		}else{
			
			eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;
			
		}
		

		return $res;
		
	}
	
	
	var $Operators;
}

?>