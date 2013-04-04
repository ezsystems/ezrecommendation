<?php
/**
 * File containing the eZRecoExportEventType class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */


include_once( 'kernel/classes/ezworkflowtype.php' );


class eZRecoExportEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE = "ezrecoexportevent";

    function eZRecoExportEventType()
    {
        $this->eZWorkflowEventType(
            eZRecoExportEventType::EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE,
            "ezrecommendation export object event"
        );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ) ) ) );
    }


    function execute( $process, $event )
    {
        //Get the objectID
        $processParameters = $process->attribute( 'parameter_list' );

        $objectID = $processParameters['object_id'];

        $api = new eZRecommendationApi();
        $api->exportObject( $objectID );
        return eZWorkflowType::STATUS_ACCEPTED;
    }


    public static function getNodeId($obj_id)
    {
        $db = eZDB::instance();

        $query = "SELECT node_id FROM ezcontentobject_tree WHERE contentobject_id = $obj_id ORDER BY modified_subnode DESC limit 0,1";
        $rows = $db -> arrayQuery( $query );
        return $rows[0]['node_id'];
    }
}

eZWorkflowEventType::registerEventType( eZRecoExportEventType::EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE, "eZRecoExportEventType" );

?>
