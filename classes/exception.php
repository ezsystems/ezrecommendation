<?php
/**
 * File containing the eZRecommendationException class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class eZRecommendationException extends Exception
{
    public function __construct( $type, $message, $fault )
    {
        $this->message = $message;
        $this->type = $type;
        $this->fault = $fault;
    }

    public $type;

    public $fault;
}
?>
