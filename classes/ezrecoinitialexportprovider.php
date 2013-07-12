<?php
/**
 * File containing the eZRecoInitialExportProvider class.
 *
 * @copyright //autogen//
 * @license //autogen//
 * @version //autogen//
 * @package ezrecommendation
 */
class eZRecoInitialExportProvider
{
    /**
     * Array of class ID ezrecommendation is enabled for
     * @var array
     */
    private $classIdArray;

    /**
     * Path
     * @var array
     */
    private $rootPathString;

    /**
     * @var eZDB
     */
    private $db;

    /**
     * @var eZCli
     */
    private $cli;

    private $rows;

    private $offset = 0;

    private $limit = 100;

    /**
    * @param $classIdArray
     * @param $rootPathString
     */
    public function __construct( $classIdArray, $rootPathString, eZCli $cli, eZDBInterface $db )
    {
        $this->classIdArray = $classIdArray;
        $this->cli = $cli;
        $this->db = $db;
        $this->rootPathString = $rootPathString;
    }

    /**
     * @param eZDBInterface|null $db
     */
    public function setDb( $db )
    {
        unset( $this->db );
        $this->db = $db;
    }

    public function getNext()
    {
        $return = false;

        if ( $item = $this->getNextItem() )
        {
            $pathString = str_replace( '/1/', '', $item['path_string'] );
            $pathStringArray = explode( '/', $pathString );
            if ( in_array( $this->rootPathString, $pathStringArray ) )
            {
                $return = array(
                    "node_id" => $item['node_id'],
                    "object_id" => $item['contentobject_id'],
                    "node_path" => '/' . $pathString,
                    "class_id" => $item['contentclass_id']
                );
            }
        }

        return $return;
    }

    /**
     * Returns a batch of up to $split items. Each call to the method advances the pointer.
     * @param int $split
     * @return eZContentObject[]
     */
    public function getNextBatch( $split )
    {
        static $batchIndex = 0;
        $offset = $batchIndex * $split;

        $rows = eZContentObjectTreeNode::subTreeByNodeID(
            array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => $this->classIdArray,
                'MainNodeOnly' => true,
                'Offset' => $offset,
                'Limit' => $split
            ),
            2
        );
        $batchIndex++;

        if ( empty( $rows ) )
            return false;

        /** @var $rows eZContentObjectTreeNode[] */
        $return = array();
        foreach ( $rows as $row )
        {
            $return[] = $row->attribute( 'object' );
        }

        return $return;
    }

    public function getItemsCount()
    {
        return eZContentObject::fetchSameClassListCount( array( $this->classIdArray ) );
    }

    private function getNextItem()
    {
        if ( !is_array( $this->rows ) )
            $this->fetchNextRows();

        $return = current( $this->rows );
        if ( !next( $this->rows ) )
            $this->rows = null;

        return $return;
    }

    /**
     * Fetches the next items batch, and returns their count
     * @return int How many items were fetched
     */
    private function fetchNextRows()
    {
        $this->rows = $rows = eZContentObjectTreeNode::subTreeByNodeID(
            array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => $this->classIdArray,
                'MainNodeOnly' => true,
                'Offset' => $this->offset,
                'Limit' => $this->limit
            ),
            2
        );

        $this->offset += $this->limit;

        return count( $this->rows );
    }
}
