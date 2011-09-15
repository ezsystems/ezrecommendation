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
				return $ContentOutputText;DataTypeStringContent;
				/*Another way to get the xml content*/
				//$XMLContent = $dataMap[$attributeIdentifier]->DataText;
				//$outputHandler = new eZXHTMLXMLOutput( $XMLContent, false, $contentObjectAttribute );
				//$htmlContent =& $outputHandler->outputText();
				return $htmlContent;
			break;
			
			case 'ezkeyword':
			
			
				$db = eZDB::instance(); 
				$query  = "select keyword from ezkeyword 
						left join ezkeyword_attribute_link ON (ezkeyword_attribute_link.objectattribute_id = ".$dataMap[$attributeIdentifier]->ID.")
						where class_id = ".$classID."
						Group By ezkeyword.id";
				$rows = $db -> arrayQuery( $query );
				$rowCount = count($rows);
				for ($i = 0 ; $i <= $rowCount ; ++$i)
				{
					$arr[] = $rows[$i]['keyword'];
				}
				$str = implode (',', $arr); 
				return substr($str,0,-1);				
			break;
			
			case 'ezdatetime':
			
				$unixDate = $dataMap[$attributeIdentifier]->DataInt ;
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
						   echo $query;
				$rows = $db -> arrayQuery( $query );
				
				 return $rows[0]['value'] * 100;

			break;
			
			default:
				return $dataMap[$attributeIdentifier]->DataText;
			break;
		}
		
	}

}

?>