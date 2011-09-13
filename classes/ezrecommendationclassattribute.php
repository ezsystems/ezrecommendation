<?php

class eZRecommendationClassAttribute
{
    function eZRecommendationClassAttribute()
    {
    }

     static function  fetchClassAttributeList( $classID )
    { 
	
        $contentClassAttributeList = array();
		$result = array();
        $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_MODIFIED );
        if ( !is_object( $contentClass ) )
            $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_TEMPORARY );
        if ( !is_object( $contentClass ) )
            $contentClass = eZContentClass::fetch( $classID, true, eZContentClass::VERSION_STATUS_DEFINED );

        if ( is_object( $contentClass ) )
        {
            $contentClassAttributeList = $contentClass->fetchAttributes();
       
		
			foreach ($contentClassAttributeList as $thisAttribute)			
			{
				
				if ( $thisAttribute->DataTypeString == 'ezrecommendation' )
				{
						$result['ycItemType']=  $thisAttribute->DataInt1 ;
						$result['ycRecommend']=  $thisAttribute->DataInt2 ;
						$result['ycExport']=  $thisAttribute->DataInt3 ;
						$result['ycTimeTrigger']=  $thisAttribute->DataInt4 ;
						$result['ycXmlMap']=  $thisAttribute->DataText5 ;
				}	
					
			}
		}
	
		
        if ( $contentClassAttributeList === null )
            return array( 'error' => array( 'error_type' => 'kernel',
                                            'error_code' => eZError::KERNEL_NOT_FOUND ) );
        return array( 'result' => $result );
    }
}

?>
