<?php
/**
 * File containing the ${NAME} class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class eZRecommendationAPI
{
    private $cacheManager;

    const CACHE_KEY = 'ezrecommendation-cache';

    public function __construct( eZRecommendationCacheManager $cacheManager = null )
    {
        if ( !isset( $cacheManager ) )
        {
            $this->cacheManager = new eZRecommendationCacheManager;
        }
    }

    public function getScenarioList()
    {
        $cacheFilePath = $this->getCacheFilePath( 'scenario_list' );
        if ( $data = $this->cacheManager->getFromCache( $cacheFilePath, self::CACHE_KEY ) )
        {
            eZDebugSetting::writeDebug( 'extension-ezrecommendation', "Loaded from cache", "Load scenario list" );
            return $data;
        }

        $scenarioList = eZRecommendationServerAPI::getScenarioList();
        $this->cacheManager->storeCache( $cacheFilePath, self::CACHE_KEY, $scenarioList );
        eZDebugSetting::writeDebug( 'extension-ezrecommendation', "Loaded from server", "Load scenario list" );

        return $scenarioList;
    }

    /**
     * Returns the path for the cache file of data identified by $identifier
     * @param string $identifier
     *
     * @return string
     */
    protected function getCacheFilePath( $identifier )
    {
        return sprintf(
            '%s/ezrecommendation/%s.php',
            eZSys::cacheDirectory(),
            $identifier
        );
    }
}