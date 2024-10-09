<?php
	namespace Site;

	/**
	 * Class SearchTagList
	 * 
	 * This class represents a list of search tags and provides methods to interact with them.
	 * It extends the BaseListClass and is specific to Site\SearchTag objects.
	 *
	 * @package Site
	 */
	class SearchTagList extends \BaseListClass {
		
		/**
		 * Constructor for the SearchTagList class.
		 * 
		 * Initializes the _modelName property with the fully qualified class name of the SearchTag model.
		 */
		public function __construct() {
			$this->_modelName = '\Site\SearchTag';
		}

		/**
		 * Get unique values from a specific column in the search_tags table.
		 *
		 * This method executes a SQL query to fetch distinct values from the specified column
		 * in the search_tags table, ordered alphabetically.
		 *
		 * @param string $column The name of the column to fetch unique values from.
		 * @return array An array of unique values from the specified column.
		 * @throws \Exception If there's an error executing the SQL query.
		 */
		public function getUniqueValues($column) {
			$database = new \Database\Service();
			$query = "SELECT DISTINCT `$column` FROM `search_tags` ORDER BY `$column` ASC";
			$rs = $database->Execute($query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return array();
			}

			$values = array();
			while ($row = $rs->FetchRow()) $values[] = $row[0];
			return $values;
		}

		/**
		 * Get unique categories and tags for autocomplete functionality, encoded as JSON.
		 *
		 * This method fetches distinct categories and values from the search_tags table,
		 * and returns them as an associative array with JSON-encoded strings for use in autocomplete fields.
		 *
		 * @return array An associative array with 'categoriesJson' and 'tagsJson' keys, each containing a JSON-encoded string of unique values.
		 * @throws \Exception If there's an error executing the SQL queries or encoding JSON.
		 */
		public function getUniqueCategoriesAndTagsJson() {
			$categories = $this->getUniqueValues('category');
			$tags = $this->getUniqueValues('value');

			return array(
				'categoriesJson' => json_encode($categories),
				'tagsJson' => json_encode($tags)
			);
		}
	}