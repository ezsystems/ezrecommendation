<?php
/**
 * File containing the ezrecommendationcachemanager class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class eZRecommendationCacheManager
{
    /** @var eZRecommendationClusterProvider */
    private $clusterProvider;

    public function __construct( eZRecommendationClusterProvider $clusterProvider = null)
    {
        if ( $clusterProvider == null )
            $clusterProvider = new eZRecommendationClusterProvider();

        $this->clusterProvider = $clusterProvider;
    }

    public function getFromCache( $path )
    {
        return false;
    }

    public function storeCache( $path, $data )
    {
    }
}
