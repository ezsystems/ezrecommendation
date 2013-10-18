<?php
/**
 * File containing the eZRecommendationClusterProvider class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class eZRecommendationClusterProvider
{
    /**
     * @return eZClusterFileHandlerInterface
     */
    public function getInstance( $path = false )
    {
        return eZClusterFileHandler::instance( $path );
    }
}
