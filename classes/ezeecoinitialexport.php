<?php
/**
 * File containing the eZRecoInitialExport class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */

class eZRecoInitialExport
{
    /**
     * Writes the XML $filePath with the data from $domDocument
     * @param string $domDocument
     * @param string $filePath
     */
    public static function writeXmlFile( $xml, $filePath )
    {
        eZFile::create( basename( $filePath ), dirname( $filePath ), $xml );
    }

    /**
     * Return the next XML bulkexport file's path
     * @param string $prefix
     * @return string
     */
    public static function getXmlFilePath( $prefix )
    {
        static $autoIncrement = 1;
        $filename = "$prefix/bulkexport_$autoIncrement.xml";
        $autoIncrement++;

        return $filename;
    }

}
