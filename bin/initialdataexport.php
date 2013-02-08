#!/usr/bin/env php
<?php
/**
*
* @copyright //autogen//
* @license //autogen//
* @version //autogen//
* @package ezrecommendation
*/
require 'autoload.php';
require_once 'extension/ezrecommendation/classes/ezrecommendationclassattribute.php' ;

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
    array ( 'split' => 'Define how many entrys are defined in each ezrecommendation initial XML export file. ',
            'classgroup' => 'Filter classes by group, default group is 1 (Content)',
            'parent_tree' => 'Subtree parent node ID. Default is 2.',
            'limit' => 'Amount of items to fetch in each iteration. Default is 1000.',
            'global_offset' => 'Initial offset for query. Used only in special cases where memory is a problem.' )
);
$split = $options[ 'split' ];
if ( $split == "" )
{
    // default initial XML export split value
    $split = $ini->variable( 'BulkExportSettings', 'XmlEntrys' );

    if ( empty( $split ) )
    {
        $cli->output( 'Missing XmlEntrys in ezrecommendation.ini' );
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

$parent_tree = $options[ 'parent_tree' ];
if ( !$parent_tree )
    $parent_tree = 2; //Home

$limit = $options[ 'limit' ];
if ( !$limit )
    $limit = 1000;

$global_offset = $options[ 'global_offset' ];

$url = $ini->variable( 'BulkExportSettings', 'SiteURL' );
$path = $ini->variable( 'BulkExportSettings', 'BulkPath' );
if ( empty( $url ) || empty( $path ) )
{
    $cli->output( 'Missing SiteURL or BulkPath in ezrecommendation.ini' );
    $script->shutdown( 1 );
}
// Check paths
if ( substr( $url, - 1 ) == '/' || $path[0] == '/' )
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
    array_push( $classArray, $classRow['id'] );
}

$object_param_array = array();
$offset = 0;
foreach ( $classArray as $class_id )
{
    $rows_count = $db->arrayQuery( "SELECT COUNT(*) AS count FROM `ezcontentobject_tree` where `contentobject_id` in (SELECT `id` FROM `ezcontentobject` where `contentclass_id` = $class_id) " );
    $numRows = $rows_count[0]['count'];
    $cli->output( "Total count for class_id ($class_id): $numRows" );
    while ($numRows > $offset)
    {
        $node_rows = $db->arrayQuery( "SELECT `node_id`,`contentobject_id`,`path_string` FROM `ezcontentobject_tree` where `contentobject_id` in (SELECT `id` FROM `ezcontentobject` where `contentclass_id` = $class_id) ", array( 'offset' => $offset, 'limit' => $limit ) );
        foreach ( $node_rows as $node_id )
        {
            $path_string = str_replace( '/1/', '', $node_id['path_string'] );
            $arr_path_string = preg_split( "/\//", $path_string );
            if ( in_array( $parent_tree, $arr_path_string ) )
                array_push( $object_param_array, array( "node_id" => $node_id['node_id'],
                        "object_id" => $node_id['contentobject_id'],
                        "node_path" => '/' . $path_string,
                        "class_id" => $class_id
                        )
                );
        }
        //$cli->output( "Offset: " . $offset . " Count: " . count( $object_param_array ) );
        $offset += $limit;
    }
}

$exportFiles = generate_xml( $object_param_array );
foreach ( $exportFiles as $xmlFiles )
{
    ezRecoFunctions::send_bulk_request( $url, $path, $xmlFiles );
}

$cli->output( 'Script finished succesfully.' );

/*
 *
 */
function get_node_informations( $object_param_arrays )
{
    global $cli, $script, $db, $solution, $limit, $global_offset;

    $params_array = array();
    $counter = 0;
    $offset = 0;

    // use only a portion of the total object set to avoid memory errors (only used in special cases );
    if ( $global_offset )
        $object_param_arrays = array_slice( $object_param_arrays, $global_offset );

    //split the array in several smaller chunks
    $object_param_arrays_offset = array_chunk( $object_param_arrays, $limit );

    foreach ( $object_param_arrays_offset as $key => $object_param_arrays_part )
    {
        $index = $key + 1;
        $cli->output( 'Processing part '. $index .' of ' . count( $object_param_arrays_offset ) . '. ', false );
        $cli->output( 'Current memory usage: '. convertMemory( memory_get_usage( true ) ) );

        foreach ( $object_param_arrays_part as $object_param )
        {
            $object_id = $object_param['object_id'];
            $node_id = $object_param['node_id'];
            $path_string = $object_param['node_path'];
            $class_id = $object_param['class_id'];

            $params_array['node_id'] = $node_id;
            $params_array['object_id'] = $object_id;
            $params_array['path_string'] = $path_string;

            // Get Datatype information from class definition
            $classIDArray = ezRecommendationClassAttribute::fetchClassAttributeList( $class_id );
            $XmlDataText = $classIDArray['result']['recoXmlMap'];
            $recoItemTypeId = $classIDArray['result']['recoItemType'];

            $params_array['class_id'] = $class_id;
            $params_array['recoitemtype_id'] = $recoItemTypeId;

            try
            {
                if ( empty( $XmlDataText ) )
                    throw new Exception( '[ezrecommendation] Recommendation XML mapping was not found for ezpublish class ID : ' . $class_id );
                else
                    $ezRecomappingArray = ezRecommendationXml::ezRecommendationArrContent( $XmlDataText );
            }
            catch ( Exception $e )
            {
                eZDebug::writeError( $e->getMessage() );
                continue;
            }

            if ( $ezRecomappingArray['export-enable'] == 0 )
                continue;

            $data_map = null;
            $contentClass = eZContentObject::fetch( $object_id );
            $dataMap = $contentClass->attribute( 'data_map' );

            if ( $solution == 'shop' )
            {
                $currency = $ezRecomappingArray['currency'];
                $price = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['price'], $ezRecomappingArray['currency'] );
                if ( empty( $currency ) || empty( $price ) )
                {
                    $cli->output( "Missing currency or price for node $node_id (object Id $object_id)" );
                    continue;
                }
                $params_array['currency'] = $currency;
                $params_array['price'] = $price;
            }
            elseif ( $solution == 'publisher' )
            {
                $valid_from = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['validfrom'], 'validfrom' );
                $valid_to = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['validto'], 'validto' );
                if ( empty( $valid_to ) || empty( $valid_from ) )
                {
                    $cli->output( "Missing valid_to or valid_from for node $node_id (object Id $object_id)" );
                    continue;
                }
                $params_array['valid_from'] = $valid_from;
                $params_array['valid_to'] = $valid_to;
            }


            $recoXmlContentSection = array( 'title', 'abstract', 'tags' );
            $recoXmlAttributesSection = array( 'author', 'agency', 'geolocation', 'newsagency', 'vendor', 'date' );
            ;
            $content_section = array();
            for ( $i = 0, $count = count( $recoXmlContentSection ); $i < $count ; ++$i )
            {
                $tagsObject = ''; //because tags (Keywords) are not on the dataMap array

                // -Get content data
                $key = $recoXmlContentSection[$i];

                if ( array_key_exists( $key, $ezRecomappingArray ) and $ezRecomappingArray[$key] != '0' )
                {
                    $dataMapKey = $ezRecomappingArray[$key];

                    if ( $dataMap[$dataMapKey ]->DataTypeString == 'ezkeyword' )
                        $tagsObject = "tags";

                    $content_section[] = array( $key => htmlentities( eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray[$key], $tagsObject ) ) );
                }
            }

            $params_array['content'] = $content_section;
            // attributes
            $attributes_section = array();
            if ( isset( $ezRecomappingArray['counter'] ) )
            {
                $addedOptAttributes = $ezRecomappingArray['counter'];
                for ( $i = 1; $i < $addedOptAttributes ; ++$i )
                {
                    $tagsObject = ''; //because tags (Keywords) are not on the dataMap array
                    if ( isset( $ezRecomappingArray['addtomap' . $i] ) )
                    {
                        if ( $dataMap[$dataMapKey ]->DataTypeString == 'ezkeyword' )
                            $tagsObject = "tags";
                        $attributes_section[] = array( $ezRecomappingArray['addtomap' . $i] => eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['addtomap' . $i], $tagsObject ) );
                    }
                }
            }
            for ( $i = 0, $count = count( $recoXmlAttributesSection ); $i < $count ; ++$i )
            {
                $tagsObject = ''; //because tags (Keywords) are not on the dataMap array
                $key = $recoXmlAttributesSection[$i];
                if ( array_key_exists( $key, $ezRecomappingArray ) && $ezRecomappingArray[$key] != '0' )
                {
                    if ( $dataMap[$dataMapKey ]->DataTypeString == 'ezkeyword' )
                        $tagsObject = "tags";
                    array_push( $attributes_section, array( $key => eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray[$key], $tagsObject ) ) );
                }
            }

            $params_array['attribute'] = $attributes_section;

            $all_params_array[$counter] = $params_array;
            $counter++;
        }
    }
    return $all_params_array;
}

/*
 *
 */
function generate_xml( $object_param_arrays )
{
    global $cli, $script, $db, $solution, $split, $path;

    $cli->output( 'Collecting object informations... ' );
    $cli->output( 'Current memory usage: '. convertMemory( memory_get_usage( true ) ) );

    $nodes_parameters = get_node_informations( $object_param_arrays );
    $cli->output( 'done' );
    if ( $nodes_parameters )
    {
        $filesNumber = ceil( count( $nodes_parameters ) / $split ) ;
        $fromEntry = 0 ;
        $xml_files = array();
        for( $i = 1 ; $i <= $filesNumber ; ++$i )
        {
            $cli->output( 'Generating bulkexport_' . $i . '.xml... ', false );
            $doc = new DOMDocument( '1.0', 'utf-8' );
            $root = $doc->createElement( 'items' );
            $root->setAttribute( 'version', 1 );
            $toEntry = ( $i * $split ) - 1 ;

            for( $j = $fromEntry ; $j <= $toEntry ; ++$j )
            {
                if ( !isset( $nodes_parameters[$j] ) || $nodes_parameters[$j]['node_id'] == "" )
                    break;

                $elementType = $doc->createElement( 'item' );
                $elementType->setAttribute( 'id', $nodes_parameters[$j]['node_id'] );

                $root->appendChild( $elementType );
                $elementType->setAttribute( 'type', $nodes_parameters[$j]['recoitemtype_id'] );
                $root->appendChild( $elementType );

                if ( $solution == 'shop' )
                {
                    $elementPriceTypeContent = $doc->createElement( 'price' );
                    $elementPriceTypeContent->setAttribute( 'currency', $nodes_parameters[$j]['currency'] );
                    $elementPriceTypeContent->appendChild( $doc->createTextNode( $nodes_parameters[$j]['price'] ) );
                    $elementType->appendChild( $elementPriceTypeContent );
                }

                if ( !empty( $nodes_parameters[$j]['valid_from'] ) )
                {
                    $elementVFromTypeContent = $doc->createElement( 'validfrom' );
                    $elementVFromTypeContent->appendChild( $doc->createTextNode( $nodes_parameters[$j]['valid_from'] ) );
                    $elementType->appendChild( $elementVFromTypeContent );
                }

                if ( !empty( $nodes_parameters[$j]['valid_to'] ) )
                {
                    $elementVToTypeContent = $doc->createElement( 'validto' );
                    $elementVToTypeContent->appendChild( $doc->createTextNode( $nodes_parameters[$j]['valid_to'] ) );
                    $elementType->appendChild( $elementVToTypeContent );
                }

                $elementTypeContent = $doc->createElement( 'categorypaths' );
                $elementType->appendChild( $elementTypeContent );

                $elementTypeCategoryChild = $doc->createElement( 'categorypath' );
                $elementTypeCategoryChild->appendChild( $doc->createTextNode( $nodes_parameters[$j]['path_string'] ) );
                $elementTypeContent->appendChild( $elementTypeCategoryChild );
                // content
                $content_elements = count( $nodes_parameters[$j]['content'] );
                if ( $content_elements > 0 )
                {
                    $elementTypeContent = $doc->createElement( 'content' );
                    $elementType->appendChild( $elementTypeContent );

                    foreach ( $nodes_parameters[$j]['content'] as $contentvalue )
                    {
                        $elementTypeContentChild = $doc->createElement( 'content-data' );
                        $elementTypeContentChild->setAttribute( 'key', key( $contentvalue ) );
                        $elementTypeContentChild->appendChild( $doc->createCDATASection(
                                utf8_encode(current( $contentvalue ))
                                )
                            );
                        $elementTypeContent->appendChild( $elementTypeContentChild );
                    }
                }
                // atributes
                $content_elements = count( $nodes_parameters[$j]['attribute'] );
                if ( $content_elements > 0 )
                {
                    $elementTypeContent = $doc->createElement( 'attributes' );
                    $elementType->appendChild( $elementTypeContent );

                    foreach ( $nodes_parameters[$j]['attribute'] as $contentvalue )
                    {
                        $elementTypeContentChild = $doc->createElement( 'attribute' );
                        $elementTypeContentChild->setAttribute( 'key', key( $contentvalue ) );
                        $elementTypeContentChild->setAttribute( 'value', current( $contentvalue ) );

                        $elementTypeContent->appendChild( $elementTypeContentChild );
                    }
                }
            }

            $fromEntry = $toEntry + 1;

            $cli->output( 'done' );
            $cli->output( 'Saving bulkexport_' . $i . '.xml...', false );
            $doc->appendChild( $root );
            $pushingItemDoc = $doc->saveXML();
            $filename = $path . 'bulkexport_' . $i . '.xml';
            $fh = fopen( $filename, 'w' );
            fwrite( $fh, $pushingItemDoc );
            fclose( $fh );

            $handler = eZClusterFileHandler::instance( $filename );
            $handler->fileStore( $filename );

            $cli->output( 'done' );

            $xml_files [] .= 'bulkexport_' . $i . '.xml' ;
        }
    }

    return $xml_files;
}

function convertMemory( $size )
{
    $unit=array( 'b', 'kb', 'mb', 'gb', 'tb', 'pb' );
    return @round( $size / pow( 1024, ( $i = floor( log( $size,1024 ) ) ) ), 2 ).' '.$unit[$i];
}


$script->shutdown();
