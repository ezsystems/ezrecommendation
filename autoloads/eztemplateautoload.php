<?php

/**
 * File containing the ZTemplateOperatorArray
 *
 * @copyright Copyright (C) 2010-2011 yoochoose GmbH. All rights reserved.
 * @license eZ Proprietary Extension License (PEL), Version 1.3
 * @version 1.0.0
 * @package ezyoochoose
 */


$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] =
 array( 'script' => 'extension/ezyoochoose/autoloads/ezyctemplatefunctions.php',
        'class' => 'ezYCTemplateFunctions',
        'operator_names' => array( 	'generate_html',
 									'generate_common_event',
 									'generate_consume_event',
 									'generate_buy_event',
 									'generate_rate_event',
 									'get_recommendations',
 									'track_rendered_items'
 									 ) );
?>