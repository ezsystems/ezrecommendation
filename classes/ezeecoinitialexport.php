<?php
/**
 * File containing the eZRecoInitialExport class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
class eZRecoInitialExport
{
    private static $recoXmlContentSection = array( 'title', 'abstract', 'tags' );
    private static $recoXmlAttributesSection = array( 'author', 'agency', 'geolocation', 'newsagency', 'vendor', 'date' );

    public static function generateNodeData( $node, $solution, eZCli $cli )
    {
        $params_array = array();

        $object_id = $node['contentobject_id'];
        $node_id = $node['node_id'];
        $path_string = $node['path_string'];
        $class_id = $node['contentclass_id'];

        $params_array['node_id'] = $node_id;
        $params_array['object_id'] = $object_id;
        $params_array['path_string'] = $path_string;

        // Get datatype mapping information from class
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
            return false;
        }

        if ( $ezRecomappingArray['export-enable'] == 0 )
            return false;

        $dataMap = eZContentObject::fetch( $object_id )->attribute( 'data_map' );
        eZContentObject::clearCache( $object_id );

        if ( $solution == 'shop' )
        {
            $currency = $ezRecomappingArray['currency'];
            $price = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap, $ezRecomappingArray['price'], $ezRecomappingArray['currency'] );
            if ( empty( $currency ) || empty( $price ) )
            {
                eZRecoLog::write( "Missing currency or price for node $node_id (object Id $object_id)" );
                return false;
            }
            $params_array['currency'] = $currency;
            $params_array['price'] = $price;
            unset( $price, $currency );
        }
        elseif ( $solution == 'publisher' )
        {
            if ( $ezRecomappingArray['validfrom'] )
            {
                $params_array['valid_from'] = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap, $ezRecomappingArray['validfrom'], 'validfrom' );
            }
            if ( $ezRecomappingArray['validto'] )
            {
                $params_array['valid_to'] = eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap, $ezRecomappingArray['validto'], 'validto' );
            }
        }

        $content_section = array();
        foreach( self::$recoXmlContentSection as $key )
        {
            $tagsObject = ''; //because tags (Keywords) are not on the dataMap array
            if ( isset( $ezRecomappingArray[$key] ) && $ezRecomappingArray[$key] != '0' )
            {
                $dataMapKey = $ezRecomappingArray[$key];
                if ( $dataMap[$dataMapKey]->DataTypeString == 'ezkeyword' )
                    $tagsObject = "tags";

                $content_section[] = array(
                    $key => htmlentities( eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap, $ezRecomappingArray[$key], $tagsObject ) )
                );
            }
        }

        $params_array['content'] = $content_section;

        // attributes
        $attributes_section = array();
        if ( isset( $ezRecomappingArray['counter'] ) )
        {
            for ( $i = 1; $i < $ezRecomappingArray['counter']; ++$i )
            {
                $tagsObject = ''; //because tags (Keywords) are not on the dataMap array
                if ( isset( $ezRecomappingArray['addtomap' . $i] ) )
                {
                    if ( $dataMap[$dataMapKey]->DataTypeString == 'ezkeyword' )
                        $tagsObject = "tags";
                    $attributes_section[] = array(
                        $ezRecomappingArray['addtomap' . $i] => eZRecoDataTypeContent::checkDatatypeString(
                            $class_id, $dataMap, $ezRecomappingArray['addtomap' . $i], $tagsObject
                        )
                    );
                }
            }
        }

        foreach ( self::$recoXmlAttributesSection as $key )
        {
            $tagsObject = ''; //because tags (Keywords) are not on the dataMap array
            if ( isset( $ezRecomappingArray[$key] ) && $ezRecomappingArray[$key] != '0' )
            {
                if ( $dataMap[$dataMapKey]->DataTypeString == 'ezkeyword' )
                    $tagsObject = "tags";
                $attributes_section[] = array(
                    $key => eZRecoDataTypeContent::checkDatatypeString( $class_id, $dataMap, $ezRecomappingArray[$key], $tagsObject )
                );
            }
        }

        $params_array['attribute'] = $attributes_section;

        return $params_array;
    }

    public static function generateDOMNode( $object, DOMDocument &$domDocument, DOMElement $domNode )
    {
        global $solution;

        $domNode->setAttribute( 'id', $object['node_id'] );

        $domNode->setAttribute( 'type', $object['recoitemtype_id'] );
        if ( $solution == 'shop' )
        {
            $priceNode = $domDocument->createElement( 'price' );
            $priceNode->setAttribute( 'currency', $object['currency'] );
            $priceNode->appendChild( $domDocument->createTextNode( $object['price'] ) );
            $domNode->appendChild( $priceNode );
        }

        if ( !empty( $object['valid_from'] ) )
        {
            $validFromNode = $domDocument->createElement( 'validfrom' );
            $validFromNode->appendChild( $domDocument->createTextNode( $object['valid_from'] ) );
            $domNode->appendChild( $validFromNode );
        }

        if ( !empty( $object['valid_to'] ) )
        {
            $validToNode = $domDocument->createElement( 'validto' );
            $validToNode->appendChild( $domDocument->createTextNode( $object['valid_to'] ) );
            $domNode->appendChild( $validToNode );
        }

        $categoryPathNode = $domDocument->createElement( 'categorypaths' );
        $domNode->appendChild( $categoryPathNode );

        $categoryPathChildrenNode = $domDocument->createElement( 'categorypath' );
        $categoryPathChildrenNode->appendChild( $domDocument->createTextNode( $object['path_string'] ) );
        $categoryPathNode->appendChild( $categoryPathChildrenNode );

        // content
        $content_elements = count( $object['content'] );
        if ( $content_elements > 0 )
        {
            $contentNode = $domDocument->createElement( 'content' );
            $domNode->appendChild( $contentNode );

            foreach ( $object['content'] as $contentValue )
            {
                $attributeNode = $domDocument->createElement( 'content-data' );
                $attributeNode->setAttribute( 'key', key( $contentValue ) );
                $attributeNode->appendChild(
                    $domDocument->createCDATASection(
                        utf8_encode( current( $contentValue ) )
                    )
                );
                $contentNode->appendChild( $attributeNode );
            }
        }
        // attributes
        $content_elements = count( $object['attribute'] );
        if ( $content_elements > 0 )
        {
            $contentNode = $domDocument->createElement( 'attributes' );
            $domNode->appendChild( $contentNode );

            foreach ( $object['attribute'] as $contentValue )
            {
                $attributeNode = $domDocument->createElement( 'attribute' );
                $attributeNode->setAttribute( 'key', key( $contentValue ) );
                $attributeNode->setAttribute( 'value', current( $contentValue ) );
                $contentNode->appendChild( $attributeNode );
            }
        }
    }

    /**
     * Writes the XML $filePath with the data from $domDocument
     * @param DOMDocument $domDocument
     * @param string $filePath
     */
    public static function writeXmlFile( DOMDocument $domDocument, $filePath )
    {
        $fh = fopen( $filePath, 'w' );
        fwrite( $fh, $domDocument->saveXML() );
        fclose( $fh );
    }

    /**
     * Return the next XML bulkexport file's path
     * @param string $prefix
     * @return string
     */
    public static function getXmlFilePath( $prefix )
    {
        static $autoIncrement = 1;
        $filename = "$prefix/bulkexport_$autoIncrement.xml";
        $autoIncrement++;

        return $filename;
    }

}
