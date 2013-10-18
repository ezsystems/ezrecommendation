<?php

/**
 * File containing the eZRecoTemplateFunctions class for generating the html output
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class ezRecoTemplateFunctions
{

    function __construct()
    {
        $this->Operators = array(
            'generate_html',
            'generate_common_event',
            'generate_consume_event',
            'generate_buy_event',
            'generate_rate_event',
            'track_rendered_items'
        );
    }

    function operatorList()
    {
        return $this->Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'generate_html' => array(
                'module_result' => array(
                    'type' => 'array',
                    'required' => true,
                    'default' => ''
                ),
                'event_type' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                )
            ),
            'generate_common_event' => array(
                'node' => array(
                    'type' => 'array',
                    'required' => true,
                    'default' => ''
                ),
                'event_type' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                ),
                'scenario' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                )
            ),
            'generate_consume_event' => array(
                'node' => array(
                    'type' => 'array',
                    'required' => true,
                    'default' => ''
                )
            ),
            'generate_buy_event' => array(
                'node' => array(
                    'type' => 'array',
                    'required' => true,
                    'default' => ''
                ),
                'quantity' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                ),
                'price' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                ),
                'currency' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                )
            ),
            'generate_rate_event' => array(
                'node' => array(
                    'type' => 'array',
                    'required' => true,
                    'default' => ''
                ),
                'rating' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                )
            ),
            'track_rendered_items' => array(
                'itemtypeid' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                ),
                'items' => array(
                    'type' => 'string',
                    'required' => true,
                    'default' => ''
                )
            )
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'generate_html':
                {
                    $operatorValue = $this->generate_html_from_module_result(
                        $namedParameters['module_result'],
                        $namedParameters['event_type']
                    );
                } break;
            case 'generate_common_event':
                {
                    $operatorValue = $this->generate_common_event(
                        $namedParameters['node'],
                        $namedParameters['event_type'],
                        $namedParameters['scenario']
                    );
                } break;
            case 'generate_consume_event':
                {
                    $operatorValue = $this->generate_consume_event( $namedParameters['node'] );
                } break;
            case 'generate_buy_event':
                {
                    $operatorValue = $this->generate_buy_event(
                        $namedParameters['node'],
                        $namedParameters['quantity'],
                        $namedParameters['price'],
                        $namedParameters['currency']
                    );
                } break;
            case 'generate_rate_event':
                {
                    $operatorValue = $this->generate_rate_event(
                        $namedParameters['node'],
                        $namedParameters['rating']
                    );
                } break;
            case 'track_rendered_items':
                {
                    $operatorValue = $this->track_rendered_items(
                        $namedParameters['itemtypeid'],
                        $namedParameters['items']
                    );
                } break;
        }
    }


    function getServerUrl()
    {
        $ezurlop = new eZURLOperator();

        $sys = $ezurlop->Sys;

        $access_path = $sys->AccessPath;

        $siteaccess_url = $access_path['siteaccess']['url'];

        if ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
        {
            $path = 'https://';
        }
        else
        {
            $path = 'http://';
        }

        $path = $path.$_SERVER['HTTP_HOST'];

        if ( $sys->WWWDir != '' )
        {
            $www_dir = $sys->WWWDir;
            $path = $path.$www_dir;
        }

        if ( $sys->IndexFile != '' )
        {
            $index_file = $sys->IndexFile;
            $path = $path.$index_file;
        }

        if ( count( $siteaccess_url ) > 0 )
        {
            $path = $path.'/'.$siteaccess_url[0];
        }

        return $path;
    }


    function get_current_url()
    {

        $moduleURL = '/ezrecommendation/request';
        $serverURL = $this->getServerUrl();

        return $serverURL.$moduleURL;
    }

    static function getCategoryPath( $ezCat )
    {
        $ezCategoryArray = explode("/",$ezCat);
        $count_ezCategoryArray = count($ezCategoryArray);
        /*e.g /1/2/174/262/ -> /2/174/ */
        $toRecoCategoryPath = "/";
        for ($i = 2; $i <= $count_ezCategoryArray-3 ; ++$i )
        {
            $toRecoCategoryPath .= $ezCategoryArray[$i].'/';
        }
        return $toRecoCategoryPath;
    }

    function get_html( $params )
    {
        return "/ezrecommendation/request$params";
    }

    function get_url_for_consume_event( $params )
    {
        $serverURL = $this->get_current_url();

        $res = $serverURL.$params;

        return $res;
    }


    function get_html_for_event( $params )
    {
        $serverURL = $this->get_current_url();

        $res = 'onclick="ezreco.evt(\''.$serverURL.$params.'\')"';

        return $res;
    }

    function generate_recommendations_array( $raw_recommendations )
    {
        $recommendations_array = array();

        foreach( $raw_recommendations as $rec )
        {
            foreach ( $rec as $rec2 )
            {
                $recommendations_array[] = array(
                    'reason' => $rec2->reason,
                    'itemType' => $rec2->itemType,
                    'itemId' => $rec2->itemId,
                    'relevance' => $rec2->relevance );
            }
        }

        if ( !empty( $recommendations_array ) )
        {
            eZDebug::writeDebug( $recommendations_array, 'processed $recommendations' );
            return $recommendations_array;
        }
        else
        {
            eZLog::write('[ezrecommendation] no recommendations received.', 'error.log', 'var/log');
            return false;
        }
    }



    function generate_consume_event( $node )
    {
        $ini = eZINI::instance('ezrecommendation.ini');

        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'object_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) )
        {
            $productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );


            if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) )
            {
                $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
            }
            else
            {
                eZLog::write('[ezrecommendation] missing CustomerID in ClientIdSettings in ezrecommendation.ini.', 'error.log', 'var/log');
                return false;
            }

            $itemtypeid = eZContentClass::classIDByIdentifier( $node->attribute( 'class_identifier' ) );

            $recoitemtypeid = '';

            $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $itemtypeid );

            if ( count( $arr['result'] ) > 0 )
            {
                $recoitemtypeid = $arr['result']['recoItemType'];

            }

            if ( !empty( $recoitemtypeid ) )
            {

                $itemid = $node->attribute( 'object' )->attribute( 'id' );

                $categorypath = $node->attribute( 'path_string' );

                $mynodeArray = $node->attribute( 'data_map' );
                $contentClass = $node->attribute( 'object' )->attribute( 'content_class' );

                foreach ( $contentClass->attribute( 'data_map' ) as $contentClassAttrib )
                {
                    if ( $contentClassAttrib->attribute( 'data_type_string' ) === 'ezrecommendation' )
                    {
                        $triggerTime = $contentClassAttrib->attribute( 'data_int4' );
                        break;
                    }
                }

                foreach ($mynodeArray as $contentObjectAttr)
                {
                    if( $contentObjectAttr->DataTypeString == "ezrecommendation" )
                    {
                        $dataTextXml = $contentObjectAttr->DataText;
                        $isEnableReco = ezRecommendationXml::getNodeAttributeValue(
                            $dataTextXml,
                            'recommendation-enable'
                        );
                        break;
                    }
                }

                $params = '?productid='.$productid.'&eventtype=consume';
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$recoitemtypeid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'object_id' ).'='.$itemid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.ezRecoTemplateFunctions::getCategoryPath($categorypath);

                $res = '<div id="ezreco-consume-event" data-trigger-time="' . $triggerTime . '">'.$this->get_url_for_consume_event( $params ).'</div>';

            }
            else
            {
                eZLog::write( '[ezrecommendation] ez-classid could not be mapped to a ezrecommendation-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezrecommendation type.', 'error.log', 'var/log' );
                return false;
            }

        }
        else
        {
            eZLog::write( '[ezrecommendation] missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.', 'error.log', 'var/log' );
            return false;
        }

        return $res;

    }



    function generate_common_event( $node, $event_type, $scenario = '' )
    {
        $ini = eZINI::instance('ezrecommendation.ini');

        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'object_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'path_string' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) )
        {

            $productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );

            if ($ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) )
            {
                $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
            }
            else
            {
                eZLog::write('[ezrecommendation] missing CustomerID in ClientIdSettings in ezrecommendation.ini.', 'error.log', 'var/log');
                return false;
            }

            $itemtypeid = eZContentClass::classIDByIdentifier( $node->attribute( 'class_identifier' ) );

            $recoitemtypeid = '';

            $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $itemtypeid );

            if ( count($arr['result'] ) > 0 )
            {
                $recoitemtypeid = $arr['result']['recoItemType'];
            }

            if ( !empty( $recoitemtypeid ) )
            {

                $itemid = $node->attribute( 'object' )->attribute( 'id' );

                $categorypath = $node->attribute( 'path_string' );

                $params = '?productid='.$productid.'&eventtype='.$event_type;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$recoitemtypeid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'object_id' ).'='.$itemid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.ezRecoTemplateFunctions::getCategoryPath($categorypath);
                $params .= '&scenario=' . $scenario;

                $res = $this->get_html_for_event( $params );
            }
            else
            {
                eZLog::write( '[ezrecommendation] ez-classid could not be mapped to a ezrecommendation-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezrecommendation type.', 'error.log', 'var/log' );
                return false;
            }

        }
        else
        {
            eZLog::write( '[ezrecommendation] missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.', 'error.log', 'var/log' );
            return false;
        }

        return $res;
    }


    function generate_buy_event( $node, $quantity, $price, $currency )
    {
        $ini = eZINI::instance( 'ezrecommendation.ini' );

        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'object_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) )
        {
            $productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );

            if ( $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) )
            {
                $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
            }
            else
            {
                eZLog::write( '[ezrecommendation] missing CustomerID in ClientIdSettings in ezrecommendation.ini.', 'error.log', 'var/log' );
                return false;
            }

            if ( !is_int( $price ) )
            {
                eZLog::write( '[ezrecommendation] use only integer for price', 'error.log', 'var/log' );
                return false;
            }

            $itemtypeid = eZContentClass::classIDByIdentifier( $node->attribute( 'class_identifier' ) );

            $recoitemtypeid = '';

            $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $itemtypeid );

            if ( count( $arr['result'] ) > 0 )
            {
                $recoitemtypeid = $arr['result']['recoItemType'];
            }

            if ( !empty( $recoitemtypeid ) )
            {
                $itemid = $node->attribute( 'object' )->attribute( 'id' );

                $params = '?productid='.$productid.'&eventtype=buy';
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$recoitemtypeid ;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'object_id' ).'='.$itemid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'quantity' ).'='.$quantity;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'price' ).'='.$price;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'currency' ).'='.$currency;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'timestamp' ).'='.time();

                $res = $this->get_html_for_event( $params );

            }
            else
            {
                eZLog::write( '[ezrecommendation] ez-classid could not be mapped to a ezrecommendation-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezrecommendation type.', 'error.log', 'var/log' );

                return false;
            }
        }
        else
        {

            eZLog::write( '[ezrecommendation]: missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.', 'error.log', 'var/log' );
            return false;
        }

        return $res;

    }

    function generate_rate_event( $node, $rating )
    {
        $ini = eZINI::instance('ezrecommendation.ini');

        if ( $ini->hasVariable( 'SolutionSettings', 'solution' ) && $ini->hasVariable( 'ParameterMapSettings', 'object_id' ) && $ini->hasVariable( 'ParameterMapSettings', 'user_id' ) )
        {

            $productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );

            if ( $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) )
            {
                $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
            }
            else
            {
                eZLog::write( '[ezrecommendation] missing CustomerID in ClientIdSettings in ezrecommendation.ini.', 'error.log', 'var/log' );
                return false;
            }

            if ( !is_int( $rating ) || ( $rating <= 0 ) || ( $rating>=100 ) ) {

                eZLog::write( '[ezrecommendation] use only integer between 0 and 100 for rating', 'error.log', 'var/log' );
                return false;

            }

            $itemtypeid = eZContentClass::classIDByIdentifier( $node->attribute( 'class_identifier' ) );

            $recoitemtypeid = '';

            $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $itemtypeid );

            if ( count( $arr['result'] ) > 0 )
            {
                $recoitemtypeid = $arr['result']['recoItemType'];
            }

            if ( !empty( $recoitemtypeid ) )
            {
                $itemid = $node->attribute( 'object' )->attribute( 'id' );
                $categorypath = $node->attribute( 'path_string' );

                $params = '?productid='.$productid.'&eventtype=rate';
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'class_id' ).'='.$recoitemtypeid ;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'object_id' ).'='.$itemid;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'rating' ).'='.$rating;
                $params .= '&'.$ini->variable( 'ParameterMapSettings', 'path_string' ).'='.ezRecoTemplateFunctions::getCategoryPath($categorypath);

                $res = $this->get_html_for_event( $params );
            }
            else
            {
                eZLog::write('[ezrecommendation] ez-classid could not be mapped to a ezrecommendation-itemtypeid. please make sure that to add the recommendation attribute to the class and to map the class with a ezrecommendation type.', 'error.log', 'var/log');

                return false;
            }
        }
        else
        {
            eZLog::write('[ezrecommendation] missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.', 'error.log', 'var/log');
            return false;
        }

        return $res;

    }




    function generate_html_from_module_result( $module_result, $event_type )
    {
        $ini = eZINI::instance('ezrecommendation.ini');

        if ( !$ini->hasVariable( 'SolutionSettings', 'solution' ) || !$ini->hasVariable( 'ParameterMapSettings', 'object_id' ) || !$ini->hasVariable( 'ParameterMapSettings', 'path_string' ) || !$ini->hasVariable( 'ParameterMapSettings', 'user_id' ) )
        {
            eZDebug::writeError(
                'Missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.',
                '[ezrecommendation] generate_html operator'
            );
            return false;
        }
        $productid = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
        $contentInfo = $module_result['content_info'];

        if ( !$contentInfo )
        {
            eZDebug::writeError(
                'Could not generate ezrecommendation-pixel. please check the include call in your pagelayout.tpl.',
                '[ezrecommendation] generate_html'
            );
            return false;
        }

        $arr = ezRecommendationClassAttribute::fetchClassAttributeList( $contentInfo['class_id'] );

        if ( !isset( $arr['result']['recoItemType'] ) && !empty( $arr['result']['recoItemType'] ) )
        {
            eZDebug::writeError(
                'ezpublish class id could not be mapped to a ezrecommendation item type id. Please make sure to add the recommendation attribute to the class and to map the class with a ezrecommendation type.',
                '[ezrecommendation] generate_html'
            );
            return false;
        }

        $node = eZContentObjectTreeNode::fetch( $contentInfo['node_id'] );
        $categoryPath = $node->attribute( 'path_string' );

        $current_user = eZUser::currentUser ();
        $current_user_id = $current_user->attribute( 'contentobject_id' );

        $params = '?productid=' . $productid . '&eventtype=' . $event_type
                . '&' . $ini->variable( 'ParameterMapSettings', 'class_id' ) . '=' . urlencode( $arr['result']['recoItemType'] )
                . '&' . $ini->variable( 'ParameterMapSettings', 'object_id' ) . '=' . urlencode( $contentInfo['object_id'] )
                . '&' . $ini->variable( 'ParameterMapSettings', 'user_id' ) . '=' . urlencode( $current_user_id )
                . '&' . $ini->variable( 'ParameterMapSettings', 'path_string' ) . '=' . ezRecoTemplateFunctions::getCategoryPath( $categoryPath );

        return $this->get_html( $params );
    }


    function track_rendered_items( $items_array )
    {
        $sorted_array = array();
        foreach ( $items_array as $key => $value )
        {
            $key = str_replace( "\"", "", $key );
            $val =  str_replace( "\"", "", $value );

            if ( empty( $sorted_array[$val] ) )
            {
                $sorted_array[$val] = $key;
            }
            else
            {
                $sorted_array[$val] = $sorted_array[$val] . ',' . $key;
            }
        }

        $ini = eZINI::instance('ezrecommendation.ini');

        if ( !$ini->hasVariable( 'SolutionSettings', 'solution' ) || !$ini->hasVariable( 'ParameterMapSettings', 'object_id' ) || !$ini->hasVariable( 'ParameterMapSettings', 'class_id' ) )
        {
            eZDebug::writeError('[ezrecommendation] missing MapSettings in generate_html_from_module_result function for ezrecommendation extension in ezrecommendation.ini.' );
            return false;
        }

        $productId = $ini->variable( 'SolutionMapSettings', $ini->variable( 'SolutionSettings', 'solution' ) );
        $renderedEventsUrl = array();

        foreach ( $sorted_array as $key => $value )
        {
            $classAttributeList = ezRecommendationClassAttribute::fetchClassAttributeList( $key );
            if ( !isset( $classAttributeList['result'] ) > 0 )
                continue;

            $params = '?productid='.$productId.'&eventtype=rendered';
            $params .= '&' . $ini->variable( 'ParameterMapSettings', 'class_id' ) . '=' . $classAttributeList['result']['recoItemType'];
            $params .= '&' . $ini->variable( 'ParameterMapSettings', 'object_id' ) . '=' . $value;

            $renderedEventsUrl[] = $this->get_current_url() . $params;
        }

        if ( !empty( $renderedEventsUrl ) )
        {
            return '<script type="text/javascript">
            (function(reco) {
                var urls = ' . json_encode( $renderedEventsUrl ) . ';
                reco.renderEvents(urls);
            })(ezreco);
            </script>';
        }
        return '';
    }

    protected $Operators;
}

?>
