#!/usr/bin/env php

/**
  *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */


<?php

require 'autoload.php';
require_once 'extension/ezyoochoose/classes/ezycxmlfunctions.php' ;
require_once 'extension/ezyoochoose/classes/ezrecommendationclassattribute.php' ;

$cli = eZCLI::instance();
$endl = $cli->endlineString();

$script = eZScript::instance();
$script->startup();
$script->initialize();
$db = eZDB::instance();

$ini = eZINI::instance('ezyoochoose.ini');




$solution = $ini->variable( 'SolutionSettings', 'solution' );

if (empty($solution)){	
	$cli->output('Missing solution in ezyoochoose.ini');
	$script->shutdown(1);
}

$url = $ini->variable( 'BulkExportSettings', 'SiteURL' );
$path = $ini->variable( 'BulkExportSettings', 'BulkPath' );
if (empty($url) || empty($path)){	
	$cli->output('Missing SiteURL or BulkPath in ezyoochoose.ini');
	$script->shutdown(1);	
}

$cli->output('Starting script.');

$class_rows = $db->arrayQuery( "SELECT `id` FROM `ezcontentclass` where `id` in (SELECT distinct(`contentclass_id`) FROM `ezcontentclass_attribute` where `data_type_string`='ezrecommendation')" );

$class_array = array();

foreach ( $class_rows as $class_row ){
	
	array_push($class_array, $class_row['id']);
	  
}

$node_id_array = array();

foreach ($class_array as $class_id){
	
	$node_rows = $db->arrayQuery( "SELECT `node_id` FROM `ezcontentobject_tree` where `contentobject_id` in (SELECT `id` FROM `ezcontentobject` where `contentclass_id` = $class_id) ");
	
	foreach ($node_rows as $node_id){
		
		array_push($node_id_array, $node_id['node_id']);		
		
	}
}


if (generate_xml($node_id_array)){
	
	ezYCFunctions::send_bulk_request($url, $path);
}

$cli->output('Script finished succesfully.');


/*
 * 
 */
function get_node_informations($node_ids){
	
	global $cli, $script, $db, $solution;
	
	$params_array = array();
	$counter = 0;
	
	foreach ($node_ids as $node_id){
		
		
		$object_id_rows = $db->arrayQuery( "SELECT `contentobject_id`, `path_string`, `contentobject_version`  FROM `ezcontentobject_tree` where `node_id`=$node_id" );
		$object_id = $object_id_rows[0]['contentobject_id'];
		$path_string = $object_id_rows[0]['path_string'];
		$path_string = str_replace('/'.$node_id.'/', '/', $path_string);
		$path_string = str_replace('/1/', '/', $path_string);
		$contentobject_version = $object_id_rows[0]['contentobject_version'];
		
		$params_array['node_id']=$node_id;
		$params_array['object_id']=$object_id;
		$params_array['path_string']=$path_string;
		
		$object_class_id_rows = $db->arrayQuery( "SELECT `contentclass_id` FROM `ezcontentobject` where `id`=$object_id" );
		$class_id = $object_class_id_rows[0]['contentclass_id'];
		$classIDArray = eZRecommendationClassAttribute::fetchClassAttributeList($class_id);
		$XmlDataText = $classIDArray['result']['ycXmlMap'];
		$ycitemtypeid = $classIDArray['result']['ycItemType'];

		$params_array['class_id']=$class_id;
		$params_array['ycitemtype_id']=$ycitemtypeid;
		
		$ezymappingArray  = array();
		$ezymappingArray = ezyRecommendationXml::ezyRecommendationArrContent( $XmlDataText );
		
		if ($ezymappingArray['export-enable'] == 0)
			continue;
		
		$contentClass = eZContentObject::fetch($object_id);
		$version = $contentClass->version( $contentobject_version );
		$objectAttributes = $version->attribute( 'contentobject_attributes' );
   
		$count_objectAttributes = count($objectAttributes);
		$data_map = null;
		for($i = 0 ; $i <= $count_objectAttributes ; ++$i)
		{
			$ArrayKey = $objectAttributes[$i]->ContentClassAttributeIdentifier ;
			$dataMap[$ArrayKey] =  $objectAttributes[$i] ;
			 			 
		}
		$currency = $ezymappingArray['currency'];
		$price = dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['price'], $ezymappingArray['currency']);
		$valid_from = dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['validfrom']);
		$valid_to = dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['validto']);

		if ($solution == 'shop'){
			if (empty($currency) || empty($price)){
				$cli->output('Missing currency or price for node '.$node_id.'.');
				continue;
			}
		}elseif($solution == 'publisher'){
			if (empty($valid_to) || empty($valid_from)){
				$cli->output('Missing valid_to or valid_from for node '.$node_id.'.');
				continue;
			}
		}
				
		$params_array['currency']=$currency;
		$params_array['price']=$price;
		$params_array['valid_from']=$valid_from;
		$params_array['valid_to']=$valid_to;
				
		$ycXmlContentSection = array('title','abstract','tags');
		$ycXmlAttributesSection = array('author','agency','geolocation','newsagency','vendor','date');
		$count_ycXmlContentSection = count($ycXmlContentSection);
		$count_ycXmlAttributesSection = count($ycXmlAttributesSection);
			

		/*
		$createContentParentNode = 0;

		for ($i = 0; $i <= $count_ycXmlContentSection ; ++$i){
				
				//-content-
			$key = $ycXmlContentSection[$i];
			
			if (array_key_exists($key, $ezymappingArray) and $ezymappingArray[$key] != '0') {
				if($createContentParentNode == 0)
				{	
					$params_array['content']=array();
				}
				
				$params_array['content'][$key]=htmlentities( dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray[$key]));
			}

		}
		*/
		$all_params_array[$counter]= $params_array;
		$counter++;
				
	}	
	return $all_params_array;
	
}

/*
 * 
 */
function generate_xml($node_ids){
	
	global $cli, $script, $db, $solution;
	$cli->output('Collecting object informations...');
	$nodes_parameters = get_node_informations($node_ids);
	$cli->output('Collecting object informations done.');
	
	if ($nodes_parameters){
		$cli->output('Generating XML...');
		$doc = new DOMDocument( '1.0', 'utf-8' );
			$root = $doc->createElement( 'items' );
			$root->setAttribute( 'version', 1 );
			foreach ($nodes_parameters as $nodes_parameter){
				$elementType = $doc->createElement( 'item' );
				$elementType->setAttribute( 'id', $nodes_parameter['node_id'] );
				
				$root->appendChild( $elementType );	
				$elementType->setAttribute( 'type', $nodes_parameter['ycitemtype_id'] );		
				$root->appendChild( $elementType );

				if ($solution == 'shop'){			
					$elementPriceTypeContent = $doc->createElement( 'price' );
					$elementPriceTypeContent->setAttribute( 'currency', $nodes_parameter['currency'] );
					$elementPriceTypeContent->appendChild( $doc->createTextNode($nodes_parameter['price']) );
					$elementType->appendChild( $elementPriceTypeContent);
				}
				
				if (!empty($nodes_parameter['valid_from'])){
					$elementVFromTypeContent = $doc->createElement( 'validfrom' );
					$elementVFromTypeContent->appendChild( $doc->createTextNode($nodes_parameter['valid_from']) );
					$elementType->appendChild( $elementVFromTypeContent);
				}
				
				if (!empty($nodes_parameter['valid_to'])){
					$elementVToTypeContent = $doc->createElement( 'validto' );
					$elementVToTypeContent->appendChild( $doc->createTextNode($nodes_parameter['valid_to']) );
					$elementType->appendChild( $elementVToTypeContent);
				}
				
				
			$elementTypeContent = $doc->createElement( 'categorypaths' );
			$elementType->appendChild( $elementTypeContent );

			$elementTypeCategoryChild = $doc->createElement( 'categorypath' );
			$elementTypeCategoryChild->appendChild( $doc->createTextNode($nodes_parameter['path_string']) );
			$elementTypeContent->appendChild( $elementTypeCategoryChild);
			
			//$elementTypeContent = $doc->createElement( 'content' );
			//$elementType->appendChild( $elementTypeContent );

			}
			
			$cli->output('Generating XML done.');
			$cli->output('Saving XML...');
			$doc->appendChild( $root );	
			$pushingItemDoc = $doc->saveXML();
			$filename = 'extension/ezyoochoose/design/standard/images/bulkexport.xml';
			$fh = fopen($filename, 'w');
    		fwrite($fh, $pushingItemDoc);
    		fclose($fh);
    		$cli->output('Saving XML done.');
			return true;
	}
	
	
	
}




$script->shutdown();

?>
