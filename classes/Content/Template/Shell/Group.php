<?php
	namespace Content\Template\Shell;

	class Group {
		public $_content;
		public $_lines = array();

		public function ($name = null, $content = null) {
			$this->_name = $name;
			$this->_content = $content;
		}

		public function name() {
			return $this->_name;
		}

		public function lines() {
			return $_lines;
		}

		public function addLine() {
			$line = new Line();
			$line->content($this->_content
			array_push($_lines,new Line());
			$line = end($_lines);
		}
	}
