<?php
/**
 * File containing the eZRecommendationException class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class eZRecommendationApiException extends Exception
{
    public function __construct( $message, Exception $previous = null )
    {
        parent::__construct( $message, 0, $previous );
    }
}
?>
