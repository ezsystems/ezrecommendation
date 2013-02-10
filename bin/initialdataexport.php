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
    "[split:][classgroup:][parent-tree:][limit:][global_offset:][memory-debug:]",
    "",
    array(
        'split' => 'Define how many entrys are defined in each ezrecommendation initial XML export file. ',
        'classgroup' => 'Filter classes by group, default group is 1 (Content)',
        'parent-tree' => 'Subtree parent node ID. Default is 2.',
        'memory-debug' => 'Memory debug level, 1 or 2',
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

$parent_tree = $options['parent-tree'];
if ( !$parent_tree )
    $parent_tree = 2; //Home

$limit = $options['limit'];
if ( !$limit )
    $limit = 1000;

$global_offset = $options['global_offset'];
$optMemoryDebug = isset( $options['memory-debug'] ) ? (int)$options['memory-debug'] : 1;

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
if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "bootstrap ", $previousMemoryUsage, $cli );

$cli->output( "Exporting objects" );
$provider = new eZRecoInitialExportProvider( $classArray, $parent_tree, $cli, $db );
$cli->output( "Total objects: " . $provider->getItemsCount() );
$cli->output( "Generating XML file(s)" );
$exportedElements = 0; $xmlFiles = array();
$initialMemoryUsage = $previousMemoryUsage = $exportMemoryUsage = memory_get_usage( true );
while( $nodeList = $provider->getNextBatch( $split ) )
{
    // we fork the process to handle the loop
    // $exportedElements is only incremented by the child process, and always has the value 0 in the parent
    if ( $exportedElements == 0 )
    {
        $xmlFilePath = eZRecoInitialExport::getXmlFilePath( $path );

        $cli->output( "Exporting " . count( $nodeList ) . " items to $xmlFilePath..." );

        eZClusterFileHandler::preFork();

        $pid = pcntl_fork();

        // re-initialize the database
        $provider->setDb( null );
        $db = eZDB::instance();
        $db->close();
        $db = null;
        eZDB::setInstance( null );

        // the parent process waits until the child is done
        if ( $pid  )
        {
            pcntl_waitpid( $pid, $status, WUNTRACED );
            if ( pcntl_wifexited( $status ) )
                $cli->output( "done" );
            else
                $cli->error( "error" );
            $provider->setDb( eZDB::instance() );
            continue;
        }
    }

    // stop output completely
    fclose( STDIN );
    fclose( STDOUT );
    fclose( STDERR );

    // the child process iterates until a file has been written, or until it runs out of objects
    // START CHILD PROCESS
    $previousMemoryUsage = memory_get_usage( true );
    $domDocument = new DOMDocument( '1.0', 'utf-8' );
    $domDocumentRoot = $domDocument->createElement( 'items' );
    $domDocumentRoot->setAttribute( 'version', 1 );
    $domDocument->appendChild( $domDocumentRoot );

    foreach ( $nodeList as $node )
    {
        try
        {
            $objectData = eZRecoInitialExport::generateNodeData( $node, $solution, $cli );
            $domNode = $domDocument->createElement( 'item' );
            $domDocumentRoot->appendChild( $domNode );
            eZRecoInitialExport::generateDOMNode( $objectData, $domDocument, $domNode );
            $exportedElements++;
        }
        catch ( Exception $e )
        {
            eZRecoLog::write( "An exception occured: " . $e->getMessage() );
            $script->shutdown( 1 );
            exit;
        }
    }

    eZRecoInitialExport::writeXmlFile( $domDocument, $xmlFilePath );

    $script->shutdown();
    $db->close();
    exit;
    // END CHILD PROCESS
}

$memoryUsage = memory_get_usage( true );
if ( $exportedElements > 0 )
{
    $cli->output( "Writing $exportedElements entries to disk... ", false );
    $filename = eZRecoInitialExport::writeXmlFile( $domDocument, $path );
    $cli->output( " done ($filename)" );
}
if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "XML file generation", $memoryUsage, $cli );

if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "Global XML export", $initialMemoryUsage, $cli );

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
        $cli->output( "done" );
    }
    catch( Exception $e )
    {
        $cli->error( "failure" );
        $cli->error( $e->getMessage() );
    }
}
if ( $optMemoryDebug >= 1 )
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

/**
 * Prints the memory usage delta as compared to $previousMemoryUsage
 *
 * @param string $label text label to print
 * @param int $previousMemoryUsage
 * @param eZCLI $cli
 * @param bool $force Set to true to show delta == 0
 *
 * @return void
 */
function printMemoryUsageDelta( $label, &$previousMemoryUsage, eZCli $cli, $force = false )
{
    $memoryUsage = memory_get_usage( true );
    $memoryUsageDelta = $memoryUsage - $previousMemoryUsage;

    if ( !$force && $memoryUsageDelta == 0 )
        return;


    $usagePrefix =  ( $memoryUsageDelta >= 0 ) ? '+' : '';
    $memoryUsageDelta = $usagePrefix . convertMemory( $memoryUsageDelta );
    $cli->output( "# $label memory usage: ", false );
    $cli->output( $memoryUsageDelta . " (total: " . convertMemory( $memoryUsage ). ")" );
    $previousMemoryUsage = $memoryUsage;
}

class eZRecoLog
{
    public static function write( $message )
    {
        eZLog::write( $message, 'ezrecommendation.log' );
    }
}