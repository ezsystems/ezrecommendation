<?php
/**
 * File containing the eZRecommendationFunctionCollection class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
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

    public function getRecommendationValue( $data )
    {
        $recommendationValue = ezRecommendationXml::getNodeAttributeValue( $data, 'recommendation-enable' );
        return array( 'result' => $recommendationValue );
    }

    function getCurrencyValues(){

        $systemCurrency = array();
        $db = eZDB::instance();
        $row = $db -> arrayQuery( "SELECT code FROM ezcurrencydata", array( 'column' => 'code' ) );
        $rowCount = count($row);
        if ($rowCount == 0)
        {
            $ini = eZINI::instance('ezrecommendation.ini');
            if ($ini->hasVariable( 'ShopPriceCurrency', 'defaultCurrency' ) && $ini->variable( 'ShopPriceCurrency', 'defaultCurrency' ) != "")
                $systemCurrency = array ('0' => $ini->variable( 'ShopPriceCurrency', 'defaultCurrency' ) );
            else
            {
                eZDebug::writeError( '[ezrecommendation] Missing defaultCurrency in ezrecommendation.ini.' );
                eZDebug::writeWarning( "[ezrecommendation] Missing defaultCurrency in ezrecommendation.ini." );
            }
        }
        else
        {
            $systemCurrency = $row;
        }


         return array( 'result' => $systemCurrency );

    }

    /**
     * Fetches available scenarii from server/cache
     *
     * @return array an array of hashes with the keys id, title and description
     */
    public function getAvailableScenario()
    {
        $api = new eZRecommendationAPI;
        return array( 'result' => $api->getScenarioList() );
    }
}
?>
