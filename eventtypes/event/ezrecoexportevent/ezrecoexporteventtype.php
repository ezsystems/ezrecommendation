<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */


include_once( 'kernel/classes/ezworkflowtype.php' );


/**
 * Class definition of the custom event called
 * "My first event".
 */
class eZRecoExportEventType extends eZWorkflowEventType
{
    const EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE = "ezrecoexportevent";

    /**
     * Constructor of this class
     */
    function eZRecoExportEventType()
    {
        // Human readable name of the event displayed in admin interface
        $this->eZWorkflowEventType( eZRecoExportEventType::EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE, "ezrecommendation export object event" );
    }


    function execute( $process, $event )
    {
        /*Read ini Settings*/
        $ini = eZINI::instance('ezrecommendation.ini');
            switch ($ini->variable( 'SolutionSettings', 'solution' )){
                case 'publisher':
                    $solution = 'publisher';
                break;
                case 'shop':
                    $solution = 'shop';
                break;
                default:
                    eZLog::write('ezrecommendation: No solution exist in settings/ezrecommendation.ini.append.php file', 'error.log', 'var/log');
                break;
            }


        //Get the objectID
        $processParameters = $process->attribute( 'parameter_list' );

        $objectID = $processParameters['object_id'];
        //Get the nodeID
        $nodeID  = eZRecoExportEventType::getNodeId($objectID);

        //get the data map from objectID
        $contentClass = eZContentObject::fetch($objectID);

        //get the Class_id
        $class_id = $contentClass->ClassID;
//
//get content object in the default language

        $dataMap = $contentClass->attribute('data_map');

//or get content object in the actually language
/*
        //get the data map from objectID
        $contentClass = eZContentObject::fetch($objectID);
        //get contentobject_attributes and transform to DataMapAsKeysAttributeIdentifier new array  e.g [index] -> [ContentClassAttributeIdentifier]
        $pubVersion = $processParameters['version'];
        $version = $contentClass->version( $processParameters['version'],  true );

        $ObjectVersionContent = $version->ContentObject->ContentObjectAttributes[$pubVersion];

        //
        try {
            $currentObjectLang = key($ObjectVersionContent) ;

            if ($currentObjectLang == '')
            {    $objectAttributes = $version->attribute( 'contentobject_attributes' );
                throw new Exception('Current object language was not detected');
            }
            else
                //Send with default language
                 $objectAttributes = $ObjectVersionContent[$currentObjectLang];

        }
        catch (Exception $e) {
            eZLog::write('[ezrecommendation] item export: Current object language was not detected. Message: '.$e->getMessage(), 'error.log', 'var/log');


            //return eZWorkflowType::STATUS_REJECTED;
        }


        $count_objectAttributes = count($objectAttributes);
        for($i = 0 ; $i <= $count_objectAttributes ; ++$i)
        {
            $ArrayKey = $objectAttributes[$i]->ContentClassAttributeIdentifier ;
            $dataMap[$ArrayKey] =  $objectAttributes[$i] ;

        }
*/
//

        //check if class has a recommendation datype else return
        $classHasRecoDatatype = false;
        foreach ($dataMap as $thisAttribute)
        {    if ( $thisAttribute->DataTypeString == 'ezrecommendation' )
            {
                unset($classHasRecoDatatype);
                break;
            }

        }
        //if class not have a recommendation DataTypeString then break
        if (isset($classHasRecoDatatype))
            return eZWorkflowType::STATUS_ACCEPTED;


        //Get node_id data
        //$nodes =& eZContentObject::allContentObjectAttributes( $nodeID, $asObject = true );
        //get categoryPath
        $path  = eZContentObjectTreeNode::fetch ($nodeID, $lang=false, $asObject=true, $conditions=false);
        $ezCategoryPath = $path->PathString;

        //get the xmlMap from ezcontentclass_attribute (All datatype information are retrieved from the Class. The recommendation(enable/disable) is the only parameter taken from Object )
        $classIDArray = ezRecommendationClassAttribute::fetchClassAttributeList($class_id);

        $XmlDataText = $classIDArray['result']['recoXmlMap'];
        $recoitemtypeid = $classIDArray['result']['recoItemType'];
        /*
        //or get the xmlMap from ezcontentobject_attribute (Yet not used)
        foreach ($dataMap as $thisAttribute)
        {    if ( $thisAttribute->DataTypeString == 'ezrecommendation' )
                {
                    $XmlDataText= $thisAttribute->DataText;
                }
        }
        */

        //get Object data with Datatype mapping xml schema
        $ezRecomappingArray  = array();

        $ezRecomappingArray = ezRecommendationXml::ezRecommendationArrContent( $XmlDataText );

        //Check if export is enable for this class
        if ($ezRecomappingArray['export-enable'] == 0)
            return eZWorkflowType::STATUS_ACCEPTED;

            //create the reco REST XML body
            $doc = new DOMDocument( '1.0', 'utf-8' );
            $root = $doc->createElement( 'items' );
            $root->setAttribute( 'version', 1 );

            $elementType = $doc->createElement( 'item' );
            $elementType->setAttribute( 'id', $nodeID );

            $root->appendChild( $elementType );
            $elementType->setAttribute( 'type', $recoitemtypeid );
            $root->appendChild( $elementType );
            //
            $recoXmlContentSection = array('title','abstract','tags');
            $recoXmlAttributesSection = array('author','agency','geolocation','newsagency','vendor','date');
            $count_recoXmlContentSection = count($recoXmlContentSection);
            $count_recoXmlAttributesSection = count($recoXmlAttributesSection);
            //
            //XML content
            if ($solution == 'shop'){

            //price
                $elementPriceTypeContent = $doc->createElement( 'price' );
                $elementPriceTypeContent->setAttribute( 'currency', $ezRecomappingArray['currency'] );
                $elementPriceTypeContent->appendChild( $doc->createTextNode(eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['price'],$ezRecomappingArray['currency'] )) );
                $elementType->appendChild( $elementPriceTypeContent);
            }
            //validfrom-valid-to

                $elementVFromTypeContent = $doc->createElement( 'validfrom' );
                $elementVFromTypeContent->appendChild( $doc->createTextNode(eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['validfrom'],'validfrom')) );
                $elementType->appendChild( $elementVFromTypeContent);

                $elementVToTypeContent = $doc->createElement( 'validto' );
                $elementVToTypeContent->appendChild( $doc->createTextNode(eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['validto'],'validto')) );
                $elementType->appendChild( $elementVToTypeContent);

            //--categorypaths--
            $elementTypeContent = $doc->createElement( 'categorypaths' );
            $elementType->appendChild( $elementTypeContent );
            //<categorypath> Section
            $elementTypeCategoryChild = $doc->createElement( 'categorypath' );
            $elementTypeCategoryChild->appendChild( $doc->createTextNode(ezRecoTemplateFunctions::getCategoryPath($ezCategoryPath)) );
            $elementTypeContent->appendChild( $elementTypeCategoryChild);
            //
            $createContentParentNode = 0;
            for ($i = 0; $i <= $count_recoXmlContentSection ; ++$i)
            {

                //-content-
                $key = $recoXmlContentSection[$i];
                if (array_key_exists($key, $ezRecomappingArray) and $ezRecomappingArray[$key] != '0') {
                    if($createContentParentNode == 0)
                    {    //<content> Section
                        $elementTypeContent = $doc->createElement( 'content' );
                        $elementType->appendChild( $elementTypeContent );
                        $createContentParentNode++;        // do not return here again
                    }
                    //create content child elements
                    $elementTypeContentChild = $doc->createElement( 'content-data' );
                    $elementTypeContentChild->setAttribute( 'key', $key );

                    $elementTypeContentChild->appendChild( $doc->createCDATASection(
                                                                    htmlentities(
                                                                    eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray[$key])
                                                                    )
                                                                )
                                                          );
                    $elementTypeContent->appendChild( $elementTypeContentChild);


                }

            }
            //-attributes-
            //Optional fields
            if (isset($ezRecomappingArray['counter']))
            {
                $addedOptAttributes = $ezRecomappingArray['counter'];
                $createAttributeParentNode = 0;
                for ($i = 1; $i < $addedOptAttributes ; ++$i)
                {
                    if (isset($ezRecomappingArray['addtomap'.$i]))
                    {
                        if($createAttributeParentNode == 0)
                        {    //<attributes> Section
                            $elementTypeAttributes = $doc->createElement( 'attributes' );
                            $elementType->appendChild( $elementTypeAttributes );
                            $createAttributeParentNode++;        // do not return here again
                        }
                        $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                        $elementTypeAttributeChild->setAttribute( 'key', $ezRecomappingArray['addtomap'.$i] );
                        $elementTypeAttributeChild->setAttribute( 'value', eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray['addtomap'.$i]) );
                        $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
                    }


                }
                for ($i = 0; $i <= $count_recoXmlAttributesSection ; ++$i)
                {
                    $key = $recoXmlAttributesSection[$i];
                    if (array_key_exists($key, $ezRecomappingArray) and $ezRecomappingArray[$key] != '0')
                    {
                        if($createAttributeParentNode == 0)
                        {
                            //<attributes> Section
                            $elementTypeAttributes = $doc->createElement( 'attributes' );
                            $elementType->appendChild( $elementTypeAttributes );
                            $createAttributeParentNode++;        // do not return here again

                        }
                        else
                        {
                            $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                            $elementTypeAttributeChild->setAttribute( 'key', $key);
                            $elementTypeAttributeChild->setAttribute( 'value', eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap , $ezRecomappingArray[$key]) );
                            $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
                        }

                    }

                }


            }

        $doc->appendChild( $root );
        $pushingItemDoc = $doc->saveXML();

        //REST API CALL(Export)
        ezRecoFunctions::sendExportContent($pushingItemDoc, $solution);

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

eZWorkflowEventType::registerEventType( eZRecoExportEventType::EZ_WORKFLOW_TYPE_EZRECOEXPORTEVENTTYPE, "eZRecoExportEventType" );

?>
