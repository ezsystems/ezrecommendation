<?php
class eZRecommendationApi
{
    public function __construct()
    {
    }

    public function getRecommendations( eZRecommendationApiGetRecommendationsStruct $parameters )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        $recoitemtypeid = '';

        // item type id
        $itemTypeId = $this->getItemTypeId( $parameters->node->attribute( 'class_identifier' ) );

        $itemId = $parameters->node->attribute( 'node_id' );

        // user ID
        $currentUser = eZUser::currentUser();
        if ( !$currentUser->isAnonymous() )
        {
            $userId = $currentUser->attribute( 'contentobject_id' );

        }
        elseif( isset( $_COOKIE['ezreco'] ) )
        {
            $userId = $_COOKIE['ezreco'];
        }
        else
        {
            throw new InvalidArgumentException(
                ezpI18n::tr(
                    'extension/ezrecommendation',
                    'Unable to fetch user id / ezreco cookie'
                )
            );
        }

        // URL generation
        $extension = $ini->variable( 'ExtensionSettings', 'usedExtension' );
        $clientId = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $productId = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );

        $path = "/$productId/$clientId/$userId/{$parameters->scenario}.$extension";
        $path .= '?' . $ini->variable( 'ParameterMapSettings', 'node_id' ).'=' . urlencode( $itemId );

        if ( $parameters->limit && $ini->hasVariable( 'ParameterMapSettings', 'numrecs' ) )
            $path .= '&' . $ini->variable( 'ParameterMapSettings', 'numrecs' ) . '=' . urlencode( $parameters->limit );

        if ( $ini->hasVariable( 'ParameterMapSettings', 'class_id' ) )
            $path .= '&' . $ini->variable( 'ParameterMapSettings', 'class_id' ).'=' . urlencode( $itemTypeId );

        if ( $parameters->isCategoryBased )
        {
            $categorypath = $node->attribute( 'path_string' );
            if  (!empty( $categorypath ) )
            {
                $path .= '&' . $ini->variable( 'ParameterMapSettings', 'path_string' )
                    . '=' . urlencode( ezRecoTemplateFunctions::getCategoryPath( $categorypath ) );
            }
        }

        try {
            $recommendations = ezRecoFunctions::send_reco_request( $ini->variable( 'URLSettings', 'RecoURL' ), $path );
            return self::processRawRecommendations( $recommendations );
        } catch( eZRecommendationException $e ) {
            eZDebug::writeError( $e, "An error occured while fetching recommendations" );
            return false;
        }

    }

    /**
     * Returns the item type id mapped to a class
     */
    private function getItemTypeId( $classIdentifier )
    {
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $classId );

        return count($arr['result'] ) > 0 ? $arr['result']['recoItemType'] : false;
    }

    /**
     * Processes a raw array of recommendations into an array
     * @param array $rawRecommendations
     * @return array|bool
     */
    private function processRawRecommendations( $rawRecommendations )
    {
        $recommendations = array();

        foreach( $rawRecommendations as $rec )
        {
            foreach( $rec as $rec2 )
            {
                $row = array(
                    'reason' => $rec2->reason,
                    'itemType' => $rec2->itemType,
                    'itemId' => $rec2->itemId,
                    'relevance' => $rec2->relevance
                );
                $recommendations[] = $row;
            }
        }

        if ( !empty( $recommendations ) )
        {
            return $recommendations;
        }

        return false;
    }

    /**
     * Instance of ezrecommendation.ini
     * @var eZINI
     */
    private $ini;
}
?>