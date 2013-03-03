<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */


$FunctionList = array();

$FunctionList['attribute_list'] = array( 'name' => 'attribute_list',
                                         'call_method' => array( 'class' => 'eZRecommendationFunctionCollection',
                                                                 'method' => 'fetchClassAttributeList' ),
                                         'parameter_type' => 'standard',
                                         'parameters' => array( array( 'name' => 'class_id',
                                                                       'type' => 'integer',
                                                                       'required' => true ) ) );

$FunctionList['recommendation_enable'] = array( 'name' => 'recommendation_enable',
                                         'call_method' => array( 'class' => 'eZRecommendationFunctionCollection',
                                                                 'method' => 'getRecommendationValue' ),
                                         'parameter_type' => 'standard',
                                         'parameters' => array( array( 'name' => 'xmlDataText',
                                                                       'type' => 'string',
                                                                       'required' => true ) )
                                          );

$FunctionList['currency_list'] = array( 'name' => 'currency_list',
                                         'call_method' => array( 'class' => 'eZRecommendationFunctionCollection',
                                                                 'method' => 'getCurrencyValues' ),

                                          );

$FunctionList['scenario_list'] = array(
    'name' => 'scenario_list',
    'call_method' => array(
        'class' => 'eZRecommendationFunctionCollection',
        'method' => 'getAvailableScenario',
    ),
    'parameter_type' => 'standard',
    'parameters' => array()
);
?>
