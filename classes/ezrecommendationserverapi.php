<?php
/**
 * File containing the eZRecommendationApi class
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

/**
 * High level class that provides interaction with the recommendation services
 */
class eZRecommendationServerAPI
{
    public function __construct()
    {
    }

    public function getRecommendations( eZRecommendationApiGetRecommendationsStruct $parameters )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        if ( $parameters->itemTypeId == -1 )
        {
            $parameters->itemTypeId = $this->getItemTypeId( $parameters->node->attribute( 'class_identifier' ) );
        }
        else if ( is_numeric( $parameters->itemTypeId ) )
        {
            $configuredTypesIds = array_keys(
                eZINI::instance( 'ezrecommendation.ini' )->variable( 'TypeSettings', 'Map' )
            );
            if ( !in_array( $parameters->itemTypeId, $configuredTypesIds ) )
            {
                throw new eZRecommendationApiException( "Unknown item type id {$parameters->itemTypeId}" );
            }
        }

        $itemId = $parameters->object->attribute( 'id' );

        // user ID
        $currentUser = eZUser::currentUser();
        if ( !$currentUser->isAnonymous() )
        {
            $userId = $currentUser->attribute( 'contentobject_id' );
        }
        else if ( isset( $_COOKIE['ezreco'] ) )
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
        $path .= '?' . $ini->variable( 'ParameterMapSettings', 'object_id' ).'=' . urlencode( $itemId );

        if ( $parameters->limit && $ini->hasVariable( 'ParameterMapSettings', 'numrecs' ) )
            $path .= '&' . $ini->variable( 'ParameterMapSettings', 'numrecs' ) . '=' . urlencode( $parameters->limit );

        if ( $parameters->itemTypeId )
            $path .= '&outputtypeid=' . urlencode( $parameters->itemTypeId );

        $categorypath = $parameters->node->attribute( 'path_string' );
        $path .= '&' . $ini->variable( 'ParameterMapSettings', 'path_string' ) . '=' . urlencode( ezRecoTemplateFunctions::getCategoryPath( $categorypath ) );
        $path .= '&recommendCategory=true';

        try
        {
            $recommendations = ezRecoFunctions::send_reco_request( $ini->variable( 'URLSettings', 'RecoURL' ), $path );
            return self::processRawRecommendations( $recommendations );
        }
        catch ( eZRecommendationException $e )
        {
            throw new eZRecommendationApiException( $e->getMessage() );
        }

    }

    /**
     * Returns the item type id mapped to a class
     */
    private function getItemTypeId( $classIdentifier )
    {
        $classId = eZContentClass::classIDByIdentifier( $classIdentifier );
        $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $classId );

        return ( isset( $arr['result'] ) && count( $arr['result'] ) ) ? $arr['result']['recoItemType'] : false;
    }

    /**
     * Processes a raw array of recommendations into an array
     * @param array $rawRecommendations
     * @return array|bool
     */
    private function processRawRecommendations( $rawRecommendations )
    {
        $recommendations = array();

        foreach ( $rawRecommendations as $rec )
        {
            foreach ( $rec as $rec2 )
            {
                $row = array(
                    'reason' => $rec2->reason,
                    'itemType' => $rec2->itemType,
                    'itemId' => $rec2->itemId,
                    'relevance' => $rec2->relevance,
                    'category' => property_exists( $rec2, 'category' ) ? $rec2->category : null
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
     * Deletes item with node ID $node in the recommendation index
     * @param int $nodeID
     *
     * @return bool
     */
    public function deleteItem( $nodeID )
    {
        if ( $node = eZContentObjectTreeNode::fetch( $nodeID ) )
        {
            $classAttributesList = ezRecommendationClassAttribute::fetchClassAttributeList( $node->ContentObject->ClassID );
            if ( !isset( $classAttributesList['result']['recoItemType'] )  )
                return false;
            ezRecoFunctions::sendDeleteItemRequest( $classAttributesList['result']['recoItemType'] . '/' . $node->attribute( 'object' )->attribute( 'id' ) );
            eZDebugSetting::writeDebug( 'ezrecommendation-extension', 'Delete event on node $nodeID executed' );
            return true;
        }
        else
        {
            eZDebug::writeError(
                "Unable to find node with ID $nodeID",
                "[ezrecommendation] eZRecommendationApi::deleteItem( $nodeID)"
            );
            return false;
        }
    }

    public function exportObject( $objectID )
    {
        $solution = eZINI::instance( 'ezrecommendation.ini' )->variable( 'SolutionSettings', 'solution' );

        $contentObject = eZContentObject::fetch( $objectID );
        $xmlhandler = new eZRecoXMLHandler;
        if ( $xml = $xmlhandler->generateContentObjectXML( $contentObject ) )
        {
            ezRecoFunctions::sendExportContent( $xml, $solution );
            return true;
        }
        else
        {
            return false;
        }

    }

    /**
     * Returns the list of configured scenarii on the yoochoose server
     *
     * @return array an array, indexed by scenario ID, of hashes with 3 keys: id, title and description
     */
    public static function getScenarioList()
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        $url = sprintf(
            'https://%s/restfrontend/ebl/v3/%d/structure/get_scenario_list',
            $ini->variable( 'URLSettings', 'ConfigURL' ),
            $ini->variable( 'ClientIdSettings', 'CustomerID' )
        );
        $request = new ezpHttpRequest( $url );
        $request->setOptions(
            array(
                'httpauthtype' => HTTP_AUTH_BASIC,
                'httpauth' => $ini->variable( 'ClientIdSettings', 'CustomerID' ) . ':' . $ini->variable( 'ClientIdSettings', 'LicenseKey' )
            )
        );
        try
        {
            $response = $request->send();
        }
        catch ( HttpRuntimeException $e )
        {
            eZDebugSetting::writeError( 'extensionezrecommendation', $e->getMessage() );
        }

        $rawScenarioList = json_decode( $response->getBody() );
        if ( !is_object( $rawScenarioList ) || !count( $rawScenarioList->scenarioInfoList ) )
            return array();

        $scenarioList = array();
        foreach ( $rawScenarioList->scenarioInfoList as $rawScenario )
        {
            if ( $rawScenario->enabled != 'ENABLED' )
                continue;

            $scenarioList[$rawScenario->referenceCode] = array(
                'id' => $rawScenario->referenceCode,
                'title' => $rawScenario->title,
                'description' => $rawScenario->description
            );
        }

        return $scenarioList;
    }

    /**
     * Instance of ezrecommendation.ini
     * @var eZINI
     */
    private $ini;
}
