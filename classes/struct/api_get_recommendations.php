<?php
/**
 * File containing the eZRecommendationApiGetRecommendationsStruct class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
/**
 * This struct is used to send a recommendation API request
 */
class eZRecommendationApiGetRecommendationsStruct
{
    /**
     * Recommendation scenario (top_clicked...)
     * @var string
     */
    public $scenario;

    /**
     * Node to get recommendations from
     * @var eZContentObjectTreeNode
     */
    public $node;

    /**
     * Content object to get recommendations for
     * @var eZContentObject
     */
    public $object;

    /**
     * How many recommendations should be returned
     */
    public $limit = 3;

    /**
     * Item type id (1 = product, 2 = article...)
     * @var integer
     */
    public $itemTypeId;
}