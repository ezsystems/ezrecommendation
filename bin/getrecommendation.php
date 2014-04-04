#!/usr/bin/env php
<?php
require 'autoload.php';

$script = eZScript::instance(
    array(
        'description' => ( "Gets recommendations for a content id" ),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true
    )
);
$script->startup();

$options = $script->getOptions(
    "[with-api][with-ezjscore]",
    "[node-id]",
    array( 'with-api' => 'Use the eZRecommendationServerAPI (default)', 'with-ezjscore' => 'Use the ezjscore function (uses templates)' )
);

$script->initialize();

$cli = eZCLI::instance();
if ( isset( $options['with-ezjscore'] ) && isset( $options['with-api'] ) )
{
    $cli->error( "--with-api and --with-ezjscore are mutually exclusive" );
    $script->shutdown( 1 );
}

$withApi = !isset( $options['with-ezjscore'] );
$withEzjscore = isset( $options['with-ezjscore'] );

$adminUser = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser(
    $adminUser,
    $adminUser->attribute( 'id' )
);

if ( !isset( $options['arguments'][0] ) )
{
    $cli->error( "Missing node-id argument" );
    $script->shutdown( 1 );
}

if ( !$node = eZContentObjectTreeNode::fetch( (int)$options['arguments'][0] ) )
{
    $cli->error( "Note #" . (int)$options['arguments'][0] . " not found" );
    $script->shutdown( 1 );
}

$scenario = 'top_clicked';

if ( $withApi )
{
    $nodes = getRecommendationsWithAPI( $node, $scenario );
    if ( !count( $nodes ) )
    {
        $cli->output( "No recommended nodes for scenario $scenario" );
    }
    else
    {
        foreach ( $nodes as $node )
        {
            $cli->output( "Node '" . $node->attribute( 'name' ) . "': " . $node->attribute( 'path_string' ) );
        }
    }
}
elseif ( $withEzjscore )
{
    $cli->output( "Recommendations HTML:" );
    echo getRecommendationsWithEzjscore( $node, $scenario );
}

$script->shutdown();

/**
 * Fetches recommendations using ezjscore for eZContentObjectTreNode $node with $scenario
 *
 * @param eZContentObjectTreeNode $node
 * @param string $scenario
 *
 * @throws RuntimeException
 * @throws eZRecommendationApiException
 * @throws InvalidArgumentException
 * @return string recommendation HTML
 */
function getRecommendationsWithEzjscore( eZContentObjectTreeNode $node, $scenario )
{
    $recommendations = ezRecoServerFunctions::getRecommendations(
        array(
            $node->attribute( 'node_id' ),
            $scenario,
            10
        )
    );

    return $recommendations;
}

/**
 * Fetches recommendations using eZRecommendationServerAPI for eZContentObjectTreNode $node with $scenario
 *
 * @param eZContentObjectTreeNode $node
 * @param $scenario
 *
 * @throws RuntimeException
 * @throws eZRecommendationApiException
 * @throws InvalidArgumentException
 * @return eZContentObjectTreeNode[] array of recommended nodes
 */
function getRecommendationsWithApi( eZContentObjectTreeNode $node, $scenario )
{
    $struct = new eZRecommendationApiGetRecommendationsStruct();
    $struct->node = $node;
    $struct->object = $node->object();
    $struct->scenario = $scenario;

    $api = new eZRecommendationServerAPI();
    $recommendations = $api->getRecommendations( $struct );

    $recommendedNodes = array();
    if ( $recommendations !== false )
    {
        foreach ( $recommendations as $recommendation )
        {
            if ( $object = eZContentObject::fetch( $recommendation['itemId'] ) )
            {
                if ( empty( $recommendation['category'] ) )
                {
                    $recommendedNodes[$recommendation['itemId']] = $object->attribute( 'main_node' );
                }
                else
                {
                    foreach ( $object->assignedNodes() as $node )
                    {
                        if ( strpos( $node->attribute( 'path_string' ), $recommendation['category'] ) !== false )
                        {
                            $recommendedNodes[$recommendation['itemId']] = $node;
                            break;
                        }
                    }
                }
            }
        }
    }
    return $recommendedNodes;

}
