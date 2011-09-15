<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezyoochoose
 */

class eZRecommendationFunctionCollection
{
    function eZRecommendationFunctionCollection()
    {
    }

    function  fetchClassAttributeList( $classID )
    {
        $contentClassAttributeList = array();
        $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_MODIFIED );
        if ( !is_object( $contentClass ) )
            $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_TEMPORARY );
        if ( !is_object( $contentClass ) )
            $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_DEFINED );

        if ( is_object( $contentClass ) )
        {
            $contentClassAttributeList = $contentClass->fetchAttributes();
			
        }

        if ( $contentClassAttributeList === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => eZError::KERNEL_NOT_FOUND ) );
		
	
        return array( 'result' => $contentClassAttributeList );
    }
	
	function getRecommendationValue($data)
	{
		
		$recommendationValue = ezyRecommendationXml::getNodeAttributeValue($data, 'recommendation-enable');
		
		return $recommendationValue;

	}
	
	function getCurrencyValues(){
	
		$systemCurrency = array();
		$db = eZDB::instance(); 
		$query  = "SELECT code FROM `ezcurrencydata` ";
		$row = $db -> arrayQuery( $query );
		$rowCount = count($row);
		if ($rowCount == 0)
		{
			$ini = eZINI::instance('ezyoochoose.ini');
			if ($ini->hasVariable( 'ShopPriceCurrency', 'defaultCurrency' ) && $ini->variable( 'ShopPriceCurrency', 'defaultCurrency' ) != "")
				$systemCurrency = array ('0' => $ini->variable( 'ShopPriceCurrency', 'defaultCurrency' ) );
			else
			{
				eZLog::write('eZYoochoose: Missing defaultCurrency in ezyoochoose.ini.', 'error.log', 'var/log');
				eZDebug::writeWarning( "eZYoochoose: Missing defaultCurrency in ezyoochoose.ini." );
			}	
		}	
		else
		{
			for ($i = 0 ; $i < $rowCount ; ++$i)
				{
					$systemCurrency[] = $row[$i]['code'];
				}
		}
			

		 return array( 'result' => $systemCurrency );
	
	}
}

?>
