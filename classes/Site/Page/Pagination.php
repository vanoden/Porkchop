<?php
	namespace Site\Page;

	class Pagination Extends \BaseModel {
		public $count = 0;								// total number of records
		public $startId = 0;							// starting record id
		public $endId = 0;								// ending record id
		public $size = 25;								// number of records per page
		public $numberPageLinks = 3;					// Max number of page link boxes to show
		public $baseURI;								// Base URI for lings
		public $divElemClass;							// CSS class for the div element
		public $linkElemClass = [];						// CSS class for the link element
		public $elemName = "pagerBar";					// Name of the pagination element
		public $elemId = "pagerBar";					// ID of the pagination element
		public $startElemName = "pagination_start_id";	// Name of the start id element
		public $sizeElemName = "pagination_size";		// Name of the size element
		private $_forwardParameters = array();			// Array of form parameters to forward

		/**
		 * Constructor
		 * @param mixed $uri Base URI
		 * @param mixed $size Number of records per page to display
		 * @param mixed $count Total number of records available
		 * @param mixed $startId Starting record id
		 * @return void
		 */
		public function __construct($uri = null, $size = null, $count = null, $startId = null) {
			$this->_tableName = 'site_pagination';
			$this->baseURI = $uri;
			if (empty($this->baseURI)) $this->baseURI = $_SERVER['SCRIPT_URI'];

			if ($startId > 0) $this->startId = $startId;
			else $this->startId = isset($_REQUEST[$this->startElemName]) && is_numeric($_REQUEST[$this->startElemName]) ? (int)$_REQUEST[$this->startElemName] : 0;

			if ($size > 0) $this->size = $size;
			else $this->size = isset($_REQUEST[$this->sizeElemName]) && is_numeric($_REQUEST[$this->sizeElemName]) ? (int)$_REQUEST[$this->sizeElemName] : 25;

			$this->endId = $this->startId + $this->size;
			if ($this->count < $this->endId) $this->endId = $this->count;
			$this->count = $count;
			if (!empty($size)) $this->size = $size;

			$this->divElemClass = array('pager_bar');
			$this->linkElemClass = array('pager');
		}

		/**
		 * Get/Set the name of the element to be used for the pagination start id
		 * @param mixed $name Name of Element
		 * @return string Name of Element
		 */
		public function startElemName($name = null) {
			if (isset($name)) $this->startElemName = $name;
			return $this->startElemName;
		}

		/**
		 * Get/Set the Number of records available
		 * @param mixed $count 
		 * @return int 
		 */
		public function count($count = null) {
			if (isset($count) && is_numeric($count)) {
				$this->count = $count;
				$this->endId = $this->startId + $this->size;
				if ($this->endId > $this->count) $this->endId = $this->count;
			}
			return $this->count;
		}

		/**
		 * Get/Set the number of Page Link Boxes to show
		 * @param mixed $numberPageLinks Number of boxes to show
		 * @return int Number of boxes to show
		 */
		public function numberPageLinks($numberPageLinks = null) {
			if (isset($numberPageLinks) && is_numeric($numberPageLinks)) $this->numberPageLinks = $numberPageLinks;
			return $this->numberPageLinks;
		}

		/**
		 * Get/Set the number of records to show on the page
		 * @param mixed $size Number of records to show
		 * @return int Number of records to show
		 */
		public function size($size = null) {
			if (isset($size) && is_numeric($size)) $this->size = $size;
			return $this->size;
		}

		/**
		 * Get/Set the starting record id
		 * @param null|int $startId First record id
		 * @return int First record id
		 */
		public function startId(?int $startId = null): int {
			if (isset($startId) && is_numeric($startId)) $this->startId = $startId;
			if ($this->startId < 0) $this->startId = 0;
			return $this->startId;
		}

		/**
		 * Get/Set the current page number based on the start id and records per page
		 * @param null|int $page Page number
		 * @return int Page number
		 */
		public function pageNumber($page = null) {
			if (isset($page)) $this->startId = ($page - 1) * $this->size;
			return ($this->startId / $this->size) + 1;
		}

		/**
		 * Provide the query string for the forward parameters
		 * @return string 
		 */
		private function paramString() {
			$string = '&'.$this->sizeElemName."=".$this->size;
			foreach ($this->_forwardParameters as $parameter) {
				if (empty($parameter[key($parameter)])) continue;
				$string .= "&" . key($parameter) . "=" . $parameter[key($parameter)];
			}
			return $string;
		}

		/**
		 * Get the total number of pages based on the total number of records and the number of records per page
		 * @return int Total number of pages
		 */
		private function totalPages() {
			return ceil($this->count / $this->size);
		}

		/**
		 * Get the link to the previous page
		 * @return string Link to the previous page
		 */
		public function prevPageLink() {
			return $this->baseURI . '?' . $this->startElemName. '=' . ($this->startId - $this->size) . $this->paramString();
		}

		/**
		 * Get the link to the next page
		 * @return string Link to the next page
		 */
		public function nextPageLink() {
			return $this->baseURI . '?' . $this->startElemName. '=' . ($this->startId + $this->size) . $this->paramString();
		}

		/**
		 * Get the link to the first page
		 * @return string Link to the first page
		 */
		public function firstPageLink() {
			return $this->baseURI . '?' . $this->startElemName. '=0' . $this->paramString();
		}

		/**
		 * Get the link to the last page
		 * @return string Link to the last page
		 */
		public function lastPageLink() {
			return $this->baseURI . '?' . $this->startElemName. '=' . ($this->count - $this->size) . $this->paramString();
		}

		/**
		 * Get the link to a specific page
		 * @param int $pageNumber Page number
		 * @return string Link to the page
		 */
		public function pageLink($pageNumber) {
			return $this->baseURI . '?' . $this->startElemName. '=' . (($pageNumber - 1) * $this->size) . $this->paramString();
		}

		/**
		 * Popuplate the forward parameters array
		 * @param mixed $parameters Parameters array
		 * @return void
		 */
		public function forwardParameters($parameters) {
			foreach ($parameters as $parameter) {
				$this->forwardParameter($parameter);
			}
		}

		/**
		 * Add an individual parameter to the forward parameters array
		 * @param mixed $name Name of parameter element
		 * @return void
		 */
		public function forwardParameter($name) {
			if (isset($_REQUEST[$name])) array_push($this->_forwardParameters,array($name => $_REQUEST[$name]));
		}

		/**
		 * Standard Pagination Bar Rendering (no boxes)
		 * @return string 
		 */
		public function render() {
			$string = "";
			if ($this->startId > 0) $string .= '<a id="paginationFirst" href="'.$this->firstPageLink().'" class="'.join("",$this->linkElemClass).'">&lt;&lt; First</a>';
			if ($this->startId >= $this->size) $string .= '<a id="paginationPrevious" href="'.$this->prevPageLink().'" class="'.join(" ",$this->linkElemClass)."\">&lt; Previous</a>";
			$string .= '<span id="paginationRange"> &nbsp;'.$this->startId.' - '.$this->endId.' of '.$this->count.' &nbsp; </span>';
			if ($this->startId <= $this->count - ($this->size * 2)) $string .= '<a id="paginationNext" href="'.$this->nextPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Next &gt;</a>";
			if ($this->startId < $this->count - $this->size) $string .= '<a id="paginationLast" href="'.$this->lastPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Last &gt;&gt;</a>";
			return $string;
		}

		/**
		 * Pagination Bar Rendering with page link boxes
		 * @return string
		 */
		public function renderPages() {
			print_r("Total Records: ".$this->count);
			print_r("Total Pages: ".$this->totalPages());
			if ($this->totalPages() > 1) {
				$string = '';
				$string .= "<ul>\n";
				if ($this->pageNumber() > 1) {
					$string .= "<li><a href=\"".$this->firstPageLink()."\">&laquo;&laquo; First</a></li>\n";
					$string .= "<li><a href=\"".$this->prevPageLink()."\">&laquo; Prev</a></li>\n";
				}

				$start = max(1, $this->pageNumber() - $this->numberPageLinks);
				$end = min($this->totalPages(), $this->pageNumber() + $this->numberPageLinks);

				// pad number of page links for small pages, there should be at least numberPageLinks doubled
				if ($end <= $this->numberPageLinks * 2) $end = $end + ($this->numberPageLinks)-$this->pageNumber() + 1;
				if ($this->numberPageLinks > $this->totalPages()) $end = $this->totalPages();

				for ($i = $start; $i <= $end; $i++) {
					$string .= "<li";
					if ($i == $this->pageNumber()) $string .= ' class="active"';
					$string .= "><a href=\"".$this->pageLink($i)."\">".$i."</a></li>\n";
				}
				if ($this->pageNumber() < $this->totalPages()) $string .= "<li><a href=\"".$this->nextPageLink()."\">Next &raquo;</a></li>\n";
				$string .= "</ul>\n";
				return $string;
			}
		}
	}
