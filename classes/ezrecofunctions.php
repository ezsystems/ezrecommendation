<?php

/**
 * File containing the eZrecommendationFunctions class for sending requests to the Recommendation Engine
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */


class ezRecoFunctions
{
    /*
     *
     */
    public static function send_http_request( $url, $path )
    {
        eZDebugSetting::writeNotice( 'extension-ezrecommendation', $url . $path, 'Trying HTTP Request' );

        $request = new ezpHttpRequest( $url );
        $request->addHeaders( array( "Authorization" => self::getAuthorizationHeaderValue() ) );

        try
        {
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $request->getRawRequestMessage(), "Sending HTTP Request to $url" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );

            self::verifyHttpResponse( $response );
            return true;
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }

    }

    /*
     *
     */
    public static function send_reco_request( $url, $path )
    {
        $request = new ezpHttpRequest( "http://{$url}{$path}" );
        $request->addHeaders( array( "Authorization" => self::getAuthorizationHeaderValue() ) );
        try
        {

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $request->getRawRequestMessage(), "Sending request to $url" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $response->getBody(), 'Received response' );

            self::verifyHttpResponse( $response );

            return json_decode( $response->getBody() );
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed", $e );
        }
    }


    /*
     *
     */
    public static function sendExportContent( $data, $solution )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );
        $path = "/$mapSetting/$customerID/item";

        eZDebugSetting::writeNotice( 'extension-ezrecommendation', $url . $path, 'Trying HTTP Request' );

        $request = new ezpHttpRequest( "https://{$url}{$path}", HTTP_METHOD_POST );
        $request->addHeaders(
            array(
                "Authorization" => self::getAuthorizationHeaderValue(),
                "Content-Length" => strlen( $data )
            )
        );
        $request->addRawPostData( $data );
        try
        {
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $request->getRawRequestMessage(), "Sending HTTP request to {$url}{$path}" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $response, 'Received response' );
            self::verifyHttpResponse( $response );

            return true;
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }


    /**
     * Sends a DELETE request for item $path
     * @param string $path
     * @return bool true if the request was sent successfully
     */
    public static function sendDeleteItemRequest( $item_path )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        $url = $ini->variable( 'URLSettings', 'ExportURL' );

        $path = sprintf(
            "/%s/%s/item/%s",
            $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) ),
            $ini->variable( 'ClientIdSettings', "CustomerID" ),
            $item_path
        );

        $request = new ezpHttpRequest( "https://{$url}{$path}", HTTP_METH_DELETE );
        $request->addHeaders(
            array(
                "Authorization" => self::getAuthorizationHeaderValue(),
                "Content-Type" => "text/xml"
            )
        );

        try
        {
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', "DELETE https://{$url}{$path}", "Sending HTTP request" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $response->getBody(), 'Received response' );
            self::verifyHttpResponse( $response );

            return true;
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }


    /*
     *
     */
    public static function get_stats_request()
    {

        $ini = eZINI::instance('ezrecommendation.ini');
        $url = $ini->variable( 'URLSettings', 'ConfigURL' );
        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );

        $path = "/$mapSetting/v3/$customerID/revenue/last_seven_days";

        eZDebugSetting::writeNotice( 'extension-ezrecommendation', $url.$path, 'Trying stats HTTP Request' );

        $request = new ezpHttpRequest( "https://{$url}{$path}" );
        $request->addHeaders(
            array(
                "Authorization" => self::getAuthorizationHeaderValue(),
            )
        );
        try
        {
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $request->getRawRequestMessage(), "Sending HTTP request to $url" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );

            self::verifyHttpResponse( $response );

            $statsObject = json_decode( $response->getBody() );
            $rawStats = $statsObject->revenueResponse;
            $rawStatsItems = $rawStats->items;

            $statsArray = array();
            foreach ( $rawStatsItems as $rawStatsItem )
            {
                $row = array();
                if ( $rawStatsItem->revenue )
                {
                    $revenueArray = array();
                    foreach ( $rawStatsItem->revenue as $key => $value )
                    {
                        if ( !isset( $revenueArray[$key] ) )
                            $revenueArray[$key] = '';
                        $revenueArray[$key] .= substr_replace( $value, ".", -2 ) . substr( $value, -2 );
                    }
                    $row['revenue'] = $revenueArray;
                }
                $row += array(
                    'timespanBegin' => date( "d.m.Y", strtotime( $rawStatsItem->timespanBegin ) ),
                    'timespanDuration' => $rawStatsItem->timespanDuration,
                    'clickEvents' => $rawStatsItem->clickEvents,
                    'consumeEvents' => $rawStatsItem->consumeEvents,
                    'purchaseEvents' => $rawStatsItem->purchaseEvents,
                    'deliveredRecommendations' => $rawStatsItem->deliveredRecommendations,
                    'clickedRecommended' => $rawStatsItem->clickedRecommended,
                    'purchasedRecommended' => $rawStatsItem->purchasedRecommended
                );

                $statsArray[] = $row;
            }
            return $statsArray;
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }

    /*
     *
     */
    public static function send_bulk_request( $xml_url, $xml_path, $xml_file )
    {

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );

        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/$customerID/item/upload?url=".$xml_url.'/'.$xml_path.$xml_file;

        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying bulk HTTP Request' );

        $request = new ezpHttpRequest( "https://{$url}" );
        $request->addHeaders(
            array(
                "Authorization" => self::getAuthorizationHeaderValue(),
            )
        );

        try
        {
            eZDebugSetting::writeDebug('extension-ezrecommendation', $xml_url, "Sending HTTP request to $url" );
            $response = $request->send();
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $response );

            return true;
        }
        catch ( Exception $e )
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }

    /**
     * Returns the value for the Authorization request header
     *
     * @return string
     */
    private static function getAuthorizationHeaderValue()
    {
        $ini = eZINI::instance('ezrecommendation.ini');
        $customerId = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $licenseKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
        return "Basic " . base64_encode( "$customerId:$licenseKey" );
    }

    /**
     * Checks $response for errors
     *
     * @param HttpMessage $response
     * @return void
     * @throws eZRecommendationApiException
     * @throws eZRecommendationException
     */
    public static function verifyHttpResponse( HttpMessage $response )
    {
        // Check header & HTTP code
        if ( $response->getResponseCode() < 200 || $response->getResponseCode() >= 300 )
        {
            throw new eZRecommendationApiException( "Unexpected HTTP code" );
        }

        $body = json_decode( $response->getBody() );
        if ( !is_object( $body ) )
        {
            return;
        }

        // since the Fault property name is unknown, we need to iterate over the object
        foreach ( $body as $property => $value )
        {
            if ( strstr( $property, 'Fault' ) !== false )
            {
                throw new eZRecommendationException( $property, $value->message, $value );
            }
        }
    }
}
