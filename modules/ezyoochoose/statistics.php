<?php

/**
 * File containing the eZyoochooseFunctions request implementation
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

	$tpl = eZTemplate::factory();

	$stats = ezYCFunctions::get_stats_request();

	if ($stats == false){
		$tpl->setVariable('stats_received', false);
	}else{
		$tpl->setVariable('stats_received', true);
		$tpl->setVariable('stats', $stats);	
	}	
	
	$Result = array();
	$Result['content'] = $tpl->fetch( 'design:ezyoochoose/statistics.tpl' ); 
	$Result['left_menu'] = "design:ezyoochoose/leftmenu.tpl";  

	$Result['path'] = array( array( 'url' => 'ezyoochoose/statistics',
                                'text' => 'ezyoochoose statistics' ) );	

?>