<?php
/**
 * File containing the ezrecommendationcachemanager class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class eZRecommendationCacheManager
{
    public function getFromCache( $path, $key )
    {
        $expiryHandler = eZExpiryHandler::instance();

        if ( !$expiryHandler->hasTimestamp( $key ) )
        {
            return false;
        }

        if ( !file_exists( $path ) || filemtime( $path ) < $expiryHandler->timestamp( $key ) )
        {
            return false;
        }

        return include( $path );
    }

    public function storeCache( $path, $key, $data )
    {
        if ( !is_array( $data ) )
        {
            throw new InvalidArgumentException( "\$data argument must be a valid array" );
        }

        $dataString = "<" . "?php\nreturn ". var_export( $data, true ) . ";\n?" . ">\n";
        if ( !eZFile::create( basename( $path ), dirname( $path ), $dataString ) )
        {
            throw new InvalidArgumentException( "Unable to open cache file $path for writing" );
        }

        $expiryHandler = eZExpiryHandler::instance();
        $expiryHandler->setTimestamp( $key, time() );
        $expiryHandler->store();
    }
}
