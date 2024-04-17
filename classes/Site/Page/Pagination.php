<?php
	namespace Site\Page;

	class Pagination Extends \BaseModel {
		public $count = 0;
		public $startId = 0;
		public $endId = 0;
		public $size = 25;
        public $numberPageLinks = 3;
		public $direction;
		public $sort;
		public $baseURI;
		public $divElemClass;
		public $linkElemClass = [];
		public $elemName = "pagerBar";
		public $elemId = "pagerBar";
        private $_forwardParameters = array();

		public function __construct($uri = null,$size = 25,$count = null,$startId = null,$sort = null, $direction = null) {
			$this->baseURI = $uri;
            if (empty($this->baseURI)) $this->baseURI = $_SERVER['SCRIPT_URI'];

			if ($startId > 0) $this->startId = $startId;
            else $this->startId = isset($_REQUEST['pagination_start_id']) && is_numeric($_REQUEST['pagination_start_id']) ? (int)$_REQUEST['pagination_start_id'] : 0;

            $this->endId = $this->startId + $this->size;
			if ($this->count < $this->endId) $this->endId = $this->count;
			$this->count = $count;
			$this->size = $size;

            $this->sort = $sort;
            if (empty($this->sort)) $this->sort = isset($_REQUEST['pagination_sort']) ? $_REQUEST['pagination_sort'] : null;

			$this->direction = $direction;
            if (empty($this->direction)) $this->direction = isset($_REQUEST['pagination_direction']) ? $_REQUEST['pagination_direction'] : null;

			$this->divElemClass = array('pager_bar');
			$this->linkElemClass = array('pager');
		}

        public function count($count = null) {
            if (isset($count) && is_numeric($count)) {
                $this->count = $count;
                $this->endId = $this->startId + $this->size;
                if ($this->endId > $this->count) $this->endId = $this->count;
            }
            return $this->count;
        }

        public function numberPageLinks($numberPageLinks = null) {
            if (isset($numberPageLinks) && is_numeric($numberPageLinks)) $this->numberPageLinks = $numberPageLinks;
            return $this->numberPageLinks;
        }

        public function size($size = null) {
            if (isset($size) && is_numeric($size)) $this->size = $size;
            return $this->size;
        }

        public function startId($startId = null) {
            if (isset($startId) && is_numeric($startId)) $this->startId = $startId;
            if ($this->startId < 0) $this->startId = 0;
        }

        public function pageNumber($page = null) {
            if (isset($page)) $this->startId = ($page - 1) * $this->size;
            return ($this->startId / $this->size) + 1;
        }

        private function paramString() {
            $string = '';
            if (!empty($this->sort)) $string .= '&pagination_sort=' . $this->sort;
            if (!empty($this->direction)) $string .= '&pagination_direction=' . $this->direction;
            foreach ($this->_forwardParameters as $parameter) {
                $string .= "&" . key($parameter) . "=" . $parameter[key($parameter)];
            }
            return $string;
        }

        private function totalPages() {
            return ceil($this->count / $this->size);
        }
		public function prevPageLink() {
			return $this->baseURI . '?pagination_start_id=' . ($this->startId - $this->size) . $this->paramString();
		}

		public function nextPageLink() {
			return $this->baseURI . '?pagination_start_id=' . ($this->startId + $this->size) . $this->paramString();
		}

		public function firstPageLink() {
			return $this->baseURI . '?pagination_start_id=0' . $this->paramString();
		}

		public function lastPageLink() {
			return $this->baseURI . '?pagination_start_id=' . ($this->count - $this->size) . $this->paramString();
		}

		public function pageLink($pageNumber) {
			return $this->baseURI . '?pagination_start_id=' . (($pageNumber - 1) * $this->size) . $this->paramString();
		}

        public function forwardParameters($parameters) {
            foreach ($parameters as $parameter) {
                $this->forwardParameter($parameter);
            }
        }
        public function forwardParameter($name) {
            if (isset($_REQUEST[$name])) array_push($this->_forwardParameters,array($name => $_REQUEST[$name]));
        }

		public function render() {
            $string = "";
			if ($this->startId > 0) $string .= '<a id="paginationFirst" href="'.$this->firstPageLink().'" class="'.join("",$this->linkElemClass).'">&lt;&lt; First</a>';
			if ($this->startId >= $this->size) $string .= '<a id="paginationPrevious" href="'.$this->prevPageLink().'" class="'.join(" ",$this->linkElemClass)."\">&lt; Previous</a>";
			$string .= '<span id="paginationRange"> &nbsp;'.$this->startId.' - '.$this->endId.' of '.$this->count.' &nbsp; </span>';
			if ($this->startId <= $this->count - ($this->size * 2)) $string .= '<a id="paginationNext" href="'.$this->nextPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Next &gt;</a>";
			if ($this->startId < $this->count - $this->size) $string .= '<a id="paginationLast" href="'.$this->lastPageLink().'" class="'.join(" ",$this->linkElemClass)."\">Last &gt;&gt;</a>";
			return $string;
		}

        public function renderPages() {
            if ($this->totalPages() > 1) {
                $string = '';
                $string .= "<ul>\n";
                if ($this->pageNumber() > 1) {
                    $string .= "<li><a href=\"".$this->firstPageLink()."\">&laquo;&laquo; First</a></li>\n";
                    $string .= "<li><a href=\"".$this->prevPageLink()."\">&laquo; Prev</a></li>\n";
                }
                $start = max(1, $this->pageNumber() - $this->numberPageLinks);
                $end = min($this->totalPages(), $this->pageNumber() + $this->numberPageLinks);

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
