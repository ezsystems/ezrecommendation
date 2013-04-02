<?php
/**
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

/**
 * High level class that provides interaction with the recommendation services
 */
class eZRecommendationApi
{
    public function __construct()
    {
    }

    public function getRecommendations( eZRecommendationApiGetRecommendationsStruct $parameters )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        // item type id
        $itemTypeId = $this->getItemTypeId( $parameters->node->attribute( 'class_identifier' ) );

        $itemId = $parameters->object->attribute( 'id' );

        // user ID
        $currentUser = eZUser::currentUser();
        if ( !$currentUser->isAnonymous() )
        {
            $userId = $currentUser->attribute( 'contentobject_id' );

        }
        else if ( isset( $_COOKIE['ezreco'] ) )
        {
            $userId = $_COOKIE['ezreco'];
        }
        else
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unable to fetch user id / ezreco cookie'
                )
            );
        }

        // URL generation
        $extension = $ini->variable( 'ExtensionSettings', 'usedExtension' );
        $clientId = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $productId = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );

        $path = "/$productId/$clientId/$userId/{$parameters->scenario}.$extension";
        $path .= '?' . $ini->variable( 'ParameterMapSettings', 'object_id' ).'=' . urlencode( $itemId );

        if ( $parameters->limit && $ini->hasVariable( 'ParameterMapSettings', 'numrecs' ) )
            $path .= '&' . $ini->variable( 'ParameterMapSettings', 'numrecs' ) . '=' . urlencode( $parameters->limit );

        if ( $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) )
            $path .= '&' . $ini->variable( 'ParameterMapSettings', 'class_id' ).'=' . urlencode( $itemTypeId );

        $categorypath = $parameters->node->attribute( 'path_string' );
        $path .= '&' . $ini->variable( 'ParameterMapSettings', 'path_string' ) . '=' . urlencode( ezRecoTemplateFunctions::getCategoryPath( $categorypath ) );
        $path .= '&recommendCategory=true';

        try
        {
            $recommendations = ezRecoFunctions::send_reco_request( $ini->variable( 'URLSettings', 'RecoURL' ), $path );
            return self::processRawRecommendations( $recommendations );
        }
        catch ( eZRecommendationException $e )
        {
            throw new eZRecommendationApiException( $e->getMessage() );
        }

    }

    /**
     * Returns the item type id mapped to a class
     */
    private function getItemTypeId( $classIdentifier )
    {
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $classId );

        return count( $arr['result'] ) > 0 ? $arr['result']['recoItemType'] : false;
    }

    /**
     * Processes a raw array of recommendations into an array
     * @param array $rawRecommendations
     * @return array|bool
     */
    private function processRawRecommendations( $rawRecommendations )
    {
        $recommendations = array();

        foreach ( $rawRecommendations as $rec )
        {
            foreach ( $rec as $rec2 )
            {
                $row = array(
                    'reason' => $rec2->reason,
                    'itemType' => $rec2->itemType,
                    'itemId' => $rec2->itemId,
                    'relevance' => $rec2->relevance,
                    'category' => property_exists( $rec2, 'category' ) ? $rec2->category : null
                );
                $recommendations[] = $row;
            }
        }

        if ( !empty( $recommendations ) )
        {
            return $recommendations;
        }

        return false;
    }

    /**
     * Deletes item with node ID $node in the recommendation index
     * @param int $nodeID
     *
     * @return bool
     */
    public function deleteItem( $nodeID )
    {
        if ( $node = eZContentObjectTreeNode::fetch( $nodeID ) )
        {
            $classAttributesList = ezRecommendationClassAttribute::fetchClassAttributeList( $node->ContentObject->ClassID );
            if ( !isset( $classAttributesList['result']['recoItemType'] )  )
                return false;
            ezRecoFunctions::sendDeleteItemRequest( $classAttributesList['result']['recoItemType'] . '/' . $node->attribute( 'object' )->attribute( 'id' ) );
            eZDebugSetting::writeDebug( 'ezrecommendation-extension', 'Delete event on node $nodeID executed' );
            return true;
        }
        else
        {
            eZDebug::writeError(
                "Unable to find node with ID $nodeID",
                "[ezrecommendation] eZRecommendationApi::deleteItem( $nodeID)"
            );
            return false;
        }
    }

    public function exportObject( $objectID )
    {
        $ini = eZINI::instance('ezrecommendation.ini');
        $solution = $ini->variable( 'SolutionSettings', 'solution' );

        //get the data map from objectID
        $contentObject = eZContentObject::fetch( $objectID );
        $classID = $contentObject->attribute( 'contentclass_id' );

        //get content object in the default language
        $dataMap = $contentObject->attribute( 'data_map' );

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
        //get the xmlMap from ezcontentclass_attribute (All datatype information are retrieved from the Class. The recommendation(enable/disable) is the only parameter taken from Object )
        $classIDArray = ezRecommendationClassAttribute::fetchClassAttributeList( $classID );
        if ( !isset( $classIDArray['result'] ) )
            return false;

        $XmlDataText = $classIDArray['result']['recoXmlMap'];
        $recoitemtypeid = $classIDArray['result']['recoItemType'];

        $ezRecomappingArray = ezRecommendationXml::ezRecommendationArrContent( $XmlDataText );

        //Check if export is enable for this class
        if ($ezRecomappingArray['export-enable'] == 0)
            return false;

        //create the reco REST XML body
        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElement( 'items' );
        $root->setAttribute( 'version', 1 );

        $elementType = $doc->createElement( 'item' );
        $elementType->setAttribute( 'id', $objectID );

        $root->appendChild( $elementType );
        $elementType->setAttribute( 'type', $recoitemtypeid );
        $root->appendChild( $elementType );
        //
        $recoXmlContentSection = array( 'title', 'abstract', 'tags' );
        $recoXmlAttributesSection = array( 'author', 'agency', 'geolocation', 'newsagency', 'vendor', 'date' );

        if ( $solution == 'shop' )
        {
            $elementPriceTypeContent = $doc->createElement( 'price' );
            $elementPriceTypeContent->setAttribute( 'currency', $ezRecomappingArray['currency'] );
            $elementPriceTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString(
                        $classID,
                        $dataMap,
                        $ezRecomappingArray['price'],
                        $ezRecomappingArray['currency']
                    )
                )
            );
            $elementType->appendChild( $elementPriceTypeContent );
        }

        if ( $ezRecomappingArray['validfrom'] )
        {
            $elementVFromTypeContent = $doc->createElement( 'validfrom' );
            $elementVFromTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap , $ezRecomappingArray['validfrom'], 'validfrom' )
                )
            );
            $elementType->appendChild( $elementVFromTypeContent);
        }

        if ( $ezRecomappingArray['validto'] )
        {
            $elementVToTypeContent = $doc->createElement( 'validto' );
            $elementVToTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecomappingArray['validto'], 'validto' )
                )
            );
            $elementType->appendChild( $elementVToTypeContent );
        }

        $elementTypeContent = $doc->createElement( 'categorypaths' );
        $elementType->appendChild( $elementTypeContent );

        foreach ( $contentObject->assignedNodes() as $node )
        {
            $elementTypeCategoryChild = $doc->createElement( 'categorypath' );
            $elementTypeCategoryChild->appendChild(
                $doc->createTextNode(
                    ezRecoTemplateFunctions::getCategoryPath( $node->attribute( 'path_string' ) )
                )
            );
            $elementTypeContent->appendChild( $elementTypeCategoryChild );
        }

        //
        $createContentParentNode = 0;
        for ( $i = 0, $recoXmlContentSectionCount = count( $recoXmlContentSection ); $i < $recoXmlContentSectionCount; ++$i )
        {
            $key = $recoXmlContentSection[$i];
            if ( array_key_exists( $key, $ezRecomappingArray ) and $ezRecomappingArray[$key] != '0' )
            {
                if ( $createContentParentNode == 0 )
                {
                    $elementTypeContent = $doc->createElement( 'content' );
                    $elementType->appendChild( $elementTypeContent );
                    // do not return here again
                    $createContentParentNode++;
                }
                //create content child elements
                $elementTypeContentChild = $doc->createElement( 'content-data' );
                $elementTypeContentChild->setAttribute( 'key', $key );

                $elementTypeContentChild->appendChild(
                    $doc->createCDATASection(
                        htmlentities(
                            eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecomappingArray[$key] )
                        )
                    )
                );
                $elementTypeContent->appendChild( $elementTypeContentChild );
            }
        }
        //-attributes-
        //Optional fields
        if ( isset( $ezRecomappingArray['counter'] ) )
        {
            $addedOptAttributes = $ezRecomappingArray['counter'];
            $createAttributeParentNode = 0;
            for ( $i = 1; $i < $addedOptAttributes; ++$i )
            {
                if ( !isset($ezRecomappingArray['addtomap' . $i] ) )
                    continue;

                if ( $createAttributeParentNode == 0 )
                {
                    $elementTypeAttributes = $doc->createElement( 'attributes' );
                    $elementType->appendChild( $elementTypeAttributes );
                    // do not return here again
                    $createAttributeParentNode++;
                }
                $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                $elementTypeAttributeChild->setAttribute( 'key', $ezRecomappingArray['addtomap'.$i] );
                $elementTypeAttributeChild->setAttribute(
                    'value',
                    eZRecoDataTypeContent::checkDatatypeString(
                        $classID,
                        $dataMap,
                        $ezRecomappingArray['addtomap'.$i]
                    )
                );
                $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
            }

            for ( $i = 0, $recoXmlAttributesSectionCount = count( $recoXmlAttributesSection ); $i < $recoXmlAttributesSectionCount; ++$i )
            {
                $key = $recoXmlAttributesSection[$i];
                if ( !isset( $ezRecomappingArray[$key] ) || $ezRecomappingArray[$key] == '0' )
                    continue;

                if ( $createAttributeParentNode == 0 )
                {
                    $elementTypeAttributes = $doc->createElement( 'attributes' );
                    $elementType->appendChild( $elementTypeAttributes );
                    // do not return here again
                    $createAttributeParentNode++;

                }
                else
                {
                    $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                    $elementTypeAttributeChild->setAttribute( 'key', $key );
                    $elementTypeAttributeChild->setAttribute(
                        'value',
                        eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecomappingArray[$key] )
                    );
                    $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
                }
            }
        }

        $doc->appendChild( $root );
        $pushingItemDoc = $doc->saveXML();

        ezRecoFunctions::sendExportContent( $pushingItemDoc, $solution );

        return true;
    }
    /**
     * Instance of ezrecommendation.ini
     * @var eZINI
     */
    private $ini;
}
?>
