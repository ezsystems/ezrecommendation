<?php

/**
 * File containing the eZRecommendationFunctions request implementation
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

$http = eZHTTPTool::instance();
$ini = eZINI::instance( 'ezrecommendation.ini' );

if ( $ini->hasVariable( 'URLSettings', 'RequestURL' ) )
{
    $url = $ini->variable( 'URLSettings', 'RequestURL' );
}
else
{
    eZLog::write(
        '[ezrecommendation] no url found for ezrecommendation extension in ezrecommendation.ini.',
        'error.log',
        'var/log'
    );
    return false;
}


if ( $http->hasGetVariable( 'productid' ) && $http->hasGetVariable( 'eventtype' ) && $http->hasGetVariable( 'itemtypeid' ) && $http->hasGetVariable( 'itemid' ) )
{
    $productid = $http->getVariable( 'productid' );

    if ( $ini->hasVariable( 'ClientIdSettings', 'CustomerID' ) )
    {
        $client_id = $ini->variable( 'ClientIdSettings', 'CustomerID' );
    }
    else
    {
        eZLog::write(
            '[ezrecommendation] no CustomerID found for ezrecommendation extension in ezrecommendation.ini.',
            'error.log',
            'var/log'
        );
        return false;
    }

    $path = '/';
    $params = array();

    $path .= $productid;

    $path .= '/'.$client_id;

    $eventtype = $http->getVariable( 'eventtype' );
    $path .= '/'.$eventtype;

    $itemtypeid = $http->getVariable( 'itemtypeid' );

    if ( $eventtype == 'consume' )
    {
        if ( $http->hasGetVariable( 'elapsedtime' ) )
        {
            $elapsedtime = $http->getVariable( 'elapsedtime' );

            $ttl = $arr['result']['recoTimeTrigger'];
            if ( $elapsedtime < $ttl )
            {
                eZLog::write(
                    '[ezrecommendation] consume-event not triggered because of to low elapsed time.',
                    'debug.log',
                    'var/log'
                );
                return false;
            }

        }
        else
        {
            eZLog::write(
                '[ezrecommendation] customer-event not triggered because of no elapsed time.',
                'debug.log',
                'var/log'
            );
            return false;
        }

    }

    $user = eZUser::currentuser();

    if ( $user->isAnonymous() && $http->hasGetVariable( 'sid' ) )
    {
        $userid = $http->getVariable( 'sid' );
        $path .= '/'.$userid;

    }
    else if ( $user->isLoggedIn() && $http->hasGetVariable( 'userid' ) )
    {
        $userid = $http->getVariable( 'userid' );

        if (
            $http->hasGetVariable( 'map' ) &&
            $http->getVariable( 'map' ) == 1 &&
            $http->hasGetVariable( 'sid' ) &&
            $_COOKIE['ezreco_usr'] != $userid
        )
        {
            $_COOKIE['ezreco_usr'] = $userid;

            $sid = $http->getVariable( 'sid' );

            $path_for_transfer = '/';
            $path_for_transfer .= $productid;
            $path_for_transfer .= '/'.$client_id;
            $path_for_transfer .= '/transfer';
            $path_for_transfer .= '/'.$sid;
            $path_for_transfer .= '/'.$userid;

            ezRecoFunctions::send_http_request( $url, $path_for_transfer );
        }
        $path .= '/'.$userid;
    }

    $itemtypeid = $http->getVariable( 'itemtypeid' );
    $path .= '/'.$itemtypeid;

    $itemid = $http->getVariable( 'itemid' );
        $path .= '/'.$itemid;

    if ( $http->hasGetVariable( 'categorypath' ) )
    {
        $tmp_categorypath = $http->getVariable( 'categorypath' );
        $params['categorypath'] = str_replace( $itemid.'/', '', $tmp_categorypath );
    }

    if ( $http->hasGetVariable( 'scenario' ) )
    {
        $params['scenario'] = $http->getVariable( 'scenario' );
    }

    if ( $http->hasGetVariable( 'quantity' ) )
    {
        $params['quantity'] = $http->getVariable( 'quantity' );
    }

    if ( $http->hasGetVariable( 'price' ) )
    {
        $params['price'] = $http->getVariable( 'price' );
    }

    if ( $http->hasGetVariable( 'currency' ) )
    {
        $params['currency'] = $http->getVariable( 'currency' );
    }

    if ( $http->hasGetVariable( 'timestamp' ) )
    {
        $params['timestamp'] = $http->getVariable( 'timestamp' );
    }

    if ( $http->hasGetVariable( 'rating' ) )
    {
        $params['rating'] = $http->getVariable( 'rating' );
    }

    $params_array = array();
    foreach ( array_keys( $params ) as $key )
    {
        array_push( $params_array, urlencode( $key ) . "=" . urlencode( $params[$key] ) );
    }

    $params_data = '';
    if ( !empty( $params_array ) )
    {
        $params_data = '?';
        $params_data .= implode( "&", $params_array );
    }

    ezRecoFunctions::send_http_request( $url, $path.$params_data );

}
else
{
    eZLog::write(
        '[ezrecommendation] required variable not set in request.',
        'error.log',
        'var/log'
    );
    return false;
}

eZExecution::cleanExit();
