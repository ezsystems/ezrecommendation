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
     * @param array $args array( node id, scenario, limit, category_based(true|false, defaut false), track_rendered_items(true|false, default false), create_clickrecommended_event(true|false, default false) )
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

        $trackRenderedItems = (bool)array_shift( $args );
        $createClickRecommendedEvent = (bool)array_shift( $args );

        $api = new eZRecommendationServerAPI();

        $time = microtime( true );

        $recommendations = $api->getRecommendations( $requestParameters );

        $time = microtime( true ) - $time;
        $debugInfo = array(
            'request_parameters' => (array)$requestParameters,
            'request_time' => $time,
            'response' => $recommendations
        );

        /// to speed up the process a bit, we add a cache here, based on
        //       - scenario
        //       - returned data (excluding reason, relevance...)
        //       - current user policies
        $ttl = $recommendationIni->variable( 'PerformanceSettings', 'RecommendationTTL' );
        if ( $ttl )
        {
            /// @todo read this path from site.ini
            $cacheDir = eZSys::cacheDirectory() . '/ezrecommendation/blocks';
            $cacheHashArray = array();
            $cacheHashArray[0] = $requestParameters->scenario;
            $cacheHashArray[1] = implode( '.', $user->roleIDList() );
            $cacheHashArray[2] = implode( '.', $user->limitValueList() );
            /// @todo allow user via ini settings to set cache tweaks: fe:
            ///       - include reason, relevance in key calculation
            ///       - add in cache key calculation something related to eZRecommendationApi::getValidityByNode
            $cacheHashArray[3] = '';
            foreach ( $recommendations as $recommendation )
            {
                $cacheHashArray[3] .= $recommendation['itemId'] . ':' . $recommendation['category'] . '.';
            }
            $cacheFile = md5( implode( '-', $cacheHashArray ) ) . '.cache';
            // we split path to file on multiple levels, in case many are generated
            $cacheFile = $cacheDir . '/' . $cacheFile[0] . '/' . $cacheFile[1] . '/' . $cacheFile;

            $clusterFile = eZClusterFileHandler::instance( $cacheFile );
            if ( $clusterFile->exists() )
            {
                $cacheDate = $clusterFile->mtime();
                if ( $cacheDate + $ttl >= time() )
                {
                    return $clusterFile->fetchContents();
                }
            }
        }

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
        $tpl->setVariable( 'debug_info', $debugInfo );

        $out = $tpl->fetch( 'design:ezrecommendation/getrecommendations.tpl' );

        if ( $ttl )
        {
            $clusterFile->storeContents( $out );
        }

        return $out;
    }
}
