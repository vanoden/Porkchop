<?php
	namespace Content\Template\Shell;

	class Group {
		public $_content;
		public $_lines = array();

		public function __construct() {
		}

		public function content($string) {
			$this->_content = $string;
		}

		public function lines() {
			return $this->_lines;
		}

		public function addLine() {
			$line = new Line();
			$line->content($this->_content);
			array_push($this->_lines,$line);
			return $line;
		}
	}
