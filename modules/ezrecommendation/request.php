<?php
/**
 * File containing ezrecommendation/request view implementation
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
    eZDebug::writeError(
        '[ezrecommendation] no url found for ezrecommendation extension in ezrecommendation.ini.',
        __METHOD__
    );
    eZExecution::cleanExit();
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
        eZDebug::writeError(
            '[ezrecommendation] no CustomerID found for ezrecommendation extension in ezrecommendation.ini.',
            __METHOD__
        );
        eZExecution::cleanExit();
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
                eZDebug::writeError(
                    '[ezrecommendation] consume-event not triggered because of to low elapsed time.',
                    'debug.log',
                    'var/log'
                );
                eZExecution::cleanExit();
            }

        }
        else
        {
            eZDebug::writeError(
                '[ezrecommendation] customer-event not triggered because of no elapsed time.',
                'debug.log',
                'var/log'
            );
            eZExecution::cleanExit();
        }

    }

    $user = eZUser::currentuser();

    if ( $user->isAnonymous() && $http->hasGetVariable( 'sid' ) )
    {
        $userid = $http->getVariable( 'sid' );
        $path .= '/'.$userid;

        $http->setSessionVariable( 'eZRecoTransfer', $userid );
    }
    else if ( $user->isLoggedIn() && $http->hasGetVariable( 'userid' ) )
    {
        $userid = $http->getVariable( 'userid' );

        if (
            $http->hasGetVariable( 'map' ) &&
            $http->getVariable( 'map' ) == 1 &&
            $http->hasGetVariable( 'sid' ) &&
            ( !$http->hasSessionVariable( 'eZRecoTransfer' ) || $http->sessionVariable( 'eZRecoTransfer' ) != $userid )
        )
        {
            // Update cookie with current user ID
            $_COOKIE['ezreco_usr'] = $userid;
            $http->setSessionVariable( 'eZRecoTransfer', $userid );

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
    if ( $http->hasGetVariable( 'fullprice' ) )
    {
        $params['fullprice'] = $http->getVariable( 'fullprice' );
    }
    else
    {
        if ( $http->hasGetVariable( 'price' ) )
        {
            $params['price'] = $http->getVariable( 'price' );
        }

        if ( $http->hasGetVariable( 'currency' ) )
        {
            $params['currency'] = $http->getVariable( 'currency' );
        }
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

    try
    {
    ezRecoFunctions::send_http_request( $url, $path.$params_data );
    }
    catch( \Exception $e )
    {
        eZDebug::writeError( $e->getMessage(), __METHOD__ );
        eZExecution::cleanExit();
    }

}
else
{
    eZDebug::writeError(
        '[ezrecommendation] required variable not set in request.',
        __METHOD__
    );
    eZExecution::cleanExit();
}

header( 'Content-type: image/gif' );
readfile( 'extension/ezrecommendation/design/standard/images/ezreco.gif' );

eZExecution::cleanExit();
