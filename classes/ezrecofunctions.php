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

            eZLog::write('[ezrecommendation] Trying request '.$url.$path, 'debug.log', 'var/log');

            $fp = fsockopen( $url, 80, $errno, $errstr, 30);

            if ($fp) {

                $out = "GET ".$path." HTTP/1.0\r\n";
                $out .= "Host: ".$url."\r\n";
                $out .= "Connection: Close\r\n\r\n";

                fwrite($fp, $out);

                eZLog::write('[ezrecommendation] Sending request '.$url.$path, 'debug.log', 'var/log');

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

                    eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');

                }

                fclose($fp);

                return true;

            }
            else {

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
                return false;

            }

    }

    /*
     *
     */
    public static function send_reco_request($url, $path ){


        /* Beginn https:443 */
/*        $ini = eZINI::instance('ezrecommendation.ini');
        $customerID = $ini->variable( 'ClientIdSettings', 'CustomerID' );
        $LicKey = $ini->variable( 'ClientIdSettings', 'LicenseKey' );
        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);
*/        /*end https:443*/

        eZLog::write('[ezrecommendation] Trying request '.$url.$path, 'debug.log', 'var/log');
        /* Beginn http:80 */

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

            eZLog::write('[ezrecommendation] Sending request '.$url.$path, 'debug.log', 'var/log');

            fwrite($fp, $out);

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


                eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');
                eZLog::write('[ezrecommendation] Received recommendations '.var_export($content , true), 'debug.log', 'var/log');

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

        eZLog::write('[ezrecommendation] Trying request '.$url.$path, 'debug.log', 'var/log');

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZLog::write('[ezrecommendation] Sending request '.$url.$path, 'debug.log', 'var/log');
                eZLog::write('[ezrecommendation] Sending Object Data '.$data, 'debug.log', 'var/log');

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


                    eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');

                }

                fclose($fp);

                return true;

            }
            else {

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
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

        eZLog::write('[ezrecommendation] Trying delete request '.$url.$path, 'debug.log', 'var/log');

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZLog::write('[ezrecommendation] Sending delete request '.$url.$path, 'debug.log', 'var/log');

                $auth=base64_encode($customerID.":".$LicKey);

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


                    eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');

                }

                fclose($fp);

                return true;

            }
            else {

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
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

        eZLog::write('[ezrecommendation] Trying stats request '.$url.$path, 'debug.log', 'var/log');

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZLog::write('[ezrecommendation] Sending stats request '.$url.$path, 'debug.log', 'var/log');

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


                eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');
                eZLog::write('[ezrecommendation] Received stats '.var_export($content , true), 'debug.log', 'var/log');

                fclose($fp);

                $raw_stats = json_decode($content );
                $raw_stats2 = $raw_stats->revenueResponse;
                $raw_stats_items = $raw_stats2->items;

                $stats_array = array();

                $i = 0;


                foreach ($raw_stats_items as $stat){
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
            else {

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
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

        eZLog::write('[ezrecommendation] Trying bulk request '.$url.$path, 'debug.log', 'var/log');

        $fp = fsockopen( 'ssl://'.$url, 443, $errno, $errstr, 60);

            if ($fp) {

                eZLog::write('[ezrecommendation] Sending bulk request '.$url.$path, 'debug.log', 'var/log');

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


                eZLog::write('[ezrecommendation] Received answer '.var_export($header, true), 'debug.log', 'var/log');
                eZLog::write('[ezrecommendation] Received answer '.var_export($content, true), 'debug.log', 'var/log');

                fclose($fp);
                return true;

            }
            else {

                eZLog::write('[ezrecommendation] Could not connect to server with url: '.$url, 'error.log', 'var/log');
                return false;

            }

    }

}