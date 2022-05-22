<?php
	namespace Register;

	class PersonIcon {
		private $_error;
		private $_person_id;

		public function __construct($person_id = null) {
			if (!empty($person_id)) {
				$person = new \Register\Person($person_id);
				$this->_person_id = $person->id;
			}
			else {
				$this->_person_id = $GLOBALS['_SESSION_']->customer->id;
			}
			$this->details();
		}

		public function content($content = null) {
			if (!empty($content)) {
				$person->setMeta("icon_content",$content);
				$this->details();
			}
			return $this->_content;
		}

		public function image($image = null) {
			if (!empty($image)) {
				$person->setMeta("icon_image",$image);
				$this->details();
			}
			return $this->_image;
		}

		public function color($color = null) {
			if (!empty($color)) {
				$person->setMeta("icon_color",$color);
				$this->details();
			}
			return $this->_color;
		}

		public function details() {
			$person = new \Register\Person($this->_person_id);
			if (! $person->id) return false;

			$this->_color = $person->metadata("icon_color");
			$this->_image = $person->metadata("icon_image");
			$this->_content = $person->metadata("icon_content");

			if (! $this->_content) {
				$this->_content = $person->initials();
			}

			return true;
		}
	}
?>
