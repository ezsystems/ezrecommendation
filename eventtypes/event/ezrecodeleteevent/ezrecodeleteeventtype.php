<?php
/**
 * File containing the eZRecoDeleteEventType class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
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
        $this->eZWorkflowEventType(
            eZRecoDeleteEventType::EZ_WORKFLOW_TYPE_EZRECODELETEEVENT,
            "ezrecommendation delete object event"
        );
        $this->setTriggerTypes( array( 'content' => array( 'delete' => array( 'before' ) ) ) );
    }

    function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );

        // change to 1 if moving to trash should not trigger a delete event to the recommendation engine
        if ( $parameters['move_to_trash'] == 2 )
        {
            return eZWorkflowType::STATUS_ACCEPTED;
        }
        else
        {
            $api = new eZRecommendationServerAPI();
            foreach ( $parameters['node_id_list'] as $nodeID )
            {
                $api->deleteItem( $nodeID );
            }
        }
        return eZWorkflowType::STATUS_ACCEPTED;
    }

}

eZWorkflowEventType::registerEventType( eZRecoDeleteEventType::EZ_WORKFLOW_TYPE_EZRECODELETEEVENT, "eZRecoDeleteEventType" );

?>
