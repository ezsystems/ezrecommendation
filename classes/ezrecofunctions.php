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
        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

        $fp = fsockopen( $url, 80, $errno, $errstr, 30);
        if ( $fp )
        {
            $out = "GET $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            eZDebugSetting::writeDebug('extension-ezrecommendation', $out, "Sending HTTP Request to $url" );
            fwrite( $fp, $out );
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );
            fclose( $fp );

            eZDebugSetting::writeDebug('extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $header, $content );

            return true;
        }
        else
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
        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying request' );

        $fp = fsockopen( $url, 80, $errno, $errstr, 30 );
        if ( $fp )
        {
            $out = "GET $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $out, "Sending request to $url" );

            fwrite( $fp, $out );
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );
            fclose( $fp );
            eZDebugSetting::writeDebug('extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );

            self::verifyHttpResponse( $header, $content );
            return json_decode( $content );
        }
        else
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }


    /*
     *
     */
    public static function sendExportContent( $data, $solution )
    {
        $ini = eZINI::instance('ezrecommendation.ini');
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );
        $path = "/$mapSetting/$customerID/item";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

        if ( $fp )
        {
            $out = "POST $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $out.$data, "Sending HTTP request to $url" );
            fwrite( $fp, $out );
            fwrite( $fp, $data );
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );
            fclose( $fp );

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $header, $content );

            return true;
        }
        else
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }


    /*
     *
     */
    public static function delete_item_request( $item_path )
    {

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/$customerID/item/$item_path";

        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

        if ($fp) {

            eZDebugSetting::writeDebug('extension-ezrecommendation', $url.$path, "Sending HTTP request" );

            $out = "DELETE $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            fwrite( $fp, $out );
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );
            fclose( $fp );

            eZDebugSetting::writeDebug('extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $header, $content );

            return true;
        }
        else
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }


    /*
     *
     */
    public static function get_stats_request(){

        $ini = eZINI::instance('ezrecommendation.ini');
        $url = $ini->variable( 'URLSettings', 'ConfigURL' );
        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );

        $path = "/$mapSetting/v3/$customerID/revenue/last_seven_days";

        eZDebugSetting::writeNotice( 'extension-ezrecommendation', $url.$path, 'Trying stats HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);
        if ( $fp )
        {
            $out = "GET $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', $out, "Sending HTTP request to $url" );
            fwrite($fp, $out);
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );

            fclose( $fp );

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $header, $content );

            $raw_stats = json_decode( $content );
            $raw_stats2 = $raw_stats->revenueResponse;
            $raw_stats_items = $raw_stats2->items;

            $stats_array = array();
            foreach ($raw_stats_items as $stat)
            {
                $row = array();
                if ( $stat->revenue )
                {
                    $revenueArray = array();
                    foreach( $stat->revenue as $key => $value )
                    {
                        if ( !isset( $revenueArray[$key] ) )
                            $revenueArray[$key] = '';
                        $revenueArray[$key] .=  substr_replace( $value, ".", -2 ) . substr( $value, -2 );
                    }
                    $row['revenue'] = $revenueArray;
                }
                $row += array(
                    'timespanBegin' => date( "d.m.Y", strtotime( $stat->timespanBegin ) ),
                    'timespanDuration' => $stat->timespanDuration,
                    'clickEvents' => $stat->clickEvents,
                    'consumeEvents' => $stat->consumeEvents,
                    'purchaseEvents' => $stat->purchaseEvents,
                    'deliveredRecommendations' => $stat->deliveredRecommendations,
                    'clickedRecommended' => $stat->clickedRecommended,
                    'purchasedRecommended' => $stat->purchasedRecommended );

                $stats_array[] = $row;
            }
            return $stats_array;
        }
        else
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }

    /*
     *
     */
    public static function send_bulk_request($xml_url, $xml_path, $xml_file){

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );

        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/$customerID/item/upload?url=".$xml_url.'/'.$xml_path.$xml_file;

        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying bulk HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

        if ( $fp )
        {
            $out = "GET $path HTTP/1.0\r\n" .
                   "Host: $url\r\n" .
                   self::getAuthorizationHeaderLine() . "\r\n\r\n";

            eZDebugSetting::writeDebug('extension-ezrecommendation', $data, "Sending HTTP request to $url" );
            fwrite( $fp, $out );
            $header = $content = '';
            self::processHttpResponse( $fp, $header, $content );
            fclose($fp);

            eZDebugSetting::writeDebug( 'extension-ezrecommendation', compact( 'header', 'content' ), 'Received response' );
            self::verifyHttpResponse( $header, $content );

            return true;
        }
        else
        {
            eZDebug::writeError( $url, '<ezrecommendation> Could not connect to server' );
            throw new eZRecommendationApiException( "Connection failed" );
        }
    }

    /**
     * Processes an HTTP open stream after a request was sent
     * @param resource $fp
     * @param string $headers
     * @param string $content
     * @return bool
     */
    private static function processHttpResponse( $fp, &$headers, &$content )
    {
        static $receiveAnswer = null;

        if ( !isset( $receiveAnswer ) )
        {
            $ini = eZINI::instance( 'ezrecommendation.ini' );
            $receiveAnswer = $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled';
        }

        if ( $receiveAnswer )
        {
            $content = "";
            $header = "";
            $isHeaderPassed = false;

            while( !feof( $fp ) )
            {
                $line = fgets( $fp, 128 );
                if( $line == "\r\n" && $isHeaderPassed == false )
                    $isHeaderPassed = true;

                if( $isHeaderPassed == true )
                    $content .= $line;
                else
                    $headers .= $line;
            }
            $content = trim( $content );
            return true;
        }
        return false;
    }

    /**
     * ezRecoFunctions::getAuthorizationHeaderLine()
     *
     * @return
     */
    private static function getAuthorizationHeaderLine()
    {
        $ini = eZINI::instance('ezrecommendation.ini');
        $customerId = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $licenseKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
        $headerLine =  "Authorization: Basic " . base64_encode( "$customerId:$licenseKey" );

        return $headerLine;
    }

    /**
     * ezRecoFunctions::verifyHttpResponse()
     *
     * @param string $headers
     * @param string $content
     * @return
     */
    public static function verifyHttpResponse( $headers, $content )
    {
        // Check header & HTTP code
        $headers = explode( "\r\n", $headers );
        $httpResponse = $headers[0];
        if ( substr( $httpResponse, 9, 1 ) != 2 )
        {
            throw new eZRecommendationApiException( "Unexpected HTTP code" );
        }

        // Check content for Fault
        if ( $content != '' )
        {
            $result = json_decode( $content );
            // since the Fault property name is unknown, we need to iterate over the object
            foreach( $result as $property => $value )
            {
                if ( strstr( $property, 'Fault' ) !== false )
                {
                    throw new eZRecommendationException( $property, $value->message, $value );
                }
            }
        }
    }
}
