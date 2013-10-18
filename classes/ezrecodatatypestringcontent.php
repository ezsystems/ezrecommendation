<?php
/**
 * File containing the eZRecoDataTypeContent class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */
class eZRecoDataTypeContent
{
    static function checkDatatypeString( $classID, $dataMap, $attributeIdentifier, $key = null )
    {
        switch ( $dataMap[$attributeIdentifier]->DataTypeString )
        {
            case 'ezxmltext':
                $Content = $dataMap[$attributeIdentifier]->content();
                $ContentOutput = $Content->attribute( 'output' );
                $ContentOutputText = $ContentOutput->attribute( 'output_text' );

                $return = preg_replace( '/<!--(.*)-->/Uis', '', $ContentOutputText );
                // $return = '';
                break;
                /*Another way to get the xml content*/
                //$XMLContent = $dataMap[$attributeIdentifier]->DataText;
                //$outputHandler = new eZXHTMLXMLOutput( $XMLContent, false, $contentObjectAttribute );
                //$htmlContent =& $outputHandler->outputText();
                //return $htmlContent;
                //or
                //$XMLContent = $dataMap[$attributeIdentifier]->attribute( 'data_text' );
                //$outputHandler = new eZXHTMLXMLOutput( $XMLContent, false, $contentObjectAttribute );
                //$htmlContent =& $outputHandler->outputText();
                //return $htmlContent;

            case 'ezkeyword':
                if ( $key == "tags" )
                {
                    //dataMap doesn't contain the tags as KeywordArray
                    //used from Bulk Interface
                    // also Using current language
                    $db = eZDB::instance();
                    $query = "SELECT keyword
                              FROM ezkeyword
                              LEFT JOIN ezkeyword_attribute_link ON (ezkeyword_attribute_link.objectattribute_id = " . $dataMap[$attributeIdentifier]->ID . ")
                              WHERE ezkeyword_attribute_link.keyword_id = ezkeyword.id AND class_id = " . $classID . "
                              GROUP BY ezkeyword.id";

                    $keywordsArray = array();
                    foreach ( $db->arrayQuery( $query ) as $row )
                    {
                        $keywordsArray[] = $row['keyword'];
                    }
                    $keywords = implode( ',', $keywordsArray );

                    $return = substr( $keywords, 0, -1 );
                }
                else
                {
                    //dataMap contains the tags as KeywordArray
                    //used by one content export
                    $ContentArray = $dataMap[$attributeIdentifier]->Content;
                    $keywords = implode( ',', $ContentArray->KeywordArray );

                    $return = $keywords;
                }
                break;

            case 'ezdatetime':
            case 'ezdate':
                $unixDate = $dataMap[$attributeIdentifier]->DataInt;
                if ( $unixDate == 0 )
                {
                    switch ( $key )
                    {
                        case 'validfrom';
                            $db = eZDB::instance();
                            $query = "SELECT published FROM ezcontentobject WHERE id = " . $dataMap[$attributeIdentifier]->ContentObjectID . " AND current_version = " . $dataMap[$attributeIdentifier]->Version;
                            $rows = $db->arrayQuery( $query );
                            $unixDate = $rows[0]['published'];

                            return date( "Y-m-d", $unixDate ) . 'T' . date( "H:i:s", $unixDate );

                        case 'validto';
                            $unixDate = '2147483647';

                            return date( "Y-m-d", $unixDate ) . 'T' . date( "H:i:s", $unixDate );

                        default:
                            return 0;
                    }
                }
                $return = date( "Y-m-d", $unixDate ) . 'T' . date( "H:i:s", $unixDate );
                break;

            case 'ezprice':
                $return = $dataMap[$attributeIdentifier]->SortKeyInt;
                break;

            case 'ezmultiprice':
                $datatypeCurrencySetting = $key;
                $contentObjectAttrVersion = $dataMap[$attributeIdentifier]->Version;
                $contentObjectAttrID = $dataMap[$attributeIdentifier]->ID;

                $db = eZDB::instance();
                $query = "SELECT value FROM ezmultipricedata
                          WHERE contentobject_attr_id = " . $contentObjectAttrID . " AND contentobject_attr_version = " . $contentObjectAttrVersion . " AND currency_code = '" . $datatypeCurrencySetting . "'";

                $rows = $db->arrayQuery( $query );
                $return = $rows[0]['value'] * 100;
                break;

            case 'ezimage':
                $return = null;
                break;

            case 'ezboolean':
                $return = $dataMap[$attributeIdentifier]->DataInt;
                break;

            case 'ezauthor':
                $authorsArray = array();
                foreach ( $dataMap[$attributeIdentifier]->attribute( 'content' )->attribute( 'author_list' ) as $author )
                {
                    $authorsArray[] = $author['name'];
                }
                $return = implode( ",", $authorsArray );
                break;

            default:
                $return = $dataMap[$attributeIdentifier]->DataText;
        }
        return $return;
    }
}
?>
