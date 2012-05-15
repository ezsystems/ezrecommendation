<?php
/**
 * File containing the eZRecommendationException class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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