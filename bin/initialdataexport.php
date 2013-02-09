#!/usr/bin/env php
<?php
/**
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */
$previousMemoryUsage = memory_get_usage( true );

require 'autoload.php';
require_once 'extension/ezrecommendation/classes/ezrecommendationclassattribute.php';

$cli = eZCLI::instance();
$endl = $cli->endlineString();

$script = eZScript::instance();
$script->startup();
$script->initialize();
$db = eZDB::instance();
$ini = eZINI::instance( 'ezrecommendation.ini' );

$options = $script->getOptions(
    "[split:][classgroup:][parent_tree:][limit:][global_offset:]",
    "",
    array(
        'split' => 'Define how many entrys are defined in each ezrecommendation initial XML export file. ',
        'classgroup' => 'Filter classes by group, default group is 1 (Content)',
        'parent_tree' => 'Subtree parent node ID. Default is 2.',
        'memory-debug' => 'Initial offset for query. Used only in special cases where memory is a problem.',
    )
);

$split = $options['split'];
if ( !$split )
{
    // default initial XML export split value
    $split = $ini->variable( 'BulkExportSettings', 'XmlEntries' );

    if ( empty( $split ) )
    {
        $cli->output( 'Missing XmlEntries parameter in ezrecommendation.ini' );
        $script->shutdown( 1 );
    }
}

$classgroup = $options['classgroup'];
if ( !$classgroup )
    $classgroup = 1;
$solution = $ini->variable( 'SolutionSettings', 'solution' );

if ( empty( $solution ) )
{
    $cli->output( 'Missing solution in ezrecommendation.ini' );
    $script->shutdown( 1 );
}

$parent_tree = $options['parent_tree'];
if ( !$parent_tree )
    $parent_tree = 2; //Home

$limit = $options['limit'];
if ( !$limit )
    $limit = 1000;

$global_offset = $options['global_offset'];

$url = $ini->variable( 'BulkExportSettings', 'SiteURL' );
$path = $ini->variable( 'BulkExportSettings', 'BulkPath' );
if ( empty( $url ) || empty( $path ) )
{
    $cli->output( 'Missing SiteURL or BulkPath in ezrecommendation.ini' );
    $script->shutdown( 1 );
}
// Check paths
if ( substr( $url, -1 ) == '/' || $path[0] == '/' )
{
    $cli->output( "SiteURL must not end with a '/' and BulkPath must not begin with '/' in ezrecommendation.ini" );
    $script->shutdown( 1 );
}

$cli->output( 'Starting script.' );
$class_group = '1'; //ezcontentclass_classgroup

$classRows = $db->arrayQuery( "SELECT `id`
                                FROM `ezcontentclass`
                                LEFT JOIN `ezcontentclass_classgroup` ON ( `contentclass_id` = `id` )
                                WHERE `id`
                                IN (

                                SELECT DISTINCT (
                                `contentclass_id`
                                )
                                FROM `ezcontentclass_attribute`
                                WHERE `data_type_string` = 'ezrecommendation'
                                )
                                AND group_id  IN (" . $class_group . ")
                                GROUP BY id "
);
$classArray = array();
foreach ( $classRows as $classRow )
{
    $classArray[] = $classRow['id'];
}
unset( $classRows );
printMemoryUsageDelta( "bootstrap ", $previousMemoryUsage, $cli );

$cli->output( "Exporting objects" );
$provider = new eZRecoInitialExportProvider( $classArray, $parent_tree, $cli, $db );
$cli->output( "Total objects: " . $provider->getItemsCount() );

$cli->output( "Generating XML file(s)" );
$domDocument = new DOMDocument( '1.0', 'utf-8' );
$domDocumentRoot = $domDocument->createElement( 'items' );
$domDocumentRoot->setAttribute( 'version', 1 );
$domDocument->appendChild( $domDocumentRoot );
$exportedElements = 0; $xmlFiles = array();
$initialMemoryUsage = $previousMemoryUsage = memory_get_usage();
while( $node = $provider->getNext() )
{
    $objectData = eZRecoInitialExport::generateNodeData( $node, $solution, $cli );
    $domNode = $domDocument->createElement( 'item' );
    $domDocumentRoot->appendChild( $domNode );
    eZRecoInitialExport::generateDOMNode( $objectData, $domDocument, $domNode );
    $exportedElements++;

    printMemoryUsageDelta( '- export iteration', $previousMemoryUsage, $cli );

    // file limit reached, write data to output file
    if ( $exportedElements == $split )
    {
        $cli->output( "Writing $exportedElements entries to disk... ", false );
        $filename = eZRecoInitialExport::writeXmlFile( $domDocument, $path );
        $cli->output( " done ($filename)" );
        $domDocument = new DOMDocument( '1.0', 'utf-8' );
        $domDocumentRoot = $domDocument->createElement( 'items' );
        $domDocumentRoot->setAttribute( 'version', 1 );
        $domDocument->appendChild( $domDocumentRoot );

        $exportedElements = 0;
        $xmlFiles[] = $filename;

        printMemoryUsageDelta( "write operation", $previousMemoryUsage, $cli );
    }
}
printMemoryUsageDelta( "XML export total", $initialMemoryUsage, $cli );

$memoryUsage = memory_get_usage( true );
if ( $exportedElements > 0 )
{
    $cli->output( "Writing $exportedElements entries to disk... ", false );
    $filename = eZRecoInitialExport::writeXmlFile( $domDocument, $path );
    $cli->output( " done ($filename)" );
}
printMemoryUsageDelta( "XML writing total", $memoryUsage, $cli );

// Store files to cluster, and send HTTP request
$memoryUsage = memory_get_usage( true );
$clusterHandler = eZClusterFileHandler::instance();
foreach( $xmlFiles as $xmlFile )
{
    $cli->output( "Sending $xmlFile... ", false );
    $clusterHandler->fileStore( $xmlFile );
    try
    {
        ezRecoFunctions::send_bulk_request( $url, $path, $xmlFiles );
    }
    catch( Exception $e )
    {
        $cli->error( "failure" );
        $cli->error( $e->getMessage() );
    }
    $cli->output( "done" );
}
printMemoryUsageDelta( "HTTP total", $memoryUsage, $cli );

$cli->output( 'Script finished successfully.' );
$script->shutdown();
exit;


function convertMemory( $size )
{
    $unit = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
    return sprintf(
        "%d %s",
        @round( $size / pow( 1024, ( $i = floor( log( abs( $size ), 1024 ) ) ) ), 2 ),
        $unit[$i]
    );
}

function printMemoryUsageDelta( $label, &$previousMemoryUsage, eZCli $cli )
{
    $memoryUsage = memory_get_usage( true );
    $memoryUsageDelta = $memoryUsage - $previousMemoryUsage;
    $usagePrefix =  ( $memoryUsageDelta >= 0 ) ? '+' : '';
    $memoryUsageDelta = $usagePrefix . convertMemory( $memoryUsageDelta );
    $cli->output( "# $label memory usage: ", false );
    $cli->output( convertMemory( $memoryUsage ) . " (delta: $memoryUsageDelta)" );
    $previousMemoryUsage = $memoryUsage;
}