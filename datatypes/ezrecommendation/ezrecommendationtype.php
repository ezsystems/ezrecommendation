<?php

/**
 * File containing the eZyoochooseFunctions class for generating the html output
 *
 * @copyright Copyright (C) 2010-2011 yoochoose GmbH. All rights reserved.
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */
 
class eZRecommendationType extends eZDataType
{
    const DATA_TYPE_STRING = "ezrecommendation";
	
	const ITEM_TYPE_VALUE_FIELD = "data_int1";
	const ITEM_TYPE_VALUE_VARIABLE = "_ezrecommendation_class_yc_item_type_value_";	
	
	const RECOMMEND_VALUE_FIELD = "data_int2";
	const RECOMMEND_VALUE_VARIABLE = "_ezrecommendation_page_yc_recommend_value_";
	
	const EXPORT_VALUE_FIELD = "data_int3";
	const EXPORT_VALUE_VARIABLE = "_ezrecommendation_page_yc_export_value_";

	const TTL_VALUE_FIELD = "data_int4";
	const TTL_VALUE_VARIABLE = "_ezrecommendation_page_yc_ttl_value_";
	
	
	const MAPPING_VALUE_FIELD = "data_text5";
	const VALIDFROM_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_validfrom_"; 
	const VALIDTO_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_validto_";
	const PRICE_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_price_"; 
	const CURRENCY_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_currency_";
	const TITLE_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_title_";
	const ABSTRACT_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_abstract_";
	const AUTHOR_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_author_";
	const NEWSAGENCY_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_newsagency_";
	const VENDOR_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_vendor_";
	const GEOLOCATION_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_geolocation_";
	const DATE_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_date_";
	const TAGS_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_tags_";
	
	const ADDTOMAP_VALUE_VARIABLE = "_ezrecommendation_attribute_mapping_addtomap_";
	public $solution = '';
	
    function eZRecommendationType()
    {
	
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Recommendation", 'Datatype name' ),
                           array( 'serialize_supported' => true ) );
        $this->IntegerValidator = new eZIntegerValidator();
		
				$ini = eZINI::instance('ezyoochoose.ini');
		if ($ini->hasVariable( 'SolutionSettings', 'solution' ))
			$this->solution = $ini->variable( 'SolutionSettings', 'solution' );
    }


	/*************The Object Attributes******************/    
    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
	
		$classAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( $contentObjectAttribute->validateIsRequired() and
             !$classAttribute->attribute( 'is_information_collector' ) )
        {
            if ( $http->hasPostVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) ) )
            {
                $data = $http->postVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) );
                if ( isset( $data ) )
				
                    return eZInputValidator::STATE_ACCEPTED;
					
            }
            else
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                     'Input required.' ) );
																	 
                return eZInputValidator::STATE_INVALID;
            }
        }
		
        return eZInputValidator::STATE_ACCEPTED;        
    }

    function fixupObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {

        if ( $currentVersion != false )
        {
			$dataText = $originalContentObjectAttribute->attribute( "data_text" );
            $contentObjectAttribute->setAttribute( "data_text", $dataText );
        }
        else
        {
            $contentClassAttribute = $contentObjectAttribute->contentClassAttribute();
            $defaultText = $contentClassAttribute->attribute( "data_text5" );
            $contentObjectAttribute->setAttribute( "data_text", $defaultText );			
        }	
    }

    function validateCollectionAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
	
		if ( $contentObjectAttribute->validateIsRequired() )
        {
            if ( $http->hasPostVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) ) )
            {
                $data = $http->postVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) );
                if ( isset( $data ) )
                    return eZInputValidator::STATE_ACCEPTED;
            }
            else
            { 
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes',
                                                                     'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        }   
		
         
    }	
    /*!
     Fetches the http post var integer input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {

		if ( $http->hasPostVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) ))
        {
            $data = $http->postVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) );
            if ( isset( $data ) && $data !== '0' && $data !== 'false' )
                $data = 1;
            else
                $data = 0;
        }
        else
        {
            $data = 0;
        }

		 $xmlDataText = $contentObjectAttribute->attribute( 'data_text' );
		 $newXml = ezyRecommendationXml::setNodeAttributeValue($xmlDataText, 'recommendation-enable',$data)	;
		 
		$contentObjectAttribute->setAttribute( 'data_text' , $newXml );	

        return true;
    }



    /*!
     Fetches the http post variables for collected information
    */
    function fetchCollectionAttributeHTTPInput( $collection, $collectionAttribute, $http, $base, $contentObjectAttribute )
    {
        if ( $http->hasPostVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) ))
        {
            $data = $http->postVariable( $base . "_data_recommendation_" . $contentObjectAttribute->attribute( "id" ) );
            if ( isset( $data ) && $data !== '0' && $data !== 'false' )
                $data = 1;
            else
                $data = 0;
        }
        else
        {
            $data = 0;
        }
		 $xmlDataText = $contentObjectAttribute->attribute( 'data_text' );
		 $newXml = ezyRecommendationXml::setNodeAttributeValue($xmlDataText, 'recommendation-enable',$data)	;
		
		 
		$contentObjectAttribute->setAttribute( 'data_text' , $newXml );	
        return true;
    }
    


    /*!
     Does nothing, the data is already present in the attribute.
    */
    function storeObjectAttribute( $object_attribute )
    {
	
    }


	/*************The Class Attributes*******************/
	function storeDefinedClassAttribute( $attribute )
    {
    }	
    function storeClassAttribute( $attribute, $version )
    {
    }
	
    function fixupClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        
    }		
	/*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( $classAttribute )
    {
        if ( $classAttribute->attribute( self::TTL_VALUE_FIELD ) == null )
            $classAttribute->setAttribute( self::TTL_VALUE_FIELD, 20 );
        if ( $classAttribute->attribute( self::RECOMMEND_VALUE_FIELD ) == null )
            $classAttribute->setAttribute( self::RECOMMEND_VALUE_FIELD, 1 );	
        if ( $classAttribute->attribute( self::EXPORT_VALUE_FIELD ) == null )
            $classAttribute->setAttribute( self::EXPORT_VALUE_FIELD, 1 );
        if ( $classAttribute->attribute( self::ITEM_TYPE_VALUE_FIELD ) == null )
            $classAttribute->setAttribute( self::ITEM_TYPE_VALUE_FIELD, 1 );			
        $classAttribute->store();
    }
	
    function validateClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
		$flagValidation = '';
		
		$ttlValueName = $base . self::TTL_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$recommendValueName = $base . self::RECOMMEND_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$exportValueName = $base . self::EXPORT_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$itemTypeValueName = $base . self::ITEM_TYPE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$validfromTypeValueName = $base . self::VALIDFROM_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$validtoTypeValueName = $base . self::VALIDTO_VALUE_VARIABLE . $classAttribute->attribute( "id" );		
		$priceTypeValueName = $base . self::PRICE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$currencyTypeValueName = $base . self::CURRENCY_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		 
	
		//check required fields
	
		if ($this->solution == 'publisher' && $http->postVariable( $exportValueName ) != '')
		{
			if ( $http->postVariable( $validfromTypeValueName ) == '0' ||  $http->postVariable( $validtoTypeValueName ) == '0')
				$flagValidation .= '[eZYoochoose]: Missing required Field for the publisher solution.' ;
							
			
		}
		
		if ($this->solution == 'shop' && $http->postVariable( $exportValueName ) != '')
		{
			if ( $http->postVariable( $priceTypeValueName ) == '0' ||  $http->postVariable( $currencyTypeValueName ) == '')
				$flagValidation .= '[eZYoochoose]: Missing required Field for the shop solution.' ;
							
			
		}
		


		//check ttl
        if ( $http->hasPostVariable( $ttlValueName ) )
        {
		

			$ttlValueValue = $http->postVariable( $ttlValueName );
			$ttlValueValue = str_replace(" ", "", $ttlValueValue );
			if ( ( $ttlValueValue != "" ) )
			{	
				$ttl_state = $this->IntegerValidator->validate( $ttlValueValue );
				if ( ( $ttl_state != eZInputValidator::STATE_ACCEPTED ) )
				{
					$flagValidation .= '[eZYoochoose]: Wrong Format (Time to trigger consumption event)';
					
				}
			
			
			}
			
			
        }
		
		if ($flagValidation == '')
			return eZInputValidator::STATE_ACCEPTED;
		else
		{
			eZLog::write($flagValidation, 'error.log', 'var/log');
			eZDebug::writeError( $flagValidation );
			
			return eZInputValidator::STATE_INVALID;
		}

        return eZInputValidator::STATE_INVALID;
    }



    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {

	    $itemTypeValueName = $base . self::ITEM_TYPE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
        $recommendValueName = $base . self::RECOMMEND_VALUE_VARIABLE . $classAttribute->attribute( "id" );
        $exportValueName = $base . self::EXPORT_VALUE_VARIABLE . $classAttribute->attribute( "id" );
    	$ttlValueName = $base . self::TTL_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		


		//get item type 
		$itemTypeXMLNode = '';
        if ( $http->hasPostVariable( $itemTypeValueName ) )
        {
			$itemTypeValueNameValue = $http->postVariable( $itemTypeValueName );
			$itemTypeXMLNode = $itemTypeValueNameValue;
			$classAttribute->setAttribute( self::ITEM_TYPE_VALUE_FIELD, $itemTypeValueNameValue  );
		}

		
		//check recommend (enable/disable) 
		if ( $http->hasPostVariable( $base . self::RECOMMEND_VALUE_VARIABLE . $classAttribute->attribute( 'id' ) . '_exists' ) )
        {	
			if ( $http->hasPostVariable( $recommendValueName ) )
			{
				$recommendValueValue = $http->postVariable( $recommendValueName );
				
				if ( isset( $recommendValueValue ) )
				{
					$recommendValueValue = 1;
					$classAttribute->setAttribute( self::RECOMMEND_VALUE_FIELD, $recommendValueValue  );
				}
			}
			else
			{	
					$classAttribute->setAttribute( self::RECOMMEND_VALUE_FIELD, 0 );
			}
		}
		
		//check content export (enable/disable) 
		if ( $http->hasPostVariable( $base . self::EXPORT_VALUE_VARIABLE . $classAttribute->attribute( 'id' ) . '_exists' ) )
        {
			$exportValueNameValue = '';
			if ( $http->hasPostVariable( $exportValueName ) )
			{
				$exportValueNameValue = $http->postVariable( $exportValueName );
				
				if ( isset( $exportValueNameValue ) )
				{
					$exportValueNameValue = 1;
					$classAttribute->setAttribute( self::EXPORT_VALUE_FIELD, $exportValueNameValue  );
				}
			}
			else
			{
					$classAttribute->setAttribute( self::EXPORT_VALUE_FIELD, 0 );
			}
		}
		//check ttl
        if ( $http->hasPostVariable( $ttlValueName ) )
        {
			$ttlValueValue = $http->postVariable( $ttlValueName );
			$ttlValueValue = str_replace(" ", "", $ttlValueValue );
			if ( ( $ttlValueValue == "" || $ttlValueValue == 0) )
			{
				$classAttribute->setAttribute( self::TTL_VALUE_FIELD, 0 );
			}
			else
			{
				
				$classAttribute->setAttribute( self::TTL_VALUE_FIELD, $ttlValueValue );
			}
		}

		//get attribute mapping
		$validfromValueName = $base . self::VALIDFROM_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$validtoValueName = $base . self::VALIDTO_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$priceValueName = $base . self::PRICE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$currencyValueName = $base . self::CURRENCY_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$titleValueName = $base . self::TITLE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$abstractValueName = $base . self::ABSTRACT_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$authorValueName = $base . self::AUTHOR_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$newsagencyValueName = $base . self::NEWSAGENCY_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$vendorValueName = $base . self::VENDOR_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$geolocationValueName = $base . self::GEOLOCATION_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$dateValueName = $base . self::DATE_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$tagsValueName = $base . self::TAGS_VALUE_VARIABLE . $classAttribute->attribute( "id" );
		$countAttr = $base . '_ezrecommendation_attribute_mapping_counter_' . $classAttribute->attribute( 'id' ) ;

		if ( $http->hasPostVariable( $countAttr )) 
		{
			$doc = new DOMDocument( '1.0', 'utf-8' );
            $root = $doc->createElement( 'mapping' );
            $contentList = eZRecommendationType::contentObjectArrayXMLMap($http->postVariable( $countAttr ));
            foreach ( $contentList as $key => $value )
            {
				if ( $http->hasPostVariable(  $base . '_ezrecommendation_attribute_mapping_' . $value . '_' . $classAttribute->attribute( 'id' ) ) )
				{
					$postValue = $http->postVariable( $base . '_ezrecommendation_attribute_mapping_' . $value . '_' . $classAttribute->attribute( 'id' ) );
					unset( $elementType );
					if ( $postValue != '')
					{
						$elementType = $doc->createElement( $key );
						$elementType->setAttribute( 'value', $postValue );
						$root->appendChild( $elementType );
					}
				}			
            }
			//create also itemTypeId , recommendation and export node for the workflow evaluation
			if ($exportValueNameValue == 1)
			{
				$elementType = $doc->createElement( 'export-enable' );
				$elementType->setAttribute( 'value', 1 );
				$root->appendChild( $elementType );			
			}
			else{
				$elementType = $doc->createElement( 'export-enable' );
				$elementType->setAttribute( 'value', 0 );
				$root->appendChild( $elementType );				
			}
			if ($recommendValueValue == 1)
			{
				$elementType = $doc->createElement( 'recommendation-enable' );
				$elementType->setAttribute( 'value', 1 );
				$root->appendChild( $elementType );			
			}
			else{
				$elementType = $doc->createElement( 'recommendation-enable' );
				$elementType->setAttribute( 'value', 0 );
				$root->appendChild( $elementType );				
			}	
			if($itemTypeXMLNode != '')
			{
				$elementType = $doc->createElement( 'itemtypeid' );
				$elementType->setAttribute( 'value', $itemTypeXMLNode );
				$root->appendChild( $elementType );					
			
			}
			//
            $doc->appendChild( $root );
            $docText = $doc->saveXML();
            $classAttribute->setAttribute( self::MAPPING_VALUE_FIELD , $docText );			

		}
		return true;
	}
	function contentObjectArrayXMLMap($countAdded)
    {
		$addToMap = array();
		for ($i = 1 ; $i <= $countAdded ; ++$i)
		{
			
			$addToMap ['addtomap'.$i] = 'addtomap'.$i ;
		}
	
		$Mapped = array(  'counter' => 'counter',
						  'validfrom' => 'validfrom',
						  'validto' => 'validto',
						  'price' => 'price',
						  'currency' => 'currency',
						  'title' => 'title',
						  'abstract' => 'abstract',
						  'author' => 'author',
						  'newsagency' => 'newsagency',
						  'vendor' => 'vendor',
						  'geolocation' => 'geolocation',
						  'date' => 'date',
						  'tags' => 'tags',
					);
		$FinalxmlArray = array_merge($addToMap, $Mapped);
		
        return $FinalxmlArray ;
    }
	

	function classAttributeContent( $classAttribute )
    {
        $xmlText = $classAttribute->attribute( 'data_text5' );
        if ( trim( $xmlText ) == '' )
        {
			
            $classAttrContent = eZRecommendationType::defaultClassAttributeContent();
            
			return $classAttrContent;
			
        }
		$xmlMapContent = ezyRecommendationXml::ezyRecommendationArrContent($xmlText);
        
  	
        return $xmlMapContent;
    }	
	
    function defaultClassAttributeContent()
    {
        return array(	'counter' => '',
						'validfrom' => '',
						'validto' => '',
						'price' => '',		
						'currency' => '',		
						'title' => '',
						'abstract' => '',
						'author' => '',
						'newsagency' => '',
						'vendor' => '',
						'geolocation' => '',
						'date' => '',
						'tags' => '',
					);
    }
	
    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
		$itemTypeValueName = $base . self::ITEM_TYPE_VALUE_FIELD . $classAttribute->attribute( "id" );
        $recommendValueName = $base . self::RECOMMEND_VALUE_FIELD . $classAttribute->attribute( "id" );
        $exportValueName = $base . self::EXPORT_VALUE_FIELD . $classAttribute->attribute( "id" );
    	$ttlValueName = $base . self::TTL_VALUE_FIELD . $classAttribute->attribute( "id" );
    	$mappingValueName = $base . self::MAPPING_VALUE_FIELD . $classAttribute->attribute( "id" );

		
		$dom = $attributeParametersNode->ownerDocument;

        $itemTypeValueNode = $dom->createElement( 'temType-value' );
        $itemTypeValueNode->appendChild( $dom->createTextNode( $itemTypeValueName ) );
        $attributeParametersNode->appendChild( $itemTypeValueNode );  
		
		$recommendValueNode = $dom->createElement( 'recommend-value' );
        $recommendValueNode->setAttribute( 'is-set', $recommendValueName ? 'true' : 'false' );
        $attributeParametersNode->appendChild( $recommendValueNode ); 		

		$exportValueNode = $dom->createElement( 'export-value' );
        $exportValueNode->setAttribute( 'is-set', $exportValueName ? 'true' : 'false' );
        $attributeParametersNode->appendChild( $exportValueNode ); 			
		
        $ttlValueNode = $dom->createElement( 'ttl-value' );
        $ttlValueNode->appendChild( $dom->createTextNode( $ttlValueName ) );
        $attributeParametersNode->appendChild( $ttlValueNode );   

        $mappingValueNode = $dom->createElement( 'title-map-value' );
        $mappingValueNode->appendChild( $dom->createTextNode( $mappingValueName ) );
        $attributeParametersNode->appendChild( $mappingValueNode );  		
		
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
		$itemTypeValueName = $attributeParametersNode->getElementsByTagName( 'temType-value' )->item( 0 )->textContent;
		$recommendValueName = strtolower( $attributeParametersNode->getElementsByTagName( 'recommend-value' )->item( 0 )->getAttribute( 'is-set' ) )== 'true';
		$exportValueName = strtolower( $attributeParametersNode->getElementsByTagName( 'export-value' )->item( 0 )->getAttribute( 'is-set' ) )== 'true';
		$ttlValueName = $attributeParametersNode->getElementsByTagName( 'ttl-value' )->item( 0 )->textContent;
		$mappingValueName = $attributeParametersNode->getElementsByTagName( 'title-map-value' )->item( 0 );	
		
		$classAttribute->setAttribute( self::ITEM_TYPE_VALUE_FIELD, $itemTypeValueName ); 
		$classAttribute->setAttribute( self::RECOMMEND_VALUE_FIELD, $recommendValueName );	
		$classAttribute->setAttribute( self::EXPORT_VALUE_FIELD, $exportValueName );	
		$classAttribute->setAttribute( self::TTL_VALUE_FIELD, $ttlValueName );         
		$classAttribute->setAttribute( self::MAPPING_VALUE_FIELD, $mappingValueName );         
    }

	/***************************************/

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
	
         return $contentObjectAttribute->attribute( "data_text" );
    }


    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }
    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
       return $contentObjectAttribute->setAttribute( 'data_text', $string );
    }

    /*!
     Returns the integer value.
    */
    function title( $contentObjectAttribute, $name = null )
    {
         return $contentObjectAttribute->attribute( "data_text" );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
       return true;
    }

    function isInformationCollector()
    {
        return true;
    }

    /*!
     \return true if the datatype can be indexed
    */
    function isIndexable()
    {
        return true;
    }

    function sortKey( $contentObjectAttribute )
    {
         return $contentObjectAttribute->attribute( 'data_text' );
    }

    function sortKeyType()
    {
         return 'int';
    }	
	

	function batchInitializeObjectAttributeData( $classAttribute )
    {

        $default =  $classAttribute->attribute( "data_text5" );
        $db = eZDB::instance();
        $xmlText = "'" . $db->escapeString( $default ) . "'";
        return array( 'data_text' => $xmlText );
    }

	 /*!
     \static
     \return the XML structure in \a $domDocument as text.
             It will take of care of the necessary charset conversions
             for content storage.
    */
    static function domString( $domDocument )
    {
        return $domDocument->saveXML();
    }
	
    function supportsBatchInitializeObjectAttribute()
    {
	
        return true;
    }

    /// \privatesection
    /// The integer value validator
    public $IntegerValidator;
}

eZDataType::register( eZRecommendationType::DATA_TYPE_STRING, "eZRecommendationType" );

?>
