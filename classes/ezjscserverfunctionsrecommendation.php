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

        // scenario argument
        $requestParameters->scenario = array_shift( $args );
        $availableScenarii = $recommendationIni->variable( 'BackendSettings', 'AvailableScenarios' );
        if ( !in_array( $requestParameters->scenario, array_keys( $availableScenarii ) ) )
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unknown scenario %scenario. Available scenarios: %available_scenarii',
                    null,
                    array( '%scenario' => $requestParameters->scenario, '%available_scenarii' => implode( ', ', $availableScenarii ) )
                )
            );
        }

        // limit argument
        if ( !$requestParameters->limit = array_shift( $args ) )
        {
            $requestParameters->limit = 3;
        }

        if ( !$requestParameters->isCategoryBased = array_shift( $args ) )
        {
            $requestParameters->isCategoryBased = false;
        }

        $trackRenderedItems = (bool) array_shift( $args );
        $createClickRecommendedEvent = (bool) array_shift( $args );

        $api = new eZRecommendationApi();
        $recommendations = $api->getRecommendations( $requestParameters );

        $tpl = eZTemplate::factory();
        $recommendedNodes = array();
        foreach( $recommendations as $key => $recommendation )
        {
            if ( $node = eZContentObjectTreeNode::fetch( $recommendation['itemId' ] ) )
            {
                $recommendedNodes[$recommendation['itemId']] = $node;
            }
        }
        $tpl->setVariable( 'recommended_nodes', $recommendedNodes );
        $tpl->setVariable( 'track_rendered_items', $trackRenderedItems );
        $tpl->setVariable( 'create_clickrecommended_event', $createClickRecommendedEvent );
        return $tpl->fetch( 'design:ezrecommendation/getrecommendations.tpl' );
    }
}
