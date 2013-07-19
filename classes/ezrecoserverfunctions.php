<?php
/**
 * File containing the ezRecoServerFunctions class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class ezRecoServerFunctions
{
    /**
     * Fetches recommendations for a node
     * @param array $args
     *              array(
     *                  node id, scenario, limit,
     *                  category_based(true|false, defaut false),
     *                  track_rendered_items(true|false, default false),
     *                  create_clickrecommended_event(true|false, default false) )
     * @throws InvalidArgumentException
     * @return string
     */
    public static function getRecommendations( $args )
    {
        $requestParameters = new eZRecommendationApiGetRecommendationsStruct;

        if ( count( $args ) < 3 || count( $args ) > 6 )
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'This action requires 3 parameters (%arguments given)',
                    null,
                    array( '%arguments' => count( $args ) )
                )
            );
        }

        $recommendationIni = eZINI::instance( 'ezrecommendation.ini' );

        // node ID argument
        $nodeId = array_shift( $args );
        if ( !$node = eZContentObjectTreeNode::fetch( $nodeId ) )
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unable to load node #%nodeid',
                    null,
                    array( '%nodeid' => $nodeId )
                )
            );
        }
        $requestParameters->node = $node;
        $requestParameters->object = $node->attribute( 'object' );

        // scenario argument
        $api = new eZRecommendationAPI;
        $requestParameters->scenario = array_shift( $args );
        $availableScenarios = $api->getScenarioList();
        if ( !in_array( $requestParameters->scenario, array_keys( $availableScenarios ) ) )
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unknown scenario %scenario. Available scenarios: %available_scenarios',
                    null,
                    array(
                        '%scenario' => $requestParameters->scenario,
                        '%available_scenarios' => implode( ', ', array_keys( $availableScenarios ) )
                    )
                )
            );
        }

        // limit argument
        if ( !$requestParameters->limit = array_shift( $args ) )
        {
            $requestParameters->limit = 3;
        }

        if ( ( $itemTypeId = array_shift( $args ) ) )
            $requestParameters->itemTypeId = $itemTypeId;

        $trackRenderedItems = (bool)array_shift( $args );
        $createClickRecommendedEvent = (bool)array_shift( $args );

        $api = new eZRecommendationServerAPI();
        $recommendations = $api->getRecommendations( $requestParameters );

        $tpl = eZTemplate::factory();
        $recommendedNodes = array();
        foreach ( $recommendations as $recommendation )
        {
            if ( $object = eZContentObject::fetch( $recommendation['itemId' ] ) )
            {
                if ( empty( $recommendation['category' ] ) )
                {
                    $recommendedNodes[$recommendation['itemId']] = $object->attribute( 'main_node' );
                }
                else
                {
                    foreach ( $object->assignedNodes() as $node )
                    {
                        if ( strpos( $node->attribute( 'path_string' ), $recommendation['category' ] ) !== false )
                        {
                            $recommendedNodes[$recommendation['itemId']] = $node;
                            break;
                        }
                    }
                }
            }
        }

        $tpl->setVariable( 'recommended_nodes', $recommendedNodes );
        $tpl->setVariable( 'scenario', $requestParameters->scenario );
        $tpl->setVariable( 'track_rendered_items', $trackRenderedItems );
        $tpl->setVariable( 'create_clickrecommended_event', $createClickRecommendedEvent );
        return $tpl->fetch( 'design:ezrecommendation/getrecommendations.tpl' );
    }
}
