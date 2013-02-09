<?php
/**
 * File containing the eZRecoInitialExportProvider class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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

    private $limit = 5;

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

    public function getItemsCount()
    {
        $classIdList = implode( ',', $this->classIdArray );
        $countRows = $this->db->arrayQuery(
            "SELECT COUNT(*) AS count FROM ezcontentobject_tree WHERE contentobject_id in (SELECT id FROM ezcontentobject where contentclass_id IN ($classIdList))"
        );
        return $countRows[0]['count'];
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
        $classIdList = implode( ',', $this->classIdArray );
        $this->rows = $this->db->arrayQuery(
            "SELECT node_id, contentobject_id, path_string, contentclass_id
            FROM ezcontentobject_tree, ezcontentobject
            WHERE ezcontentobject_tree.contentobject_id = ezcontentobject.id
              AND contentclass_id IN ($classIdList)",
            array(
                'offset' => $this->offset,
                'limit' => $this->limit
            )
        );
        $this->offset += $this->limit;

        return count( $this->rows );
    }
}
