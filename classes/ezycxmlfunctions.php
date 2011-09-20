<?php

/**
 * File containing the eZyoochooseFunctions class for generating the xml for the yoochoose engine
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */


class ezYCXMLFunctions{ 
	
	/*
	 * 
	 */
	public static function get_params_for_xml($node_id, $db){
		
		$object_id_rows = $db->arrayQuery( "SELECT `contentobject_id`, `path_string`, `contentobject_version`  FROM `ezcontentobject_tree` where `node_id`=$node_id" );
		$object_id = $object_id_rows[0]['contentobject_id'];
		$path_string = $object_id_rows[0]['path_string'];
		$path_string = str_replace('/'.$node_id, '', $path_string);
		$contentobject_version = $object_id_rows[0]['contentobject_version'];
		$classIDArray = eZRecommendationClassAttribute::fetchClassAttributeList($class_id);
		var_export($classIDArray, true);
		
		return 'func';	
		
		
	}
	
}