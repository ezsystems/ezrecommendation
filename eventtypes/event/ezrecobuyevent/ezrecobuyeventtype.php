<?php
/**
 * File containing the eZRecoBuyEventType class
 *
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
class eZRecoBuyEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_EZRECOBUYEVENT = "ezrecobuyevent";

    /**
     * Constructor of this class
     */
    function eZRecoBuyEventType()
    {
        $this->eZWorkflowEventType(
            eZRecoBuyEventType::EZ_WORKFLOW_TYPE_EZRECOBUYEVENT,
            "ezrecommendation buy object event"
        );
        $this->setTriggerTypes( array( 'shop' => array( 'confirmorder' => array( 'before' ) ) ) );
    }

    function execute( $process, $event )
    {

        $parameters = $process->parameterList();


        $order_id = $parameters['order_id'];
        $user_id = $parameters['user_id'];

        if (!empty($order_id) && !empty($user_id)){
            $this->get_products_for_order($order_id, $user_id);
            eZDebug::writeDebug('[ezrecommendation] buy event executed.');
        }else{
            eZLog::write('[ezrecommendation] buy event could not be executed. Missing orderid or userid.', 'error.log', 'var/log');
            eZDebug::writeDebug('[ezrecommendation] buy event could not be executed. Missing orderid or userid.');
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }


    function get_products_for_order($orderid, $userid){

        $db = eZDB::instance();

        $query = "SELECT productcollection_id FROM ezorder WHERE id = $orderid";
        $rows = $db -> arrayQuery( $query );
           $productcollection_id = $rows[0]['productcollection_id'];

        $query = "SELECT currency_code FROM ezproductcollection WHERE id = $productcollection_id";
        $rows = $db -> arrayQuery( $query );
        $currency_code = $rows[0]['currency_code'];

        $query = "SELECT * FROM ezproductcollection_item WHERE productcollection_id = $productcollection_id";
        $product_rows = $db -> arrayQuery( $query );

        $ini = eZINI::instance('ezrecommendation.ini');

        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) && $ini->hasVariable( 'URLSettings', 'RequestURL' )){

            $product = $ini->variable( 'SolutionSettings', 'solution' );

            if ( $ini->hasVariable( 'SolutionMapSettings', $product )){

                $solution = $ini->variable( 'SolutionMapSettings', $product );
                $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
                $url = $ini->variable( 'URLSettings', 'RequestURL' );
                $timestamp = time();

                 foreach ($product_rows as $row){

                     $object_id = $row['contentobject_id'];


                    $main_node_id_query = "SELECT main_node_id, path_string FROM ezcontentobject_tree WHERE contentobject_id = $object_id";
                    $main_node_id_rows = $db -> arrayQuery( $main_node_id_query, array ( 'offset' => 0, 'limit' => 1 ) );
                    $main_node_id = $main_node_id_rows[0]['main_node_id'];
                    $pathString = urlencode(ezRecoTemplateFunctions::getCategoryPath($main_node_id_rows[0]['path_string']));


                     $class_id_query = "SELECT contentclass_id FROM ezcontentobject WHERE id = $object_id";
                    $class_id_rows = $db -> arrayQuery( $class_id_query );
                    $class_id = $class_id_rows[0]['contentclass_id'];

                    $recoitemtypeid = '';

                    $arr = ezRecommendationClassAttribute::fetchClassAttributeList($class_id);

                    if (count($arr['result']) > 0)
                    {
                        $recoitemtypeid = $arr['result']['recoItemType'];

                    }else{
                        $recoitemtypeid = $class_id;
                    }

                    if (!empty($recoitemtypeid))
                    {
                        $count = $row['item_count'];
                        $price = $row['price']*100;

                        $path = '/'.$solution.'/'.$client_id.'/buy'.'/'.$userid.'/'.$recoitemtypeid.'/'.$main_node_id.'?quantity='.$count.'&price='.$price.'&currency='.$currency_code.'&timestamp='.$timestamp.'&categorypath='.$pathString;

                        ezRecoFunctions::send_http_request($url, $path);
                    }else{
                        continue;
                    }

                }


            }else{
                eZLog::write('[ezrecommendation] missing setting in ezrecommendation.ini.', 'error.log', 'var/log');
                return false;
            }

        }else{

            eZLog::write('[ezrecommendation] missing setting in ezrecommendation.ini.', 'error.log', 'var/log');
            return false;

        }

    }

}

eZWorkflowEventType::registerEventType(eZRecoBuyEventType::EZ_WORKFLOW_TYPE_EZRECOBUYEVENT, "eZRecoBuyEventType" );

?>
