<?php

/**
 * File containing the eZyoochooseFunctions module implementation
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

$module = array( 'name' => 'ezyoochoose' );
$ViewList = array();
$ViewList['request'] = array( 'script' => 'request.php',
                               'functions' => array( 'request' ));
$ViewList['statistics'] = array( 'script' => 'statistics.php',
                               'functions' => array( 'statistics' ));
							   
$ViewList['consume'] = array( 'script' => 'consume.php',
                               'functions' => array( 'consume' ));
							   
						   

$FunctionList = array(); 
$FunctionList['request'] = array(); 
$FunctionList['statistics'] = array(); 
$FunctionList['consume'] = array(); 


 
?>