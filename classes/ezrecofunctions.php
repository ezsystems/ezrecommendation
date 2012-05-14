<?php

/**
 * File containing the eZrecommendationFunctions class for sending requests to the Recommendation Engine
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */


class ezRecoFunctions{

    /*
     *
     */
    public static function send_http_request($url, $path ){

            $ini = eZINI::instance('ezrecommendation.ini');

            eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

            $fp = fsockopen( $url, 80, $errno, $errstr, 30);

            if ($fp) {

                $out = "GET ".$path." HTTP/1.0\r\n";
                $out .= "Host: ".$url."\r\n";
                $out .= "Connection: Close\r\n\r\n";

                fwrite($fp, $out);

                eZDebugSetting::writeDebug('extension-ezrecommendation', $url.$path, 'Sending HTTP Request' );

                if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){

                    $content = "";
                    $header = "";

                    $header_passed = "not yet";

                    while( !feof( $fp ) ) {
                        $line = fgets( $fp, 128 );
                        if( $line == "\r\n" && $header_passed == "not yet" ) {
                            $header_passed = "passed";
                        }
                        if( $header_passed == "passed" ) {
                            $content .= $line;
                        }else{
                            $header .= $line;
                        }
                    }

                    eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( $header, true ), 'Received response' );
                }

                fclose($fp);
                return true;

            }
            else {

                eZDebug::writeError( "url: $url", '<ezrecommendation> Could not connect to server' );
                return false;

            }

    }

    /*
     *
     */
    public static function send_reco_request($url, $path ){


        /* Begin https:443 */
/*        $ini = eZINI::instance('ezrecommendation.ini');
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);
*/        /*end https:443*/

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying request' );
        /* Begin http:80 */

        $fp = fsockopen( $url, 80, $errno, $errstr, 30);
        /*end http:80*/

        if ($fp) {

            /* Beginn https:443 */
/*            $auth=base64_encode($customerID.":".$LicKey);
            $out = "POST ".$path." HTTP/1.0\r\n";
            $out .= "Host: ".$url."\r\n";
            $out .= "Accept: text/html\r\n";
            $out = "Authorization: Basic $auth\r\n\r\n";*/
            /*end https:443*/

            /* Beginn http:80 */
            $out = "GET ".$path." HTTP/1.0\r\n";
            $out .= "Host: ".$url."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            /*end http:80*/

            eZDebugSetting::writeDebug('extension-ezrecommendation', $url.$path, 'Sending request' );

            fwrite( $fp, $out );

            $ini = eZINI::instance('ezrecommendation.ini');

            if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){

                $content = "";
                $header = "";

                $header_passed = "not yet";

                while( !feof( $fp ) ) {
                    $line = fgets( $fp, 128 );
                    if( $line == "\r\n" && $header_passed == "not yet" ) {
                        $header_passed = "passed";
                    }
                    if( $header_passed == "passed" ) {
                        $content .= $line;
                    }else{
                        $header .= $line;
                    }
                }


                eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( compact( 'header', 'content' ), true ), 'Received response' );
            }

            fclose($fp);

            return json_decode(substr( $content, 2 ) );

            }else{

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
                return false;

            }

    }


    /*
     *
     */
    public static function sendExportContent($data, $solution){

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/$customerID/item";

        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZDebugSetting::writeDebug('extension-ezrecommendation', $data, "Sending HTTP request to $url.$path" );

                $auth=base64_encode($customerID.":".$LicKey);

                $out = "POST ".$path." HTTP/1.0\r\n";
                $out .= "Host: ".$url."\r\n";
                $out .= "Content-type:$contenttype\r\n";
                $out .= "Content-Length: ".strlen($data)."\r\n";
                $out .= "Accept: text/html\r\n";
                $out .= "Authorization: Basic $auth\r\n\r\n";

                fwrite($fp, $out);
                fwrite($fp, $data);

                if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){

                    $content = "";
                    $header = "";

                    $header_passed = "not yet";

                    while( !feof( $fp ) ) {
                        $line = fgets( $fp, 128 );
                        if( $line == "\r\n" && $header_passed == "not yet" ) {
                            $header_passed = "passed";
                        }
                        if( $header_passed == "passed" ) {
                            $content .= $line;
                        }else{
                            $header .= $line;
                        }
                    }


                    eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( $header, true ), "HTTP Response header" );
                }

                fclose($fp);

                return true;
            }
            else {

                eZDebug::writeError( "url: $url", '<ezrecommendation> Could not connect to server' );
                return false;
            }
    }


    /*
     *
     */
    public static function delete_item_request($item_path){

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ExportURL' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );

        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/$customerID/item/$item_path";


        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZDebugSetting::writeDebug('extension-ezrecommendation', $url.$path, "Sending HTTP request" );

                $auth = base64_encode( "$customerID:$LicKey" );

                $out = "DELETE ".$path." HTTP/1.0\r\n";
                $out .= "Host: ".$url."\r\n";
                //$out .= "Content-type:$contenttype\r\n";
                //$out .= "Content-Length: ".strlen($data)."\r\n";
                $out .= "Accept: text/html\r\n";
                $out .= "Authorization: Basic $auth\r\n\r\n";

                fwrite($fp, $out);
                //fwrite($fp, $data);

                if ( $ini->hasVariable( 'RequestSettings', 'ReceiveAnswer' ) && $ini->variable( 'RequestSettings', 'ReceiveAnswer' ) == 'enabled' ){

                    $content = "";
                    $header = "";

                    $header_passed = "not yet";

                    while( !feof( $fp ) ) {
                        $line = fgets( $fp, 128 );
                        if( $line == "\r\n" && $header_passed == "not yet" ) {
                            $header_passed = "passed";
                        }
                        if( $header_passed == "passed" ) {
                            $content .= $line;
                        }else{
                            $header .= $line;
                        }
                    }

                    eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( $header, true ), "HTTP Response header" );
                }

                fclose($fp);

                return true;
            }
            else {

                eZDebug::writeError( "url: $url", '<ezrecommendation> Could not connect to server' );
                return false;

            }

    }


    /*
     *
     */
    public static function get_stats_request(){

        $ini = eZINI::instance('ezrecommendation.ini');

        $url = $ini->variable( 'URLSettings', 'ConfigURL' );
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );

        $solution = $ini->variable( 'SolutionSettings', 'solution' );
        $mapSetting = $ini->variable( 'SolutionMapSettings', $solution );

        $path = "/$mapSetting/v3/$customerID/revenue/last_seven_days";

        $contenttype = "text/xml";

        eZDebugSetting::writeNotice('extension-ezrecommendation', $url.$path, 'Trying stats HTTP Request' );

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

        if ( $fp )
        {

            eZDebugSetting::writeDebug('extension-ezrecommendation', $url.$path, "Sending HTTP request" );

            $auth=base64_encode($customerID.":".$LicKey);

            $out = "GET ".$path." HTTP/1.0\r\n";
            $out .= "Host: ".$url."\r\n";
            //$out .= "Content-type:$contenttype\r\n";
            //$out .= "Content-Length: ".strlen($data)."\r\n";
            $out .= "Accept: text/html\r\n";
            $out .= "Authorization: Basic $auth\r\n\r\n";

            fwrite($fp, $out);
            //fwrite($fp, $data);

            $content = "";
            $header = "";

            $header_passed = "not yet";

            while( !feof( $fp ) ) {
                $line = fgets( $fp, 128 );
                if( $line == "\r\n" && $header_passed == "not yet" ) {
                    $header_passed = "passed";
                }
                if( $header_passed == "passed" ) {
                    $content .= $line;
                }else{
                    $header .= $line;
                }
            }


            eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( compact( 'header', 'content' ), true ), "HTTP Response header" );

            fclose($fp);

            $raw_stats = json_decode($content );
            $raw_stats2 = $raw_stats->revenueResponse;
            $raw_stats_items = $raw_stats2->items;

            $stats_array = array();

            $i = 0;


            foreach ($raw_stats_items as $stat)
            {
                if ($stat->revenue){
                    $revenue_array = array();
                    foreach($stat->revenue as $key => $value){
                        $revenue_array[$key] .=  substr_replace($value, ".", -2).substr($value,-2);
                    }
                    $stats_array[$i]['revenue']= $revenue_array;
                }
                $stats_array[$i]['timespanBegin']= date("d.m.Y", strtotime($stat->timespanBegin));
                $stats_array[$i]['timespanDuration']= $stat->timespanDuration;
                $stats_array[$i]['clickEvents']= $stat->clickEvents;
                $stats_array[$i]['consumeEvents']= $stat->consumeEvents;
                $stats_array[$i]['purchaseEvents']= $stat->purchaseEvents;
                $stats_array[$i]['deliveredRecommendations']= $stat->deliveredRecommendations;
                $stats_array[$i]['clickedRecommended']= $stat->clickedRecommended;
                $stats_array[$i]['purchasedRecommended']= $stat->purchasedRecommended;
                $i++;

            }
            return $stats_array;
        }
        else
        {

            eZDebug::writeError( "url: $url", '<ezrecommendation> Could not connect to server' );
            return false;
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

        if ($fp) {

            eZDebugSetting::writeDebug('extension-ezrecommendation', $data, "Sending HTTP request to $url.$path" );

            $auth = base64_encode( "$customerID:$LicKey" );

            $out = "GET $path HTTP/1.0\r\n";
            $out .= "Host: $url\r\n";
            //$out .= "Content-type:$contenttype\r\n";
            //$out .= "Content-Length: ".strlen($data)."\r\n";
            $out .= "Accept: text/html\r\n";
            $out .= "Authorization: Basic $auth\r\n\r\n";

            fwrite($fp, $out);
            //fwrite($fp, $data);

            $content = "";
            $header = "";

            $header_passed = "not yet";

            while( !feof( $fp ) ) {
                $line = fgets( $fp, 128 );
                if( $line == "\r\n" && $header_passed == "not yet" ) {
                    $header_passed = "passed";
                }
                if( $header_passed == "passed" ) {
                    $content .= $line;
                }else{
                    $header .= $line;
                }
            }


            eZDebugSetting::writeDebug('extension-ezrecommendation', var_export( compact( 'header', 'content' ), true ), "HTTP Response header" );

            fclose($fp);
            return true;

        }
        else
        {
            eZDebug::writeError( "url: $url", '<ezrecommendation> Could not connect to server' );
            return false;
        }
    }

    /**
     * @throw eZRecommendationException
     */
    private static function handleFault( $result )
    {
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
