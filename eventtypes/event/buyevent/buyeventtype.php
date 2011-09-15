<?php


/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

include_once( 'kernel/classes/ezworkflowtype.php' );


/**
 * Class definition of the custom event called
 * "My first event".
 */
class buyEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_BUYEVENT = "buyevent";

    /**
     * Constructor of this class
     */
    function buyEventType()
    {
        // Human readable name of the event displayed in admin interface
        $this->eZWorkflowEventType( buyEventType::EZ_WORKFLOW_TYPE_BUYEVENT, "ezyoochoose buy event" );
    }

    function execute( $process, $event )
    {

    	$parameters = $process->parameterList();
    	$order_id = $parameters['order_id'];
    	$user_id = $parameters['user_id'];
    	
    	$this->get_products_for_order($order_id, $user_id);
        eZDebug::writeDebug('ezyoochoose buy event executed.');
        return eZWorkflowType::STATUS_ACCEPTED;
    }
    
    
    function get_products_for_order($orderid, $userid){
    	
    	$db = eZDB::instance(); 
		
        $query = "SELECT `productcollection_id` FROM `ezorder` WHERE `id` = $orderid"; 
        $rows = $db -> arrayQuery( $query ); 
       	$productcollection_id = $rows[0]['productcollection_id'];
       	
    	$query = "SELECT `currency_code` FROM `ezproductcollection` WHERE `id` = $productcollection_id"; 
        $rows = $db -> arrayQuery( $query ); 
        $currency_code = $rows[0]['currency_code'];
        
        $query = "SELECT * FROM `ezproductcollection_item` WHERE `productcollection_id` = $productcollection_id"; 
        $product_rows = $db -> arrayQuery( $query ); 
		
        $ini = eZINI::instance('ezyoochoose.ini');
		
        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) && $ini->hasVariable( 'URLSettings', 'RequestURL' )){
			
        	$product = $ini->variable( 'SolutionSettings', 'solution' );
        	
        	if ( $ini->hasVariable( 'SolutionMapSettings', $product )){

        		$solution = $ini->variable( 'SolutionMapSettings', $product );
        		$client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
				$url = $ini->variable( 'URLSettings', 'RequestURL' );
				$timestamp = time();
				
				 foreach ($product_rows as $row){

				 	$object_id = $row['contentobject_id'];
				 	
				 	
        			$main_node_id_query = "SELECT `main_node_id` FROM `ezcontentobject_tree` WHERE `contentobject_id` = $object_id"; 
        			$main_node_id_rows = $db -> arrayQuery( $main_node_id_query ); 
        			$main_node_id = $main_node_id_rows[0]['main_node_id'];			 	
				 	
				 	$class_id_query = "SELECT `contentclass_id` FROM `ezcontentobject` WHERE `id` = $object_id"; 
        			$class_id_rows = $db -> arrayQuery( $class_id_query );
        			$class_id = $class_id_rows[0]['contentclass_id'];
        			
        			$ycitemtypeid = '';

					$arr = eZRecommendationClassAttribute::fetchClassAttributeList($class_id);
					
					if (count($arr['result']) > 0)
					{
						$ycitemtypeid = $arr['result']['ycItemType'];
						
					}else{
						$ycitemtypeid = $class_id;
					}
					
					if (!empty($ycitemtypeid))
					{
			        	$count = $row['item_count'];
			        	$price = $row['price']*100;
			        	
			        	$path = '/'.$solution.'/'.$client_id.'/buy'.'/'.$userid.'/'.$ycitemtypeid.'/'.$main_node_id.'?quantity='.$count.'&price='.$price.'&currency='.$currency_code.'&timestamp='.$timestamp;
						
			        	ezYCFunctions::send_http_request($url, $path);
					}else{
						continue;
					}
			 	  	      	
		        }


        	}else{
        		eZLog::write('eZYoochoose: missing setting in ezyoochoose.ini.', 'error.log', 'var/log');	
				return false;
        	}
		
		}else{
			
			eZLog::write('eZYoochoose: missing setting in ezyoochoose.ini.', 'error.log', 'var/log');	
			return false;
			
		}
            	
    }
    
}

eZWorkflowEventType::registerEventType( buyEventType::EZ_WORKFLOW_TYPE_BUYEVENT, "buyEventType" );

?>
