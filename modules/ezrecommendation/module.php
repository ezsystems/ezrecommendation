<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

$Module = array( "name" => "eZrecommendation" );

$ViewList = array();
$ViewList['request'] = array( 'script' => 'request.php',
                               'functions' => array( 'request' ));
$ViewList['statistics'] = array( 'script' => 'statistics.php',
                                'default_navigation_part' => 'ezsetupnavigationpart',
                                   'functions' => array( 'statistics' ));

$ViewList['consume'] = array( 'script' => 'consume.php',
                               'functions' => array( 'consume' ));
$FunctionList = array();
$FunctionList['request'] = array();
$FunctionList['statistics'] = array();
$FunctionList['consume'] = array();

?>
