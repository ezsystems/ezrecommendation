<?php
/**
 * File containing the eZRecommendationClusterProvider class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
