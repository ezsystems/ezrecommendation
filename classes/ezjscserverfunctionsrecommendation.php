<?php
/**
 * File containing the ezRecommendationJsCoreServer class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class ezjscServerFunctionsRecommendation
{
    /**
     * Fetches recommendations for a node
     * @param array $args array( node id, scenario, limit, category_based(true|false, defaut false), track_rendered_items(true|false, default false), create_clickrecommended_event(true|false, default false) )
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
        $requestParameters->scenario = array_shift( $args );
        $availableScenario = $recommendationIni->variable( 'BackendSettings', 'AvailableScenarios' );
        if ( !in_array( $requestParameters->scenario, array_keys( $availableScenario ) ) )
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unknown scenario %scenario. Available scenarios: %available_scenarii',
                    null,
                    array( '%scenario' => $requestParameters->scenario, '%available_scenarii' => implode( ', ', $availableScenario ) )
                )
            );
        }

        // limit argument
        if ( !$requestParameters->limit = array_shift( $args ) )
        {
            $requestParameters->limit = 3;
        }

        $trackRenderedItems = (bool)array_shift( $args );
        $createClickRecommendedEvent = (bool)array_shift( $args );

        $api = new eZRecommendationApi();
        $recommendations = $api->getRecommendations( $requestParameters );

        $tpl = eZTemplate::factory();
        $recommendedNodes = array();
        foreach( $recommendations as $recommendation )
        {
            if ( $object = eZContentObject::fetch( $recommendation['itemId' ] ) )
            {
                if ( empty( $recommendation['category' ] ) )
                {
                    $recommendedNodes[$recommendation['itemId']] = $object->attribute( 'main_node' );
                }
                else 
                {
                    foreach( $object->assignedNodes() as $node )
                    {
                        if ( strpos( $node->attribute('path_string'), $recommendation['category' ] )  !== false )
                        {
                            $recommendedNodes[$recommendation['itemId']] = $node;
                            break;
                        }
                    }
                }
            }
        }

        $tpl->setVariable( 'recommended_nodes', $recommendedNodes );
        $tpl->setVariable( 'track_rendered_items', $trackRenderedItems );
        $tpl->setVariable( 'create_clickrecommended_event', $createClickRecommendedEvent );
        return $tpl->fetch( 'design:ezrecommendation/getrecommendations.tpl' );
    }
}
