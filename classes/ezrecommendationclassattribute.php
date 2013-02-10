<?php

/**
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class ezRecommendationClassAttribute
{
    private static $classAttributeList = array();

    function ezRecommendationClassAttribute()
    {
    }

    static function  fetchClassAttributeList( $classID )
    {
        if ( !isset( self::$classAttributeList[$classID] ) )
        {
            $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_MODIFIED );
            if ( !is_object( $contentClass ) )
                $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_TEMPORARY );
            if ( !is_object( $contentClass ) )
                $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_DEFINED );

            if ( is_object( $contentClass ) )
            {
                $contentClassAttributeList = $contentClass->fetchAttributes( false, false );
                foreach ( $contentClassAttributeList as $thisAttribute )
                {
                    if ( $thisAttribute['data_type_string'] == 'ezrecommendation' )
                    {
                        self::$classAttributeList[$classID] = array(
                            'recoItemType' => $thisAttribute['data_int1'],
                            'recoRecommend' => $thisAttribute['data_int2'],
                            'recoExport' => $thisAttribute['data_int3'],
                            'recoTimeTrigger' => $thisAttribute['data_int4'],
                            'recoXmlMap' => $thisAttribute['data_text5']
                        );
                        break;
                    }
                }
            }
        }

        if ( !isset( self::$classAttributeList[$classID] ) )
        {
            return array(
                'error' => array(
                    'error_type' => 'kernel',
                    'error_code' => eZError::KERNEL_NOT_FOUND
                )
            );
        }

        return array( 'result' => self::$classAttributeList[$classID] );
    }
}

?>
