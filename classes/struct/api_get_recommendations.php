<?php
/**
 * File containing the eZRecommendationApiGetRecommendationsStruct class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
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
