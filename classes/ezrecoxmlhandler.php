<?php
/**
 * File containing the eZRecoXMLHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
class eZRecoXMLHandler
{
    /**
     * @param eZContentObject[]|eZContentObject $contentObject
     * @return string XML
     */
    public function generateContentObjectXML( $contentObjectsArray )
    {
        //create the reco REST XML body
        $doc = new DOMDocument( '1.0', 'utf-8' );
        $root = $doc->createElement( 'items' );
        $root->setAttribute( 'version', 1 );

        if ( !is_array( $contentObjectsArray ) )
            $contentObjectsArray = array( $contentObjectsArray );

        foreach ( $contentObjectsArray as $contentObject )
        {
            if ( $domNode = $this->generateContentObjectDOMNode( $contentObject, $doc ) )
            {
                $root->appendChild( $domNode );
            }
        }

        if ( !$root->hasChildNodes() )
        {
            return false;
        }

        $doc->appendChild( $root );

        return $doc->saveXML();
    }

    /**
     * @param eZContentObject $contentObject
     * @param DOMDocument     $doc
     * @return DOMNode
     */
    private function generateContentObjectDOMNode( eZContentObject $contentObject, DOMDocument $doc )
    {
        $ini = eZINI::instance('ezrecommendation.ini');
        $solution = $ini->variable( 'SolutionSettings', 'solution' );

        //get the data map from objectID
        $classID = $contentObject->attribute( 'contentclass_id' );

        //get content object in the default language
        $dataMap = $contentObject->attribute( 'data_map' );

        //get the xmlMap from ezcontentclass_attribute (All datatype information are retrieved from the Class. The recommendation(enable/disable) is the only parameter taken from Object )
        $classIDArray = ezRecommendationClassAttribute::fetchClassAttributeList( $classID );
        if ( !isset( $classIDArray['result'] ) )
            return false;

        $XmlDataText = $classIDArray['result']['recoXmlMap'];
        $recoItemTypeId = $classIDArray['result']['recoItemType'];

        $ezRecoMappingArray = ezRecommendationXml::ezRecommendationArrContent( $XmlDataText );

        //Check if export is enable for this class
        if ( $ezRecoMappingArray['export-enable'] == 0 )
            return false;


        $itemNode = $doc->createElement( 'item' );
        $itemNode->setAttribute( 'id', $contentObject->attribute( 'id' ) );

        $itemNode->setAttribute( 'type', $recoItemTypeId );

        $recoXmlContentSection = array( 'title', 'abstract', 'tags' );
        $recoXmlAttributesSection = array( 'author', 'agency', 'geolocation', 'newsagency', 'vendor', 'date' );

        if ( $solution == 'shop' )
        {
            $elementPriceTypeContent = $doc->createElement( 'price' );
            $elementPriceTypeContent->setAttribute( 'currency', $ezRecoMappingArray['currency'] );
            $elementPriceTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString(
                        $classID,
                        $dataMap,
                        $ezRecoMappingArray['price'],
                        $ezRecoMappingArray['currency']
                    )
                )
            );
            $itemNode->appendChild( $elementPriceTypeContent );
        }

        if ( $ezRecoMappingArray['validfrom'] )
        {
            $elementVFromTypeContent = $doc->createElement( 'validfrom' );
            $elementVFromTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap , $ezRecoMappingArray['validfrom'], 'validfrom' )
                )
            );
            $itemNode->appendChild( $elementVFromTypeContent);
        }

        if ( $ezRecoMappingArray['validto'] )
        {
            $elementVToTypeContent = $doc->createElement( 'validto' );
            $elementVToTypeContent->appendChild(
                $doc->createTextNode(
                    eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecoMappingArray['validto'], 'validto' )
                )
            );
            $itemNode->appendChild( $elementVToTypeContent );
        }

        $elementTypeContent = $doc->createElement( 'categorypaths' );
        $itemNode->appendChild( $elementTypeContent );

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
            if ( array_key_exists( $key, $ezRecoMappingArray ) and $ezRecoMappingArray[$key] != '0' )
            {
                if ( $createContentParentNode == 0 )
                {
                    $elementTypeContent = $doc->createElement( 'content' );
                    $itemNode->appendChild( $elementTypeContent );
                    // do not return here again
                    $createContentParentNode++;
                }
                //create content child elements
                $elementTypeContentChild = $doc->createElement( 'content-data' );
                $elementTypeContentChild->setAttribute( 'key', $key );

                $elementTypeContentChild->appendChild(
                    $doc->createCDATASection(
                        htmlentities(
                            eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecoMappingArray[$key] ),
                            ENT_COMPAT | ENT_HTML401,
                            'UTF-8'
                        )
                    )
                );
                $elementTypeContent->appendChild( $elementTypeContentChild );
            }
        }
        //-attributes-
        //Optional fields
        if ( isset( $ezRecoMappingArray['counter'] ) )
        {
            $addedOptAttributes = $ezRecoMappingArray['counter'];
            $createAttributeParentNode = 0;
            for ( $i = 1; $i < $addedOptAttributes; ++$i )
            {
                if ( !isset($ezRecoMappingArray['addtomap' . $i] ) )
                    continue;

                if ( $createAttributeParentNode == 0 )
                {
                    $elementTypeAttributes = $doc->createElement( 'attributes' );
                    $itemNode->appendChild( $elementTypeAttributes );
                    // do not return here again
                    $createAttributeParentNode++;
                }
                $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                $elementTypeAttributeChild->setAttribute( 'key', $ezRecoMappingArray['addtomap'.$i] );
                $elementTypeAttributeChild->setAttribute(
                    'value',
                    eZRecoDataTypeContent::checkDatatypeString(
                        $classID,
                        $dataMap,
                        $ezRecoMappingArray['addtomap'.$i]
                    )
                );
                $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
            }

            for ( $i = 0, $recoXmlAttributesSectionCount = count( $recoXmlAttributesSection ); $i < $recoXmlAttributesSectionCount; ++$i )
            {
                $key = $recoXmlAttributesSection[$i];
                if ( !isset( $ezRecoMappingArray[$key] ) || $ezRecoMappingArray[$key] == '0' )
                    continue;

                if ( $createAttributeParentNode == 0 )
                {
                    $elementTypeAttributes = $doc->createElement( 'attributes' );
                    $itemNode->appendChild( $elementTypeAttributes );
                    // do not return here again
                    $createAttributeParentNode++;

                }
                else
                {
                    $elementTypeAttributeChild = $doc->createElement( 'attribute' );
                    $elementTypeAttributeChild->setAttribute( 'key', $key );
                    $elementTypeAttributeChild->setAttribute(
                        'value',
                        eZRecoDataTypeContent::checkDatatypeString( $classID, $dataMap, $ezRecoMappingArray[$key] )
                    );
                    $elementTypeAttributes->appendChild( $elementTypeAttributeChild );
                }
            }
        }

        return $itemNode;
    }
}
