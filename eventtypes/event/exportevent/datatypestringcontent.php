<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */


class dataTypeContent
{


	static function checkDatatypeString ($classID, $dataMap , $attributeIdentifier, $key = NULL)
	{
	
		switch ($dataMap[$attributeIdentifier]->DataTypeString)
		{
	
			case 'ezxmltext':
				$Content =& $dataMap[$attributeIdentifier]->content();
				$ContentOutput =& $Content->attribute('output');
				$ContentOutputText = $ContentOutput->attribute('output_text');
				return preg_replace('/<!--(.*)-->/Uis', '', $ContentOutputText);
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
			break;
			
			case 'ezkeyword':

				if ($key == "tags"){
					
					//dataMap don t contain the tags as KeywordArray
					//used from Bulk Interface
					// also Using current language
					$db = eZDB::instance(); 
					$query  = "select keyword from ezkeyword 
					
							left join ezkeyword_attribute_link ON (ezkeyword_attribute_link.objectattribute_id = ".$dataMap[$attributeIdentifier]->ID.")
							where 
							ezkeyword_attribute_link.keyword_id = ezkeyword.id and
							class_id = ".$classID."
							Group By ezkeyword.id";
							
					$rows = $db -> arrayQuery( $query );
					$rowCount = count($rows);
					for ($i = 0 ; $i <= $rowCount ; ++$i)
					{
						$arr[] = $rows[$i]['keyword'];
					}
					$keywords = implode (',', $arr); 
					
					return substr($keywords,0,-1);					
					
					
				}else{
					//dataMap contain the tags as KeywordArray
					//used by one content export
				
					$ContentArray = $dataMap[$attributeIdentifier]->Content ;
				
					$keywords = implode (',', $ContentArray->KeywordArray); 
					
					return $keywords;
				}

			break;
			
			case 'ezdatetime':
			case 'ezdate':
				$type = $key ;


				$unixDate = $dataMap[$attributeIdentifier]->DataInt ;
				if($unixDate == 0){
					switch ($key){
						case 'validfrom';
							
								$db = eZDB::instance();
								$query  = "select published from  ezcontentobject where id = ".$dataMap[$attributeIdentifier]->ContentObjectID ." and current_version = ". $dataMap[$attributeIdentifier]->Version   ;
								$rows = $db -> arrayQuery( $query );
								$unixDate = $rows[0]['published'];
								
								return date("Y-m-d", $unixDate) . 'T' . date("H:i:s", $unixDate);	
							

						break;
						case 'validto';
							$unixDate = '2147483647' ;
												 
							return date("Y-m-d", $unixDate) . 'T' . date("H:i:s", $unixDate);					
						
						break;
						default:
							return 0;
						break;	
						
					}
				}
			

				
				return date("Y-m-d", $unixDate) . 'T' . date("H:i:s", $unixDate);

			break;	
			case 'ezprice':
				//Simple Price
				 return $dataMap[$attributeIdentifier]->SortKeyInt;

			break;
			
			case 'ezmultiprice':
				//Multiple Price
				
				$datatypeCurrencySetting = $key;
				$contentObjectAttrVersion = $dataMap[$attributeIdentifier]->Version;
				$contentObjectAttrID = $dataMap[$attributeIdentifier]->ID;
				
				$db = eZDB::instance(); 
				$query  = "	SELECT value FROM ezmultipricedata 
							WHERE contentobject_attr_id = ".$contentObjectAttrID." AND contentobject_attr_version = ".$contentObjectAttrVersion." AND currency_code = '".$datatypeCurrencySetting."'";

				$rows = $db -> arrayQuery( $query );
				
				 return $rows[0]['value'] * 100;


			break;
			case 'ezimage':
					
				 return null;

			break;
			case 'ezboolean':
				
				 return $dataMap[$attributeIdentifier]->DataInt;

			break;			
			case 'ezauthor':

				$doc = ezyRecommendationXml::parseXML( $dataMap[$attributeIdentifier]->DataText );
				$root = $doc->documentElement;
		
				$nodes = $root->getElementsByTagName( 'author' );
				$nodeListLength = $nodes->length; // this value will also change
				for ($i = 0; $i < $nodeListLength; $i ++)
				{
					if ( $nodes )
					{
						$content[] = $nodes->item($i)->getAttribute( 'name' );
					}	
				}		
				return implode(",", $content);
		 
			break;
					
			default:
				return $dataMap[$attributeIdentifier]->DataText;
			break;
		}
		
	}

}

?>