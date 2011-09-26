<?php

/**
 * File containing the eZyoochooseFunctions class for generating the html output
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

include_once( 'lib/ezutils/classes/ezini.php' );


class ezYCTemplateFunctions {


	
	function ezYCTemplateFunctions() {
		$this->Operators = array( 	'generate_html',
									'generate_common_event',		
									'generate_consume_event',		
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
	                        'event_type' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'generate_common_event' => array(
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' ),		 
	                        'event_type' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'generate_consume_event' => array(
	                        'node' => array( 'type' => 'array',
	                                            'required' => true,
	                                            'default' => '' )		 
								),
						'generate_buy_event' => array(
	                        'node' => array( 'type' => 'array',
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
	                        'rating' => array( 'type' => 'string',
	                                            'required' => true,
	                                            'default' => '' )
								),
						'get_recommendations' => array(
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
																$namedParameters['event_type']
													 );
				} break;
			case 'generate_common_event':
				{
					$operatorValue = $this->generate_common_event( 
																$namedParameters['node'],
																$namedParameters['event_type']
													 );
				} break;
			case 'generate_consume_event':
				{
					$operatorValue = $this->generate_consume_event( 
																$namedParameters['node']									
													 );
				} break;
			case 'generate_buy_event':
				{
					$operatorValue = $this->generate_buy_event( 
																$namedParameters['node'],
																$namedParameters['quantity'],
																$namedParameters['price'],
																$namedParameters['currency']
													 );
				} break;
			case 'generate_rate_event':
				{
					$operatorValue = $this->generate_rate_event( 
																$namedParameters['node'],
																$namedParameters['rating']
													 );
				} break;
			case 'get_recommendations':
				{
					$operatorValue = $this->get_recommendations( 
																$namedParameters['scenario'],
																$namedParameters['node'],
																$namedParameters['limit'],
																$namedParameters['category_based']
													 );
				} break;
			case 'track_rendered_items':
				{
					$operatorValue = $this->track_rendered_items( 
																$namedParameters['itemtypeid'],
																$namedParameters['items']																
													 );
				} break;
		 
		}
	}

	
	function get_server_url() {
		
		$ezurlop = new eZURLOperator();
		
		$sys = $ezurlop->Sys;
		
		$access_path = $sys->AccessPath;
		
		$siteaccess_url = $access_path['siteaccess']['url'];
		
		if ($_SERVER['HTTPS'] == 'on'){
			
			$path = 'https://';
		
		}else{

			$path = 'http://';
		
		}
			
		$path = $path.$_SERVER['HTTP_HOST'];
		
		if ($sys->WWWDir != ''){
			
			$www_dir = $sys->WWWDir;
			$path = $path.$www_dir;
			
		}

		if ($sys->IndexFile != ''){
			
			$index_file = $sys->IndexFile;
			$path = $path.$index_file;
			
		}
		
		if (count($siteaccess_url)>0){
			
			$path = $path.'/'.$siteaccess_url[0];
			
		}
		
		return $path;		
	}
	
	
	function get_current_url() {
		
		$moduleURL = '/ezyoochoose/request';
		$serverURL = $this->get_server_url();
		
		return $serverURL.$moduleURL;
	}
	
			 
	function get_html( $params ) {
		
		$serverURL = '/ezyoochoose/request';
		
		$res = $serverURL.$params;
						
		return $res;
	}	 

	function get_url_for_consume_event( $params ) {
		
		$serverURL = $this->get_current_url();
		
		$res = $serverURL.$params;
						
		return $res;
	}

	
	function get_html_for_event( $params, $userid ) {
		
		$serverURL = $this->get_current_url();
		
		$res = 'onclick="ezyc.evt(\''.$serverURL.$params.'\', '.$userid.')"';
						
		return $res;
	}

	

	function get_html_for_rendered_items( $params, $userid, $i ) {
		
		$time = 500;
		$serverURL = $this->get_current_url();
		
		$res = '<script type="text/javascript">setTimeout("ezyc.evt(\''.$serverURL.$params.'\', '.$userid.')",'.$time*$i.')</script>';
						
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
		else{
			eZLog::write('[ezyoochoose] no recommendations received.', 'error.log', 'var/log');
			return false;
		}
	}
	
	
	
	function generate_consume_event( $node ){
	
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
				
			if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
			
			}else{
					
				eZLog::write('[ezyoochoose] missing CustomerID in ClientIdSettings in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}

			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);

			$ycitemtypeid = '';

			$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
			
			if (count($arr['result']) > 0)
			{
				$ycitemtypeid = $arr['result']['ycItemType'];
				
			}
			
			if (!empty($ycitemtypeid))
			{

				$itemid = $node->NodeID;
							
				$categorypath = $node->PathString;	
				
				$current_user_id = $this->get_current_user_id();
				/////////////////////////
				$mynodeArray = $node->attribute( 'data_map' );
			
				foreach ($mynodeArray as $contentObjectAttr)
				{		
					if($contentObjectAttr->DataTypeString == "ezrecommendation"){
							$dataTextXml = $contentObjectAttr->DataText;
							 $isEnableReco = ezyRecommendationXml::getNodeAttributeValue($dataTextXml, 'recommendation-enable')	;
							break 1;
					}		

				}
			
				//////////////////////////
				$params = '?productid='.$productid.'&eventtype=consume';
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$itemtypeid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.$categorypath;

				$res = '<div id="ezyc-consume-event">'.$this->get_url_for_consume_event( $params ).'</div><div id="ezyc-consume-event-userid">'.$current_user_id.'</div>';
				
			}
			else
			{
				eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');
				return false;
			
			}
	
		}else{
			eZLog::write('[ezyoochoose] missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;	
			
		}
		
		return $res;
				
	}
	
	function generate_common_event( $node, $event_type )
	{

		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
			
			
			if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
			
			}else{
					
				eZLog::write('[ezyoochoose] missing CustomerID in ClientIdSettings in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;				
				
			}

			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);

			$ycitemtypeid = '';

			$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
			
			if (count($arr['result']) > 0)
			{
				$ycitemtypeid = $arr['result']['ycItemType'];
				
			}
			
			if (!empty($ycitemtypeid))
			{

				$itemid = $node->NodeID;
							
				$categorypath = $node->PathString;	
				
				$current_user_id = $this->get_current_user_id();
				/////////////////////////
				/*$mynodeArray = $node->attribute( 'data_map' );
			
				foreach ($mynodeArray as $contentObjectAttr)
				{		
					if($contentObjectAttr->DataTypeString == "ezrecommendation"){
							$dataTextXml = $contentObjectAttr->DataText;
							 $isEnableReco = ezyRecommendationXml::getNodeAttributeValue($dataTextXml, 'recommendation-enable')	;
							break 1;
					}		

				}
				if ( $isEnableReco != 1 and $event_type == 'clickrecommended')
					return false;
				*/			
				//////////////////////////
				$params = '?productid='.$productid.'&eventtype='.$event_type;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$ycitemtypeid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.$categorypath;

				$res = $this->get_html_for_event( $params, $current_user_id );
			}
			else
			{
				eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');
				return false;
			
			}
	
		}else{
			eZLog::write('[ezyoochoose] missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;
			
		}
		
		return $res;
		
	}

	
	function generate_buy_event( $node, $quantity, $price, $currency )
	{
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
			if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
			
			}else{
					
				eZLog::write('[ezyoochoose] missing CustomerID in ClientIdSettings in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;					
				
			}
			
			if (!is_int($price)){
				
				eZLog::write('[ezyoochoose] use only integer for price', 'error.log', 'var/log');
				return false;
				
			}
			
			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
			
			$ycitemtypeid = '';

			$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
			
			if (count($arr['result']) > 0)
			{
				$ycitemtypeid = $arr['result']['ycItemType'];
				
			}
			
			if (!empty($ycitemtypeid))
			{
			
				$itemid = $node->NodeID;
							
				$current_user_id = $this->get_current_user_id();
				
				$params = '?productid='.$productid.'&eventtype=buy';
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$ycitemtypeid ;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'quantity' ).'='.$quantity;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'price' ).'='.$price;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'currency' ).'='.$currency;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'timestamp' ).'='.time();

				$res = $this->get_html_for_event( $params, $current_user_id );
				
			}else{
				eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');

				return false;
			}	
		}else{
			
			eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;
			
		}
		
		return $res;
		
	}
	
	
	function generate_rate_event( $node, $rating )
	{
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
			if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
			
			}else{
					
				eZLog::write('[ezyoochoose] missing CustomerID in ClientIdSettings in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;					
				
			}
			
			if ( !is_int( $rating ) || ( $rating <= 0 ) || ( $rating>=100 ) ) {
				
				eZLog::write('[ezyoochoose] use only integer between 0 and 100 for rating', 'error.log', 'var/log');
				return false;
				
			}
			
			$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
			
			$ycitemtypeid = '';

			$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
			
			if (count($arr['result']) > 0)
			{
				$ycitemtypeid = $arr['result']['ycItemType'];
				
			}
			
			if (!empty($ycitemtypeid))
			{			
				$itemid = $node->NodeID;
							
				$current_user_id = $this->get_current_user_id();
				
				$params = '?productid='.$productid.'&eventtype=rate';
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$ycitemtypeid ;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
				$params .= '&'.$ini->variable( 'ParameterMapSettings', 'rating' ).'='.$rating;
				
				$res = $this->get_html_for_event( $params, $current_user_id );
			}else{
				eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');

				return false;
			}	
		}else{
			
			eZLog::write('[ezyoochoose] missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;
			
		}

		return $res;
		
	}
	
	
	
	
	function generate_html_from_module_result( $module_result, $event_type )
	{
		
			$ini = eZINI::instance('ezyoochoose.ini');
	
			if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
				
				$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
				
				$content_info = $module_result['content_info'];
				
				if ($content_info){				
				
					
					$itemtypeid = $content_info['class_id'];

					
					$ycitemtypeid = '';

					$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
					
					if (count($arr['result']) > 0)
					{
						$ycitemtypeid = $arr['result']['ycItemType'];
						
					}
					
					if (!empty($ycitemtypeid))
					{
	
						$itemid = $content_info['node_id'];
						
						$item_node = eZContentObjectTreeNode::fetch($itemid);
						$categorypath = $item_node->PathString;	
						
						$current_user = eZUser::currentUser ();
						$current_user_id = $current_user->attribute( 'contentobject_id' );
						
						$params = '?productid='.$productid.'&eventtype='.$event_type;
						$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$ycitemtypeid;
						$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$itemid;
						$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
						$params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.$categorypath;
									
						$res = $this->get_html( $params );
					}else{
						eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');

						return false;
					}
					
				}else{
					eZLog::write('[ezyoochoose] could not generate ezyoochoose-pixel. please check the include call in your pagelayout.tpl.', 'error.log', 'var/log');	

					$res = false;
					
				}
					
			}else{
				
				eZLog::write('eZYoochoose: missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;

				
			}
		
		return $res;

	}
	
	
	function get_recommendations( $scenario, $node, $limit, $category_based=false){

		$ini = eZINI::instance('ezyoochoose.ini');
		
		if ( $ini->hasVariable( 'URLSettings', 'RecoURL' ) && $ini->hasVariable( 'ExtensionSettings', 'usedExtension' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' )){
	
			$url = $ini->variable( 'URLSettings', 'RecoURL' );
			$extension = $ini->variable( 'ExtensionSettings', 'usedExtension' );
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
			if ( $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
	
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
				
				$itemtypeid = eZContentClass::classIDByIdentifier($node->ClassIdentifier);
				
				
				$ycitemtypeid = '';

				$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($itemtypeid);
				
				if (count($arr['result']) > 0)
				{
					$ycitemtypeid = $arr['result']['ycItemType'];
					
				}
				
				if (!empty($ycitemtypeid))
				{
					
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
					
					//$path .= '/'.$ycitemtypeid;
					
					$path .= '/'.$scenario.'.'.$extension;
					$path .= '?'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.urlencode($itemid);
						
					if ($limit && $ini->hasVariable( 'ParameterMapSettings', 'limit' ) ) {
						$path .= '&'.$ini->variable( 'ParameterMapSettings', 'limit' ).'='.urlencode($limit);
					}

					if ($ini->hasVariable( 'ParameterMapSettings', 'class_id' ) ) {
						
						$path .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.urlencode($ycitemtypeid);
						
					}
					
					require_once( 'extension/ezyoochoose/classes/ezycfunctions.php' );

					$recommendations = ezYCFunctions::send_reco_request($url, $path);

					return $this->generate_recommendations_array($recommendations);
					
					
				}else{
					eZLog::write('[ezyoochoose] ez-classid could not be mapped to a ezyoochoose-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezyoochoose type.', 'error.log', 'var/log');

					return false;
				}
			 
			}else{
			
				eZLog::write('eZYoochoose: no clientid found for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');	
				return false;
			
			}
								
		}else{
			
			eZLog::write('eZYoochoose: missing settings in ezyoochoose.ini.', 'error.log', 'var/log');			
			return false;
			
		}
		
			
	}
	
	
	function track_rendered_items( $items_array )
	{
		$sorted_array = array();
		foreach ($items_array as $key => $value){
			$k = str_replace("\"", "", $key);
			$val =  str_replace("\"", "", $value);

			if (empty($sorted_array[$val])){
				$sorted_array[$val] = $k; 
			}else{
				$sorted_array[$val] = $sorted_array[$val].','.$k;
			}
			
		}
		
		$ini = eZINI::instance('ezyoochoose.ini');

		if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'node_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) ){
			
			$productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
			
			if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) ){
				 
				$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
			
			}else{
					
				eZLog::write('[ezyoochoose] missing CustomerID in ClientIdSettings in ezyoochoose.ini.', 'error.log', 'var/log');
				return false;			
				
			}
			
			
			$res = '';
			
			$i = 0;
			foreach ($sorted_array as $key => $value){
				
				$ycitemtypeid = '';
	
				$arr = ezYCRecommendationClassAttribute::fetchClassAttributeList($key);
					
				if (count($arr['result']) > 0)
				{
					$ycitemtypeid = $arr['result']['ycItemType'];
						
				}
				
				if (!empty($ycitemtypeid))
				{
					$i++;
					
					$current_user_id = $this->get_current_user_id();
						
					$params = '?productid='.$productid.'&eventtype=rendered';
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$ycitemtypeid;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'node_id' ).'='.$value;
					$params .= '&'.$ini->variable( 'ParameterMapSettings', 'user_id' ).'='.$current_user_id;
						
					
					$res = $res.''.$this->get_html_for_rendered_items( $params, $current_user_id, $i );
					
				}else{
				
					continue;
				}
					
			}
			
			
		}
		else
		{
			
			eZLog::write('[ezyoochoose] missing MapSettings in generate_html_from_module_result function for ezyoochoose extension in ezyoochoose.ini.', 'error.log', 'var/log');
			return false;
			
		}
		

		return $res;
		
	}
	
	
	var $Operators;
}

?>