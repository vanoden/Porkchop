<?php
	namespace Site\Page;

	class Pagination Extends \BaseModel {
		public $count = 0;
		public $startId = 0;
		public $endId = 0;
		public $size = 20;
		public $direction = 'asc';
		public $search = '';
		public $parameters;
		public $sort;
		public $baseURI;
		public $divElemClass;
		public $linkElemClass;
		public $elemName = "pagerBar";
		public $elemId = "pagerBar";

		public function __construct($count,$uri = '') {
			if (!empty($uri)) $this->baseURI = $uri;
			else $this->baseURI = $_SERVER['REQUEST_URI'];
			$this->startId = $_REQUEST['start'];
			$this->endId = $this->startId + $this->size;
			if ($this->count < $this->endId) $this->endId = $this->count;
			$this->count = $count;
			if (!empty($_REQUEST['size'])) $this->size = $_REQUEST['size'];
			$this->sort = $_REQUEST['sort'];
			$this->direction = $_REQUEST['direction'];
			$this->search = $_REQUEST['search'];
			$this->divElemClass = array('pager_bar');
			$this->linkElemClass = array('pager');
		}

		public function prevPageLink() {
			return $this->baseURI . '?start=' . ($this->startId - $this->size) . '&size=' . $this->size . '&sort=' . $this->sort . '&direction=' . $this->direction . '&search=' . $this->search;
		}

		public function nextPageLink() {
			return $this->baseURI . '?start=' . ($this->startId + $this->size) . '&size=' . $this->size . '&sort=' . $this->sort . '&direction=' . $this->direction . '&search=' . $this->search;
		}

		public function firstPageLink() {
			return $this->baseURI . '?start=0&size=' . $this->size . '&sort=' . $this->sort . '&direction=' . $this->direction . '&search=' . $this->search;
		}

		public function lastPageLink() {
			return $this->baseURI . '?start=' . ($this->count - $this->size) . '&size=' . $this->size . '&sort=' . $this->sort . '&direction=' . $this->direction . '&search=' . $this->search;
		}

		public function formContent() {
			$string  = '<div id="'.$this->elemId.'" name="'.$this->elemName.'" class="'.join(" ",$this->linkElemClass).'">"';
			$string .= '<a href="'.$this->firstPageLink().'" class="'.join("",$this->linkElemClass).'">&lt;&lt; First</a>';
			$string .= '<a href="'.$this->prevPageLink().'" class="'.join(" ",$this->linkElemClass)."\">&lt; Previous</a>";
			$string .= ' &nbsp;'.$this->startId.' - '.$this->endId.' of '.$this->count.' &nbsp; ';
			$string .= '<a href="'.$this->nextPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Next &gt;</a>";
			$string .= '<a href="'.$this->lastPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Last &gt;&gt;</a>";
			return $string;
		}
	}
