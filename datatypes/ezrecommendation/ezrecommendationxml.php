<?php
/**
 * File containing the ezRecommendationXml class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class ezRecommendationXml
{

    static function ezRecommendationArrContent ($xmlText)
    {

        $doc = ezRecommendationXml::parseXML( $xmlText );
        $root = $doc->documentElement;

        $type = $root->getElementsByTagName( 'itemtypeid' )->item( 0 );
        if ( $type )
        {
            $content['itemtypeid'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'recommendation-enable' )->item( 0 );
        if ( $type )
        {
            $content['recommendation-enable'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'export-enable' )->item( 0 );
        if ( $type )
        {
            $content['export-enable'] = $type->getAttribute( 'value' );
        }
        //
        $type = $root->getElementsByTagName( 'validfrom' )->item( 0 );
        if ( $type )
        {
            $content['validfrom'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'validto' )->item( 0 );
        if ( $type )
        {
            $content['validto'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'price' )->item( 0 );
        if ( $type )
        {
            $content['price'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'currency' )->item( 0 );
        if ( $type )
        {
            $content['currency'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'title' )->item( 0 );
        if ( $type )
        {
            $content['title'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'abstract' )->item( 0 );
        if ( $type )
        {
            $content['abstract'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'author' )->item( 0 );
        if ( $type )
        {
            $content['author'] = $type->getAttribute( 'value' );
        }

        $type = $root->getElementsByTagName( 'newsagency' )->item( 0 );
        if ( $type )
        {
        $content['newsagency'] = $type->getAttribute( 'value' );
        }
         $type = $root->getElementsByTagName( 'vendor' )->item( 0 );
        if ( $type )
        {
            $content['vendor'] = $type->getAttribute( 'value' );
        }
         $type = $root->getElementsByTagName( 'geolocation' )->item( 0 );
        if ( $type )
        {
            $content['geolocation'] = $type->getAttribute( 'value' );
        }
         $type = $root->getElementsByTagName( 'date' )->item( 0 );
        if ( $type )
        {
            $content['date'] = $type->getAttribute( 'value' );
        }
        $type = $root->getElementsByTagName( 'tags' )->item( 0 );
        if ( $type )
        {
            $content['tags'] = $type->getAttribute( 'value' );
        }

        //Client Adding mapping
        $type = $root->getElementsByTagName( 'counter' )->item( 0 );
        if ( $type )
        {
            $content['counter'] = $type->getAttribute( 'value' );
        }
        for ( $i = 1 ; $i <= $content['counter'] ; ++$i )
        {
            $type = $root->getElementsByTagName( 'addtomap'.$i )->item( 0 );
            if ( $type )
            {
                $content['addtomap'.$i] = $type->getAttribute( 'value' );
            }
        }
         return $content;
    }

    static function getNodeAttributeValue($xml, $node)
    {
        if ( trim( $xml ) == '' )
        {
            return false;
        }

        $doc = ezRecommendationXml::parseXML( $xml );
        $root = $doc->documentElement;
        $type = $root->getElementsByTagName( $node )->item( 0 );
        if ( $type )
        {
            $nodeValue = $type->getAttribute( 'value' );
        }

        return $nodeValue;
    }

    static function setNodeAttributeValue($xml, $node, $value)
    {
        if ( !$doc = ezRecommendationXml::parseXML( $xml ) )
        {
            return false;
        }

        $root = $doc->documentElement;
        $type = $root->getElementsByTagName( $node )->item( 0 );

        $type->setAttribute('value',$value);

        $docText = $doc->saveXML();

        return $docText;

    }

    static function parseXML( $xml )
    {
        if ( trim( $xml ) == '' )
        {
            return false;
        }

        $dom = new DOMDocument;
        $dom->loadXML( $xml );
        return $dom;
    }
}
?>
