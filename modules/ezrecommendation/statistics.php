<?php

/**
 * File containing the eZRecommendationFunctions request implementation
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

$tpl = eZTemplate::factory();

try
{
    $stats = ezRecoFunctions::get_stats_request();
}
catch ( Exception $e )
{
    $stats = false;
    eZDebug::writeError( $e, "An exception occured" );
}

if ( $stats == false )
{
    $tpl->setVariable( 'stats_received', false );
}
else
{
    $tpl->setVariable( 'stats_received', true );
    $tpl->setVariable( 'stats', $stats );
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:ezrecommendation/statistics.tpl' );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'extension/ezrecommendation/statistics', 'eZ Recommendation statistics' ) ) );
?>