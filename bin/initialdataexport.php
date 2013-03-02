#!/usr/bin/env php
<?php
/**
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */
$initialMemoryUsage = memory_get_usage( true );

require 'autoload.php';
require_once 'extension/ezrecommendation/classes/ezrecommendationclassattribute.php';

$cli = eZCLI::instance();
$endl = $cli->endlineString();

if ( !function_exists( 'pcntl_fork' ) )
{
    $cli->error( "The PCNTL php extension isn't installed / enabled on your system" );
    $script->shutdown( 2 );
}

$script = eZScript::instance(
    array(
        'description' => ( "eZ Recommendation initial data export" ),
        'use-session' => false,
        'use-modules' => true,
        'use-extensions' => true
    )
);
$script->startup();

$options = $script->getOptions(
    "[split:][class-group:][parent-tree:][memory-debug:]",
    "",
    array(
        'split' => 'Maximum number of entries per XML file. Default: 1000',
        'class-group' => 'Filter classes by group. Default: 1 (Content)',
        'parent-tree' => 'Subtree parent node ID. Default: 2.',
        'memory-debug' => 'Memory debug level, either 1 or 2. Default: 0 (disabled)',
    )
);

$script->initialize();
$db = eZDB::instance();

$ini = eZINI::instance( 'ezrecommendation.ini' );

if ( !$split = $options['split'] )
{
    // default initial XML export split value
    if ( !$split = $ini->variable( 'BulkExportSettings', 'XmlEntries' ) )
    {
        $cli->output( 'Missing XmlEntries parameter in ezrecommendation.ini' );
        $script->shutdown( 1 );
    }
}

if ( !$classGroup = $options['class-group'] )
    $classGroup = 1;

if ( !$solution = $ini->variable( 'SolutionSettings', 'solution' ) )
{
    $cli->output( 'Missing solution in ezrecommendation.ini' );
    $script->shutdown( 1 );
}

if ( !$parentTree = $options['parent-tree'] )
    $parentTree = 2;

$optMemoryDebug = isset( $options['memory-debug'] ) ? (int)$options['memory-debug'] : 0;

if ( !$url = $ini->variable( 'BulkExportSettings', 'SiteURL' ) )
{
    $cli->output( 'Missing or empty SiteURL in ezrecommendation.ini' );
    $script->shutdown( 1 );
}

if ( !$path = $ini->variable( 'BulkExportSettings', 'BulkPath' ) )
{
    $cli->output( 'Missing or empty BulkPath in ezrecommendation.ini' );
    $script->shutdown( 1 );
}

// Check paths
if ( substr( $url, -1 ) == '/' )
{
    $cli->output( "SiteURL must not end with a '/' in ezrecommendation.ini" );
    $script->shutdown( 1 );
}

if ( $path[0] == '/' )
{
    $cli->output( "BulkPath must not begin with '/' in ezrecommendation.ini" );
    $script->shutdown( 1 );
}

$cli->output( 'Starting script.' );

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
                                AND group_id  IN ($classGroup)
                                GROUP BY id "
);
$classArray = array();
foreach ( $classRows as $classRow )
{
    $classArray[] = $classRow['id'];
}
unset( $classRows, $classRow );
if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "bootstrap ", $previousMemoryUsage, $cli );

$cli->output( "Exporting objects" );
$provider = new eZRecoInitialExportProvider( $classArray, $parentTree, $cli, $db );
$cli->output( "Total objects: " . $provider->getItemsCount() );
$cli->output( "Generating XML file(s)" );
$exportedElements = 0;
$xmlFiles = array();
$memoryUsageOne = $memoryUsageTwo = memory_get_usage( true );
while ( $nodeList = $provider->getNextBatch( $split ) )
{
    // we fork the process to handle the loop
    // $exportedElements is only incremented by the child process, and always has the value 0 in the parent
    if ( $exportedElements == 0 )
    {
        $xmlFilePath = eZRecoInitialExport::getXmlFilePath( $path );
        $cli->output( "Exporting " . count( $nodeList ) . " items to $xmlFilePath...", false );
        eZClusterFileHandler::preFork();
        $pid = pcntl_fork();

        // re-initialize the database
        $provider->setDb( null );
        $db = eZDB::instance();
        $db->close();
        $db = null;
        eZDB::setInstance( null );

        // the parent process waits until the child is done
        if ( $pid )
        {
            pcntl_waitpid( $pid, $status, WUNTRACED );
            if ( pcntl_wifexited( $status ) )
                $cli->output( "done" );
            else
                $cli->error( "error" );
            $provider->setDb( eZDB::instance() );
            $xmlFiles[] = $xmlFilePath;

            if ( $optMemoryDebug >= 2 )
                printMemoryUsageDelta( "$xmlFilePath export", $memoryUsageTwo, $cli );
            continue;
        }
    }

    // START CHILD PROCESS
    $initialMemoryUsage = memory_get_usage( true );
    $domDocument = new DOMDocument( '1.0', 'utf-8' );
    $domDocumentRoot = $domDocument->createElement( 'items' );
    $domDocumentRoot->setAttribute( 'version', 1 );
    $domDocument->appendChild( $domDocumentRoot );

    $iterationMemoryUsage = memory_get_usage( true );
    // the child process iterates until a file has been written, or until it runs out of objects
    foreach ( $nodeList as $node )
    {
        try
        {
            $objectData = eZRecoInitialExport::generateNodeData( $node, $solution, $cli );
            $domNode = $domDocument->createElement( 'item' );
            $domDocumentRoot->appendChild( $domNode );
            eZRecoInitialExport::generateDOMNode( $objectData, $domDocument, $domNode );
            $exportedElements++;
            if ( $optMemoryDebug >= 3 )
                printMemoryUsageDelta( "export child iteration", $iterationMemoryUsage, $cli, true );
        }
        catch ( Exception $e )
        {
            $cli->error( "Export child: an exception has occured: " . $e->getMessage() );
            $script->shutdown( 1 );
            exit;
        }
    }

    eZRecoInitialExport::writeXmlFile( $domDocument, $xmlFilePath );
    eZClusterFileHandler::instance()->fileStore( $xmlFilePath );

    if ( $optMemoryDebug >= 2 )
    {
        $cli->output();
        printMemoryUsageDelta( "Export child total", $initialMemoryUsage, $cli );
    }
    $script->shutdown();
    eZDB::instance()->close();
    exit;
    // END CHILD PROCESS
}

if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "XML export", $memoryUsageOne, $cli );

// Store files to cluster, and send HTTP request
$memoryUsageTwo = memory_get_usage( true );
foreach ( $xmlFiles as $xmlFile )
{
    $cli->output( "Sending $xmlFile... ", false );
    try
    {
        ezRecoFunctions::send_bulk_request( $url, $path, $xmlFile );
        $cli->output( "done" );
    }
    catch ( Exception $e )
    {
        $cli->error( "failure: " . $e->getMessage() );
    }
    if ( $optMemoryDebug >= 2 )
        printMemoryUsageDelta( "HTTP sending of $xmlFile", $memoryUsageTwo, $cli, true );
}
if ( $optMemoryDebug >= 1 )
    printMemoryUsageDelta( "HTTP sending total", $memoryUsageOne, $cli, true );

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