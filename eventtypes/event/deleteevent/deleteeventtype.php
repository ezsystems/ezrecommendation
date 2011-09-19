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
class deleteEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_deleteEVENT = "deleteevent";

    /**
     * Constructor of this class
     */
    function deleteEventType()
    {
        // Human readable name of the event displayed in admin interface
        $this->eZWorkflowEventType( deleteEventType::EZ_WORKFLOW_TYPE_deleteEVENT, "ezyoochoose delete object event" );
    }

    function execute( $process, $event )
    {
    	
    	//print_r($process);
    	
		
    	$parameters = $process->attribute( 'parameter_list' );
    	
    	if ($parameters['move_to_trash'] === 2){ // change to 1 if moving to trash should not trigger a delete event to the yoochoose engine
    		
    		return eZWorkflowType::STATUS_ACCEPTED;
    		
    	}else{
    	
	    	$node_list = $parameters['node_id_list'];
	    	
	    	foreach ($node_list as $node){

	    		$node_obj = eZContentObjectTreeNode::fetch($node);
	    		if ($node_obj){
	    			$obj = $node_obj->ContentObject;
	    			$class_id = $obj->ClassID;
	    			
	    			$ycitemtypeid = '';
	
					$arr = eZRecommendationClassAttribute::fetchClassAttributeList($class_id);
					
					if (count($arr['result']) > 0){

						$ycitemtypeid = $arr['result']['ycItemType'];
						if (!empty($ycitemtypeid)){
	    			
	    					$path = $ycitemtypeid.'/'.$node;
    				        eZDebug::writeDebug('ezyoochoose delete event executed.');
	    					ezYCFunctions::delete_item_request($path);

						}
						
					}
				
	    		}
	    	
	    	}
	    		    	

	        
    	}
        return eZWorkflowType::STATUS_ACCEPTED;
    }

}

eZWorkflowEventType::registerEventType( deleteEventType::EZ_WORKFLOW_TYPE_deleteEVENT, "deleteEventType" );

?>
