<?php

include_once( 'kernel/classes/ezworkflowtype.php' );


/**
 * Class definition of the custom event called
 * "My first event".
 */
class exportEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_EXPORTEVENT = "exportevent";

    /**
     * Constructor of this class
     */
    function exportEventType()
    {
        // Human readable name of the event displayed in admin interface
        $this->eZWorkflowEventType( exportEventType::EZ_WORKFLOW_TYPE_EXPORTEVENT, "Export to recommender engine" );
    }


    function execute( $process, $event )
    {
		/*Read ini Settings*/
		$ini = eZINI::instance('ezyoochoose.ini');
			switch ($ini->variable( 'SolutionSettings', 'solution' )){
				case 'publisher':
					$solution = 'publisher';
				break;
				case 'shop':
					$solution = 'shop';
				break;
				default:
					eZLog::write('eZYoochoose: No solution exist in settings/ezyoochoose.ini.append.php file', 'error.log', 'var/log');
				break;
			}	
			

		//Get the objectID
		$processParameters = $process->attribute( 'parameter_list' );
		
		$objectID = $processParameters['object_id'];
		//Get the nodeID
		$nodeID  = exportEventType::getNodeId($objectID);	
		//get the data map from objectID	
		$contentClass = eZContentObject::fetch($objectID);
		//$dataMap = $contentClass->attribute('data_map');
		//get the Class_id
		$class_id = $contentClass->ClassID;

		//Get node_id data
		//$nodes =& eZContentObject::allContentObjectAttributes( $nodeID, $asObject = true );
		//get categoryPath
		$path  =& eZContentObjectTreeNode::fetch ($nodeID, $lang=false, $asObject=true, $conditions=false);
		$ezCategoryPath = $path->PathString;
		$ezCategoryArray = explode("/",$ezCategoryPath);
		$count_ezCategoryArray = count($ezCategoryArray);
		/*e.g /1/2/174/262/ -> /2/174/ */
		$toYcCategoryPath = "/";
		for ($i = 2; $i <= $count_ezCategoryArray-3 ; ++$i )
		{
			$toYcCategoryPath .= $ezCategoryArray[$i].'/';
		}

		//get contentobject_attributes and transform to DataMapAsKeysAttributeIdentifier new array  e.g [index] -> [ContentClassAttributeIdentifier]
		$version = $contentClass->version( $processParameters['version'] );
        $objectAttributes = $version->attribute( 'contentobject_attributes' );
   
		$count_objectAttributes = count($objectAttributes);
		for($i = 0 ; $i <= $count_objectAttributes ; ++$i)
		{
			$ArrayKey = $objectAttributes[$i]->ContentClassAttributeIdentifier ;
			$dataMap[$ArrayKey] =  $objectAttributes[$i] ;
			 			 
		}
		
		//get the xmlMap from ezcontentclass_attribute (All datatype information are retrieved from the Class. The recommendation(enable/disable) is the only parameter taken from Object )
		$classIDArray = eZRecommendationClassAttribute::fetchClassAttributeList($class_id);
		$XmlDataText = $classIDArray['result']['ycXmlMap'];
		$ycitemtypeid = $classIDArray['result']['ycItemType'];
		/*
		//or get the xmlMap from ezcontentobject_attribute (Yet not used)
		foreach ($dataMap as $thisAttribute)			
		{	if ( $thisAttribute->DataTypeString == 'ezrecommendation' )
				{
					$XmlDataText= $thisAttribute->DataText;
				}
		}	
		*/
		
	
		//get Object data with Datatype mapping xml schema
		$ezymappingArray  = array();
		$ezymappingArray = ezyRecommendationXml::ezyRecommendationArrContent( $XmlDataText );

		//Check if export is enable for this class
		if ($ezymappingArray['export-enable'] == 0)
			return eZWorkflowType::STATUS_ACCEPTED;
		
			//create the YC REST XML body
			$doc = new DOMDocument( '1.0', 'utf-8' );
			$root = $doc->createElement( 'items' );
			$root->setAttribute( 'version', 1 );
		
			$elementType = $doc->createElement( 'item' );
			$elementType->setAttribute( 'id', $nodeID );	
			
			$root->appendChild( $elementType );	
			$elementType->setAttribute( 'type', $ycitemtypeid );		
			$root->appendChild( $elementType );	
			//
			$ycXmlContentSection = array('title','abstract','tags');
			$ycXmlAttributesSection = array('author','agency','geolocation','newsagency','vendor','date');
			$count_ycXmlContentSection = count($ycXmlContentSection);
			$count_ycXmlAttributesSection = count($ycXmlAttributesSection);
			//
			//XML content
			if ($solution == 'shop'){

			//price
				$elementPriceTypeContent = $doc->createElement( 'price' );
				$elementPriceTypeContent->setAttribute( 'currency', $ezymappingArray['currency'] );
				$elementPriceTypeContent->appendChild( $doc->createTextNode(dataTypeContent::checkDatatypeString( $class_id, $dataMap , 'preis',$ezymappingArray['currency'] )) );
				$elementType->appendChild( $elementPriceTypeContent);
			}
			//validfrom-valid-to
			
				$elementVFromTypeContent = $doc->createElement( 'validfrom' );
				$elementVFromTypeContent->appendChild( $doc->createTextNode(dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['validfrom'])) );
				$elementType->appendChild( $elementVFromTypeContent);
				
				$elementVToTypeContent = $doc->createElement( 'validto' );
				$elementVToTypeContent->appendChild( $doc->createTextNode(dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['validto'])) );
				$elementType->appendChild( $elementVToTypeContent);		
			
			//--categorypaths-- 
			$elementTypeContent = $doc->createElement( 'categorypaths' );
			$elementType->appendChild( $elementTypeContent );
			//<categorypath> Section
			$elementTypeCategoryChild = $doc->createElement( 'categorypath' );
			$elementTypeCategoryChild->appendChild( $doc->createTextNode($toYcCategoryPath) );
			$elementTypeContent->appendChild( $elementTypeCategoryChild);
			//
			$createContentParentNode = 0;
			for ($i = 0; $i <= $count_ycXmlContentSection ; ++$i)
			{
				
				//-content-
				$key = $ycXmlContentSection[$i];
				if (array_key_exists($key, $ezymappingArray) and $ezymappingArray[$key] != '0') {
					if($createContentParentNode == 0)
					{	//<content> Section
						$elementTypeContent = $doc->createElement( 'content' );
						$elementType->appendChild( $elementTypeContent );	
						$createContentParentNode++;		// do not return here again
					}
					//create content child elements
					$elementTypeContentChild = $doc->createElement( 'content-data' );
					$elementTypeContentChild->setAttribute( 'key', $key );
					$elementTypeContentChild->appendChild( $doc->createCDATASection(
																	htmlentities(
																	dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray[$key]) 
																	)
																)
														  );
					$elementTypeContent->appendChild( $elementTypeContentChild);
					
					
				}

			}
			//-attributes-
			//Optional fields
			if (isset($ezymappingArray['counter']))
			{
				$addedOptAttributes = $ezymappingArray['counter'];
				$createAttributeParentNode = 0;
				for ($i = 1; $i < $addedOptAttributes ; ++$i)
				{
					if (isset($ezymappingArray['addtomap'.$i]))
					{
						if($createAttributeParentNode == 0)
						{	//<attributes> Section
							$elementTypeAttributes = $doc->createElement( 'attributes' );
							$elementType->appendChild( $elementTypeAttributes );	
							$createAttributeParentNode++;		// do not return here again
						}
						$elementTypeAttributeChild = $doc->createElement( 'attribute' );
						$elementTypeAttributeChild->setAttribute( 'key', $ezymappingArray['addtomap'.$i] );
						$elementTypeAttributeChild->setAttribute( 'value', dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray['addtomap'.$i]) ); 
						$elementTypeAttributes->appendChild( $elementTypeAttributeChild );
					}
					
				
				}
				for ($i = 0; $i <= $count_ycXmlAttributesSection ; ++$i)
				{
					$key = $ycXmlAttributesSection[$i];
					if (array_key_exists($key, $ezymappingArray) and $ezymappingArray[$key] != '0') 
					{
						if($createAttributeParentNode == 0)
						{
							//<attributes> Section
							$elementTypeAttributes = $doc->createElement( 'attributes' );
							$elementType->appendChild( $elementTypeAttributes );	
							$createAttributeParentNode++;		// do not return here again
						
						}
						else
						{
							$elementTypeAttributeChild = $doc->createElement( 'attribute' );
							$elementTypeAttributeChild->setAttribute( 'key', $key);
							$elementTypeAttributeChild->setAttribute( 'value', dataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezymappingArray[$key]) );
							$elementTypeAttributes->appendChild( $elementTypeAttributeChild );							
						}
				
					}
				
				}

			
			}

		$doc->appendChild( $root );	
		$pushingItemDoc = $doc->saveXML();

		//REST API CALL(Export)


		ezYCFunctions::sendExportContent($pushingItemDoc, $solution);
echo $pushingItemDoc ;
exit;
		return eZWorkflowType::STATUS_ACCEPTED;
    }
	

    public static function getNodeId($obj_id) 
    { 
        $db = eZDB::instance(); 
		
        $query = "SELECT node_id FROM ezcontentobject_tree WHERE contentobject_id = $obj_id ORDER BY modified_subnode DESC limit 0,1"; 
        $rows = $db -> arrayQuery( $query ); 
        return $rows[0]['node_id'];
    } 	
}
	
eZWorkflowEventType::registerEventType( exportEventType::EZ_WORKFLOW_TYPE_EXPORTEVENT, "exportEventType" );

?>
