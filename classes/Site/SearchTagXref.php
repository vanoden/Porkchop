<?php

namespace Site;

/**
 * SearchTagXref Class
 *
 * This class represents a cross-reference between a search tag and an object.
 * It provides methods for deleting tags from objects and finding objects by tags.
 */
class SearchTagXref extends \BaseModel {

    /**
     * The ID of the search tag.
     *
     * @var int
     */
    public $tag_id;

    /**
     * The ID of the object.
     *
     * @var int
     */
    public $object_id;

    /**
     * Constructor for the SearchTagXref class.
     *
     * @param int $id The ID of the search tag cross-reference.
     */
    public function __construct($id = 0) {
        $this->_tableName = 'search_tags_xref';
        $this->_addFields(array('id', 'tag_id', 'object_id'));
        parent::__construct($id);
    }

    /**
     * Deletes a search tag from an object.
     *
     * @param int $searchTagId The ID of the search tag to delete.
     * @param string $searchTagClass The class of the search tag to delete.
     * @param int $objectId The ID of the object to delete the tag from.
     *
     * @return bool True if the tag was successfully deleted, false otherwise.
     */
    public function deleteTagForObject($searchTagId, $searchTagClass, $objectId) {
        $database = new \Database\Service();
        $delete_xref_query = "
            DELETE FROM search_tags_xref
            WHERE tag_id = ? AND object_id = ?
            AND tag_id IN (SELECT id FROM search_tags WHERE class = ?)
        ";
        $rs = $database->Execute($delete_xref_query, array($searchTagId, $objectId, $searchTagClass));
        if (!$rs) {
            $this->SQLError($database->ErrorMsg());
            return false;
        }
        return true;
    }

    /**
     * Finds objects associated with specific tags.
     *
     * @param string $class The class of the search tag.
     * @param string $category The category of the search tag.
     * @param string $value The value of the search tag.
     *
     * @return array An array of object IDs associated with the specified tags.
     */
    public function findObjectsByTags($class, $category, $value) {

        $database = new \Database\Service();
        $tag_id = $this->tag_id;
        $object_id = $this->object_id;

        $find_objects_query = "
            SELECT stx.object_id
            FROM search_tags_xref stx INNER JOIN search_tags st
            ON stx.tag_id = st.id
            WHERE st.class = '$class' AND st.category = '$category' AND st.value = '$value'
        ";
        $rs = $database->Execute($find_objects_query);
        if (!$rs) {
            $this->SQLError($database->ErrorMsg());
            return array();
        }
        $objectIds = array();
        while (list($object_id) = $rs->FetchRow()) {
            array_push($objectIds, $object_id);
            $this->incrementCount();
        }
        return $objectIds;
    }
}
