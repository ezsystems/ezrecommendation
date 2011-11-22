<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

include_once( 'kernel/classes/ezworkflowtype.php' );


/**
 * Class definition of the custom event called
 * "My first event".
 */
class eZRecoDeleteEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_EZRECODELETEEVENT = "ezrecodeleteevent";

    /**
     * Constructor of this class
     */
    function eZRecoDeleteEventType()
    {
        // Human readable name of the event displayed in admin interface
        $this->eZWorkflowEventType( eZRecoDeleteEventType::EZ_WORKFLOW_TYPE_EZRECODELETEEVENT, "ezrecommendation delete object event" );
    }

    function execute( $process, $event )
    {
    	
    	$parameters = $process->attribute( 'parameter_list' );
    	
    	if ($parameters['move_to_trash'] === 2){ // change to 1 if moving to trash should not trigger a delete event to the recommendation engine
    		
    		return eZWorkflowType::STATUS_ACCEPTED;
    		
    	}else{
    	
	    	$node_list = $parameters['node_id_list'];
	    	
	    	foreach ($node_list as $node){

	    		$node_obj = eZContentObjectTreeNode::fetch($node);
	    		if ($node_obj){
	    			$obj = $node_obj->ContentObject;
	    			$class_id = $obj->ClassID;
	    			
	    			$recoitemtypeid = '';
	
					$arr = ezRecommendationClassAttribute::fetchClassAttributeList($class_id);
					
					if (count($arr['result']) > 0){

						$recoitemtypeid = $arr['result']['recoItemType'];
						if (!empty($recoitemtypeid)){
	    			
	    					$path = $recoitemtypeid.'/'.$node;
    				        eZDebug::writeDebug('ezrecommendation delete event executed.');
	    					ezRecoFunctions::delete_item_request($path);

						}
						
					}
				
	    		}
	    	
	    	}
	    		    	

	        
    	}
        return eZWorkflowType::STATUS_ACCEPTED;
    }

}

eZWorkflowEventType::registerEventType( eZRecoDeleteEventType::EZ_WORKFLOW_TYPE_EZRECODELETEEVENT, "eZRecoDeleteEventType" );

?>
