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

	$stats = ezRecoFunctions::get_stats_request();

	if ($stats == false){
		$tpl->setVariable('stats_received', false);
	}else{
		$tpl->setVariable('stats_received', true);
		$tpl->setVariable('stats', $stats);	
	}	
	
	$Result = array();
	$Result['content'] = $tpl->fetch( 'design:ezrecommendation/statistics.tpl' ); 
	$Result['left_menu'] = "design:ezrecommendation/leftmenu.tpl";  

	$Result['path'] = array( array( 'url' => 'ezrecommendation/statistics',
                                'text' => 'ezrecommendation statistics' ) );	

?>